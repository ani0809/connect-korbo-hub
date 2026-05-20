<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerRegistrationController extends Controller
{
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (! (bool) setting('allow_seller_registration', true)) {
            abort(403, 'Seller registration is closed');
        }

        if (auth()->check() && auth()->user()->role === 'seller') {
            return redirect()->route('seller.dashboard');
        }

        return view('frontend.seller.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'shop_name' => 'required|string|max:191|unique:sellers,shop_name',
            'shop_address' => 'required|string|max:255',
            'agree_terms' => 'required|accepted',
        ]);

        $autoApprove = (bool) setting('seller_auto_approve', false);

        return DB::transaction(function () use ($data, $autoApprove) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => bcrypt($data['password']),
                'role' => 'seller',
                'status' => 'active',
            ]);

            $seller = Seller::query()->create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'shop_slug' => Str::slug($data['shop_name']).'-'.Str::lower(Str::random(4)),
                'shop_address' => $data['shop_address'],
                'shop_phone' => $data['phone'],
                'shop_email' => $data['email'],
                'status' => $autoApprove ? 'active' : 'pending',
            ]);

            $adminEmail = setting('admin_email', null);
            if ($adminEmail) {
                Mail::raw("New seller registration: {$seller->shop_name}", fn ($m) => $m->to($adminEmail)->subject('New Seller Registration'));
            }
            Mail::raw("Welcome to seller panel, {$seller->shop_name}", fn ($m) => $m->to($user->email)->subject('Seller Registration'));

            if ($seller->status === 'pending') {
                return redirect()->route('seller.registration.pending')->with('success', 'Registration successful! Awaiting approval.');
            }

            auth()->login($user);
            return redirect()->route('seller.dashboard')->with('success', 'Welcome to your seller panel!');
        });
    }

    public function showPendingPage(): View
    {
        return view('frontend.seller.pending');
    }
}
