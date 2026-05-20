<?php

namespace App\Services\Migration;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MigrationJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrationService
{
    private array $log = [];
    private array $stats = [
        'products' => ['total' => 0, 'imported' => 0, 'failed' => 0, 'skipped' => 0],
        'customers' => ['total' => 0, 'imported' => 0, 'failed' => 0],
        'orders' => ['total' => 0, 'imported' => 0, 'failed' => 0],
        'categories' => ['total' => 0, 'imported' => 0, 'failed' => 0],
    ];

    /** @var array<int, array{type:string,id:int}> */
    private array $importedRefs = [];

    public function migrateFromWooCommerce(array $config, int $jobId): array
    {
        $method = (string) ($config['method'] ?? 'api');
        if ($method === 'api') {
            return $this->migrateViaWooApi($config, $jobId);
        }

        $this->log('error', 'Only API migration is enabled in this build.');
        return $this->stats;
    }

    public function testWooApi(array $config): array
    {
        $baseUrl = rtrim((string) ($config['site_url'] ?? ''), '/');
        $headers = $this->buildAuthHeaders($config);

        $p = Http::withHeaders($headers)->timeout(10)->get("{$baseUrl}/wp-json/wc/v3/products", ['per_page' => 1]);
        $c = Http::withHeaders($headers)->timeout(10)->get("{$baseUrl}/wp-json/wc/v3/customers", ['per_page' => 1]);

        if (! $p->successful() || ! $c->successful()) {
            return ['success' => false, 'message' => 'WooCommerce API credentials are invalid or blocked.'];
        }

        return [
            'success' => true,
            'products' => (int) ($p->header('X-WP-Total') ?? 0),
            'customers' => (int) ($c->header('X-WP-Total') ?? 0),
        ];
    }

    private function migrateViaWooApi(array $config, int $jobId): array
    {
        $baseUrl = rtrim((string) $config['site_url'], '/');
        $headers = $this->buildAuthHeaders($config);
        $dryRun = (bool) ($config['dry_run'] ?? false);
        $options = (array) ($config['options'] ?? []);

        if (($options['categories'] ?? true) === true) {
            $this->migrateCategoriesFromApi($baseUrl, $headers, $jobId, $dryRun);
        }
        if (($options['products'] ?? true) === true) {
            $this->migrateProductsFromApi($baseUrl, $headers, $jobId, $dryRun);
        }
        if (($options['customers'] ?? true) === true) {
            $this->migrateCustomersFromApi($baseUrl, $headers, $jobId, $dryRun);
        }
        if (($options['orders'] ?? false) === true) {
            $this->migrateOrdersFromApi($baseUrl, $headers, $jobId, $dryRun);
        }

        $this->log('info', 'Imported refs: '.json_encode($this->importedRefs));
        return $this->stats;
    }

    private function migrateCategoriesFromApi(string $baseUrl, array $headers, int $jobId, bool $dryRun): void
    {
        $resp = Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/products/categories", ['per_page' => 100, 'page' => 1]);
        if (! $resp->successful()) {
            $this->log('error', 'Failed to fetch categories.');
            return;
        }

        $totalPages = max(1, (int) ($resp->header('X-WP-TotalPages') ?? 1));
        for ($page = 1; $page <= $totalPages; $page++) {
            $rows = $page === 1 ? $resp->json() : Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/products/categories", ['per_page' => 100, 'page' => $page])->json();
            foreach ((array) $rows as $cat) {
                $this->stats['categories']['total']++;
                try {
                    if (! $dryRun) {
                        Category::query()->firstOrCreate(
                            ['slug' => Str::slug((string) ($cat['slug'] ?? $cat['name'] ?? 'category'))],
                            ['name' => (string) ($cat['name'] ?? 'Category'), 'is_active' => true]
                        );
                    }
                    $this->stats['categories']['imported']++;
                } catch (\Throwable $e) {
                    $this->stats['categories']['failed']++;
                    $this->log('error', 'Category failed: '.$e->getMessage());
                }
            }
            $this->updateJobProgress($jobId);
        }
    }

