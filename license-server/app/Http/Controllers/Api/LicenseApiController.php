<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddonLicense;
use App\Models\ApiLog;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Product;
use App\Models\UpdateRelease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseApiController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string','app_version'=>'nullable|string','product_slug'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = $this->loadLicense($data['license_key'], $data['product_slug']);

        if (! $license || $license->status !== 'active') {
            return $this->signed(['valid'=>false,'message'=>'License not found or inactive'], 422, $request, $data, $domain);
        }

        $activeCount = $license->activations()->where('is_active', true)->count();
        $already = $license->activations()->where('domain', $domain)->where('is_active', true)->first();

        if (! $already && $activeCount >= $license->max_domains) {
            return $this->signed(['valid'=>false,'message'=>'Maximum domain activation limit reached'], 422, $request, $data, $domain);
        }

        return $this->signed($this->licensePayload($license, $domain, true, 'License verified successfully'), 200, $request, $data, $domain);
    }

    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string','app_version'=>'nullable|string','product_slug'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = $this->loadLicense($data['license_key'], $data['product_slug']);

        if (! $license || $license->status !== 'active') {
            return $this->signed(['valid'=>false,'message'=>'License invalid or suspended'], 422, $request, $data, $domain);
        }

        $taken = LicenseActivation::query()->where('domain', $domain)->where('is_active', true)->where('license_id', '!=', $license->id)->exists();
        if ($taken) {
            return $this->signed(['valid'=>false,'message'=>'Domain is already activated by another license'], 422, $request, $data, $domain);
        }

        $activation = $license->activations()->firstOrNew(['domain' => $domain]);
        if (! $activation->exists && $license->activations()->where('is_active', true)->count() >= $license->max_domains) {
            return $this->signed(['valid'=>false,'message'=>'Activation limit reached'], 422, $request, $data, $domain);
        }

        $activation->fill(['ip_address'=>$request->ip(),'app_version'=>$data['app_version'] ?? null,'last_ping_at'=>now(),'is_active'=>true,'deactivated_at'=>null]);
        $activation->save();

        return $this->signed($this->licensePayload($license, $domain, true, 'License activated successfully'), 200, $request, $data, $domain);
    }

    public function deactivate(Request $request): JsonResponse
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = License::query()->where('license_key', $data['license_key'])->first();

        if (! $license) {
            return $this->signed(['success'=>false,'message'=>'License not found'], 404, $request, $data, $domain);
        }

        $activation = $license->activations()->where('domain', $domain)->where('is_active', true)->first();
        if (! $activation) {
            return $this->signed(['success'=>false,'message'=>'Activation not found'], 404, $request, $data, $domain);
        }

        $activation->update(['is_active'=>false,'deactivated_at'=>now()]);
        return $this->signed(['success'=>true,'message'=>'License deactivated'], 200, $request, $data, $domain);
    }

    public function ping(Request $request): JsonResponse
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string','app_version'=>'nullable|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = License::query()->where('license_key', $data['license_key'])->with(['plan','addonLicenses'])->first();

        $activation = $license?->activations()->where('domain', $domain)->where('is_active', true)->first();
        if (! $license || ! $activation) {
            return $this->signed(['status'=>'inactive','message'=>'Activation not found'], 404, $request, $data, $domain);
        }

        $activation->update(['last_ping_at'=>now(),'app_version'=>$data['app_version'] ?? $activation->app_version]);
        $latest = UpdateRelease::query()->where('product_id', $license->product_id)->where('is_published', true)->latest('id')->first();

        return $this->signed([
            'status' => $license->status,
            'plan' => $license->plan?->slug,
            'addons' => $license->addonLicenses()->where('status','active')->pluck('addon_slug')->values()->all(),
            'latest_version' => $latest?->version,
            'has_update' => $latest ? version_compare((string) ($data['app_version'] ?? '0.0.0'), $latest->version, '<') : false,
        ], 200, $request, $data, $domain);
    }

    public function info(Request $request): JsonResponse
    {
        return $this->verify($request);
    }

    public function addonVerify(Request $request): JsonResponse
    {
        $data = $request->validate(['addon_key'=>'required|string','domain'=>'required|string','addon_slug'=>'required|string','license_key'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = License::query()->where('license_key', $data['license_key'])->first();
        if (! $license || ! $license->activations()->where('domain', $domain)->where('is_active', true)->exists()) {
            return $this->signed(['valid'=>false,'message'=>'Main license is not active for this domain'], 422, $request, $data, $domain);
        }

        $addon = AddonLicense::query()->where('addon_license_key', $data['addon_key'])->where('parent_license_id', $license->id)->first();
        if (! $addon || $addon->addon_slug !== $data['addon_slug'] || $addon->status !== 'active') {
            return $this->signed(['valid'=>false,'message'=>'Addon license invalid'], 422, $request, $data, $domain);
        }

        if ($addon->expires_at && now()->gt($addon->expires_at) && ! $addon->is_lifetime) {
            return $this->signed(['valid'=>false,'message'=>'Addon license expired'], 422, $request, $data, $domain);
        }

        if (! $addon->domain) { $addon->update(['domain' => $domain]); }

        return $this->signed(['valid'=>true,'addon'=>$addon->addon_slug,'expires_at'=>$addon->expires_at?->toDateString(),'is_lifetime'=>$addon->is_lifetime], 200, $request, $data, $domain);
    }

    public function checkUpdate(Request $request): JsonResponse
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string','current_version'=>'required|string','product_slug'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = $this->loadLicense($data['license_key'], $data['product_slug']);
        if (! $license || ! $license->activations()->where('domain', $domain)->where('is_active', true)->exists()) {
            return $this->signed(['has_update'=>false,'can_update'=>false,'reason'=>'License/domain mismatch'], 422, $request, $data, $domain);
        }

        $release = UpdateRelease::query()->where('product_id', $license->product_id)->where('is_published', true)->latest('id')->first();
        if (! $release) {
            return $this->signed(['has_update'=>false,'can_update'=>false,'reason'=>'No update release published'], 200, $request, $data, $domain);
        }

        $can = $license->is_lifetime || ! $license->update_expires_at || now()->lte($license->update_expires_at);
        $has = version_compare($data['current_version'], $release->version, '<');

        return $this->signed(['has_update'=>$has,'latest_version'=>$release->version,'current_version'=>$data['current_version'],'changelog'=>$release->changelog,'can_update'=>$can,'reason'=>$can ? null : 'Update period expired'], 200, $request, $data, $domain);
    }

    public function downloadUpdate(Request $request, string $version)
    {
        $data = $request->validate(['license_key'=>'required|string','domain'=>'required|string']);
        $domain = $this->normalizeDomain($data['domain']);
        $license = License::query()->where('license_key', $data['license_key'])->first();
        if (! $license || ! $license->activations()->where('domain', $domain)->where('is_active', true)->exists()) {
            return response()->json(['success'=>false,'message'=>'License/domain invalid'], 422);
        }

        if (! ($license->is_lifetime || ! $license->update_expires_at || now()->lte($license->update_expires_at))) {
            return response()->json(['success'=>false,'message'=>'Update access expired'], 422);
        }

        $release = UpdateRelease::query()->where('product_id', $license->product_id)->where('version', $version)->where('is_published', true)->first();
        if (! $release || ! file_exists($release->zip_path)) {
            return response()->json(['success'=>false,'message'=>'Release not found'], 404);
        }

        $release->increment('download_count');
        $this->log('/api/update/download/'.$version, $request, ['success'=>true], 200, $data['license_key'], $domain);

        return response()->download($release->zip_path);
    }

    private function loadLicense(string $key, string $productSlug): ?License
    {
        return License::query()->where('license_key', $key)->whereHas('product', fn ($q) => $q->where('slug', $productSlug))->with(['plan','addonLicenses','product'])->first();
    }

    private function normalizeDomain(string $input): string
    {
        $domain = trim(strtolower($input));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = preg_replace('#^www\.#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');
        $host = parse_url('http://'.$domain, PHP_URL_HOST) ?: $domain;
        if (app()->environment('production') && in_array($host, ['localhost','127.0.0.1'], true)) {
            abort(422, 'Localhost domains are not allowed in production.');
        }
        return $host;
    }

    private function licensePayload(License $license, string $domain, bool $valid, string $message): array
    {
        $latest = UpdateRelease::query()->where('product_id', $license->product_id)->where('is_published', true)->latest('id')->first();

        return [
            'valid' => $valid,
            'plan' => $license->plan?->slug,
            'buyer_name' => $license->buyer_name,
            'buyer_email' => $license->buyer_email,
            'domain' => $domain,
            'support_expires_at' => $license->support_expires_at?->toDateString(),
            'update_expires_at' => $license->update_expires_at?->toDateString(),
            'is_lifetime' => $license->is_lifetime,
            'addons' => $license->addonLicenses()->where('status', 'active')->pluck('addon_slug')->values()->all(),
            'latest_version' => $latest?->version,
            'message' => $message,
        ];
    }

    private function signed(array $payload, int $status, Request $request, array $reqData, ?string $domain): JsonResponse
    {
        $signature = hash_hmac('sha256', json_encode($payload) ?: '', (string) env('LICENSE_API_SECRET', 'change-me'));
        $payload['signature'] = $signature;
        $this->log($request->path(), $request, $payload, $status, $reqData['license_key'] ?? null, $domain);
        return response()->json($payload, $status)->header('X-Signature', $signature);
    }

    private function log(string $endpoint, Request $request, array $response, int $status, ?string $key, ?string $domain): void
    {
        ApiLog::query()->create([
            'endpoint' => $endpoint,
            'license_key' => $key,
            'domain' => $domain,
            'request_data' => $request->all(),
            'response_data' => $response,
            'ip_address' => $request->ip(),
            'response_code' => $status,
            'created_at' => now(),
        ]);
    }
}
