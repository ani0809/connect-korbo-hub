<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\GenericMail;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'unread');
        $query = ContactInquiry::query()->orderByDesc('created_at');

        if ($tab === 'unread') {
            $query->where('status', 'unread');
        } elseif ($tab === 'read') {
            $query->where('status', 'read');
        } elseif ($tab === 'replied') {
            $query->where('status', 'replied');
        }

        $inquiries = $query->paginate(20)->withQueryString();

        $counts = [
            'unread' => ContactInquiry::query()->where('status', 'unread')->count(),
            'read' => ContactInquiry::query()->where('status', 'read')->count(),
            'replied' => ContactInquiry::query()->where('status', 'replied')->count(),
            'all' => ContactInquiry::query()->count(),
        ];

        return view('admin.contact.index', compact('inquiries', 'tab', 'counts'));
    }

    public function show(int $id): View
    {
        $inquiry = ContactInquiry::query()->findOrFail($id);
        if ($inquiry->status === 'unread') {
            $inquiry->update(['status' => 'read']);
        }

        return view('admin.contact.show', compact('inquiry'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reply' => 'required|string|min:3']);

        $inquiry = ContactInquiry::query()->findOrFail($id);
        $inquiry->update([
            'admin_reply' => $request->reply,
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        $body = '<div style="font-family:sans-serif;font-size:14px">'.nl2br(e($request->reply)).'</div>';
        Mail::to($inquiry->email)->queue(new GenericMail(
            'Re: '.$inquiry->subject,
            $body,
            ['email' => $inquiry->email]
        ));

        return back()->with('ok', 'Reply sent.');
    }
}
