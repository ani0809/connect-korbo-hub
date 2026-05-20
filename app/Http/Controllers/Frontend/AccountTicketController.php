<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::query()->where('user_id', auth()->id())->with(['replies' => fn ($q) => $q->latest()->take(1)])->latest()->paginate(10);
        return view('frontend.account.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:191',
            'message' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,png,pdf,doc,docx,zip',
        ]);

        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
            'user_id' => auth()->id(),
            'subject' => (string) $request->string('subject')->value(),
            'status' => 'open',
            'priority' => (string) $request->string('priority')->value(),
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets', 'local');
                $attachments[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'size' => $file->getSize()];
            }
        }

        $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => (string) $request->string('message')->value(),
            'attachments' => $attachments,
        ]);

        return redirect()->route('account.support.show', $ticket->ticket_number)->with('success', 'Ticket created!');
    }

    public function show(string $number)
    {
        $ticket = SupportTicket::query()->where(['ticket_number' => $number, 'user_id' => auth()->id()])->with(['replies.user'])->firstOrFail();
        return view('frontend.account.support.show', compact('ticket'));
    }

    public function reply(Request $request, string $number)
    {
        $request->validate([
            'message' => 'required|string|min:2',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,png,pdf,doc,docx,zip',
        ]);

        $ticket = SupportTicket::query()->where(['ticket_number' => $number, 'user_id' => auth()->id()])->whereNotIn('status', ['closed'])->firstOrFail();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets', 'local');
                $attachments[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'size' => $file->getSize()];
            }
        }

        $ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => (string) $request->string('message')->value(),
            'attachments' => $attachments,
        ]);

        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent!');
    }

    public function close(string $number)
    {
        $ticket = SupportTicket::query()->where(['ticket_number' => $number, 'user_id' => auth()->id()])->firstOrFail();
        $ticket->update(['status' => 'closed']);
        return back()->with('success', 'Ticket closed');
    }
}
