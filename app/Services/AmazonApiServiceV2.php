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
        // Si usar datos mock, generar datos de demostración
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
     * Buscar producto similar específico de la base de datos
     */
    public function findDatabaseProduct($productName, $brand = null)
    {
        Log::info("Amazon API: Buscando producto específico de BDD - {$productName} [{$brand}]");
        
        // Buscar producto específico primero
        $searchTerm = $this->buildSmartSearchTerm($productName, $brand);
        
        if ($this->useMockData) {
            return $this->getMockAmazonDataForSpecificProduct($productName, $brand, $searchTerm);
        }
        
        return $this->searchProducts($searchTerm);
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
        
        // Extraer modelo del nombre del producto
        $cleanName = $this->extractModelFromProductName($productName);
        $terms[] = $cleanName;
        
        return implode(' ', $terms);
    }

    /**
     * Extraer modelo específico del nombre del producto
     */
    private function extractModelFromProductName($productName)
    {
        // Limpiar y extraer información relevante
        $name = strtolower($productName);
        
        // Para productos específicos de la BDD
        if (str_contains($name, 'haylou s35')) return 'Haylou S35 ANC';
        if (str_contains($name, 'crusher anc 2')) return 'Skullcandy Crusher ANC 2';
        if (str_contains($name, 'redmi buds 6')) return 'Xiaomi Redmi Buds 6 Active';
        
        // Si no hay coincidencia específica, devolver el nombre original
        return $productName;
    }

    /**
     * Generar datos mock de Amazon para demostración
     */
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
            '_note' => 'Datos de demostración - Configure RAPIDAPI_KEY válida para datos reales'
        ];
    }

    /**
     * Generar datos mock específicos para productos de la BDD
     */
    private function getMockAmazonDataForSpecificProduct($productName, $brand, $searchTerm)
    {
        $name = strtolower($productName);
        $mockProducts = [];
        
        // Detectar productos específicos de tu base de datos
        if (str_contains($name, 'haylou') && str_contains($name, 's35')) {
            $mockProducts = $this->getMockHaylouS35($productName);
        } elseif (str_contains($name, 'skullcandy') && str_contains($name, 'crusher')) {
            $mockProducts = $this->getMockSkullcandyCrusher($productName);
        } elseif (str_contains($name, 'xiaomi') && (str_contains($name, 'redmi') || str_contains($name, 'buds'))) {
            $mockProducts = $this->getMockXiaomiRedmiBuds($productName);
        } else {
            // Productos genéricos por marca
            if (str_contains($name, 'haylou')) {
                $mockProducts = $this->getMockHaylouProducts($productName);
            } elseif (str_contains($name, 'skullcandy')) {
                $mockProducts = $this->getMockSkullcandyProducts($productName);
            } elseif (str_contains($name, 'xiaomi')) {
                $mockProducts = $this->getMockXiaomiProducts($productName);
            } else {
                $mockProducts = $this->getMockGenericProducts($productName);
            }
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
            '_note' => "Producto específico de tu BDD: {$productName}"
        ];
    }

    /**
     * Generar productos mock según el término de búsqueda
     */
    private function generateMockProducts($searchTerm)
    {
        $term = strtolower($searchTerm);
        $products = [];

        // Detectar productos específicos de la base de datos
        if (str_contains($term, 'haylou') && str_contains($term, 's35')) {
            $products = $this->getMockHaylouS35($searchTerm);
        } elseif (str_contains($term, 'skullcandy') && str_contains($term, 'crusher')) {
            $products = $this->getMockSkullcandyCrusher($searchTerm);
        } elseif (str_contains($term, 'xiaomi') && (str_contains($term, 'redmi') || str_contains($term, 'buds'))) {
            $products = $this->getMockXiaomiRedmiBuds($searchTerm);
        }
        // Detectar por marca
        elseif (str_contains($term, 'haylou')) {
            $products = $this->getMockHaylouProducts($searchTerm);
        } elseif (str_contains($term, 'skullcandy')) {
            $products = $this->getMockSkullcandyProducts($searchTerm);
        } elseif (str_contains($term, 'xiaomi')) {
            $products = $this->getMockXiaomiProducts($searchTerm);
        }
        // Detectar categoría por palabras clave
        elseif (str_contains($term, 'auricular') || str_contains($term, 'headphone') || str_contains($term, 'buds') || str_contains($term, 'audifonos')) {
            $products = $this->getMockHeadphones($searchTerm);
        } elseif (str_contains($term, 'mouse') || str_contains($term, 'ratón')) {
            $products = $this->getMockMice($searchTerm);
        } elseif (str_contains($term, 'teclado') || str_contains($term, 'keyboard')) {
            $products = $this->getMockKeyboards($searchTerm);
        } else {
            // Productos genéricos
            $products = $this->getMockGenericProducts($searchTerm);
        }

        return array_slice($products, 0, 6); // Máximo 6 productos
    }

    // ===== PRODUCTOS ESPECÍFICOS DE TU BASE DE DATOS =====

    private function getMockHaylouS35($searchTerm)
    {
        return [
            [
                'asin' => 'B0B1HAYLOU35',
                'product_title' => 'Haylou S35 ANC Active Noise Cancelling Wireless Headphones - Bluetooth 5.2',
                'product_price' => '$34.99',
                'product_original_price' => '$49.99',
                'currency' => 'USD',
                'product_star_rating' => '4.3',
                'product_num_ratings' => 2847,
                'product_url' => 'https://amazon.com/dp/B0B1HAYLOU35',
                'product_photo' => 'https://m.media-amazon.com/images/I/61HaylouS35L._AC_SX466_.jpg',
                'is_best_seller' => false,
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Tue, Nov 5',
                'features' => ['ANC', 'Bluetooth 5.2', 'Cable/Bluetooth'],
                '_db_match' => 'Exacto: Haylou S35 ANC - $800 MXN en tu BDD',
                '_similarity' => 95
            ],
            [
                'asin' => 'B0B2HAYLOU36',
                'product_title' => 'Haylou GT7 TWS Earbuds with Active Noise Cancellation',
                'product_price' => '$28.99',
                'currency' => 'USD',
                'product_star_rating' => '4.1',
                'product_num_ratings' => 1523,
                'product_url' => 'https://amazon.com/dp/B0B2HAYLOU36',
                'product_photo' => 'https://m.media-amazon.com/images/I/61HaylouGT7L._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Nov 6',
                '_similarity' => 80
            ]
        ];
    }

    private function getMockSkullcandyCrusher($searchTerm)
    {
        return [
            [
                'asin' => 'B0SKULLCANDY2',
                'product_title' => 'Skullcandy Crusher ANC 2 Personalized Active Noise Canceling Wireless Headphones',
                'product_price' => '$169.99',
                'product_original_price' => '$199.99',
                'currency' => 'USD',
                'product_star_rating' => '4.5',
                'product_num_ratings' => 8934,
                'product_url' => 'https://amazon.com/dp/B0SKULLCANDY2',
                'product_photo' => 'https://m.media-amazon.com/images/I/71SkullCrusher._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_amazon_choice' => false,
                'is_prime' => true,
                'delivery' => 'FREE delivery Mon, Nov 4',
                'features' => ['Personalized ANC', 'Sensory Bass', 'Bluetooth 5.2'],
                '_db_match' => 'Exacto: Skullcandy Crusher ANC 2 - $4000 MXN en tu BDD',
                '_similarity' => 98
            ],
            [
                'asin' => 'B0SKULLCANDY1',
                'product_title' => 'Skullcandy Crusher Evo Wireless Over-Ear Headphone',
                'product_price' => '$149.99',
                'currency' => 'USD',
                'product_star_rating' => '4.3',
                'product_num_ratings' => 12456,
                'product_url' => 'https://amazon.com/dp/B0SKULLCANDY1',
                'product_photo' => 'https://m.media-amazon.com/images/I/71SkullEvo._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Tue, Nov 5',
                '_similarity' => 85
            ]
        ];
    }

    private function getMockXiaomiRedmiBuds($searchTerm)
    {
        return [
            [
                'asin' => 'B0XIAOMIRB6A',
                'product_title' => 'Xiaomi Redmi Buds 6 Active True Wireless Earbuds - Bluetooth 5.3',
                'product_price' => '$35.99',
                'product_original_price' => '$45.99',
                'currency' => 'USD',
                'product_star_rating' => '4.2',
                'product_num_ratings' => 3724,
                'product_url' => 'https://amazon.com/dp/B0XIAOMIRB6A',
                'product_photo' => 'https://m.media-amazon.com/images/I/61XiaomiRedmi._AC_SX466_.jpg',
                'is_best_seller' => false,
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Thu, Nov 7',
                'features' => ['Bluetooth 5.3', 'IPX4 Waterproof', '6h Battery'],
                '_db_match' => 'Exacto: Xiaomi Redmi Buds 6 Active - $840 MXN en tu BDD',
                '_similarity' => 92
            ],
            [
                'asin' => 'B0XIAOMIRB5',
                'product_title' => 'Xiaomi Redmi Buds 5 Pro Wireless Earbuds with ANC',
                'product_price' => '$59.99',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 5621,
                'product_url' => 'https://amazon.com/dp/B0XIAOMIRB5',
                'product_photo' => 'https://m.media-amazon.com/images/I/61XiaomiPro._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Fri, Nov 8',
                '_similarity' => 78
            ]
        ];
    }

    private function getMockHaylouProducts($searchTerm)
    {
        return $this->getMockHaylouS35($searchTerm);
    }

    private function getMockSkullcandyProducts($searchTerm)
    {
        return $this->getMockSkullcandyCrusher($searchTerm);
    }

    private function getMockXiaomiProducts($searchTerm)
    {
        return $this->getMockXiaomiRedmiBuds($searchTerm);
    }

    // ===== PRODUCTOS GENÉRICOS (CATEGORÍAS) =====

    private function getMockHeadphones($searchTerm)
    {
        return [
            [
                'asin' => 'B08PZHYWJS',
                'product_title' => 'Sony WH-1000XM4 Wireless Premium Noise Canceling Overhead Headphones with Mic',
                'product_price' => '$348.00',
                'product_original_price' => '$399.99',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 54891,
                'product_url' => 'https://amazon.com/dp/B08PZHYWJS',
                'product_photo' => 'https://m.media-amazon.com/images/I/71o8Q5XJS5L._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Oct 4'
            ],
            [
                'asin' => 'B07Q9MJKBV', 
                'product_title' => 'Bose QuietComfort 45 Wireless Bluetooth Noise Cancelling Headphones',
                'product_price' => '$279.00',
                'product_original_price' => '$329.00',
                'currency' => 'USD',
                'product_star_rating' => '4.3',
                'product_num_ratings' => 28914,
                'product_url' => 'https://amazon.com/dp/B07Q9MJKBV',
                'product_photo' => 'https://m.media-amazon.com/images/I/81+jNVOUsJL._AC_SX466_.jpg',
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Thu, Oct 5'
            ]
        ];
    }

    private function getMockMice($searchTerm)
    {
        return [
            [
                'asin' => 'B07CMS5Q6P',
                'product_title' => 'Logitech MX Master 3 Advanced Wireless Mouse',
                'product_price' => '$79.99',
                'product_original_price' => '$99.99',
                'currency' => 'USD',
                'product_star_rating' => '4.5',
                'product_num_ratings' => 41234,
                'product_url' => 'https://amazon.com/dp/B07CMS5Q6P',
                'product_photo' => 'https://m.media-amazon.com/images/I/61ni3t1ryQL._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Oct 4'
            ]
        ];
    }

    private function getMockKeyboards($searchTerm)
    {
        return [
            [
                'asin' => 'B07ZGDPT4M',
                'product_title' => 'Corsair K95 RGB Platinum XT Mechanical Gaming Keyboard',
                'product_price' => '$179.99',
                'product_original_price' => '$199.99',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 8934,
                'product_url' => 'https://amazon.com/dp/B07ZGDPT4M',
                'product_photo' => 'https://m.media-amazon.com/images/I/81QTsAO52fL._AC_SX466_.jpg',
                'is_best_seller' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Wed, Oct 4'
            ]
        ];
    }

    private function getMockGenericProducts($searchTerm)
    {
        return [
            [
                'asin' => 'B08N5WRWNW',
                'product_title' => ucwords($searchTerm) . ' - Premium Quality Product',
                'product_price' => '$' . rand(25, 150) . '.99',
                'currency' => 'USD',
                'product_star_rating' => number_format(rand(38, 48) / 10, 1),
                'product_num_ratings' => rand(500, 5000),
                'product_url' => 'https://amazon.com/dp/B08N5WRWNW',
                'product_photo' => 'https://via.placeholder.com/300x300?text=Product',
                'is_prime' => true,
                'delivery' => 'FREE delivery within 2-3 days'
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
                Log::info("Amazon API: Detalles desde cache para ASIN: {$asin}");
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
                Log::info("Amazon API: Detalles obtenidos para ASIN: {$asin}");
                return $data;
            } else {
                Log::error("Amazon API Product Details Error: " . $response->body());
                return $this->getErrorResponse('Error al obtener detalles del producto');
            }

        } catch (\Exception $e) {
            Log::error("Amazon API Product Details Exception: " . $e->getMessage());
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
}