    private function migrateProductsFromApi(string $baseUrl, array $headers, int $jobId, bool $dryRun): void
    {
        $first = Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/products", ['per_page' => 20, 'page' => 1, 'status' => 'publish']);
        if (! $first->successful()) {
            $this->log('error', 'Failed to fetch products.');
            return;
        }
        $totalPages = max(1, (int) ($first->header('X-WP-TotalPages') ?? 1));
        for ($page = 1; $page <= $totalPages; $page++) {
            $products = $page === 1 ? $first->json() : Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/products", ['per_page' => 20, 'page' => $page, 'status' => 'publish'])->json();
            foreach ((array) $products as $product) {
                $this->migrateProduct((array) $product, $dryRun);
                $this->updateJobProgress($jobId);
            }
        }
    }

    private function migrateCustomersFromApi(string $baseUrl, array $headers, int $jobId, bool $dryRun): void
    {
        $first = Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/customers", ['per_page' => 50, 'page' => 1]);
        if (! $first->successful()) {
            $this->log('error', 'Failed to fetch customers.');
            return;
        }
        $totalPages = max(1, (int) ($first->header('X-WP-TotalPages') ?? 1));
        for ($page = 1; $page <= $totalPages; $page++) {
            $customers = $page === 1 ? $first->json() : Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/customers", ['per_page' => 50, 'page' => $page])->json();
            foreach ((array) $customers as $customer) {
                $this->migrateCustomer((array) $customer, $dryRun);
            }
            $this->updateJobProgress($jobId);
        }
    }

    private function migrateOrdersFromApi(string $baseUrl, array $headers, int $jobId, bool $dryRun): void
    {
        $first = Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/orders", ['per_page' => 20, 'page' => 1]);
        if (! $first->successful()) {
            $this->log('error', 'Failed to fetch orders.');
            return;
        }
        $totalPages = max(1, (int) ($first->header('X-WP-TotalPages') ?? 1));
        for ($page = 1; $page <= $totalPages; $page++) {
            $orders = $page === 1 ? $first->json() : Http::withHeaders($headers)->timeout(20)->get("{$baseUrl}/wp-json/wc/v3/orders", ['per_page' => 20, 'page' => $page])->json();
            foreach ((array) $orders as $order) {
                $this->migrateOrder((array) $order, $dryRun);
            }
            $this->updateJobProgress($jobId);
        }
    }

