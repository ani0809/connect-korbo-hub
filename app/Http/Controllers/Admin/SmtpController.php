<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Services\EnvWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class SmtpController extends Controller
{
    public function index()
    {
        $settings = [
            'mail_driver' => setting('mail_driver', 'smtp'),
            'mail_host' => setting('mail_host'),
            'mail_port' => setting('mail_port', '587'),
            'mail_username' => setting('mail_username'),
            'mail_password' => setting('mail_password'),
            'mail_encryption' => setting('mail_encryption', 'tls'),
            'mail_from_address' => setting('mail_from_address'),
            'mail_from_name' => setting('mail_from_name', setting('site_name')),
        ];
        return view('admin.settings.smtp', compact('settings'));
    }

    public function save(Request $request, EnvWriter $writer)
    {
        Setting::setMany([
            'mail_driver' => $request->mail_driver,
            'mail_host' => $request->mail_host,
            'mail_port' => $request->mail_port,
            'mail_username' => $request->mail_username,
            'mail_password' => $request->mail_password,
            'mail_encryption' => $request->mail_encryption,
            'mail_from_address' => $request->mail_from_address,
            'mail_from_name' => $request->mail_from_name,
        ]);

        $writer->write([
            'MAIL_MAILER' => $request->mail_driver,
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_ENCRYPTION' => $request->mail_encryption,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => '"'.$request->mail_from_name.'"',
        ]);

        Artisan::call('config:clear');
        return back()->with('success', 'SMTP settings saved.');
    }

    public function test(Request $request)
    {
        $testTo = $request->input('test_email', auth()->user()->email);
        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $request->mail_host,
                'mail.mailers.smtp.port' => $request->mail_port,
                'mail.mailers.smtp.username' => $request->mail_username,
                'mail.mailers.smtp.password' => $request->mail_password,
                'mail.mailers.smtp.encryption' => $request->mail_encryption,
                'mail.from.address' => $request->mail_from_address,
                'mail.from.name' => $request->mail_from_name,
            ]);
            Mail::to($testTo)->send(new TestMail());
            return response()->json(['success' => true, 'message' => "Test email sent to {$testTo}"]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'SMTP Error: '.$e->getMessage()]);
        }
    }
}
