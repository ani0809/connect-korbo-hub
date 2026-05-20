<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->with(['replies' => fn ($q) => $q->latest()->take(1)])
            ->latest()
            ->paginate(15);

        return $this->paginated($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:191',
            'message' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,png,pdf,doc,docx,zip',
        ]);

        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'subject' => (string) $request->string('subject'),
            'status' => 'open',
            'priority' => (string) $request->string('priority'),
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') ?? [] as $file) {
                $path = $file->store('tickets', 'local');
                $attachments[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'size' => $file->getSize()];
            }
        }

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => (string) $request->string('message'),
            'attachments' => $attachments,
        ]);

        return $this->success(['ticket_number' => $ticket->ticket_number, 'id' => $ticket->id], 'Ticket created', 201);
    }

    public function show(Request $request, string $number): JsonResponse
    {
        $ticket = SupportTicket::query()
            ->where(['ticket_number' => $number, 'user_id' => $request->user()->id])
            ->with(['replies.user'])
            ->firstOrFail();

        return $this->success($ticket);
    }

    public function reply(Request $request, string $number): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:2',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,png,pdf,doc,docx,zip',
        ]);

        $ticket = SupportTicket::query()
            ->where(['ticket_number' => $number, 'user_id' => $request->user()->id])
            ->whereNotIn('status', ['closed'])
            ->firstOrFail();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') ?? [] as $file) {
                $path = $file->store('tickets', 'local');
                $attachments[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'size' => $file->getSize()];
            }
        }

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => (string) $request->string('message'),
            'attachments' => $attachments,
        ]);

        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'open']);
        }

        return $this->success(null, 'Reply sent');
    }
}
