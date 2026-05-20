<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->success($request->user()->addresses()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $address = $request->user()->addresses()->create($data);

        return $this->success($address, 'Address created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->update($this->validated($request));

        return $this->success($address->fresh(), 'Address updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->addresses()->where('id', $id)->delete();

        return $this->success(null, 'Address deleted');
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $user->addresses()->update(['is_default_shipping' => false, 'is_default_billing' => false]);
        $address = $user->addresses()->findOrFail($id);
        $address->update(['is_default_shipping' => true]);

        return $this->success($user->addresses()->get(), 'Default address updated');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:120',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'nullable|string|max:120',
            'country' => 'required|string|max:120',
            'postal_code' => 'nullable|string|max:40',
            'is_default_shipping' => 'sometimes|boolean',
            'is_default_billing' => 'sometimes|boolean',
        ]);
    }
}
