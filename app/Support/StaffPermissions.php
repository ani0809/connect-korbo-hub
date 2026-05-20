<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class StaffPermissions
{
    public const PERMISSIONS = [
        'dashboard' => [
            'view_dashboard' => 'View Dashboard',
        ],
        'products' => [
            'view_products' => 'View Products',
            'create_products' => 'Create Products',
            'edit_products' => 'Edit Products',
            'delete_products' => 'Delete Products',
            'publish_products' => 'Publish/Unpublish Products',
            'approve_products' => 'Approve Seller Products',
        ],
        'categories' => [
            'view_categories' => 'View Categories',
            'manage_categories' => 'Manage Categories',
        ],
        'brands' => [
            'view_brands' => 'View Brands',
            'manage_brands' => 'Manage Brands',
        ],
        'orders' => [
            'view_orders' => 'View Orders',
            'update_order_status' => 'Update Order Status',
            'cancel_orders' => 'Cancel Orders',
            'export_orders' => 'Export Orders',
            'view_invoices' => 'View Invoices',
        ],
        'customers' => [
            'view_customers' => 'View Customers',
            'create_customers' => 'Create Customers',
            'edit_customers' => 'Edit Customers',
            'delete_customers' => 'Delete Customers',
        ],
        'sellers' => [
            'view_sellers' => 'View Sellers',
            'approve_sellers' => 'Approve/Reject Sellers',
            'suspend_sellers' => 'Suspend Sellers',
            'view_seller_earnings' => 'View Seller Earnings',
            'manage_withdrawals' => 'Manage Withdrawals',
        ],
        'reports' => [
            'view_reports' => 'View Reports',
            'export_reports' => 'Export Reports',
        ],
        'marketing' => [
            'manage_coupons' => 'Manage Coupons',
            'manage_flash_deals' => 'Manage Flash Deals',
            'send_newsletters' => 'Send Newsletters',
            'manage_banners' => 'Manage Banners',
        ],
        'reviews' => [
            'view_reviews' => 'View Reviews',
            'approve_reviews' => 'Approve Reviews',
            'delete_reviews' => 'Delete Reviews',
            'reply_reviews' => 'Reply to Reviews',
            'manage_qna' => 'Manage Q&A',
        ],
        'support' => [
            'view_tickets' => 'View Support Tickets',
            'reply_tickets' => 'Reply to Tickets',
            'close_tickets' => 'Close Tickets',
        ],
        'shipping' => [
            'view_shipping' => 'View Shipping Settings',
            'manage_shipping' => 'Manage Shipping Settings',
        ],
        'payments' => [
            'view_payments' => 'View Payment Settings',
            'manage_payments' => 'Manage Payment Gateways',
        ],
        'settings' => [
            'view_settings' => 'View Settings',
            'manage_settings' => 'Manage Settings',
            'manage_theme' => 'Manage Theme & Builder',
        ],
        'staff' => [
            'view_staff' => 'View Staff',
            'manage_staff' => 'Manage Staff',
        ],
    ];

    public static function has(User $user, string $permission): bool
    {
        if (($user->role ?? null) === 'admin') {
            return true;
        }

        /** @var array<int, string>|null $permissions */
        $permissions = $user->staff_permissions ?? [];

        return in_array($permission, $permissions, true)
            || in_array('*', $permissions, true);
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return Collection::make(self::PERMISSIONS)
            ->flatMap(fn (array $perms) => $perms)
            ->all();
    }

    /** @return array<string, array<string, string>> */
    public static function grouped(): array
    {
        return self::PERMISSIONS;
    }

    /** @return list<string> */
    public static function allKeys(): array
    {
        return array_keys(self::all());
    }
}
