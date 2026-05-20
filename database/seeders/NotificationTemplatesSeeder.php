<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['slug' => 'order.placed', 'title' => 'Order Placed', 'subject' => 'Order [[order_number]] Confirmed - [[site_name]]', 'body' => '<p>Hi [[customer_name]], your order [[order_number]] has been placed.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'order.confirmed', 'title' => 'Order Confirmed', 'subject' => 'Your order [[order_number]] is confirmed!', 'body' => '<p>Your order [[order_number]] is now confirmed.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'order.shipped', 'title' => 'Order Shipped', 'subject' => 'Your order [[order_number]] has been shipped!', 'body' => '<p>Your order [[order_number]] is on the way.</p>', 'channels' => ['email', 'sms', 'database']],
            ['slug' => 'order.delivered', 'title' => 'Order Delivered', 'subject' => 'Your order [[order_number]] delivered!', 'body' => '<p>Order [[order_number]] delivered successfully.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'order.cancelled', 'title' => 'Order Cancelled', 'subject' => 'Order [[order_number]] cancelled', 'body' => '<p>Order [[order_number]] has been cancelled.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'seller.new_order', 'title' => 'Seller New Order', 'subject' => 'New order [[order_number]] for your shop', 'body' => '<p>You have a new order.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'seller.approved', 'title' => 'Seller Approved', 'subject' => 'Your seller account is approved', 'body' => '<p>Congratulations! Your seller account is approved.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'seller.suspended', 'title' => 'Seller Suspended', 'subject' => 'Seller account suspended', 'body' => '<p>Your seller account has been suspended.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'seller.withdrawal_processed', 'title' => 'Withdrawal Processed', 'subject' => 'Withdrawal processed', 'body' => '<p>Your withdrawal request has been processed.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'seller.withdrawal_rejected', 'title' => 'Withdrawal Rejected', 'subject' => 'Withdrawal rejected', 'body' => '<p>Your withdrawal request has been rejected.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'customer.welcome', 'title' => 'Welcome Customer', 'subject' => 'Welcome to [[site_name]]', 'body' => '<p>Welcome [[customer_name]]!</p>', 'channels' => ['email']],
            ['slug' => 'customer.password_reset', 'title' => 'Password Reset', 'subject' => 'Reset your password', 'body' => '<p>Use the reset link to update password.</p>', 'channels' => ['email']],
            ['slug' => 'customer.email_verify', 'title' => 'Email Verification', 'subject' => 'Verify your email', 'body' => '<p>Please verify your email.</p>', 'channels' => ['email']],
            ['slug' => 'customer.otp', 'title' => 'OTP Code', 'subject' => 'Your OTP Code', 'body' => '<p>Your OTP is [[otp]].</p>', 'channels' => ['email', 'sms']],
            ['slug' => 'customer.review_approved', 'title' => 'Review Approved', 'subject' => 'Your review is approved', 'body' => '<p>Your review was approved.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'admin.new_order', 'title' => 'Admin New Order', 'subject' => 'New order [[order_number]]', 'body' => '<p>New order placed.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'admin.new_seller', 'title' => 'Admin New Seller', 'subject' => 'New seller registration', 'body' => '<p>A new seller registered.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'admin.low_stock', 'title' => 'Low Stock Alert', 'subject' => 'Low stock alert', 'body' => '<p>Some products are low in stock.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'admin.withdrawal_request', 'title' => 'Withdrawal Request', 'subject' => 'New withdrawal request', 'body' => '<p>A withdrawal request is pending.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'support.ticket_reply', 'title' => 'Support Ticket Reply', 'subject' => 'Support ticket [[ticket_number]] reply', 'body' => '<p>You have a new support reply.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'points.earned', 'title' => 'Points earned', 'subject' => 'You earned [[points]] points!', 'body' => '<p>You earned [[points]] points on order [[order_number]]. Your balance is now [[balance]] points.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'points.expiring_soon', 'title' => 'Points expiring soon', 'subject' => '[[points]] points expiring on [[expiry_date]]', 'body' => '<p>You have [[points]] reward points expiring on [[expiry_date]]. Current balance: [[balance]] points. Shop soon to use them.</p>', 'channels' => ['email', 'database']],
            ['slug' => 'wallet.credited', 'title' => 'Wallet credited', 'subject' => 'Your [[site_name]] wallet was credited', 'body' => '<p>Your wallet balance has been updated. Thank you for shopping with us.</p>', 'channels' => ['email', 'database']],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'title' => $template['title'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'channels' => $template['channels'],
                    'is_active' => true,
                ]
            );
        }
    }
}
