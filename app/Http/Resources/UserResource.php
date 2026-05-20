<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $u = $this->resource;

        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role' => $u->role,
            'status' => $u->status,
            'avatar_url' => function_exists('avatar_url') ? avatar_url($u) : ($u->avatar ? asset('storage/'.$u->avatar) : null),
            'preferred_locale' => $u->preferred_locale ?? null,
            'email_verified_at' => $u->email_verified_at?->toIso8601String(),
        ];
    }
}
