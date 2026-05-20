<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class ProductAiController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'category' => 'nullable|string|max:191',
            'keywords' => 'nullable|string|max:500',
            'type' => 'nullable|string|max:50',
        ]);

        $key = 'ai:product:'.sha1(strtolower($data['name'].'|'.($data['category'] ?? '').'|'.($data['keywords'] ?? '')));

        if (RateLimiter::tooManyAttempts('ai-product:'.$request->ip(), 10)) {
            return response()->json(['success' => false, 'message' => 'Rate limit exceeded (10/minute).'], 429);
        }
        RateLimiter::hit('ai-product:'.$request->ip(), 60);

        $cached = Cache::get($key);
        if ($cached) {
            return response()->json(['success' => true, 'message' => 'AI content generated (cached).', 'data' => $cached]);
        }

        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-1.5-flash');
        if (! $apiKey) {
            return response()->json(['success' => false, 'message' => 'Gemini API key is missing.'], 422);
        }

        $prompt = "You are an eCommerce product copywriter.\nGenerate product content for:\nProduct Name: {$data['name']}\nCategory: ".($data['category'] ?? '')."\nKeywords: ".($data['keywords'] ?? '')."\n\nReturn ONLY a JSON object with these keys:\n{\n  \"short_description\": \"2-3 sentence product summary, compelling, under 200 chars\",\n  \"description\": \"Full HTML product description with features list, 3-5 paragraphs\",\n  \"meta_title\": \"SEO title under 60 chars\",\n  \"meta_description\": \"SEO description under 160 chars\",\n  \"meta_keywords\": \"comma separated keywords\"\n}";

        try {
            $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'key' => $apiKey,
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            if (! $response->successful()) {
                return response()->json(['success' => false, 'message' => 'AI provider error. Please try again.'], 502);
            }

            $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '{}');
            $json = json_decode(trim($text), true);
            if (! is_array($json)) {
                return response()->json(['success' => false, 'message' => 'Could not parse AI response.'], 422);
            }

            Cache::put($key, $json, now()->addHour());

            return response()->json(['success' => true, 'message' => 'AI content generated.', 'data' => $json]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'AI service unavailable right now.'], 500);
        }
    }
}
