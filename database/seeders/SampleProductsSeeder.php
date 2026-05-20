<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleProductsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['name' => 'Zenith Pro Running Shoes', 'category' => 'Footwear', 'brand' => 'StrideX', 'price' => 89.99, 'sale' => 79.99, 'stock' => 52],
            ['name' => 'AeroLite Casual Sneakers', 'category' => 'Footwear', 'brand' => 'StrideX', 'price' => 64.50, 'sale' => 59.00, 'stock' => 73],
            ['name' => 'CoreFit Compression T-Shirt', 'category' => 'Apparel', 'brand' => 'FlexWear', 'price' => 24.99, 'sale' => 19.99, 'stock' => 110],
            ['name' => 'Urban Cargo Joggers', 'category' => 'Apparel', 'brand' => 'FlexWear', 'price' => 39.99, 'sale' => 34.99, 'stock' => 68],
            ['name' => 'HydraSteel Water Bottle 1L', 'category' => 'Accessories', 'brand' => 'PeakGear', 'price' => 18.50, 'sale' => 15.99, 'stock' => 140],
            ['name' => 'TrailBlaze Backpack 28L', 'category' => 'Accessories', 'brand' => 'PeakGear', 'price' => 54.00, 'sale' => 47.50, 'stock' => 37],
            ['name' => 'Pulse Smart Fitness Watch', 'category' => 'Electronics', 'brand' => 'NovaTech', 'price' => 129.99, 'sale' => 114.99, 'stock' => 26],
            ['name' => 'ZenPods Wireless Earbuds', 'category' => 'Electronics', 'brand' => 'NovaTech', 'price' => 79.99, 'sale' => 69.99, 'stock' => 61],
            ['name' => 'Recovery Foam Roller', 'category' => 'Fitness', 'brand' => 'CoreMotion', 'price' => 29.99, 'sale' => 24.99, 'stock' => 48],
            ['name' => 'PowerGrip Resistance Band Set', 'category' => 'Fitness', 'brand' => 'CoreMotion', 'price' => 34.99, 'sale' => 29.99, 'stock' => 82],
        ];

        foreach ($catalog as $index => $item) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                ['name' => $item['category'], 'is_active' => true, 'is_featured' => false]
            );

            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($item['brand'])],
                ['name' => $item['brand'], 'is_active' => true, 'is_featured' => false]
            );

            $sku = 'SMP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $slug = Str::slug($item['name']).'-sample';

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $item['name'],
                    'slug' => $slug,
                    'sku' => $sku,
                    'type' => 'simple',
                    'description' => 'Sample product seeded for admin UI testing. Includes realistic pricing, stock, and metadata.',
                    'short_description' => 'High-quality sample product for storefront and admin demos.',
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'tags' => ['sample', 'demo', 'seed'],
                    'tax_rate' => 5,
                    'tax_type' => 'percent',
                    'shipping_type' => 'flat',
                    'shipping_cost' => 4.99,
                    'is_featured' => $index % 3 === 0,
                    'is_todays_deal' => $index % 2 === 0,
                    'is_published' => true,
                    'is_approved' => true,
                    'club_point' => 10 + $index,
                    'meta_title' => $item['name'].' | Demo Store',
                    'meta_description' => 'Buy '.$item['name'].' at a great price.',
                    'meta_keywords' => strtolower(str_replace(' ', ',', $item['name'])).',sample,demo',
                    'total_sales' => random_int(20, 240),
                    'rating' => number_format(random_int(38, 50) / 10, 2, '.', ''),
                    'total_reviews' => random_int(5, 90),
                ]
            );

            $product->categories()->syncWithoutDetaching([$category->id]);

            ProductVariant::updateOrCreate(
                ['sku' => $sku.'-V1'],
                [
                    'product_id' => $product->id,
                    'price' => $item['price'],
                    'sale_price' => $item['sale'],
                    'stock' => $item['stock'],
                    'low_stock_threshold' => 8,
                    'sort_order' => 0,
                ]
            );
        }
    }
}

