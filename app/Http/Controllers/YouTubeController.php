<?php

namespace App\Http\Controllers;

use App\Services\YouTubeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar integración con YouTube API
 */
class YouTubeController extends Controller
{
    private $youtubeService;

    public function __construct(YouTubeApiService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    /**
     * Buscar videos de reviews para un producto
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchVideos(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|min:3|max:200',
            'search_type' => 'nullable|in:review,unboxing,tutorial,comparison',
            'max_results' => 'nullable|integer|min:1|max:10',
        ]);

        $productName = $validated['product_name'];
        $searchType = $validated['search_type'] ?? 'review';
        $maxResults = $validated['max_results'] ?? 5;

        try {
            $result = $this->youtubeService->searchProductVideos(
                $productName,
                $searchType,
                $maxResults
            );

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("❌ Error en YouTubeController::searchVideos", [
                'error' => $e->getMessage(),
                'product' => $productName,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar videos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Obtener todos los tipos de videos para un producto
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllVideos(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|min:3|max:200',
        ]);

        $productName = $validated['product_name'];

        try {
            $result = $this->youtubeService->getAllProductVideos($productName);

            return response()->json([
                'success' => true,
                'product' => $productName,
                'data' => $result,
                'timestamp' => now()->toIso8601String(),
            ], 200);

        } catch (\Exception $e) {
            Log::error("❌ Error en YouTubeController::getAllVideos", [
                'error' => $e->getMessage(),
                'product' => $productName,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener videos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Obtener detalles de un video específico
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVideoDetails(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'required|string|min:5|max:50',
        ]);

        $videoId = $validated['video_id'];

        try {
            $result = $this->youtubeService->getVideoDetails($videoId);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("❌ Error en YouTubeController::getVideoDetails", [
                'error' => $e->getMessage(),
                'video_id' => $videoId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del video',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Vista de prueba para mostrar videos de YouTube
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function testView(Request $request)
    {
        $productName = $request->get('product', 'Logitech G502');
        
        try {
            $videos = $this->youtubeService->searchProductVideos($productName, 'review', 5);
            
            return view('youtube.test', [
                'product' => $productName,
                'videos' => $videos,
            ]);

        } catch (\Exception $e) {
            return view('youtube.test', [
                'product' => $productName,
                'videos' => ['success' => false, 'message' => $e->getMessage()],
            ]);
        }
    }
}
