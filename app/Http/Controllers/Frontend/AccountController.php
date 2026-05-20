<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\MediaAsset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SupportTicket;
use App\Models\Wishlist;
use App\Services\ImageService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $data = [
            'recent_orders' => Order::query()->where('user_id', $user->id)->with(['items.product'])->latest()->take(5)->get(),
            'orders_count' => Order::query()->where('user_id', $user->id)->count(),
            'pending_orders' => Order::query()->where('user_id', $user->id)->where('order_status', 'pending')->count(),
            'wishlist_count' => Wishlist::query()->where('user_id', $user->id)->count(),
            'points_balance' => $this->getPointsBalance($user->id),
            'total_spent' => (float) Order::query()->where('user_id', $user->id)->where('payment_status', 'paid')->sum('total'),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'open_tickets' => SupportTicket::query()->where('user_id', $user->id)->whereIn('status', ['open', 'pending'])->count(),
            'following_count' => Follower::query()->where('user_id', $user->id)->count(),
            'pending_reviews' => OrderItem::query()->where('is_reviewed', false)->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('order_status', 'delivered'))->count(),
            'wallet_balance' => function_exists('feature') && feature('wallet')
                ? app(WalletService::class)->getBalance((int) $user->id)
                : null,
        ];

        return view('frontend.account.dashboard', compact('data', 'user'));
    }

    public function profile()
    {
        return view('frontend.account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30|unique:users,phone,'.$user->id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not',
        ]);

        if ($validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.', 'data' => ['user' => $user->fresh()]]);
    }

    public function updateAvatar(Request $request, ImageService $imageService): JsonResponse
    {
        $request->validate([
            'avatar' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'avatar_media_id' => 'nullable|integer|exists:media_assets,id',
        ]);
        $user = auth()->user();
        $payload = [];
        if ($request->hasFile('avatar')) {
            $payload['avatar'] = $imageService->upload($request->file('avatar'), 'avatars', 200, 200, 85);
            $payload['avatar_media_id'] = null;
        } elseif ($request->filled('avatar_media_id')) {
            $asset = MediaAsset::query()->find((int) $request->input('avatar_media_id'));
            if ($asset) {
                $payload['avatar'] = $asset->path;
                $payload['avatar_media_id'] = $asset->id;
            }
        }
        if ($payload === []) {
            return response()->json(['success' => false, 'message' => 'No avatar selected.', 'data' => []], 422);
        }
        $user->update($payload);

        return response()->json(['success' => true, 'message' => 'Avatar updated.', 'data' => ['avatar' => $user->avatar]]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        if (! Hash::check((string) $request->string('current_password')->value(), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.', 'data' => []], 422);
        }

        $user->update(['password' => bcrypt((string) $request->string('password')->value())]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully.', 'data' => []]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string']);
        $user = auth()->user();

        if (! Hash::check((string) $request->string('password')->value(), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password verification failed.', 'data' => []], 422);
        }

        $hasPendingOrders = Order::query()->where('user_id', $user->id)->whereIn('order_status', ['pending', 'confirmed', 'processing', 'shipped'])->exists();
        if ($hasPendingOrders) {
            return response()->json(['success' => false, 'message' => 'Cannot delete account with active orders.', 'data' => []], 422);
        }

        DB::transaction(function () use ($user): void {
            $user->update([
                'name' => 'Deleted User',
                'email' => 'deleted_'.$user->id.'@deleted.com',
                'phone' => null,
                'avatar' => null,
                'status' => 'deleted',
            ]);
            $user->delete();
        });

        auth()->logout();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully.', 'data' => []]);
    }

    public function following()
    {
        $following = Follower::query()->where('user_id', auth()->id())->with(['seller.user'])->paginate(12);
        return view('frontend.account.following', compact('following'));
    }

    public function notifications()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $user->unreadNotifications()->update(['read_at' => now()]);
        return view('frontend.account.notifications', compact('notifications'));
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string']);
        $notification = auth()->user()->notifications()->where('id', $request->id)->firstOrFail();
        $notification->markAsRead();
        return response()->json(['success' => true, 'message' => 'Notification marked as read.', 'data' => []]);
    }

    public function markAllRead(): JsonResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true, 'message' => 'All notifications marked as read.', 'data' => []]);
    }

    private function getPointsBalance(int $userId): int
    {
        return (int) DB::table('club_point_transactions')->where('user_id', $userId)->sum('points');
    }
}
