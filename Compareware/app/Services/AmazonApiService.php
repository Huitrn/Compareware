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

    public function __construct()
    {
        $this->apiKey = config('services.rapidapi.key');
        $this->apiHost = config('services.rapidapi.host');
        $this->baseUrl = config('services.rapidapi.base_url');
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
        try {
            // Cache key para evitar llamadas repetitivas
            $cacheKey = "amazon_search_" . md5($searchTerm . $country);
            
            // Verificar si ya existe en cache (válido por 1 hora)
            if (Cache::has($cacheKey)) {
                Log::info("Amazon API: Datos obtenidos desde cache para: {$searchTerm}");
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
     *
     * @param string $asin
     * @param string $country
     * @return array
     */
    public function getProductDetails($asin, $country = 'US')
    {
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
                
                // Cache por 2 horas (datos más estables)
                Cache::put($cacheKey, $data, 7200);
                
                Log::info("Amazon API: Detalles obtenidos para ASIN: {$asin}");
                return $data;
            } else {
                Log::error("Amazon API Error al obtener producto {$asin}: " . $response->body());
                return $this->getErrorResponse('Error al obtener detalles del producto');
            }

        } catch (\Exception $e) {
            Log::error("Amazon API Exception para ASIN {$asin}: " . $e->getMessage());
            return $this->getErrorResponse('Error de conexión al obtener detalles');
        }
    }

    /**
     * Buscar productos similares a los de nuestra base de datos
     *
     * @param string $productName
     * @param string $brand
     * @param string $category
     * @return array
     */
    public function findSimilarProduct($productName, $brand = null, $category = null)
    {
        // Construir término de búsqueda inteligente
        $searchTerms = [];
        
        if ($brand) {
            $searchTerms[] = $brand;
        }
        
        $searchTerms[] = $productName;
        
        if ($category) {
            $searchTerms[] = $this->mapCategoryToAmazon($category);
        }
        
        $searchTerm = implode(' ', $searchTerms);
        
        Log::info("Amazon API: Buscando producto similar para: {$searchTerm}");
        
        $results = $this->searchProducts($searchTerm);
        
        if (isset($results['data']['products']) && count($results['data']['products']) > 0) {
            // Retornar solo los primeros 3 resultados más relevantes
            return [
                'success' => true,
                'products' => array_slice($results['data']['products'], 0, 3),
                'search_term' => $searchTerm
            ];
        }
        
        return [
            'success' => false,
            'products' => [],
            'message' => 'No se encontraron productos similares en Amazon'
        ];
    }

    /**
     * Mapear categorías de nuestra BD a términos de Amazon
     *
     * @param string $category
     * @return string
     */
    private function mapCategoryToAmazon($category)
    {
        $mapping = [
            'Audífonos' => 'headphones earphones',
            'Teclados' => 'gaming keyboard mechanical',
            'Monitores' => 'monitor display screen',
            'Micrófonos' => 'microphone recording audio',
            'Ratones' => 'gaming mouse mice',
            'Parlantes' => 'speakers audio sound'
        ];

        return $mapping[$category] ?? $category;
    }

    /**
     * Formatear respuesta de error estándar
     *
     * @param string $message
     * @return array
     */
    private function getErrorResponse($message)
    {
        return [
            'success' => false,
            'error' => true,
            'message' => $message,
            'data' => []
        ];
    }

    /**
     * Limpiar cache de Amazon API
     *
     * @return bool
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            Log::info("Amazon API: Cache limpiado exitosamente");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al limpiar cache de Amazon API: " . $e->getMessage());
            return false;
        }
    }
}