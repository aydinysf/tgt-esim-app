<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IwcService
{
    protected string $baseUrl;
    protected string $profileId;
    protected string $accessKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) (\App\Models\Setting::get('iwc_base_url') ?: config('services.iwc.base_url', 'https://partners-api.istanbulwelcomecard.com')), '/');
        $this->profileId = (string) (\App\Models\Setting::get('iwc_profile_id') ?: config('services.iwc.profile_id', ''));
        $this->accessKey = (string) (\App\Models\Setting::get('iwc_access_key') ?: config('services.iwc.access_key', ''));
    }

    /**
     * Check if IWC credentials are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->profileId) && !empty($this->accessKey);
    }

    /**
     * Helper to make authorized HTTP requests
     */
    protected function client()
    {
        return Http::withHeaders([
            'X-Profile-ID' => $this->profileId,
            'X-Access-Key' => $this->accessKey,
            'Accept' => 'application/json',
            'User-Agent' => 'PostmanRuntime/7.39.0',
        ])->timeout(30);
    }

    /**
     * Get list of products from IWC API
     */
    public function getProducts(?string $startDate = null, ?string $endDate = null, int $perPage = 50, int $page = 1): array
    {
        if (!$this->isConfigured()) {
            Log::warning('IWC API credentials are not configured.');
            return ['status' => false, 'message' => 'IWC API kimlik bilgileri tanımlanmamış.', 'data' => []];
        }

        try {
            $query = [
                'per_page' => $perPage,
                'page' => $page,
            ];

            if ($startDate) $query['start_date'] = $startDate;
            if ($endDate) $query['end_date'] = $endDate;

            $response = $this->client()->get("{$this->baseUrl}/v2/products", $query);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('IWC API getProducts failed: ' . $response->body());
            return [
                'status' => false,
                'message' => $response->json('message') ?? 'IWC API hatası: ' . $response->status(),
                'data' => []
            ];
        } catch (\Throwable $e) {
            Log::error('IWC API getProducts exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Get product details & availability slots for a specific date range
     */
    public function getProduct(int|string $productId, ?string $startDate = null, ?string $endDate = null): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => 'IWC API kimlik bilgileri tanımlanmamış.', 'data' => null];
        }

        try {
            $query = [];
            if ($startDate) $query['start_date'] = $startDate;
            if ($endDate) $query['end_date'] = $endDate;

            $response = $this->client()->get("{$this->baseUrl}/v2/products/{$productId}", $query);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("IWC API getProduct {$productId} failed: " . $response->body());
            return [
                'status' => false,
                'message' => $response->json('message') ?? 'IWC API hatası: ' . $response->status(),
                'data' => null
            ];
        } catch (\Throwable $e) {
            Log::error("IWC API getProduct {$productId} exception: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Create an order / booking on IWC
     */
    public function createOrder(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => 'IWC API kimlik bilgileri tanımlanmamış.', 'data' => null];
        }

        try {
            $response = $this->client()->post("{$this->baseUrl}/v2/orders", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('IWC API createOrder failed: ' . $response->body());
            $errorMsg = $response->json('message') ?? 'IWC sipariş oluşturulamadı (' . $response->status() . ')';
            return [
                'status' => false,
                'message' => $errorMsg,
                'data' => $response->json('data') ?? null,
                'errors' => $response->json('errors') ?? null,
                'status_code' => $response->status(),
                'raw_body' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('IWC API createOrder exception: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Get order details by IWC Order ID
     */
    public function getOrder(int|string $orderId): array
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => 'IWC API kimlik bilgileri tanımlanmamış.'];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/v2/orders/{$orderId}");
            return $response->json();
        } catch (\Throwable $e) {
            Log::error("IWC API getOrder {$orderId} exception: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get partner profile
     */
    public function getProfile(): array
    {
        if (!$this->isConfigured()) return ['status' => false, 'message' => 'Kimlik bilgileri eksik.'];
        try {
            return $this->client()->get("{$this->baseUrl}/v2/profile")->json();
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get partner account balance
     */
    public function getBalance(): array
    {
        if (!$this->isConfigured()) return ['status' => false, 'message' => 'Kimlik bilgileri eksik.'];
        try {
            return $this->client()->get("{$this->baseUrl}/v2/balance")->json();
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
