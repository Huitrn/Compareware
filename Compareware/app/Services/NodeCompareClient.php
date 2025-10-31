<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodeCompareClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.node_api.base_url'), '/');
    }

    public function getSpecs(string $query, ?string $ean = null, ?string $upc = null, ?string $brand = null, ?string $category = null): array
    {
        try {
            $response = Http::timeout(20)->get($this->baseUrl.'/products/specs', array_filter([
                'query' => $query,
                'ean' => $ean,
                'upc' => $upc,
                'brand' => $brand,
                'category' => $category,
            ]));
            return $response->json();
        } catch (\Throwable $e) {
            Log::error('NodeCompareClient:getSpecs error: '.$e->getMessage());
            return ['success' => false, 'message' => 'Node API error'];
        }
    }

    public function getPrices(string $query, string $country = 'US', string $currency = 'USD', int $limit = 5): array
    {
        try {
            $response = Http::timeout(20)->get($this->baseUrl.'/products/prices', [
                'query' => $query,
                'country' => $country,
                'currency' => $currency,
                'limit' => $limit,
            ]);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error('NodeCompareClient:getPrices error: '.$e->getMessage());
            return ['success' => false, 'message' => 'Node API error'];
        }
    }

    public function compare(string $product1, string $product2, string $country = 'US', string $currency = 'USD'): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl.'/products/compare', [
                'product1' => $product1,
                'product2' => $product2,
                'country' => $country,
                'currency' => $currency,
            ]);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error('NodeCompareClient:compare error: '.$e->getMessage());
            return ['success' => false, 'message' => 'Node API error'];
        }
    }
}