    private function migrateProduct(array $product, bool $dryRun = false): void
    {
        $this->stats['products']['total']++;

        try {
            $sku = (string) ($product['sku'] ?? '');
            if ($sku !== '' && Product::query()->where('sku', $sku)->exists()) {
                $this->stats['products']['skipped']++;
                return;
            }

            $name = (string) ($product['name'] ?? 'Imported Product');
            $categoryId = null;
            if (! empty($product['categories'][0]['name'])) {
                $cat = Category::query()->firstOrCreate(
                    ['slug' => Str::slug((string) $product['categories'][0]['name'])],
                    ['name' => (string) $product['categories'][0]['name'], 'is_active' => true]
                );
                $categoryId = $cat->id;
            }

            $thumbUrl = (string) ($product['images'][0]['src'] ?? '');
            $thumbnail = $dryRun ? null : $this->downloadImage($thumbUrl, Str::slug($name));

            if (! $dryRun) {
                $payload = [
                    'name' => $name,
                    'slug' => $this->uniqueSlug(Str::slug($name)),
                    'sku' => $sku ?: null,
                    'type' => ((string) ($product['type'] ?? 'simple')) === 'variable' ? 'variable' : 'simple',
                    'category_id' => $categoryId,
                    'brand_id' => null,
                    'description' => (string) ($product['description'] ?? ''),
                    'short_description' => (string) ($product['short_description'] ?? ''),
                    'thumbnail' => $thumbnail,
                    'is_published' => true,
                    'is_approved' => true,
                    'weight' => (float) ($product['weight'] ?? 0),
                ];
                if (Schema::hasColumn('products', 'meta')) {
                    $payload['meta'] = json_encode(['woo_id' => $product['id'] ?? null, 'imported_at' => now()->toISOString()]);
                }
                $new = Product::query()->create($payload);

                ProductVariant::query()->create([
                    'product_id' => $new->id,
                    'sku' => $sku ?: null,
                    'price' => (float) (($product['regular_price'] ?? '') !== '' ? $product['regular_price'] : ($product['price'] ?? 0)),
                    'sale_price' => (($product['sale_price'] ?? '') !== '') ? (float) $product['sale_price'] : null,
                    'stock' => (int) ($product['stock_quantity'] ?? 0),
                    'is_default' => true,
                ]);

                foreach (array_slice((array) ($product['images'] ?? []), 1, 8) as $img) {
                    $imgUrl = (string) ($img['src'] ?? '');
                    if ($imgUrl === '') {
                        continue;
                    }
                    dispatch(function () use ($new, $imgUrl, $name): void {
                        $path = $this->downloadImage($imgUrl, Str::slug($name).'-gallery');
                        if ($path) {
                            ProductImage::query()->create(['product_id' => $new->id, 'image' => $path]);
                        }
                    })->afterResponse();
                }
                $this->importedRefs[] = ['type' => 'product', 'id' => $new->id];
            }

            $this->stats['products']['imported']++;
            $this->log('success', 'Product imported: '.$name);
        } catch (\Throwable $e) {
            $this->stats['products']['failed']++;
            $this->log('error', 'Product failed: '.($product['name'] ?? 'unknown').' - '.$e->getMessage());
        }
    }

    private function migrateCustomer(array $customer, bool $dryRun = false): void
    {
        $this->stats['customers']['total']++;
        try {
            $email = (string) ($customer['email'] ?? '');
            if ($email === '') {
                $this->stats['customers']['failed']++;
                return;
            }
            if (User::query()->where('email', $email)->exists()) {
                return;
            }
            if (! $dryRun) {
                $user = User::query()->create([
                    'name' => trim(((string) ($customer['first_name'] ?? '')).' '.((string) ($customer['last_name'] ?? ''))) ?: 'Customer',
                    'email' => $email,
                    'phone' => (string) ($customer['billing']['phone'] ?? ''),
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'customer',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'created_at' => ! empty($customer['date_created']) ? Carbon::parse($customer['date_created']) : now(),
                ]);

                if (! empty($customer['shipping']['address_1'])) {
                    Address::query()->create([
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'phone' => (string) ($customer['billing']['phone'] ?? ''),
                        'address_line1' => (string) ($customer['shipping']['address_1'] ?? ''),
                        'address_line2' => (string) ($customer['shipping']['address_2'] ?? ''),
                        'city' => (string) ($customer['shipping']['city'] ?? ''),
                        'country' => (string) ($customer['shipping']['country'] ?? 'BD'),
                        'email' => $email,
                        'is_default_shipping' => true,
                    ]);
                }
                $this->importedRefs[] = ['type' => 'customer', 'id' => $user->id];
            }
            $this->stats['customers']['imported']++;
        } catch (\Throwable $e) {
            $this->stats['customers']['failed']++;
            $this->log('error', 'Customer failed: '.$e->getMessage());
        }
    }

