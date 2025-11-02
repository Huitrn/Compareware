<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AmazonApiService
{
    private $apiKey;
    private $apiHost;
    private $baseUrl;
    private $useMockData;

    public function __construct()
    {
        $this->apiKey = config('services.rapidapi.key');
        $this->apiHost = config('services.rapidapi.host');
        $this->baseUrl = config('services.rapidapi.base_url');
        
        // Usar datos mock si la API key no es válida o es de prueba
        $this->useMockData = empty($this->apiKey) || 
                           str_contains($this->apiKey, 'staging') || 
                           str_contains($this->apiKey, 'test') ||
                           config('app.env') !== 'production';
    }

    /**
     * Buscar productos en Amazon por término de búsqueda
     */
    public function searchProducts($searchTerm, $country = 'US')
    {
        if ($this->useMockData) {
            Log::info("Amazon API: Usando datos mock para demostración - término: {$searchTerm}");
            return $this->getMockAmazonData($searchTerm);
        }

        try {
            $cacheKey = "amazon_search_" . md5($searchTerm . $country);
            
            if (Cache::has($cacheKey)) {
                Log::info("Amazon API: Datos desde cache para: {$searchTerm}");
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => $this->apiHost,
            ])->get($this->baseUrl . '/search', [
                'query' => $searchTerm,
                'page' => 1,
                'country' => $country,
                'sort_by' => 'RELEVANCE',
                'product_condition' => 'ALL'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 3600);
                Log::info("Amazon API: Búsqueda exitosa para: {$searchTerm}");
                return $data;
            } else {
                Log::error("Amazon API Error: " . $response->body());
                return $this->getErrorResponse('Error al buscar productos en Amazon');
            }

        } catch (\Exception $e) {
            Log::error("Amazon API Exception: " . $e->getMessage());
            return $this->getErrorResponse('Error de conexión con Amazon API');
        }
    }

    /**
     * Buscar producto similar específico de la base de datos con URLs reales
     */
    public function findDatabaseProduct($productName, $brand = null)
    {
        Log::info("Amazon API: Buscando producto específico de BDD - {$productName} [{$brand}]");
        
        if ($this->useMockData) {
            return $this->getMockAmazonDataForSpecificProduct($productName, $brand);
        }
        
        return $this->searchProducts($this->buildSmartSearchTerm($productName, $brand));
    }

    /**
     * Construir término de búsqueda inteligente para productos específicos
     */
    private function buildSmartSearchTerm($productName, $brand = null)
    {
        $terms = [];
        
        if ($brand) {
            $terms[] = $brand;
        }
        
        $cleanName = $this->extractModelFromProductName($productName);
        $terms[] = $cleanName;
        
        return implode(' ', $terms);
    }

    /**
     * Extraer modelo específico del nombre del producto
     */
    private function extractModelFromProductName($productName)
    {
        $name = strtolower($productName);
        
        if (str_contains($name, 'haylou s35')) return 'Haylou S35 ANC';
        if (str_contains($name, 'crusher anc 2')) return 'Skullcandy Crusher ANC 2';
        if (str_contains($name, 'redmi buds 6')) return 'Xiaomi Redmi Buds 6 Active';
        
        return $productName;
    }

    /**
     * Generar datos mock específicos para productos de la BDD con URLs REALES
     */
    private function getMockAmazonDataForSpecificProduct($productName, $brand)
    {
        $name = strtolower($productName);
        $mockProducts = [];
        
        // Detectar productos específicos de tu base de datos y asignar URLs reales
        if (str_contains($name, 'haylou') && str_contains($name, 's35')) {
            $mockProducts = $this->getMockHaylouS35WithRealUrls($productName);
        } elseif (str_contains($name, 'skullcandy') && str_contains($name, 'crusher')) {
            $mockProducts = $this->getMockSkullcandyCrusherWithRealUrls($productName);
        } elseif (str_contains($name, 'xiaomi') && (str_contains($name, 'redmi') || str_contains($name, 'buds'))) {
            $mockProducts = $this->getMockXiaomiRedmiBudsWithRealUrls($productName);
        } else {
            $mockProducts = $this->getMockGenericProductsWithRealUrls($productName, $brand);
        }
        
        return [
            'success' => true,
            'status' => 'OK',
            'request_id' => 'mock-specific-' . uniqid(),
            'data' => [
                'total_results' => count($mockProducts),
                'country' => 'US',
                'domain' => 'amazon.com',
                'products' => $mockProducts
            ],
            '_mock' => true,
            '_db_product' => $productName,
            '_note' => "Producto específico de tu BDD: {$productName} - URLs REALES de Amazon"
        ];
    }

    // ===== PRODUCTOS ESPECÍFICOS CON URLS REALES =====

    private function getMockHaylouS35WithRealUrls($searchTerm)
    {
        return [
            [
                'asin' => 'REAL_SEARCH_HAYLOU',
                'product_title' => 'Haylou S35 ANC Active Noise Cancelling Wireless Headphones - Bluetooth 5.2',
                'product_price' => '$34.99',
                'product_original_price' => '$49.99',
                'currency' => 'USD',
                'product_star_rating' => '4.3',
                'product_num_ratings' => 2847,
                'product_url' => $this->generateAmazonSearchUrl('Haylou S35 ANC wireless headphones bluetooth active noise canceling'),
                'product_photo' => 'https://m.media-amazon.com/images/I/61HaylouS35L._AC_SX466_.jpg',
                'is_best_seller' => false,
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Tue, Nov 5',
                'features' => ['ANC', 'Bluetooth 5.2', 'Cable/Bluetooth'],
                '_db_match' => 'Exacto: Haylou S35 ANC - $800 MXN en tu BDD',
                '_similarity' => 95,
                '_real_amazon_search' => true,
                'alternative_urls' => [
                    'specific_model' => $this->generateAmazonSearchUrl('Haylou S35 ANC headphones'),
                    'brand_search' => $this->generateAmazonSearchUrl('Haylou wireless headphones ANC'),
                    'amazon_mx' => $this->generateAmazonSearchUrl('Haylou S35 ANC wireless headphones', 'MX')
                ]
            ],
            [
                'asin' => 'REAL_HAYLOU_ALT',
                'product_title' => 'Haylou GT7 TWS Earbuds with Active Noise Cancellation',
                'product_price' => '$28.99',
                'currency' => 'USD',
                'product_star_rating' => '4.1',
                'product_num_ratings' => 1523,
                'product_url' => $this->generateAmazonSearchUrl('Haylou GT7 wireless earbuds ANC bluetooth'),
                'product_photo' => 'https://m.media-amazon.com/images/I/61HaylouGT7L._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Nov 6',
                '_similarity' => 80,
                '_real_amazon_search' => true
            ]
        ];
    }

    private function getMockSkullcandyCrusherWithRealUrls($searchTerm)
    {
        return [
            [
                'asin' => 'REAL_SKULLCANDY',
                'product_title' => 'Skullcandy Crusher ANC 2 Personalized Active Noise Canceling Wireless Headphones',
                'product_price' => '$169.99',
                'product_original_price' => '$199.99',
                'currency' => 'USD',
                'product_star_rating' => '4.5',
                'product_num_ratings' => 8934,
                'product_url' => $this->generateAmazonSearchUrl('Skullcandy Crusher ANC 2 wireless headphones active noise canceling personalized'),
                'product_photo' => 'https://m.media-amazon.com/images/I/71SkullCrusher._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_amazon_choice' => false,
                'is_prime' => true,
                'delivery' => 'FREE delivery Mon, Nov 4',
                'features' => ['Personalized ANC', 'Sensory Bass', 'Bluetooth 5.2'],
                '_db_match' => 'Exacto: Skullcandy Crusher ANC 2 - $4000 MXN en tu BDD',
                '_similarity' => 98,
                '_real_amazon_search' => true,
                'alternative_urls' => [
                    'exact_model' => $this->generateAmazonSearchUrl('Skullcandy Crusher ANC 2'),
                    'brand_line' => $this->generateAmazonSearchUrl('Skullcandy Crusher wireless headphones'),
                    'amazon_mx' => $this->generateAmazonSearchUrl('Skullcandy Crusher ANC 2 inalambricos', 'MX')
                ]
            ],
            [
                'asin' => 'REAL_SKULL_ALT',
                'product_title' => 'Skullcandy Crusher Evo Wireless Over-Ear Headphone',
                'product_price' => '$149.99',
                'currency' => 'USD',
                'product_star_rating' => '4.3',
                'product_num_ratings' => 12456,
                'product_url' => $this->generateAmazonSearchUrl('Skullcandy Crusher Evo wireless headphones'),
                'product_photo' => 'https://m.media-amazon.com/images/I/71SkullEvo._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Tue, Nov 5',
                '_similarity' => 85,
                '_real_amazon_search' => true
            ]
        ];
    }

    private function getMockXiaomiRedmiBudsWithRealUrls($searchTerm)
    {
        return [
            [
                'asin' => 'REAL_XIAOMI_REDMI',
                'product_title' => 'Xiaomi Redmi Buds 6 Active True Wireless Earbuds - Bluetooth 5.3',
                'product_price' => '$35.99',
                'product_original_price' => '$45.99',
                'currency' => 'USD',
                'product_star_rating' => '4.2',
                'product_num_ratings' => 3724,
                'product_url' => $this->generateAmazonSearchUrl('Xiaomi Redmi Buds 6 Active wireless earbuds bluetooth true wireless'),
                'product_photo' => 'https://m.media-amazon.com/images/I/61XiaomiRedmi._AC_SX466_.jpg',
                'is_best_seller' => false,
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Thu, Nov 7',
                'features' => ['Bluetooth 5.3', 'IPX4 Waterproof', '6h Battery'],
                '_db_match' => 'Exacto: Xiaomi Redmi Buds 6 Active - $840 MXN en tu BDD',
                '_similarity' => 92,
                '_real_amazon_search' => true,
                'alternative_urls' => [
                    'specific_model' => $this->generateAmazonSearchUrl('Xiaomi Redmi Buds 6 Active earbuds'),
                    'brand_line' => $this->generateAmazonSearchUrl('Xiaomi Redmi Buds wireless earbuds'),
                    'amazon_mx' => $this->generateAmazonSearchUrl('Xiaomi Redmi Buds 6 Active audifonos inalambricos', 'MX')
                ]
            ],
            [
                'asin' => 'REAL_XIAOMI_PRO',
                'product_title' => 'Xiaomi Redmi Buds 5 Pro Wireless Earbuds with ANC',
                'product_price' => '$59.99',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 5621,
                'product_url' => $this->generateAmazonSearchUrl('Xiaomi Redmi Buds 5 Pro wireless earbuds ANC'),
                'product_photo' => 'https://m.media-amazon.com/images/I/61XiaomiPro._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Fri, Nov 8',
                '_similarity' => 78,
                '_real_amazon_search' => true
            ]
        ];
    }

    private function getMockGenericProductsWithRealUrls($searchTerm, $brand = null)
    {
        $searchQuery = $brand ? "$brand $searchTerm wireless headphones" : "$searchTerm wireless headphones";
        
        return [
            [
                'asin' => 'REAL_GENERIC_SEARCH',
                'product_title' => ucwords($searchTerm) . ' - Premium Quality Wireless Headphones',
                'product_price' => '$' . rand(25, 150) . '.99',
                'currency' => 'USD',
                'product_star_rating' => number_format(rand(38, 48) / 10, 1),
                'product_num_ratings' => rand(500, 5000),
                'product_url' => $this->generateAmazonSearchUrl($searchQuery),
                'product_photo' => 'https://via.placeholder.com/300x300?text=Product',
                'is_prime' => true,
                'delivery' => 'FREE delivery within 2-3 days',
                '_real_amazon_search' => true,
                'alternative_urls' => [
                    'brand_only' => $this->generateAmazonSearchUrl($brand ?: $searchTerm),
                    'category' => $this->generateAmazonSearchUrl('wireless headphones bluetooth'),
                    'amazon_mx' => $this->generateAmazonSearchUrl($searchQuery, 'MX')
                ]
            ]
        ];
    }

    // ===== FUNCIONES GENÉRICAS (mantener compatibilidad) =====

    private function getMockAmazonData($searchTerm)
    {
        $mockProducts = $this->generateMockProducts($searchTerm);
        return [
            'success' => true,
            'status' => 'OK',
            'request_id' => 'mock-' . uniqid(),
            'data' => [
                'total_results' => count($mockProducts),
                'country' => 'US',
                'domain' => 'amazon.com',
                'products' => $mockProducts
            ],
            '_mock' => true,
            '_note' => 'Datos de demostración con URLs reales de búsqueda'
        ];
    }

    private function generateMockProducts($searchTerm)
    {
        $term = strtolower($searchTerm);
        
        if (str_contains($term, 'auricular') || str_contains($term, 'headphone') || str_contains($term, 'buds')) {
            return $this->getMockHeadphonesWithRealUrls($searchTerm);
        } elseif (str_contains($term, 'mouse')) {
            return $this->getMockMiceWithRealUrls($searchTerm);
        } else {
            return $this->getMockGenericProductsWithRealUrls($searchTerm);
        }
    }

    private function getMockHeadphonesWithRealUrls($searchTerm)
    {
        return [
            [
                'asin' => 'REAL_SONY_WH1000XM4',
                'product_title' => 'Sony WH-1000XM4 Wireless Premium Noise Canceling Overhead Headphones',
                'product_price' => '$348.00',
                'product_original_price' => '$399.99',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 54891,
                'product_url' => $this->generateAmazonSearchUrl('Sony WH-1000XM4 wireless headphones noise canceling'),
                'product_photo' => 'https://m.media-amazon.com/images/I/71o8Q5XJS5L._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Oct 4',
                '_real_amazon_search' => true
            ]
        ];
    }

    private function getMockMiceWithRealUrls($searchTerm)
    {
        return [
            [
                'asin' => 'REAL_LOGITECH_MX3',
                'product_title' => 'Logitech MX Master 3 Advanced Wireless Mouse',
                'product_price' => '$79.99',
                'product_original_price' => '$99.99',
                'currency' => 'USD',
                'product_star_rating' => '4.5',
                'product_num_ratings' => 41234,
                'product_url' => $this->generateAmazonSearchUrl('Logitech MX Master 3 wireless mouse'),
                'product_photo' => 'https://m.media-amazon.com/images/I/61ni3t1ryQL._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Oct 4',
                '_real_amazon_search' => true
            ]
        ];
    }

    // ===== UTILIDADES =====

    public function getProductDetails($asin, $country = 'US')
    {
        if ($this->useMockData) {
            return $this->getMockProductDetails($asin);
        }

        try {
            $cacheKey = "amazon_product_" . $asin . "_" . $country;
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => $this->apiHost,
            ])->get($this->baseUrl . '/product-details', [
                'asin' => $asin,
                'country' => $country
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 7200);
                return $data;
            } else {
                return $this->getErrorResponse('Error al obtener detalles del producto');
            }

        } catch (\Exception $e) {
            return $this->getErrorResponse('Error de conexión con Amazon API');
        }
    }

    private function getMockProductDetails($asin)
    {
        return [
            'success' => true,
            'data' => [
                'asin' => $asin,
                'product_title' => 'Mock Product Details for ' . $asin,
                'product_price' => '$99.99',
                'product_star_rating' => '4.2',
                'product_description' => 'Detailed product information for demonstration purposes.',
                '_mock' => true
            ]
        ];
    }

    private function getErrorResponse($message)
    {
        return [
            'success' => false,
            'error' => $message,
            'products' => [],
            'data' => null
        ];
    }

    public function clearCache()
    {
        try {
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            Log::error("Error clearing Amazon cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar URL real de búsqueda en Amazon
     */
    private function generateAmazonSearchUrl($searchTerms, $country = 'US')
    {
        $baseUrls = [
            'US' => 'https://www.amazon.com/s',
            'MX' => 'https://www.amazon.com.mx/s',
            'ES' => 'https://www.amazon.es/s'
        ];
        
        $baseUrl = $baseUrls[$country] ?? $baseUrls['US'];
        $encodedTerms = urlencode($searchTerms);
        
        return $baseUrl . '?k=' . $encodedTerms . '&ref=sr_st_relevancerank';
    }
}