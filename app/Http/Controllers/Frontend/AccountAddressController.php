<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AccountAddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default_shipping')->get();
        return view('frontend.account.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $limit = (int) setting('max_addresses_per_user', 5);
        if (auth()->user()->addresses()->count() >= $limit) {
            return back()->with('error', 'Address limit reached.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:191',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        $validated['user_id'] = auth()->id();
        Address::query()->create($validated);
        return back()->with('success', 'Address added.');
    }

    public function update(Request $request, int $id)
    {
        $address = Address::query()->where('user_id', auth()->id())->findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:191',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);
        $address->update($validated);
        return back()->with('success', 'Address updated.');
    }

    public function destroy(int $id)
    {
        $address = Address::query()->where('user_id', auth()->id())->findOrFail($id);
        if (Address::query()->where('user_id', auth()->id())->count() <= 1) {
            return back()->with('error', 'At least one address is required.');
        }
        $wasDefaultShipping = $address->is_default_shipping;
        $wasDefaultBilling = $address->is_default_billing;
        $address->delete();

        $next = Address::query()->where('user_id', auth()->id())->first();
        if ($next && ($wasDefaultShipping || $wasDefaultBilling)) {
            $next->update([
                'is_default_shipping' => $wasDefaultShipping,
                'is_default_billing' => $wasDefaultBilling,
            ]);
        }

        return back()->with('success', 'Address deleted.');
    }

    public function setDefaultShipping(int $id)
    {
        Address::query()->where('user_id', auth()->id())->update(['is_default_shipping' => false]);
        Address::query()->where('user_id', auth()->id())->where('id', $id)->update(['is_default_shipping' => true]);
        return back()->with('success', 'Default shipping address updated.');
    }

    public function setDefaultBilling(int $id)
    {
        Address::query()->where('user_id', auth()->id())->update(['is_default_billing' => false]);
        Address::query()->where('user_id', auth()->id())->where('id', $id)->update(['is_default_billing' => true]);
        return back()->with('success', 'Default billing address updated.');
    }
}
