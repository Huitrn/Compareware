<?php

namespace App\Http\Controllers;

use App\Services\GoogleShoppingApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GoogleShoppingController extends Controller
{
    protected $googleShoppingService;
    
    public function __construct(GoogleShoppingApiService $googleShoppingService)
    {
        $this->googleShoppingService = $googleShoppingService;
    }
    
    /**
     * Buscar productos en Google Shopping
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProducts(Request $request)
    {
        try {
            // Validar entrada
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2|max:200',
                'country' => 'nullable|string|size:2',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            $query = $request->input('query');
            $country = $request->input('country', 'mx');
            $limit = $request->input('limit', 10);
            
            Log::info("🛒 GoogleShoppingController: Búsqueda de productos", [
                'query' => $query,
                'country' => $country,
                'limit' => $limit,
            ]);
            
            $result = $this->googleShoppingService->searchProducts($query, $country, $limit);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productos encontrados exitosamente',
                    'data' => $result,
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'No se encontraron productos',
                'data' => $result,
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ GoogleShoppingController: Error en búsqueda", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtener detalles de un producto específico
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'url' => 'required|url',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL inválida',
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            $url = $request->input('url');
            
            Log::info("🔍 GoogleShoppingController: Obteniendo detalles del producto", [
                'url' => $url,
            ]);
            
            $result = $this->googleShoppingService->getProductDetails($url);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Detalles del producto obtenidos exitosamente',
                    'data' => $result,
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron detalles del producto',
                'data' => $result,
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ GoogleShoppingController: Error al obtener detalles", [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Comparar precios de un producto en múltiples tiendas
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comparePrices(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2|max:200',
                'limit' => 'nullable|integer|min:5|max:50',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            $query = $request->input('query');
            $limit = $request->input('limit', 20);
            
            Log::info("💰 GoogleShoppingController: Comparando precios", [
                'query' => $query,
                'limit' => $limit,
            ]);
            
            $result = $this->googleShoppingService->comparePrices($query, $limit);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comparación de precios completada',
                    'data' => $result,
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Error al comparar precios',
            ], 404);
            
        } catch (\Exception $e) {
            Log::error("❌ GoogleShoppingController: Error en comparación", [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al comparar precios: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Vista de prueba para Google Shopping API
     * 
     * @return \Illuminate\View\View
     */
    public function testView()
    {
        return view('google-shopping.test');
    }
}
