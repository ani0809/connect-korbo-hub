<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $campaigns = NewsletterCampaign::latest()->paginate(20);
        $subscriberCount = NewsletterSubscriber::where('is_verified', true)->whereNull('unsubscribed_at')->count();
        return view('admin.newsletter.index', compact('campaigns', 'subscriberCount'));
    }

    public function subscribers()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(50);
        return view('admin.newsletter.subscribers', compact('subscribers'));
    }

    public function create()
    {
        return view('admin.newsletter.create');
    }

    public function store(Request $request)
    {
        $campaign = NewsletterCampaign::create([
            'subject' => $request->subject,
            'content' => $request->content,
            'recipients' => $request->recipients,
            'recipient_list' => $request->recipients === 'custom' ? array_filter(array_map('trim', explode("\n", (string) $request->custom_emails))) : null,
            'status' => 'draft',
            'scheduled_at' => $request->scheduled_at,
        ]);

        return redirect()->route('admin.newsletter.index')->with('success', 'Campaign created! ID: '.$campaign->id);
    }

    public function send(int $id, NewsletterService $newsletterService)
    {
        $newsletterService->queueCampaign($id);
        NewsletterCampaign::query()->where('id', $id)->update([
            'status' => request()->filled('scheduled_at') ? 'scheduled' : 'sending',
            'scheduled_at' => request('scheduled_at'),
        ]);
        return back()->with('success', 'Campaign queued for sending!');
    }

    public function retry(int $id, NewsletterService $newsletterService)
    {
        $newsletterService->queueCampaign($id);
        NewsletterCampaign::query()->where('id', $id)->update(['status' => 'sending']);
        return back()->with('success', 'Campaign retry queued.');
    }

    public function cancel(int $id)
    {
        NewsletterCampaign::query()->where('id', $id)->whereIn('status', ['draft', 'scheduled', 'sending'])->update(['status' => 'failed']);
        return back()->with('success', 'Campaign marked as cancelled.');
    }
}
