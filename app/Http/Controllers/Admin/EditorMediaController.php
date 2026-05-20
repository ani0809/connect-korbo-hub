<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaLibraryController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorMediaController extends Controller
{
    public function index(): JsonResponse
    {
        return app(MediaLibraryController::class)->index(request());
    }

    public function upload(Request $request): JsonResponse
    {
        return app(MediaLibraryController::class)->upload($request);
    }

    public function resolve(Request $request): JsonResponse
    {
        return app(MediaLibraryController::class)->resolve($request);
    }
}

