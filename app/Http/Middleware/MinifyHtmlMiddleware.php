<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtmlMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!setting('minify_html', false)) return $response;
        if (!str_contains((string) $response->headers->get('Content-Type', ''), 'text/html')) return $response;
        $content = (string) $response->getContent();
        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content) ?? $content;
        $content = preg_replace('/>\s+</', '><', $content) ?? $content;
        $response->setContent(trim($content));
        return $response;
    }
}