    private function migrateOrder(array $wooOrder, bool $dryRun = false): void
    {
        $this->stats['orders']['total']++;
        try {
            $orderNumber = 'WOO-'.(string) ($wooOrder['id'] ?? Str::random(6));
            if (Order::query()->where('order_number', $orderNumber)->exists()) {
                return;
            }

            $user = null;
            $email = (string) ($wooOrder['billing']['email'] ?? '');
            if ($email !== '') {
                $user = User::query()->where('email', $email)->first();
            }
            $orderStatus = match ((string) ($wooOrder['status'] ?? 'pending')) {
                'completed', 'processing' => 'delivered',
                'cancelled' => 'cancelled',
                'refunded' => 'returned',
                'pending', 'on-hold' => 'pending',
                default => 'delivered',
            };
            $paymentStatus = in_array((string) ($wooOrder['status'] ?? ''), ['completed', 'processing', 'refunded'], true) ? 'paid' : 'pending';

            if (! $dryRun) {
                $order = Order::query()->create([
                    'order_number' => $orderNumber,
                    'user_id' => $user?->id,
                    'guest_email' => $user ? null : $email,
                    'shipping_name' => trim(((string) ($wooOrder['shipping']['first_name'] ?? '')).' '.((string) ($wooOrder['shipping']['last_name'] ?? ''))),
                    'shipping_phone' => (string) ($wooOrder['billing']['phone'] ?? ''),
                    'shipping_address' => (string) ($wooOrder['shipping']['address_1'] ?? ''),
                    'shipping_city' => (string) ($wooOrder['shipping']['city'] ?? ''),
                    'shipping_country' => (string) ($wooOrder['shipping']['country'] ?? 'BD'),
                    'subtotal' => (float) ($wooOrder['subtotal'] ?? 0),
                    'shipping_cost' => (float) ($wooOrder['shipping_total'] ?? 0),
                    'tax_amount' => (float) ($wooOrder['total_tax'] ?? 0),
                    'coupon_discount' => (float) ($wooOrder['discount_total'] ?? 0),
                    'total' => (float) ($wooOrder['total'] ?? 0),
                    'payment_method' => (string) ($wooOrder['payment_method'] ?? 'other'),
                    'payment_status' => $paymentStatus,
                    'order_status' => $orderStatus,
                    'notes' => (string) ($wooOrder['customer_note'] ?? ''),
                    'created_at' => ! empty($wooOrder['date_created']) ? Carbon::parse($wooOrder['date_created']) : now(),
                ]);
                foreach ((array) ($wooOrder['line_items'] ?? []) as $item) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_name' => (string) ($item['name'] ?? 'Product'),
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'unit_price' => (float) ($item['price'] ?? 0),
                        'subtotal' => (float) ($item['total'] ?? 0),
                    ]);
                }
                $this->importedRefs[] = ['type' => 'order', 'id' => $order->id];
            }
            $this->stats['orders']['imported']++;
        } catch (\Throwable $e) {
            $this->stats['orders']['failed']++;
            $this->log('error', 'Order failed: WOO-'.($wooOrder['id'] ?? '?').' - '.$e->getMessage());
        }
    }

    private function buildAuthHeaders(array $config): array
    {
        $key = (string) ($config['consumer_key'] ?? '');
        $secret = (string) ($config['consumer_secret'] ?? '');
        return ['Authorization' => 'Basic '.base64_encode($key.':'.$secret)];
    }

    private function downloadImage(string $url, string $name): ?string
    {
        if ($url === '') {
            return null;
        }
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->successful()) {
                return null;
            }
            $ext = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $ext = strtolower((string) preg_replace('/\?.*/', '', $ext));
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $ext = 'jpg';
            }
            $filename = Str::slug($name).'-'.Str::random(6).'.'.$ext;
            $path = 'uploads/migrated/'.$filename;
            Storage::disk('public')->put($path, $response->body());
            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function uniqueSlug(string $slug): string
    {
        $original = $slug ?: 'product';
        $count = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$count++;
        }
        return $slug;
    }

    private function log(string $level, string $message): void
    {
        $this->log[] = ['level' => $level, 'message' => $message, 'time' => now()->format('H:i:s')];
    }

    private function updateJobProgress(int $jobId): void
    {
        if ($jobId <= 0) {
            return;
        }
        MigrationJob::query()->where('id', $jobId)->update([
            'stats' => $this->stats,
            'log' => json_encode($this->log, JSON_UNESCAPED_UNICODE),
        ]);
    }
}

