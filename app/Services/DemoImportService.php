<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DemoImportService
{
    private array $demos = [
        'classic-shop' => ['name' => 'Classic Shop', 'description' => 'General merchandise store', 'preview' => '/images/demos/classic.jpg', 'seeder' => 'ClassicDemoSeeder'],
        'electronics-store' => ['name' => 'Electronics Store', 'description' => 'Tech and gadgets marketplace', 'preview' => '/images/demos/electronics.jpg', 'seeder' => 'ElectronicsDemoSeeder'],
        'grocery-market' => ['name' => 'Grocery Market', 'description' => 'Fresh produce and food items', 'preview' => '/images/demos/grocery.jpg', 'seeder' => 'GroceryDemoSeeder'],
        'multi-vendor' => ['name' => 'Multi-Vendor Market', 'description' => 'Marketplace with multiple sellers', 'preview' => '/images/demos/multivendor.jpg', 'seeder' => 'MultiVendorDemoSeeder'],
        'minimal-store' => ['name' => 'Minimal Store', 'description' => 'Clean and minimal design', 'preview' => '/images/demos/minimal.jpg', 'seeder' => 'MinimalDemoSeeder'],
    ];

    public function getDemos(): array { return $this->demos; }

    public function import(string $demoKey): \Generator
    {
        if (!isset($this->demos[$demoKey])) throw new \RuntimeException('Invalid demo selection');
        yield ['message' => 'Clearing existing data...']; $this->clearExistingData();
        yield ['message' => 'Importing categories...']; $this->importCategories($demoKey);
        yield ['message' => 'Importing brands...']; $this->importBrands($demoKey);
        yield ['message' => 'Importing products...']; $this->importProducts($demoKey);
        yield ['message' => 'Importing demo images...']; $this->copyDemoImages($demoKey);
        yield ['message' => 'Applying theme settings...']; $this->applyThemeSettings($demoKey);
        yield ['message' => 'Setting up homepage...']; $this->setupHomepage($demoKey);
        yield ['message' => 'Complete!', 'done' => true];
    }

    private function clearExistingData(): void
    {
        $hasRealOrders = Order::query()->where('payment_status', 'paid')->exists();
        if ($hasRealOrders) return;
        DB::table('order_items')->truncate(); DB::table('orders')->truncate(); DB::table('cart_items')->truncate(); DB::table('carts')->truncate(); DB::table('product_images')->truncate(); DB::table('product_variants')->truncate(); DB::table('products')->truncate(); DB::table('categories')->truncate(); DB::table('brands')->truncate();
    }

    private function importCategories(string $demo): void
    {
        foreach ($this->loadDemoData($demo, 'categories') as $category) Category::query()->create($category);
    }
    private function importBrands(string $demo): void
    {
        foreach ($this->loadDemoData($demo, 'brands') as $brand) Brand::query()->create($brand);
    }
    private function importProducts(string $demo): void
    {
        foreach ($this->loadDemoData($demo, 'products') as $product) {
            $variants = $product['variants'] ?? []; $images = $product['images'] ?? []; unset($product['variants'], $product['images']);
            $prod = Product::query()->create($product);
            foreach ($variants as $variant) $prod->variants()->create($variant);
            foreach ($images as $image) $prod->images()->create($image);
        }
    }

    private function copyDemoImages(string $demo): void
    {
        $source = resource_path("demo-data/{$demo}/images"); $dest = public_path('uploads/demo');
        if (is_dir($source)) $this->copyDirectory($source, $dest);
    }

    private function applyThemeSettings(string $demo): void
    {
        $settings = $this->loadDemoData($demo, 'settings'); if ($settings) Setting::setMany($settings);
    }

    private function setupHomepage(string $demo): void
    {
        $sections = $this->loadDemoData($demo, 'homepage'); if ($sections) Setting::set('homepage_sections_json', json_encode($sections));
    }

    private function loadDemoData(string $demo, string $type): array
    {
        $file = resource_path("demo-data/{$demo}/{$type}.json"); if (!file_exists($file)) return [];
        return json_decode((string) file_get_contents($file), true) ?? [];
    }

    private function copyDirectory(string $from, string $to): void
    {
        if (!is_dir($to)) mkdir($to, 0755, true);
        foreach (array_diff(scandir($from) ?: [], ['.', '..']) as $file) {
            $src = "$from/$file"; $dst = "$to/$file";
            is_dir($src) ? $this->copyDirectory($src, $dst) : copy($src, $dst);
        }
    }
}
