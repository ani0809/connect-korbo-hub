<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function sanitizeHtml(string $html): string
    {
        return strip_tags($html, '<p><br><strong><em><u><h1><h2><h3><h4><ul><ol><li><blockquote><a><img><table><tr><td><th><thead><tbody><span><div>');
    }

    public static function validateUpload($file, array $allowedMimes = []): bool
    {
        if (empty($allowedMimes)) $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->file($file->getRealPath());
        return in_array($detected, $allowedMimes, true);
    }

    public static function safePath(string $path): string
    {
        return basename((string) realpath(storage_path('app/'.$path)));
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes((int) ($length / 2)));
    }
}
