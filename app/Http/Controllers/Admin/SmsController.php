<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        return view('admin.settings.sms');
    }

    public function save(Request $request)
    {
        Setting::setMany([
            'sms_enabled' => (bool) $request->boolean('sms_enabled', false),
            'sms_gateway' => (string) $request->input('sms_gateway', 'twilio'),
            'twilio_account_sid' => (string) $request->input('twilio_account_sid'),
            'twilio_auth_token' => (string) $request->input('twilio_auth_token'),
            'twilio_from_number' => (string) $request->input('twilio_from_number'),
            'msg91_authkey' => (string) $request->input('msg91_authkey'),
            'msg91_sender_id' => (string) $request->input('msg91_sender_id'),
            'msg91_template_id' => (string) $request->input('msg91_template_id'),
            'sms_custom_api_url' => (string) $request->input('sms_custom_api_url'),
            'sms_custom_api_method' => (string) $request->input('sms_custom_api_method', 'GET'),
            'sms_custom_api_params' => (string) $request->input('sms_custom_api_params', '{}'),
            'sms_otp_template' => (string) $request->input('sms_otp_template'),
        ]);

        return back()->with('success', 'SMS settings updated.');
    }

    public function test(Request $request, SmsService $smsService)
    {
        $request->validate(['phone' => 'required|string', 'message' => 'required|string']);
        $ok = $smsService->send((string) $request->phone, (string) $request->message);
        return response()->json(['success' => $ok, 'message' => $ok ? 'Test SMS sent.' : 'Failed to send SMS']);
    }
}
