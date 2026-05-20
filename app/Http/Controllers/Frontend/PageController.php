<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\Faq;
use App\Models\StaticPage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = StaticPage::query()->active()->where('slug', $slug)->firstOrFail();
        if ((bool) ($page->is_builder_page ?? false) && ! empty($page->builder_data)) {
            $content = json_decode((string) $page->builder_data, true) ?: [];
            $isBlank = $page->template === 'blank';
            return view('frontend.builder-page.show', [
                'page' => $page,
                'content' => [
                    'html' => (string) ($content['html'] ?? ''),
                    'css' => (string) ($content['css'] ?? ''),
                ],
                'isBlank' => $isBlank,
            ]);
        }

        if ($page->template === 'blank') {
            return view('frontend.pages.blank', compact('page'));
        }

        return view('frontend.pages.show', compact('page'));
    }

    public function about() { return view('frontend.pages.about'); }
    public function contact() { return view('frontend.pages.contact'); }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:40',
            'subject' => 'required|string|max:191',
            'message' => 'required|string|min:10',
            'website' => 'nullable|string',
        ]);
        if ($request->filled('website')) return back()->with('success', 'Message sent! We\'ll reply soon.');

        ContactInquiry::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'unread',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Message sent! We\'ll reply soon.');
    }

    public function faq()
    {
        $faqs = Faq::query()->active()->orderBy('sort_order')->get()->groupBy(fn (Faq $f) => $f->category ?? '');

        return view('frontend.pages.faq', compact('faqs'));
    }
}
