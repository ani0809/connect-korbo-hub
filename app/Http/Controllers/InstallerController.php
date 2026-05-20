<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use App\Services\EnvWriter;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class InstallerController extends Controller
{
    public function welcome(): RedirectResponse|\Illuminate\View\View
    {
        if (file_exists(storage_path('installed.lock'))) {
            return redirect('/admin');
        }

        return view('installer.welcome', ['step' => 1, 'software' => config('shop.name'), 'version' => config('shop.version')]);
    }

    public function requirements(): \Illuminate\View\View
    {
        $checks = [
            ['label' => 'PHP >= 8.2', 'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')],
            ['label' => 'BCMath', 'ok' => extension_loaded('bcmath')],
            ['label' => 'Ctype', 'ok' => extension_loaded('ctype')],
            ['label' => 'JSON', 'ok' => extension_loaded('json')],
            ['label' => 'Mbstring', 'ok' => extension_loaded('mbstring')],
            ['label' => 'OpenSSL', 'ok' => extension_loaded('openssl')],
            ['label' => 'PDO', 'ok' => extension_loaded('pdo')],
            ['label' => 'PDO_MySQL', 'ok' => extension_loaded('pdo_mysql')],
            ['label' => 'Tokenizer', 'ok' => extension_loaded('tokenizer')],
            ['label' => 'XML', 'ok' => extension_loaded('xml')],
            ['label' => 'Zip', 'ok' => class_exists(\ZipArchive::class)],
            ['label' => 'GD or Imagick', 'ok' => extension_loaded('gd') || extension_loaded('imagick')],
            ['label' => 'cURL', 'ok' => extension_loaded('curl')],
        ];

        $warnings = [
            ['label' => 'proc_open', 'ok' => function_exists('proc_open')],
            ['label' => 'symlink', 'ok' => function_exists('symlink')],
            ['label' => 'file_uploads enabled', 'ok' => (bool) ini_get('file_uploads')],
            ['label' => 'max_execution_time >= 60', 'ok' => ((int) ini_get('max_execution_time')) >= 60 || ((int) ini_get('max_execution_time')) === 0],
        ];

        $criticalFail = collect($checks)->contains(fn ($c) => ! $c['ok']);

        return view('installer.requirements', ['step' => 2, 'checks' => $checks, 'warnings' => $warnings, 'criticalFail' => $criticalFail]);
    }

    public function permissions(Request $request): \Illuminate\View\View
    {
        $paths = ['storage', 'bootstrap/cache', 'public/uploads', '.env'];
        if ($request->boolean('autofix')) {
            foreach ($paths as $path) {
                @chmod(base_path($path), 0775);
            }
        }

        $rows = [];
        foreach ($paths as $path) {
            $full = base_path($path);
            $rows[] = [
                'path' => $path,
                'exists' => file_exists($full),
                'writable' => is_writable($full),
                'perm' => file_exists($full) ? substr(sprintf('%o', fileperms($full)), -4) : '----',
            ];
        }

        return view('installer.permissions', ['step' => 3, 'rows' => $rows]);
    }

    public function database(): \Illuminate\View\View
    {
        return view('installer.database', ['step' => 4]);
    }

    public function saveDatabase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'prefix' => 'nullable|string',
        ]);

        try {
            new \PDO("mysql:host={$data['host']};port={$data['port']};dbname={$data['database']};charset=utf8mb4", $data['username'], $data['password'] ?? '');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Database connection failed: '.$e->getMessage(), 'data' => []], 422);
        }

        EnvWriter::write([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['host'],
            'DB_PORT' => (string) $data['port'],
            'DB_DATABASE' => $data['database'],
            'DB_USERNAME' => $data['username'],
            'DB_PASSWORD' => (string) ($data['password'] ?? ''),
            'DB_PREFIX' => (string) ($data['prefix'] ?? ''),
        ]);

        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'Database saved successfully', 'data' => []]);
    }

    public function migration(): \Illuminate\View\View
    {
        return view('installer.migration', ['step' => 5]);
    }

    public function runMigration(): JsonResponse
    {
        try {
            // Project migrations are not strictly ordered by FK dependencies.
            // Disable FK checks during install migration for maximum compatibility.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = Artisan::output();
            Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
            $seedOutput = Artisan::output();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return response()->json(['success' => true, 'message' => 'Migration and seeding completed', 'data' => ['migrate' => $migrateOutput, 'seed' => $seedOutput]]);
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable) {
            }
            return response()->json(['success' => false, 'message' => 'Migration failed: '.$e->getMessage(), 'data' => []], 500);
        }
    }

    public function setup(): \Illuminate\View\View
    {
        return view('installer.setup', ['step' => 6, 'timezones' => timezone_identifiers_list()]);
    }

    public function saveSetup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_name' => 'required|string|max:191',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'currency' => 'required|string',
            'timezone' => 'required|string',
            'site_logo' => 'nullable|image',
        ]);

        $admin = User::query()->create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => 'admin',
            'status' => 'active',
        ]);

        Setting::setMany([
            'site_name' => $data['site_name'],
            'currency' => $data['currency'],
            'timezone' => $data['timezone'],
        ]);

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('uploads/settings', 'public');
            Setting::set('site_logo', $path);
        }

        EnvWriter::write(['APP_NAME' => $data['site_name'], 'APP_TIMEZONE' => $data['timezone']]);

        return response()->json(['success' => true, 'message' => 'Setup saved', 'data' => ['admin_id' => $admin->id]]);
    }

    public function license(): \Illuminate\View\View
    {
        return view('installer.license', ['step' => 7, 'domain' => request()->getHost()]);
    }

    public function activateLicense(Request $request, LicenseService $service): JsonResponse
    {
        $skip = $request->boolean('skip');
        if ($skip) {
            License::query()->updateOrCreate(['id' => 1], ['status' => 'inactive']);
            return response()->json(['success' => true, 'message' => 'License skipped for now', 'data' => []]);
        }

        $data = $request->validate(['license_key' => 'required|string']);
        $domain = request()->getHost();
        $response = $service->activate($data['license_key'], $domain);

        if (($response['valid'] ?? false) !== true) {
            return response()->json(['success' => false, 'message' => (string) ($response['message'] ?? 'License activation failed'), 'data' => $response], 422);
        }

        EnvWriter::write(['LICENSE_KEY' => $data['license_key']]);
        return response()->json(['success' => true, 'message' => 'License activated', 'data' => $response]);
    }

    public function demo(): \Illuminate\View\View
    {
        return view('installer.demo', ['step' => 8]);
    }

    public function importDemo(Request $request): JsonResponse
    {
        $data = $request->validate(['demo' => 'required|string']);

        if ($data['demo'] !== 'fresh') {
            Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
        }

        if (! is_dir(public_path('uploads'))) {
            @mkdir(public_path('uploads'), 0775, true);
        }

        return response()->json(['success' => true, 'message' => 'Demo import complete', 'data' => ['output' => Artisan::output()]]);
    }

    public function finish(): \Illuminate\View\View
    {
        if (! file_exists(storage_path('installed.lock'))) {
            file_put_contents(storage_path('installed.lock'), now()->toDateTimeString());
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable) {
        }

        try {
            Artisan::call('optimize');
        } catch (\Throwable) {
        }

        return view('installer.finish', ['step' => 9, 'admin_email' => User::query()->where('role', 'admin')->value('email')]);
    }
}
