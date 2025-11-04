<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleShoppingApiService
{
    protected $rapidApiKey;
    protected $rapidApiHost;
    protected $baseUrl;
    protected $cacheTime; // 6 horas en segundos
    
    public function __construct()
    {
        $this->rapidApiKey = config('services.google_shopping.rapidapi_key');
        $this->rapidApiHost = config('services.google_shopping.rapidapi_host');
        $this->baseUrl = config('services.google_shopping.base_url');
        $this->cacheTime = 21600; // 6 horas
    }
    
    /**
     * Buscar productos en Google Shopping
     * 
     * @param string $query Término de búsqueda
     * @param string $country Código de país (default: MX)
     * @param int $limit Número de resultados (default: 10)
     * @return array
     */
    public function searchProducts($query, $country = 'mx', $limit = 10)
    {
        try {
            // Validar entrada
            if (empty($query)) {
                throw new \Exception('El término de búsqueda no puede estar vacío');
            }
            
            // Generar clave de caché única
            $cacheKey = "google_shopping_search_{$query}_{$country}_{$limit}";
            
            Log::info("🛒 Google Shopping: Buscando productos", [
                'query' => $query,
                'country' => $country,
                'limit' => $limit
            ]);
            
            // Verificar si hay datos en caché
            if (Cache::has($cacheKey)) {
                Log::info("📦 Google Shopping: Datos encontrados en caché");
                return Cache::get($cacheKey);
            }
            
            // Verificar si debemos usar datos mock
            if ($this->shouldUseMockData()) {
                Log::info("🎭 Google Shopping: Usando datos de ejemplo (mock)");
                return $this->getMockProductData($query, $limit);
            }
            
            // Hacer la petición a la API real
            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->rapidApiKey,
                'x-rapidapi-host' => $this->rapidApiHost,
            ])->timeout(15)->get("{$this->baseUrl}/search", [
                'q' => $query,
                'country' => $country,
                'language' => 'es',
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Formatear resultados
                $products = $this->formatProducts($data['data'] ?? []);
                
                $result = [
                    'success' => true,
                    'query' => $query,
                    'country' => $country,
                    'total_results' => count($products),
                    'products' => $products,
                    'source' => 'google_shopping_api',
                    'timestamp' => now()->toIso8601String(),
                ];
                
                // Guardar en caché
                Cache::put($cacheKey, $result, $this->cacheTime);
                
                Log::info("✅ Google Shopping: Productos obtenidos exitosamente", [
                    'count' => count($products)
                ]);
                
                return $result;
            }
            
            throw new \Exception("Error en la API: {$response->status()}");
            
        } catch (\Exception $e) {
            Log::error("❌ Google Shopping: Error al buscar productos", [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            // Retornar datos mock en caso de error
            return $this->getMockProductData($query, $limit);
        }
    }
    
    /**
     * Obtener detalles de un producto específico por URL
     * 
     * @param string $productUrl URL del producto
     * @return array
     */
    public function getProductDetails($productUrl)
    {
        try {
            if (empty($productUrl)) {
                throw new \Exception('La URL del producto no puede estar vacía');
            }
            
            $cacheKey = "google_shopping_product_" . md5($productUrl);
            
            Log::info("🔍 Google Shopping: Obteniendo detalles del producto", [
                'url' => $productUrl
            ]);
            
            if (Cache::has($cacheKey)) {
                Log::info("📦 Google Shopping: Detalles encontrados en caché");
                return Cache::get($cacheKey);
            }
            
            if ($this->shouldUseMockData()) {
                Log::info("🎭 Google Shopping: Usando detalles mock");
                return $this->getMockProductDetails($productUrl);
            }
            
            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->rapidApiKey,
                'x-rapidapi-host' => $this->rapidApiHost,
            ])->timeout(15)->get("{$this->baseUrl}/product-details", [
                'url' => $productUrl,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'success' => true,
                    'product' => $data['data'] ?? [],
                    'timestamp' => now()->toIso8601String(),
                ];
                
                Cache::put($cacheKey, $result, $this->cacheTime);
                
                Log::info("✅ Google Shopping: Detalles obtenidos exitosamente");
                
                return $result;
            }
            
            throw new \Exception("Error en la API: {$response->status()}");
            
        } catch (\Exception $e) {
            Log::error("❌ Google Shopping: Error al obtener detalles", [
                'error' => $e->getMessage(),
                'url' => $productUrl
            ]);
            
            return $this->getMockProductDetails($productUrl);
        }
    }
    
    /**
     * Comparar precios de un producto en múltiples tiendas
     * 
     * @param string $query Nombre del producto
     * @param int $limit Número de resultados
     * @return array
     */
    public function comparePrices($query, $limit = 20)
    {
        try {
            Log::info("🔍 GoogleShopping: Iniciando comparación de precios", [
                'query' => $query,
                'limit' => $limit
            ]);
            
            $searchResults = $this->searchProducts($query, 'mx', $limit);
            
            Log::info("📊 GoogleShopping: Resultados de búsqueda", [
                'success' => $searchResults['success'],
                'products_count' => count($searchResults['products'] ?? []),
                'is_demo' => $searchResults['is_demo'] ?? false
            ]);
            
            if (!$searchResults['success']) {
                Log::warning("⚠️ GoogleShopping: Búsqueda no exitosa");
                return $searchResults;
            }
            
            $products = $searchResults['products'];
            
            // Agrupar por tienda y ordenar por precio
            $stores = [];
            $lowestPrice = null;
            $highestPrice = null;
            
            foreach ($products as $product) {
                $store = $product['store'] ?? 'Desconocido';
                
                if (!isset($stores[$store])) {
                    $stores[$store] = [];
                }
                
                $stores[$store][] = $product;
                
                // Encontrar precios más bajos y altos
                if ($product['price_numeric'] > 0) {
                    if ($lowestPrice === null || $product['price_numeric'] < $lowestPrice['price']) {
                        $lowestPrice = [
                            'price' => $product['price_numeric'],
                            'store' => $store,
                            'product' => $product
                        ];
                    }
                    
                    if ($highestPrice === null || $product['price_numeric'] > $highestPrice['price']) {
                        $highestPrice = [
                            'price' => $product['price_numeric'],
                            'store' => $store,
                            'product' => $product
                        ];
                    }
                }
            }
            
            // Calcular estadísticas
            $prices = array_filter(array_column($products, 'price_numeric'), fn($p) => $p > 0);
            $averagePrice = count($prices) > 0 ? array_sum($prices) / count($prices) : 0;
            
            return [
                'success' => true,
                'query' => $query,
                'total_products' => count($products),
                'total_stores' => count($stores),
                'stores' => $stores,
                'price_analysis' => [
                    'lowest' => $lowestPrice,
                    'highest' => $highestPrice,
                    'average' => $averagePrice,
                    'difference' => $highestPrice && $lowestPrice 
                        ? $highestPrice['price'] - $lowestPrice['price'] 
                        : 0,
                    'savings_percentage' => $highestPrice && $lowestPrice && $highestPrice['price'] > 0
                        ? round((($highestPrice['price'] - $lowestPrice['price']) / $highestPrice['price']) * 100, 2)
                        : 0,
                ],
                'products' => $products,
                'is_demo' => $searchResults['is_demo'] ?? false,
                'source' => $searchResults['source'] ?? 'google_shopping_api',
                'timestamp' => now()->toIso8601String(),
            ];
            
        } catch (\Exception $e) {
            Log::error("❌ Google Shopping: Error al comparar precios", [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al comparar precios: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Formatear productos de la API
     */
    protected function formatProducts($products)
    {
        return array_map(function ($product) {
            return [
                'title' => $product['product_title'] ?? $product['title'] ?? 'Sin título',
                'price' => $product['offer']['price'] ?? $product['price'] ?? 'N/A',
                'price_numeric' => $this->extractNumericPrice($product['offer']['price'] ?? $product['price'] ?? '0'),
                'currency' => $product['offer']['currency'] ?? 'MXN',
                'store' => $product['source'] ?? $product['store'] ?? 'Tienda Online',
                'image' => $product['product_photos'][0] ?? $product['image'] ?? null,
                'rating' => $product['product_rating'] ?? $product['rating'] ?? null,
                'reviews_count' => $product['product_num_reviews'] ?? $product['reviews'] ?? 0,
                'url' => $product['product_page_url'] ?? $product['url'] ?? '#',
                'availability' => $product['offer']['availability'] ?? 'En stock',
                'delivery' => $product['delivery'] ?? null,
            ];
        }, $products);
    }
    
    /**
     * Extraer valor numérico del precio
     */
    protected function extractNumericPrice($priceString)
    {
        // Remover símbolos de moneda y espacios
        $cleaned = preg_replace('/[^0-9.,]/', '', $priceString);
        
        // Convertir comas a puntos
        $cleaned = str_replace(',', '.', $cleaned);
        
        // Manejar formato con múltiples puntos (ej: 1.500.000)
        if (substr_count($cleaned, '.') > 1) {
            $cleaned = str_replace('.', '', $cleaned);
        }
        
        return floatval($cleaned);
    }
    
    /**
     * Determinar si usar datos mock
     */
    protected function shouldUseMockData()
    {
        // Usar mock si no hay API key configurada o estamos en local
        return empty($this->rapidApiKey) || 
               config('app.env') === 'local' ||
               $this->rapidApiKey === 'your_rapidapi_key_here';
    }
    
    /**
     * Obtener datos mock de productos
     */
    protected function getMockProductData($query, $limit = 10)
    {
        Log::info("🎭 Generando datos mock para búsqueda: {$query}");
        
        $stores = ['Amazon', 'Mercado Libre', 'Best Buy', 'Liverpool', 'Elektra', 'Walmart', 'Coppel', 'Cyberpuerta'];
        $basePrice = rand(500, 5000);
        
        $mockProducts = [];
        
        for ($i = 0; $i < min($limit, 10); $i++) {
            $store = $stores[$i % count($stores)];
            $priceVariation = rand(-30, 30) / 100; // -30% a +30%
            $price = $basePrice * (1 + $priceVariation);
            $price = round($price, 2);
            
            $mockProducts[] = [
                'title' => "{$query} - Modelo " . chr(65 + $i) . " ({$store})",
                'price' => "$" . number_format($price, 2) . " MXN",
                'price_numeric' => $price,
                'currency' => 'MXN',
                'store' => $store,
                'image' => "https://via.placeholder.com/200x200?text=" . urlencode($store),
                'rating' => rand(35, 50) / 10,
                'reviews_count' => rand(10, 500),
                'url' => "https://example.com/product-{$i}",
                'availability' => $i % 3 === 0 ? 'En stock' : 'Disponible',
                'delivery' => rand(1, 7) . " días hábiles",
            ];
        }
        
        // Ordenar por precio
        usort($mockProducts, fn($a, $b) => $a['price_numeric'] <=> $b['price_numeric']);
        
        return [
            'success' => true,
            'query' => $query,
            'country' => 'mx',
            'total_results' => count($mockProducts),
            'products' => $mockProducts,
            'source' => 'mock_data',
            'timestamp' => now()->toIso8601String(),
            'is_demo' => true,
        ];
    }
    
    /**
     * Obtener detalles mock de un producto
     */
    protected function getMockProductDetails($url)
    {
        return [
            'success' => true,
            'product' => [
                'title' => 'Producto de Ejemplo',
                'price' => '$1,299.00 MXN',
                'price_numeric' => 1299.00,
                'description' => 'Este es un producto de demostración con datos de ejemplo.',
                'store' => 'Tienda Demo',
                'rating' => 4.5,
                'reviews_count' => 123,
                'image' => 'https://via.placeholder.com/400x400',
                'url' => $url,
                'availability' => 'En stock',
            ],
            'is_demo' => true,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
