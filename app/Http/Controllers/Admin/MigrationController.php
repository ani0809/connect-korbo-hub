<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunMigrationJob;
use App\Models\MigrationJob;
use App\Services\Migration\MigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MigrationController extends Controller
{
    public function index(): View
    {
        $jobs = MigrationJob::query()->with('creator')->latest()->paginate(15);
        return view('admin.migration.index', compact('jobs'));
    }

    public function wizard(): View
    {
        return view('admin.migration.wizard');
    }

    public function testConnection(Request $request, MigrationService $service): JsonResponse
    {
        $request->validate([
            'source' => 'required|string|in:woocommerce,shopify,opencart,csv,custom',
            'site_url' => 'required_if:source,woocommerce|nullable|url',
            'consumer_key' => 'required_if:source,woocommerce|nullable|string',
            'consumer_secret' => 'required_if:source,woocommerce|nullable|string',
        ]);

        if ($request->string('source')->value() !== 'woocommerce') {
            return response()->json(['success' => true, 'products' => 0, 'customers' => 0]);
        }

        return response()->json($service->testWooApi($request->all()));
    }

    public function startMigration(Request $request): JsonResponse
    {
        $request->validate([
            'source_type' => 'required|string|in:woocommerce,shopify,opencart,csv,custom',
            'site_url' => 'required_if:source_type,woocommerce|nullable|url',
            'consumer_key' => 'required_if:source_type,woocommerce|nullable|string',
            'consumer_secret' => 'required_if:source_type,woocommerce|nullable|string',
        ]);

        $config = collect($request->except(['_token', 'name', 'source_type']))->toArray();
        $config['dry_run'] = filter_var($request->input('dry_run', false), FILTER_VALIDATE_BOOL);

        $job = MigrationJob::query()->create([
            'name' => (string) $request->input('name', ucfirst((string) $request->source_type).' Migration '.now()->format('d M Y')),
            'source_type' => (string) $request->source_type,
            'status' => 'pending',
            'config' => $config,
            'stats' => null,
            'created_by' => auth()->id() ?? 1,
        ]);

        RunMigrationJob::dispatch((int) $job->id)->onQueue('migrations');

        return response()->json([
            'success' => true,
            'job_id' => $job->id,
            'message' => 'Migration started. Monitor progress below.',
        ]);
    }

    public function progress(int $id): JsonResponse
    {
        $job = MigrationJob::query()->findOrFail($id);
        $log = json_decode((string) ($job->log ?? '[]'), true) ?: [];
        return response()->json([
            'status' => $job->status,
            'stats' => $job->stats ?? [],
            'log' => collect($log)->take(-20)->values(),
            'completed' => $job->status === 'completed',
            'failed' => $job->status === 'failed',
        ]);
    }
}

