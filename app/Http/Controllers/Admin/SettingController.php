<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function general() { return view('admin.settings.general', ['settings' => Setting::getGroup('general')]); }
    public function saveGeneral(Request $request): RedirectResponse
    {
        $payload = $request->except('_token', 'site_logo', 'favicon');

        foreach (['site_logo', 'favicon'] as $fileKey) {
            if (! $request->hasFile($fileKey)) {
                continue;
            }

            $oldPath = (string) setting($fileKey, '');
            $path = $request->file($fileKey)->store('uploads/settings', 'public');
            $payload[$fileKey] = $path;

            if ($oldPath !== '' && $oldPath !== $path && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        Setting::setMany($payload);

        return back()->with('ok', 'General settings saved.');
    }
    public function business() { return view('admin.settings.business', ['settings' => Setting::getGroup('shop')]); }
    public function saveBusiness(Request $request): RedirectResponse { Setting::setMany($request->except('_token')); return back()->with('ok','Business settings saved.'); }
    public function smtp() { return view('admin.settings.smtp', ['settings' => Setting::getGroup('smtp')]); }
    public function saveSmtp(Request $request): RedirectResponse { Setting::setMany($request->except('_token')); return back()->with('ok','SMTP saved.'); }
    public function testSmtp(Request $request): JsonResponse { try { Mail::raw('SMTP test', fn ($m) => $m->to($request->string('email'))->subject('SMTP Test')); return response()->json(['success'=>true,'message'=>'Test mail sent','data'=>[]]); } catch (\Throwable $e) { return response()->json(['success'=>false,'message'=>'SMTP failed: '.$e->getMessage(),'data'=>[]],422);} }
    public function socialLogin() { return view('admin.settings.social-login', ['settings' => Setting::getGroup('social_login')]); }
    public function saveSocialLogin(Request $request): RedirectResponse { Setting::setMany($request->except('_token')); return back()->with('ok','Social login saved.'); }
    public function seo() { return view('admin.settings.seo', ['settings' => Setting::getGroup('seo')]); }
    public function saveSeo(Request $request): RedirectResponse { Setting::setMany($request->except('_token')); return back()->with('ok','SEO saved.'); }
    public function license() { return view('admin.settings.license', ['license' => \App\Models\License::query()->first()]); }
    public function reactivateLicense(LicenseService $licenseService): RedirectResponse { $license = \App\Models\License::query()->first(); if ($license && $license->license_key && $license->domain) { $licenseService->activate($license->license_key, $license->domain); Cache::forget('license_status'); } return back()->with('ok', 'License re-validated.'); }

    public function reviews()
    {
        return view('admin.settings.reviews', ['settings' => Setting::getGroup('reviews')]);
    }

    public function saveReviews(Request $request): RedirectResponse
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => (string) $key],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'group' => 'reviews',
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }
        Cache::forget('app_settings');

        return back()->with('ok', 'Review & Q&A settings saved.');
    }

    public function points()
    {
        return view('admin.settings.points', ['settings' => Setting::getGroup('points')]);
    }

    public function savePoints(Request $request): RedirectResponse
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => (string) $key],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'group' => 'points',
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }
        Cache::forget('app_settings');

        return back()->with('ok', 'Points settings saved.');
    }

    public function whatsapp()
    {
        return view('admin.settings.whatsapp', ['settings' => Setting::getGroup('whatsapp')]);
    }

    public function saveWhatsapp(Request $request): RedirectResponse
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => (string) $key],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'group' => 'whatsapp',
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }
        Cache::forget('app_settings');

        return back()->with('ok', 'WhatsApp settings saved.');
    }

    public function testWhatsappApi(Request $request): JsonResponse
    {
        $token = setting('whatsapp_business_token');
        $phoneId = setting('whatsapp_business_phone_id');
        if (! $token || ! $phoneId) {
            return response()->json(['success' => false, 'message' => 'Token or Phone ID missing'], 422);
        }

        try {
            $res = Http::withToken($token)->get("https://graph.facebook.com/v18.0/{$phoneId}");

            return response()->json([
                'success' => $res->successful(),
                'message' => $res->successful() ? 'Connection OK' : 'API error: '.$res->body(),
            ], $res->successful() ? 200 : 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
