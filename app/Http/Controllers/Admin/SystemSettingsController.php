<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\EnvWriter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    private function persist(Request $request, string $group): void
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => (string) $key],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'group' => $group,
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }
        Cache::forget('app_settings');
    }

    public function system(): View
    {
        return view('admin.settings.system', ['settings' => Setting::getGroup('system')]);
    }

    public function saveSystem(Request $request): RedirectResponse
    {
        $this->persist($request, 'system');

        return back()->with('ok', 'System settings saved.');
    }

    public function order(): View
    {
        return view('admin.settings.order', ['settings' => Setting::getGroup('order')]);
    }

    public function saveOrder(Request $request): RedirectResponse
    {
        $this->persist($request, 'order');

        return back()->with('ok', 'Order settings saved.');
    }

    public function tax(): View
    {
        return view('admin.settings.tax', ['settings' => Setting::getGroup('tax')]);
    }

    public function saveTax(Request $request): RedirectResponse
    {
        $this->persist($request, 'tax');

        return back()->with('ok', 'Tax settings saved.');
    }

    public function customer(): View
    {
        return view('admin.settings.customer', ['settings' => Setting::getGroup('customer')]);
    }

    public function saveCustomer(Request $request): RedirectResponse
    {
        $this->persist($request, 'customer');

        return back()->with('ok', 'Customer settings saved.');
    }

    public function seller(): View
    {
        return view('admin.settings.seller', ['settings' => Setting::getGroup('seller')]);
    }

    public function saveSeller(Request $request): RedirectResponse
    {
        $this->persist($request, 'seller');

        return back()->with('ok', 'Seller settings saved.');
    }

    public function units(): View
    {
        return view('admin.settings.units', ['settings' => Setting::getGroup('units')]);
    }

    public function saveUnits(Request $request): RedirectResponse
    {
        $this->persist($request, 'units');

        return back()->with('ok', 'Unit settings saved.');
    }

    public function cookie(): View
    {
        return view('admin.settings.cookie', ['settings' => Setting::getGroup('cookie')]);
    }

    public function saveCookie(Request $request): RedirectResponse
    {
        $this->persist($request, 'cookie');

        return back()->with('ok', 'Cookie settings saved.');
    }

    public function maintenance(): View
    {
        return view('admin.settings.maintenance', ['settings' => Setting::getGroup('maintenance')]);
    }

    public function saveMaintenance(Request $request): RedirectResponse
    {
        $this->persist($request, 'maintenance');

        return back()->with('ok', 'Maintenance settings saved.');
    }

    public function backup(): View
    {
        return view('admin.settings.backup', ['settings' => Setting::getGroup('backup')]);
    }

    public function saveBackup(Request $request): RedirectResponse
    {
        $this->persist($request, 'backup');

        return back()->with('ok', 'Backup settings saved.');
    }

    public function runBackup(Request $request): RedirectResponse
    {
        $path = storage_path('app/backups');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $ok = false;
        $msg = 'Automatic backup is not available on this server. Use mysqldump or export from phpMyAdmin.';
        if (function_exists('exec') && ! in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            try {
                $file = $path.'/backup-'.now()->format('Y-m-d-His').'.sql';
                $db = config('database.connections.'.config('database.default'));
                if (($db['driver'] ?? '') === 'mysql') {
                    $cmd = sprintf(
                        'mysqldump --user=%s --password=%s --host=%s %s > %s',
                        escapeshellarg((string) ($db['username'] ?? '')),
                        escapeshellarg((string) ($db['password'] ?? '')),
                        escapeshellarg((string) ($db['host'] ?? '127.0.0.1')),
                        escapeshellarg((string) ($db['database'] ?? '')),
                        escapeshellarg($file)
                    );
                    exec($cmd.' 2>&1', $out, $code);
                    $ok = $code === 0 && is_file($file);
                    $msg = $ok ? 'Backup file created.' : 'Backup failed: '.implode("\n", $out);
                }
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
            }
        }

        return back()->with($ok ? 'ok' : 'error', $msg);
    }

    public function socialLogin(): View
    {
        return view('admin.settings.social-auth', ['settings' => Setting::getGroup('social_auth')]);
    }

    public function saveSocialLogin(Request $request): RedirectResponse
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => (string) $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value, 'group' => 'social_auth', 'type' => 'string', 'autoload' => true]
            );
        }
        EnvWriter::write([
            'GOOGLE_CLIENT_ID' => (string) $request->input('google_client_id', ''),
            'GOOGLE_CLIENT_SECRET' => (string) $request->input('google_client_secret', ''),
            'FACEBOOK_APP_ID' => (string) $request->input('facebook_app_id', ''),
            'FACEBOOK_APP_SECRET' => (string) $request->input('facebook_app_secret', ''),
        ]);
        Artisan::call('config:clear');
        Cache::forget('app_settings');

        return back()->with('ok', 'Social auth settings saved.');
    }

    public function promotions(): View
    {
        return view('admin.settings.promotions', ['settings' => Setting::getGroup('promotions')]);
    }

    public function savePromotions(Request $request): RedirectResponse
    {
        $this->persist($request, 'promotions');
        return back()->with('ok', 'Promotion settings saved.');
    }

    public function pos(): View
    {
        return view('admin.settings.pos', ['settings' => Setting::getGroup('pos')]);
    }

    public function savePos(Request $request): RedirectResponse
    {
        $this->persist($request, 'pos');
        return back()->with('ok', 'POS settings saved.');
    }
}
