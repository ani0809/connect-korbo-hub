<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProductsJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\ProductExportService;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ImportExportController extends Controller
{
    public function productImportPage(): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $sellers = Seller::query()->orderBy('shop_name')->get(['id', 'shop_name']);

        return view('admin.import-export.products', compact('categories', 'sellers'));
    }

    public function importProducts(Request $request, ProductImportService $importService): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'update_existing' => 'boolean',
        ]);

        $file = $request->file('csv_file');
        $path = $file->storeAs('imports', 'products-import-'.now()->format('YmdHis').'.csv', 'local');
        $fullPath = storage_path('app/'.$path);
        $updateExisting = $request->boolean('update_existing');

        if ($file->getSize() > 500000) {
            ImportProductsJob::dispatch($path, $updateExisting, (int) auth()->id());

            return response()->json([
                'success' => true,
                'queued' => true,
                'message' => 'Large file detected. Import queued. Check import status in a few minutes.',
            ]);
        }

        $result = $importService->importFromCsv($fullPath, $updateExisting);
        Cache::put('import_products_result_'.auth()->id(), $result, now()->addDay());
        @unlink($fullPath);

        return response()->json($result);
    }

    public function importStatus(): JsonResponse
    {
        $result = Cache::get('import_products_result_'.auth()->id());

        return response()->json(['result' => $result]);
    }

    public function exportProducts(Request $request, ProductExportService $exportService): Response
    {
        $filters = $request->only(['category', 'seller', 'status', 'type', 'date_from', 'date_to']);
        $csv = $exportService->exportToCsv($filters);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function downloadTemplate(ProductImportService $importService): Response
    {
        $csv = $importService->generateTemplate();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products-import-template.csv"',
        ]);
    }

    public function exportOrders(Request $request): Response
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $buffer = fopen('php://temp', 'r+');
        if ($buffer === false) {
            return response('', 500);
        }

        fputcsv($buffer, [
            'Order Number', 'Date', 'Customer Name', 'Customer Email', 'Phone', 'Shipping Address',
            'City', 'Country', 'Items', 'Subtotal', 'Shipping', 'Discount', 'Tax', 'Total',
            'Payment Method', 'Payment Status', 'Order Status',
        ]);

        Order::query()
            ->with(['items', 'user'])
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when($status !== 'all', fn ($q) => $q->where('order_status', $status))
            ->orderBy('id')
            ->chunk(500, function ($orders) use ($buffer): void {
                foreach ($orders as $order) {
                    $itemsSummary = $order->items
                        ->map(fn ($item) => $item->product_name.' x'.$item->quantity)
                        ->implode(', ');
                    fputcsv($buffer, [
                        $order->order_number,
                        $order->created_at?->format('Y-m-d H:i'),
                        $order->shipping_name ?? $order->user?->name,
                        $order->guest_email ?? $order->user?->email,
                        $order->shipping_phone,
                        $order->shipping_address,
                        $order->shipping_city,
                        $order->shipping_country,
                        $itemsSummary,
                        $order->subtotal,
                        $order->shipping_cost,
                        $order->coupon_discount,
                        $order->tax_amount,
                        $order->total,
                        $order->payment_method,
                        $order->payment_status,
                        $order->order_status,
                    ]);
                }
            });

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return response($csv !== false ? $csv : '', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="orders-'.$from.'-to-'.$to.'.csv"',
        ]);
    }

    public function exportCustomers(): Response
    {
        $buffer = fopen('php://temp', 'r+');
        if ($buffer === false) {
            return response('', 500);
        }

        fputcsv($buffer, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Total Orders', 'Total Spent', 'Joined Date', 'Last Login']);

        User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->orderBy('id')
            ->chunk(500, function ($customers) use ($buffer): void {
                foreach ($customers as $customer) {
                    fputcsv($buffer, [
                        $customer->id,
                        $customer->name,
                        $customer->email,
                        $customer->phone,
                        $customer->status,
                        $customer->orders_count,
                        $customer->total_spent ?? 0,
                        $customer->created_at?->format('Y-m-d'),
                        $customer->last_login_at?->format('Y-m-d H:i') ?? 'Never',
                    ]);
                }
            });

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return response($csv !== false ? $csv : '', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customers-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}
