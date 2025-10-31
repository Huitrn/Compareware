<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AmazonApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    protected $amazonService;

    public function __construct(AmazonApiService $amazonService)
    {
        $this->amazonService = $amazonService;
    }

    /**
     * Buscar productos en Amazon
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAmazon(Request $request)
    {
        $request->validate([
            'search_term' => 'required|string|min:2',
            'country' => 'string|in:US,CA,MX'
        ]);

        $searchTerm = $request->input('search_term');
        $country = $request->input('country', 'US');

        try {
            $results = $this->amazonService->searchProducts($searchTerm, $country);
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'search_term' => $searchTerm,
                'country' => $country
            ]);

        } catch (\Exception $e) {
            Log::error("Error en búsqueda Amazon: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => 'No se pudo realizar la búsqueda en Amazon'
            ], 500);
        }
    }

    /**
     * Obtener comparación enriquecida con datos de Amazon
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnhancedComparison(Request $request)
    {
        $request->validate([
            'periferico1' => 'required|integer|exists:perifericos,id',
            'periferico2' => 'required|integer|exists:perifericos,id'
        ]);

        try {
            // Obtener productos locales con detalles completos
            $producto1 = $this->getProductWithDetails($request->periferico1);
            $producto2 = $this->getProductWithDetails($request->periferico2);

            // Buscar productos similares en Amazon
            $amazonData1 = $this->amazonService->findSimilarProduct(
                $producto1->nombre,
                $producto1->marca,
                $producto1->categoria_nombre
            );

            $amazonData2 = $this->amazonService->findSimilarProduct(
                $producto2->nombre,
                $producto2->marca,
                $producto2->categoria_nombre
            );

            return response()->json([
                'success' => true,
                'comparison' => [
                    'local_products' => [
                        'product1' => $producto1,
                        'product2' => $producto2
                    ],
                    'amazon_data' => [
                        'product1' => $amazonData1,
                        'product2' => $amazonData2
                    ]
                ],
                'analysis' => $this->generateComparisonAnalysis($producto1, $producto2, $amazonData1, $amazonData2)
            ]);

        } catch (\Exception $e) {
            Log::error("Error en comparación enriquecida: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener datos de comparación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener precios actuales de Amazon para un producto específico
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAmazonPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:perifericos,id'
        ]);

        try {
            $producto = $this->getProductWithDetails($request->product_id);
            
            $amazonData = $this->amazonService->findSimilarProduct(
                $producto->nombre,
                $producto->marca,
                $producto->categoria_nombre
            );

            $priceComparison = null;
            if ($amazonData['success'] && count($amazonData['products']) > 0) {
                $amazonProduct = $amazonData['products'][0];
                $amazonPrice = $this->extractPrice($amazonProduct);
                $localPrice = (float) $producto->precio;

                $priceComparison = [
                    'local_price' => $localPrice,
                    'amazon_price' => $amazonPrice,
                    'difference' => $amazonPrice - $localPrice,
                    'percentage_diff' => $localPrice > 0 ? (($amazonPrice - $localPrice) / $localPrice) * 100 : 0,
                    'is_amazon_cheaper' => $amazonPrice < $localPrice,
                    'savings' => $localPrice - $amazonPrice
                ];
            }

            return response()->json([
                'success' => true,
                'product' => $producto,
                'amazon_data' => $amazonData,
                'price_comparison' => $priceComparison
            ]);

        } catch (\Exception $e) {
            Log::error("Error al obtener precio Amazon: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al consultar precios de Amazon'
            ], 500);
        }
    }

    /**
     * Limpiar cache de datos de Amazon
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAmazonCache()
    {
        try {
            $cleared = $this->amazonService->clearCache();
            
            return response()->json([
                'success' => $cleared,
                'message' => $cleared ? 'Cache limpiado exitosamente' : 'Error al limpiar cache'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al limpiar cache',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener producto con todos sus detalles relacionados
     *
     * @param int $productId
     * @return object
     */
    private function getProductWithDetails($productId)
    {
        return DB::table('perifericos')
            ->join('categorias', 'perifericos.categoria_id', '=', 'categorias.id')
            ->select(
                'perifericos.*',
                'categorias.nombre as categoria_nombre'
            )
            ->where('perifericos.id', $productId)
            ->first();
    }

    /**
     * Extraer precio numérico de datos de Amazon
     *
     * @param array $amazonProduct
     * @return float
     */
    private function extractPrice($amazonProduct)
    {
        // La API puede devolver precios en diferentes formatos
        $price = 0;
        
        if (isset($amazonProduct['price'])) {
            $priceStr = is_array($amazonProduct['price']) 
                ? ($amazonProduct['price']['current_price'] ?? $amazonProduct['price']['value'] ?? '0')
                : $amazonProduct['price'];
            
            // Limpiar formato de precio (remover $, comas, etc.)
            $price = (float) preg_replace('/[^\d.]/', '', $priceStr);
        }
        
        return $price;
    }

    /**
     * Generar análisis de comparación con datos de Amazon
     *
     * @param object $producto1
     * @param object $producto2
     * @param array $amazonData1
     * @param array $amazonData2
     * @return array
     */
    private function generateComparisonAnalysis($producto1, $producto2, $amazonData1, $amazonData2)
    {
        $analysis = [
            'price_analysis' => [],
            'availability' => [],
            'recommendations' => []
        ];

        // Análisis de precios
        if ($amazonData1['success'] && count($amazonData1['products']) > 0) {
            $amazonPrice1 = $this->extractPrice($amazonData1['products'][0]);
            $localPrice1 = (float) $producto1->precio;
            
            $analysis['price_analysis']['product1'] = [
                'local_price' => $localPrice1,
                'amazon_price' => $amazonPrice1,
                'savings_vs_amazon' => $amazonPrice1 - $localPrice1,
                'is_local_cheaper' => $localPrice1 < $amazonPrice1
            ];
        }

        if ($amazonData2['success'] && count($amazonData2['products']) > 0) {
            $amazonPrice2 = $this->extractPrice($amazonData2['products'][0]);
            $localPrice2 = (float) $producto2->precio;
            
            $analysis['price_analysis']['product2'] = [
                'local_price' => $localPrice2,
                'amazon_price' => $amazonPrice2,
                'savings_vs_amazon' => $amazonPrice2 - $localPrice2,
                'is_local_cheaper' => $localPrice2 < $amazonPrice2
            ];
        }

        // Disponibilidad
        $analysis['availability'] = [
            'product1_on_amazon' => $amazonData1['success'] && count($amazonData1['products']) > 0,
            'product2_on_amazon' => $amazonData2['success'] && count($amazonData2['products']) > 0,
        ];

        // Recomendaciones
        $recommendations = [];
        
        if (isset($analysis['price_analysis']['product1']) && $analysis['price_analysis']['product1']['is_local_cheaper']) {
            $recommendations[] = "El {$producto1->nombre} está a mejor precio aquí que en Amazon";
        }
        
        if (isset($analysis['price_analysis']['product2']) && $analysis['price_analysis']['product2']['is_local_cheaper']) {
            $recommendations[] = "El {$producto2->nombre} está a mejor precio aquí que en Amazon";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Compara precios en ambas plataformas antes de comprar";
        }
        
        $analysis['recommendations'] = $recommendations;

        return $analysis;
    }
}