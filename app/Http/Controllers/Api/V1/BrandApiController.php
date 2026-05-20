<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()->orderBy('name')->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'slug' => $b->slug,
            'logo_url' => $b->logo ? asset('storage/'.$b->logo) : null,
        ]);

        return $this->success($brands);
    }
}
