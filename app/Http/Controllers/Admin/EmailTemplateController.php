<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::all()->groupBy(fn ($t) => explode('.', (string) $t->slug)[0] ?: 'misc');
        return view('admin.emails.index', compact('templates'));
    }

    public function edit(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        return view('admin.emails.edit', compact('template'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'channels' => 'required|array',
            'is_active' => 'nullable|boolean',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $template->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'channels' => $request->channels,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', 'Template updated successfully.');
    }

    public function sendTest(int $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $admin = auth()->user();

        app(NotificationService::class)->send($template->slug, $admin, [
            'customer_name' => 'Test Customer',
            'order_number' => '#TEST-001',
            'amount' => '$99.99',
            'email' => $admin->email,
        ], ['email']);

        return response()->json(['success' => true, 'message' => 'Test email sent to '.$admin->email]);
    }
}
