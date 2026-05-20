<?php

namespace App\Services;

use App\Models\Product;

class ProductExportService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportToCsv(array $filters = []): string
    {
        $buffer = fopen('php://temp', 'r+');
        if ($buffer === false) {
            return '';
        }

        fputcsv($buffer, [
            'ID', 'Name', 'SKU', 'Type', 'Category', 'Brand', 'Seller',
            'Price', 'Sale Price', 'Stock', 'Unit', 'Weight',
            'Description', 'Short Description', 'Published', 'Featured',
            'Rating', 'Total Reviews', 'Total Sales', 'Created At',
        ]);

        Product::query()
            ->with(['category', 'brand', 'variants', 'seller'])
            ->filter($filters)
            ->orderBy('id')
            ->chunk(500, function ($products) use ($buffer): void {
                foreach ($products as $product) {
                    $mainVariant = $product->variants->first();
                    fputcsv($buffer, [
                        $product->id,
                        $product->name,
                        $product->sku ?? $mainVariant?->sku,
                        $product->type,
                        $product->category?->name,
                        $product->brand?->name,
                        $product->seller?->shop_name,
                        $mainVariant?->price ?? $product->main_price,
                        $mainVariant?->sale_price,
                        $product->variants->sum('stock'),
                        $product->unit,
                        $product->weight,
                        strip_tags((string) ($product->description ?? '')),
                        $product->short_description,
                        $product->is_published ? 'yes' : 'no',
                        $product->is_featured ? 'yes' : 'no',
                        $product->rating,
                        $product->total_reviews,
                        $product->total_sales,
                        $product->created_at?->format('Y-m-d') ?? '',
                    ]);
                }
            });

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $csv !== false ? $csv : '';
    }
}
