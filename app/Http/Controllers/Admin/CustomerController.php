<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use App\Services\ImageService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'customer')
            ->withCount([
                'orders',
                'orders as completed_orders' => fn ($q) => $q->where('payment_status', 'paid'),
                'wishlists',
            ])
            ->withSum('orders as total_spent', 'total')
            ->adminCustomerFilter($request->only([
                'search', 'status', 'verified', 'date_from', 'date_to',
            ]));

        if ($request->filled('min_spent')) {
            $min = (float) $request->input('min_spent');
            $query->whereRaw(
                '(select coalesce(sum(total),0) from orders where orders.user_id = users.id and orders.deleted_at is null) >= ?',
                [$min]
            );
        }
        if ($request->filled('max_spent')) {
            $max = (float) $request->input('max_spent');
            $query->whereRaw(
                '(select coalesce(sum(total),0) from orders where orders.user_id = users.id and orders.deleted_at is null) <= ?',
                [$max]
            );
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => User::query()->where('role', 'customer')->count(),
            'new_month' => User::query()->where('role', 'customer')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'active' => User::query()->where('role', 'customer')->where('status', 'active')->count(),
            'banned' => User::query()->where('role', 'customer')->where('status', 'banned')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'send_welcome_email' => 'boolean',
        ]);

        $customer = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($request->boolean('send_welcome_email')) {
            try {
                $notifications->send('customer.welcome', $customer, [
                    'password' => $validated['password'],
                    'email' => $customer->email,
                ], ['email']);
            } catch (\Throwable) {
            }
        }

        log_activity('customer.created', $customer, [], ['email' => $customer->email]);

        return redirect()->route('admin.customers.show', $customer->id)->with('success', 'Customer created.');
    }

    public function show(int $id): View
    {
        $customer = User::query()
            ->where('role', 'customer')
            ->with([
                'orders' => fn ($q) => $q->latest()->take(10)->with('items.product'),
                'addresses',
                'wishlists' => fn ($q) => $q->with('product:id,name,thumbnail')->take(5),
            ])
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->findOrFail($id);

        $pointsBalance = 0;
        if (Schema::hasTable('club_point_transactions')) {
            $pointsBalance = (int) DB::table('club_point_transactions')->where('user_id', $id)->sum('points');
        }

        $walletBalance = 0.0;
        if (Schema::hasTable('wallet_transactions')) {
            $walletBalance = (float) DB::table('wallet_transactions')->where('user_id', $id)->sum('amount');
        }

        $avgOrder = $customer->orders_count > 0
            ? (float) $customer->total_spent / max(1, $customer->orders_count)
            : 0;

        $lastOrder = $customer->orders->first();

        return view('admin.customers.show', compact(
            'customer', 'pointsBalance', 'walletBalance', 'avgOrder', 'lastOrder'
        ));
    }

    public function edit(int $id): View
    {
        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, int $id, ImageService $imageService): RedirectResponse
    {
        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$customer->id,
            'phone' => 'nullable|string|unique:users,phone,'.$customer->id,
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $old = ['name' => $customer->name, 'email' => $customer->email];

        $customer->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $customer->password = $validated['password'];
        }

        if ($request->hasFile('avatar')) {
            $customer->avatar = $imageService->upload($request->file('avatar'), 'avatars', 200, 200);
        }

        $customer->save();

        log_activity('customer.updated', $customer, $old, ['name' => $customer->name, 'email' => $customer->email]);

        return redirect()->route('admin.customers.show', $customer->id)->with('success', 'Customer updated.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $customer = User::query()->where('role', 'customer')->findOrFail($id);
        $old = $customer->status;
        $newStatus = $customer->status === 'active' ? 'banned' : 'active';
        $customer->update(['status' => $newStatus]);

        log_activity('customer.status_changed', $customer, ['status' => $old], ['status' => $newStatus]);

        return back()->with('success', 'Customer status updated.');
    }

    public function adjustWallet(Request $request, int $id, WalletService $walletService): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:add,deduct',
            'reason' => 'nullable|string|max:500',
        ]);

        abort_unless(Schema::hasTable('wallet_transactions'), 404);

        $customer = User::query()->where('role', 'customer')->findOrFail($id);
        $reason = (string) $request->input('reason', 'Admin adjustment');
        $amt = (float) $request->input('amount');

        if ($request->input('type') === 'add') {
            $walletService->credit($id, $amt, 'admin_credit', $reason);
        } else {
            $r = $walletService->debit($id, $amt, 'admin_debit', $reason);
            if (! $r['success']) {
                return back()->withErrors(['amount' => $r['message'] ?? 'Could not deduct.']);
            }
        }

        log_activity('customer.wallet_adjusted', $customer, [], ['amount' => $request->input('type') === 'deduct' ? -$amt : $amt, 'reason' => $reason]);

        return back()->with('success', 'Wallet updated.');
    }

    public function adjustPoints(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'type' => 'required|in:add,deduct',
            'reason' => 'nullable|string|max:500',
        ]);

        abort_unless(Schema::hasTable('club_point_transactions'), 404);

        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        $points = (int) $request->input('points');
        $points = $request->input('type') === 'deduct' ? -abs($points) : abs($points);

        $currentBalance = (int) DB::table('club_point_transactions')->where('user_id', $id)->sum('points');
        $newBalance = $currentBalance + $points;
        if ($newBalance < 0) {
            return back()->withErrors(['points' => 'Points balance cannot go below zero.']);
        }

        DB::transaction(function () use ($id, $points, $request, $newBalance): void {
            DB::table('club_point_transactions')->insert([
                'user_id' => $id,
                'order_id' => null,
                'points' => $points,
                'type' => 'bonus',
                'description' => $request->input('reason', 'Admin adjustment'),
                'balance_after' => $newBalance,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        log_activity('customer.points_adjusted', $customer, [], ['points' => $points, 'reason' => $request->input('reason')]);

        return back()->with('success', 'Points updated.');
    }

    public function loginAsCustomer(int $id): RedirectResponse
    {
        $allowed = config('app.env') !== 'production' || config('shop.allow_impersonation', false);
        abort_unless($allowed, 403);

        $customer = User::query()->where('role', 'customer')->findOrFail($id);

        session([
            'impersonating_as' => $customer->id,
            'impersonator_id' => auth()->id(),
        ]);

        auth()->loginUsingId($customer->id);

        return redirect('/')->with('info', 'Logged in as '.$customer->name.'.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = User::query()
            ->where('role', 'customer')
            ->adminCustomerFilter($request->only([
                'search', 'status', 'verified', 'date_from', 'date_to',
            ]))
            ->latest();

        $filename = 'customers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Joined']);
            $query->chunk(500, function ($rows) use ($out): void {
                foreach ($rows as $u) {
                    fputcsv($out, [$u->id, $u->name, $u->email, $u->phone, $u->status, $u->created_at?->toDateTimeString()]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
