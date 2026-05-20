<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MediaAsset::query()->latest('id');
        $user = auth()->user();

        if (($user->role ?? null) === 'seller' || ($user->role ?? null) === 'customer') {
            $query->where('uploader_id', $user->id);
        }

        if ($request->filled('type') && $request->string('type')->value() !== 'all') {
            $query->where('media_type', $request->string('type')->value());
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->value().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', $term)
                    ->orWhere('alt_text', 'like', $term)
                    ->orWhere('path', 'like', $term);
            });
        }

        $items = $query->paginate(max(12, min(120, (int) $request->integer('per_page', 60))));

        return response()->json([
            'success' => true,
            'data' => $items->getCollection()->map(fn (MediaAsset $asset) => $this->serializeAsset($asset))->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
                'per_page' => $items->perPage(),
            ],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        if ($request->filled('file_data')) {
            return $this->uploadFromBase64($request);
        }

        $uploadError = $this->detectUploadError($request);
        if ($uploadError !== null) {
            return response()->json([
                'success' => false,
                'message' => $uploadError,
            ], 422);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:25600|mimes:jpg,jpeg,png,webp,gif,bmp,svg,mp4,webm,ogg,mov,m4v,pdf,doc,docx,zip',
            'title' => 'nullable|string|max:191',
            'alt_text' => 'nullable|string|max:191',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $filename = Str::slug($name ?: 'media').'-'.Str::random(8).'.'.$ext;
        $path = $file->storeAs('media-library/'.date('Y/m'), $filename, 'public');
        $mime = (string) Storage::disk('public')->mimeType($path);

        $asset = MediaAsset::query()->create([
            'uploader_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'extension' => $ext,
            'size' => (int) $file->getSize(),
            'media_type' => $this->detectType($mime),
            'title' => $validated['title'] ?? $name,
            'alt_text' => $validated['alt_text'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->serializeAsset($asset),
        ]);
    }

    private function uploadFromBase64(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file_data' => 'required|string',
            'file_name' => 'nullable|string|max:191',
            'mime_type' => 'nullable|string|max:120',
            'title' => 'nullable|string|max:191',
            'alt_text' => 'nullable|string|max:191',
        ]);

        $raw = (string) $validated['file_data'];
        $mime = (string) ($validated['mime_type'] ?? '');
        $data = $raw;
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $raw, $m) === 1) {
            $mime = $mime ?: (string) $m[1];
            $data = (string) $m[2];
        }

        $binary = base64_decode($data, true);
        if ($binary === false || $binary === '') {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: invalid image payload.',
            ], 422);
        }

        // Keep payload safe and bounded for JSON route.
        if (strlen($binary) > 3_500_000) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: image is too large. Please use a smaller image.',
            ], 422);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($mime, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: only jpg, png, webp, gif are supported in this mode.',
            ], 422);
        }

        $name = pathinfo((string) ($validated['file_name'] ?? 'image'), PATHINFO_FILENAME) ?: 'image';
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $filename = Str::slug($name).'-'.Str::random(8).'.'.$ext;
        $path = 'media-library/'.date('Y/m').'/'.$filename;
        Storage::disk('public')->put($path, $binary);

        $asset = MediaAsset::query()->create([
            'uploader_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'extension' => $ext,
            'size' => strlen($binary),
            'media_type' => $this->detectType($mime),
            'title' => $validated['title'] ?? $name,
            'alt_text' => $validated['alt_text'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->serializeAsset($asset),
        ]);
    }

    private function detectUploadError(Request $request): ?string
    {
        if (! array_key_exists('file', $_FILES)) {
            return null;
        }

        $raw = $_FILES['file'];
        $errorCode = (int) ($raw['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode === UPLOAD_ERR_OK) {
            $uploaded = $request->file('file');
            if ($uploaded && ! $uploaded->isValid()) {
                return 'Upload failed: '.$uploaded->getErrorMessage();
            }
            return null;
        }

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'Upload failed: file exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE => 'Upload failed: file exceeds the allowed form size.',
            UPLOAD_ERR_PARTIAL => 'Upload failed: file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'Upload failed: no file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload failed: server temporary upload directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Upload failed: server cannot write uploaded file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload failed: a PHP extension stopped the upload.',
            default => 'Upload failed: unknown server upload error.',
        };
    }

    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:media_assets,id',
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $query = MediaAsset::query()->whereIn('id', $ids);
        $user = auth()->user();
        if (($user->role ?? null) === 'seller' || ($user->role ?? null) === 'customer') {
            $query->where('uploader_id', $user->id);
        }

        $assets = $query->get()->sortBy(fn (MediaAsset $asset) => array_search($asset->id, $ids, true))->values();

        return response()->json([
            'success' => true,
            'data' => $assets->map(fn (MediaAsset $asset) => $this->serializeAsset($asset))->values(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:media_assets,id',
            'title' => 'nullable|string|max:191',
            'alt_text' => 'nullable|string|max:191',
        ]);

        $query = MediaAsset::query()->whereKey((int) $validated['id']);
        $user = auth()->user();
        if (($user->role ?? null) === 'seller' || ($user->role ?? null) === 'customer') {
            $query->where('uploader_id', $user->id);
        }

        /** @var MediaAsset|null $asset */
        $asset = $query->first();
        if (! $asset) {
            return response()->json([
                'success' => false,
                'message' => 'Media item not found.',
            ], 404);
        }

        $asset->title = $validated['title'] ?? null;
        $asset->alt_text = $validated['alt_text'] ?? null;
        $asset->save();

        return response()->json([
            'success' => true,
            'data' => $this->serializeAsset($asset->fresh()),
        ]);
    }

    private function detectType(?string $mime): string
    {
        $mime = (string) $mime;
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true) => 'document',
            default => 'other',
        };
    }

    private function serializeAsset(MediaAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'name' => basename($asset->path),
            'title' => $asset->title,
            'path' => $asset->path,
            'url' => $asset->url,
            'thumbnail_url' => $asset->thumbnail_url,
            'mime' => $asset->mime_type,
            'type' => $asset->media_type,
            'size' => $asset->size,
            'alt_text' => $asset->alt_text,
            'created_at' => optional($asset->created_at)->toISOString(),
        ];
    }
}

