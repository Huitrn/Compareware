<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
     *
     * @param string $searchTerm
     * @param string $country
     * @return array
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
                
                // Guardar en cache por 1 hora
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
     * Obtener detalles específicos de un producto por ASIN
     */
    public function getProductDetails($asin, $country = 'US')
    {
        if ($this->useMockData) {
            return $this->getMockProductDetails($asin);
        }

        try {
            $cacheKey = "amazon_product_" . $asin . "_" . $country;
            
            if (Cache::has($cacheKey)) {
                Log::info("Amazon API: Detalles de producto desde cache para ASIN: {$asin}");
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
                
                // Guardar en cache por 2 horas
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

    /**
     * Generar datos mock de Amazon para demostración
     */
    private function getMockAmazonData($searchTerm)
    {
        // Datos mock realistas basados en el término de búsqueda
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
     * Generar productos mock según el término de búsqueda
     */
    private function generateMockProducts($searchTerm)
    {
        $term = strtolower($searchTerm);
        $products = [];

        // Detectar categoría por palabras clave
        if (str_contains($term, 'auricular') || str_contains($term, 'headphone') || str_contains($term, 'buds')) {
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
                'product_minimum_offer_price' => '$320.00',
                'is_best_seller' => true,
                'is_amazon_choice' => false,
                'is_prime' => true,
                'climate_pledge_friendly' => false,
                'delivery' => 'FREE delivery Wed, Oct 4 on $35 of items shipped by Amazon'
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
                'is_best_seller' => false,
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Thu, Oct 5'
            ],
            [
                'asin' => 'B0863TXGM3',
                'product_title' => 'Apple AirPods Max - Space Gray',
                'product_price' => '$449.00',
                'product_original_price' => '$549.00',
                'currency' => 'USD',
                'product_star_rating' => '4.4',
                'product_num_ratings' => 15782,
                'product_url' => 'https://amazon.com/dp/B0863TXGM3',
                'product_photo' => 'https://m.media-amazon.com/images/I/81SH-paCc6L._AC_SX466_.jpg',
                'is_prime' => true,
                'delivery' => 'FREE delivery Fri, Oct 6'
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
            ],
            [
                'asin' => 'B07YN82X3G',
                'product_title' => 'Razer DeathAdder V3 Gaming Mouse',
                'product_price' => '$89.99',
                'currency' => 'USD',
                'product_star_rating' => '4.6',
                'product_num_ratings' => 12456,
                'product_url' => 'https://amazon.com/dp/B07YN82X3G',
                'product_photo' => 'https://m.media-amazon.com/images/I/61mpMH5TzkL._AC_SX466_.jpg',
                'is_amazon_choice' => true,
                'is_prime' => true,
                'delivery' => 'FREE delivery Thu, Oct 5'
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

    /**
     * Respuesta de error estándar
     */
    private function getErrorResponse($message)
    {
        return [
            'success' => false,
            'error' => $message,
            'products' => [],
            'data' => null
        ];
    }

    /**
     * Limpiar cache de Amazon
     */
    public function clearCache()
    {
        try {
            // En un entorno real, limpiarías claves específicas
            // Por simplicidad, esto es un placeholder
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            Log::error("Error clearing Amazon cache: " . $e->getMessage());
            return false;
        }
    }
}