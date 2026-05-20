<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $parentCategories = collect(['Electronics','Fashion','Home','Beauty','Sports'])->map(fn ($name, $i) => Category::query()->create([
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
            'sort_order' => $i,
        ]));

        foreach ($parentCategories as $parent) {
            Category::query()->create(['parent_id'=>$parent->id,'name'=>$parent->name.' Sub','slug'=>$parent->slug.'-sub','is_active'=>true]);
        }

        $brands = collect(['Acme','Nova','Prime'])->map(fn ($name) => Brand::query()->create(['name'=>$name,'slug'=>Str::slug($name),'is_active'=>true]));

        $sellerUsers = collect([1,2])->map(function ($idx) {
            return User::query()->create([
                'name' => 'Seller '.$idx,
                'email' => 'seller'.$idx.'@example.com',
                'password' => 'password',
                'role' => 'seller',
                'status' => 'active',
            ]);
        });

        $sellers = $sellerUsers->map(fn ($user, $i) => Seller::query()->create([
            'user_id' => $user->id,
            'shop_name' => 'Shop '.($i + 1),
            'shop_slug' => 'shop-'.($i + 1),
            'status' => 'active',
            'is_verified' => true,
        ]));

        $customers = collect(range(1, 10))->map(fn ($i) => User::query()->create([
            'name' => 'Customer '.$i,
            'email' => 'customer'.$i.'@example.com',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]));

        $products = collect(range(1, 20))->map(function ($i) use ($sellers, $parentCategories, $brands) {
            $type = $i % 3 === 0 ? 'variable' : 'simple';
            $product = Product::query()->create([
                'seller_id' => $sellers[$i % 2]->id,
                'name' => 'Demo Product '.$i,
                'slug' => 'demo-product-'.$i,
                'type' => $type,
                'category_id' => $parentCategories[$i % 5]->id,
                'brand_id' => $brands[$i % 3]->id,
                'is_published' => true,
                'is_approved' => true,
                'is_featured' => $i <= 4,
            ]);

            ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => 'SKU-'.$i,
                'price' => rand(10, 200),
                'sale_price' => $i % 4 === 0 ? rand(5, 50) : null,
                'stock' => rand(5, 100),
            ]);

            return $product;
        });

        foreach (range(1, 5) as $i) {
            $customer = $customers[$i - 1];
            $product = $products[$i - 1];
            $variant = $product->variants()->first();
            $order = Order::query()->create([
                'order_number' => '#ORD-2026-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'shipping_name' => $customer->name,
                'shipping_phone' => '1234567890',
                'shipping_address' => 'Demo Address',
                'shipping_city' => 'City',
                'shipping_country' => 'Country',
                'subtotal' => $variant->price,
                'total' => $variant->price,
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'order_status' => 'delivered',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'seller_id' => $product->seller_id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price' => $variant->price,
                'subtotal' => $variant->price,
                'item_status' => 'delivered',
            ]);
        }

        foreach (range(1, 10) as $i) {
            Review::query()->create([
                'product_id' => $products[$i - 1]->id,
                'user_id' => $customers[$i - 1]->id,
                'rating' => rand(4, 5),
                'comment' => 'Great product '.$i,
                'is_verified_purchase' => true,
            ]);
        }

        $blogCategory = BlogCategory::query()->create(['name'=>'News','slug'=>'news','is_active'=>true]);
        foreach (range(1, 4) as $i) {
            BlogPost::query()->create([
                'user_id' => $sellerUsers[0]->id,
                'blog_category_id' => $blogCategory->id,
                'title' => 'Blog Post '.$i,
                'slug' => 'blog-post-'.$i,
                'content' => 'Demo blog content '.$i,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
