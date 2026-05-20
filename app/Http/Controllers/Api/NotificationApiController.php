<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class NotificationApiController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->take((int) request('limit', 10))->get()->map(fn ($n) => [
            'id' => $n->id,
            'title' => $n->data['title'] ?? class_basename($n->type),
            'body' => $n->data['body'] ?? '',
            'url' => $n->data['url'] ?? null,
            'icon' => $n->data['icon'] ?? '??',
            'read_at' => $n->read_at,
            'time' => $n->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
