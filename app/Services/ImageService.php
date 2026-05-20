<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class ImageService
{
    public function upload($file, string $folder, ?int $width = null, ?int $height = null, int $quality = 85): string
    {
        $filename = Str::uuid().'.webp';
        $path = "uploads/{$folder}/{$filename}";
        $image = Image::make($file)->orientate();
        if ($width || $height) {
            $image->resize($width, $height, function ($c): void { $c->aspectRatio(); $c->upsize(); });
        } else {
            $maxWidth = (int) setting('max_image_width', 1920);
            if ($image->width() > $maxWidth) $image->resize($maxWidth, null, function ($c): void { $c->aspectRatio(); });
        }
        if (setting('webp_conversion', true)) $image->encode('webp', $quality); else $image->encode(null, $quality);
        Storage::disk('public')->put($path, (string) $image->stream());
        return $path;
    }

    public function thumbnail(string $path, int $width = 300, int $height = 300): string
    {
        $thumbPath = str_replace('uploads/', "uploads/thumbs/{$width}x{$height}/", $path);
        if (Storage::disk('public')->exists($thumbPath)) return $thumbPath;
        $absPath = Storage::disk('public')->path($path);
        $thumbAbs = Storage::disk('public')->path($thumbPath);
        $dir = dirname($thumbAbs); if (!is_dir($dir)) mkdir($dir, 0755, true);
        Image::make($absPath)->fit($width, $height)->save($thumbAbs);
        return $thumbPath;
    }

    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) Storage::disk('public')->delete($path);
        $pattern = storage_path('app/public/uploads/thumbs/*/'.basename($path));
        foreach (glob($pattern) ?: [] as $thumb) @unlink($thumb);
    }
}
