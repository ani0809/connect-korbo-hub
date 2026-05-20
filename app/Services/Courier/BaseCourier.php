<?php

namespace App\Services\Courier;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseCourier implements CourierInterface
{
    protected string $apiKey = '';
    protected string $secretKey = '';
    protected string $baseUrl = '';
    protected string $name = '';

    public function __construct()
    {
        $this->loadCredentials();
    }

    abstract protected function loadCredentials(): void;

    protected function post(string $endpoint, array $data = [], array $headers = []): array
    {
        try {
            $response = Http::withHeaders(array_merge($this->getHeaders(), $headers))
                ->timeout(30)
                ->post($this->baseUrl.$endpoint, $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error($this->name.' API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $data,
            ]);

            return [
                'success' => false,
                'message' => $response->json('message', 'Courier API error'),
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error($this->name.' exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'payload' => $data,
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get($this->baseUrl.$endpoint, $params);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::warning($this->name.' API get failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'params' => $params,
                'body' => $response->body(),
            ]);

            return ['success' => false, 'message' => 'API error: '.$response->status()];
        } catch (\Throwable $e) {
            Log::error($this->name.' get exception', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    abstract protected function getHeaders(): array;

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }
}
