<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImportService
{
    /** @var list<string> */
    private array $errors = [];

    private int $imported = 0;

    private int $skipped = 0;

    private int $updated = 0;

    /**
     * @return array{success: bool, message?: string, imported?: int, updated?: int, skipped?: int, errors?: list<string>, total_processed?: int}
     */
    public function importFromCsv(string $filePath, bool $updateExisting = false): array
    {
        $this->errors = [];
        $this->imported = 0;
        $this->skipped = 0;
        $this->updated = 0;

        $rows = $this->parseCsv($filePath);
        if ($rows === []) {
            return ['success' => false, 'message' => 'Empty or invalid file'];
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows));
        $requiredColumns = ['name', 'category', 'price', 'stock'];

        foreach ($requiredColumns as $col) {
            if (! in_array($col, $header, true)) {
                return ['success' => false, 'message' => "Missing required column: {$col}"];
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2;
            if (count($row) !== count($header)) {
                $this->errors[] = "Line {$lineNum}: Column count mismatch";
                $this->skipped++;

                continue;
            }

            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }

            if ($this->rowIsEmpty($data)) {
                continue;
            }

            $validation = $this->validateRow($data, $lineNum);
            if (! $validation['valid']) {
                $this->errors[] = $validation['message'];
                $this->skipped++;

                continue;
            }

            try {
                $this->processRow($data, $updateExisting);
            } catch (\Throwable $e) {
                $this->errors[] = "Line {$lineNum}: ".$e->getMessage();
                $this->skipped++;
            }
        }

        return [
            'success' => true,
            'imported' => $this->imported,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total_processed' => count($rows),
        ];
    }

    /**
     * @param  array<string, string>  $data
     */
    private function rowIsEmpty(array $data): bool
    {
        foreach ($data as $v) {
            if ($v !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $data
     */
    private function processRow(array $data, bool $updateExisting): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => Str::slug($data['category'])],
            [
                'name' => $data['category'],
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        $brand = null;
        if (! empty($data['brand'])) {
            $brand = Brand::query()->firstOrCreate(
                ['slug' => Str::slug($data['brand'])],
                ['name' => $data['brand'], 'is_active' => true]
            );
        }

        $skuInput = trim($data['sku'] ?? '');
        $existingProduct = null;
        if ($skuInput !== '') {
            $existingProduct = Product::query()->where('sku', $skuInput)->first();
            if (! $existingProduct) {
                $existingProduct = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', $skuInput))->first();
            }
        }

        if ($existingProduct && ! $updateExisting) {
            $this->skipped++;

            return;
        }

        $sellerId = Seller::query()->value('id');

        $productData = [
            'seller_id' => $sellerId,
            'name' => trim($data['name']),
            'sku' => $skuInput !== '' ? $skuInput : null,
            'type' => 'simple',
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'unit' => $data['unit'] !== '' ? $data['unit'] : 'pc',
            'weight' => is_numeric($data['weight'] ?? null) ? $data['weight'] : null,
            'is_published' => in_array(strtolower($data['published'] ?? 'yes'), ['yes', '1', 'true'], true),
            'is_approved' => true,
        ];

        $productData['slug'] = Str::slug($productData['name']);
        $originalSlug = $productData['slug'];
        $n = 1;
        while (Product::query()->where('slug', $productData['slug'])->when($existingProduct, fn ($q) => $q->where('id', '!=', $existingProduct->id))->exists()) {
            $productData['slug'] = $originalSlug.'-'.$n++;
        }

        if (! empty($data['image_url'])) {
            $imagePath = $this->downloadImage($data['image_url'], $productData['slug']);
            if ($imagePath) {
                $productData['thumbnail'] = $imagePath;
            }
        }

        if ($existingProduct && $updateExisting) {
            $existingProduct->update($productData);
            $product = $existingProduct;
            $this->updated++;
        } else {
            $product = Product::query()->create($productData);
            $this->imported++;
        }

        $variantSku = $skuInput !== '' ? $skuInput : 'IMP-'.$product->id.'-'.Str::lower(Str::random(6));
        $try = 0;
        while (ProductVariant::query()->where('sku', $variantSku)->where('product_id', '!=', $product->id)->exists() && $try < 20) {
            $variantSku = 'IMP-'.$product->id.'-'.Str::lower(Str::random(8));
            $try++;
        }

        $variant = ProductVariant::query()->where('product_id', $product->id)->orderBy('id')->first();
        if (! $variant) {
            $variant = new ProductVariant(['product_id' => $product->id]);
        }
        $variant->fill([
            'sku' => $variantSku,
            'price' => (float) ($data['price'] ?? 0),
            'sale_price' => ! empty($data['sale_price']) ? (float) $data['sale_price'] : null,
            'stock' => (int) ($data['stock'] ?? 0),
        ]);
        $variant->save();

        $product->categories()->syncWithoutDetaching([$category->id]);
    }

    /**
     * @param  array<string, string>  $data
     * @return array{valid: bool, message?: string}
     */
    private function validateRow(array $data, int $lineNum): array
    {
        if (trim($data['name'] ?? '') === '') {
            return ['valid' => false, 'message' => "Line {$lineNum}: Name is required"];
        }

        if (! is_numeric($data['price'] ?? null) || (float) $data['price'] < 0) {
            return ['valid' => false, 'message' => "Line {$lineNum}: Invalid price"];
        }

        return ['valid' => true];
    }

    private function downloadImage(string $url, string $name): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $pathPart = parse_url($url, PHP_URL_PATH) ?: '';
            $extension = pathinfo($pathPart, PATHINFO_EXTENSION) ?: 'jpg';
            $extension = preg_replace('/[^a-z0-9]/i', '', $extension) ?: 'jpg';

            $filename = Str::slug($name).'-'.Str::random(6).'.'.$extension;
            $path = 'uploads/products/'.$filename;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    public function generateTemplate(): string
    {
        $headers = ['name', 'sku', 'category', 'brand', 'price', 'sale_price', 'stock', 'unit', 'weight', 'description', 'short_description', 'image_url', 'published'];
        $sampleRow = ['Sample Product', 'SKU-001', 'Electronics', 'Samsung', '29.99', '24.99', '100', 'pc', '0.5', 'Full product description here', 'Short description', 'https://example.com/image.jpg', 'yes'];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        fputcsv($output, $sampleRow);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv !== false ? $csv : '';
    }

    /**
     * @return list<list<string>>
     */
    private function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }
}
