<?php

namespace App\Services;

use App\Models\License;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseService
{
    private string $serverUrl;
    private string $apiSecret;

    public function __construct()
    {
        $this->serverUrl = (string) config('shop.license_server');
        $this->apiSecret = (string) config('shop.license_api_secret', env('LICENSE_API_SECRET', ''));
    }

    public function activate(string $key, string $domain): array
    {
        $response = $this->callApi('/api/license/activate', [
            'license_key' => $key,
            'domain' => $domain,
            'app_version' => (string) config('shop.version', '1.0.0'),
            'product_slug' => (string) config('shop.product_slug', 'cibato-commerce'),
        ]);

        if (($response['valid'] ?? false) === true) {
            License::query()->updateOrCreate(['id' => 1], [
                'license_key' => $key,
                'domain' => $domain,
                'plan' => $response['plan'] ?? null,
                'expires_at' => $response['update_expires_at'] ?? null,
                'last_verified_at' => now(),
                'status' => 'active',
                'response_cache' => $response,
            ]);
        }

        return $response;
    }

    public function ping(): array
    {
        $license = License::query()->first();
        if (! $license || ! $license->license_key || ! $license->domain) {
            return ['status' => 'inactive', 'message' => 'License not configured'];
        }

        $response = $this->callApi('/api/license/ping', [
            'license_key' => $license->license_key,
            'domain' => $license->domain,
            'app_version' => (string) config('shop.version', '1.0.0'),
        ]);

        $license->update([
            'last_verified_at' => now(),
            'status' => ($response['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
            'response_cache' => $response,
        ]);

        return $response;
    }

    public function deactivate(): bool
    {
        $license = License::query()->first();
        if (! $license || ! $license->license_key || ! $license->domain) {
            return false;
        }

        $response = $this->callApi('/api/license/deactivate', [
            'license_key' => $license->license_key,
            'domain' => $license->domain,
        ]);

        return (bool) ($response['success'] ?? false);
    }

    public function isValid(): bool
    {
        $cached = Cache::get('license_status');
        if (is_array($cached)) {
            return (($cached['status'] ?? '') === 'active') || (($cached['valid'] ?? false) === true);
        }

        $license = License::query()->first();
        return (string) ($license?->status ?? 'inactive') === 'active';
    }

    public function getPlan(): string
    {
        $cached = Cache::get('license_status');
        return (string) ($cached['plan'] ?? License::query()->value('plan') ?? 'free');
    }

    public function getActiveAddons(): array
    {
        $cached = Cache::get('license_status');
        return is_array($cached['addons'] ?? null) ? $cached['addons'] : [];
    }

    public function hasAddon(string $slug): bool
    {
        return in_array($slug, $this->getActiveAddons(), true);
    }

    public function checkForUpdate(): array
    {
        $license = License::query()->first();
        if (! $license || ! $license->license_key || ! $license->domain) {
            return ['has_update' => false, 'message' => 'License unavailable'];
        }

        return $this->callApi('/api/update/check', [
            'license_key' => $license->license_key,
            'domain' => $license->domain,
            'current_version' => (string) config('shop.version', '1.0.0'),
            'product_slug' => (string) config('shop.product_slug', 'cibato-commerce'),
        ], 'GET');
    }

    private function callApi(string $endpoint, array $data, string $method = 'POST'): array
    {
        $url = rtrim($this->serverUrl, '/').$endpoint;

        try {
            $request = Http::withoutVerifying()->timeout(10)->withHeaders([
                'X-API-Secret' => $this->apiSecret,
                'X-Domain' => request()?->getHost() ?? EnvWriter::get('APP_URL') ?? '',
                'X-Version' => (string) config('shop.version', '1.0.0'),
            ]);

            $response = strtoupper($method) === 'GET' ? $request->get($url, $data) : $request->post($url, $data);

            if ($response->successful()) {
                $payload = $response->json();
                $signature = (string) ($response->header('X-Signature')[0] ?? ($payload['signature'] ?? ''));
                if (! $this->verifySignature($payload, $signature)) {
                    return ['success' => false, 'message' => 'Response signature verification failed'];
                }

                Cache::put('license_status', $payload, now()->addDay());
                return $payload;
            }
        } catch (\Throwable) {
        }

        return Cache::get('license_status', ['success' => false, 'message' => 'License server unavailable']);
    }

    private function verifySignature(array $response, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        $copy = $response;
        unset($copy['signature']);
        $expected = hash_hmac('sha256', json_encode($copy) ?: '', (string) config('shop.license_api_secret', env('LICENSE_API_SECRET', '')));

        return hash_equals($expected, $signature);
    }
}
