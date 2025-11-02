<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\YouTubeController;
use App\Models\Periferico;
use App\Models\Categoria;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ruta de prueba
Route::get('/test-api', function () {
    return 'API funcionando';
});

// Rutas públicas para periféricos
Route::get('/perifericos', function () {
    return Periferico::with('categoria')->get();
});

Route::get('/perifericos/categoria/{id}', function ($id) {
    return Periferico::where('categoria_id', $id)->get();
});

Route::get('/categorias', function () {
    return Categoria::with('perifericos')->get();
});

// Generar token para usuarios autenticados
Route::middleware('auth')->get('/token', function () {
    $user = auth()->user();
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['token' => $token]);
});

// Rutas de integración con Amazon API
Route::middleware('auth:sanctum')->group(function () {
    // Búsqueda en Amazon
    Route::post('/amazon/search', [ApiController::class, 'searchAmazon']);
    
    // Comparación enriquecida con datos de Amazon
    Route::post('/amazon/enhanced-comparison', [ApiController::class, 'getEnhancedComparison']);
    
    // Obtener precio actual de Amazon para un producto
    Route::post('/amazon/price', [ApiController::class, 'getAmazonPrice']);
    
    // Limpiar cache de Amazon
    Route::delete('/amazon/cache', [ApiController::class, 'clearAmazonCache']);
});

// ============================================
// RUTAS DE YOUTUBE API
// ============================================
Route::prefix('youtube')->group(function () {
    // Buscar videos por producto (público)
    Route::post('/search', [YouTubeController::class, 'searchVideos']);
    
    // Obtener todos los tipos de videos (reviews, unboxings, tutorials)
    Route::post('/all-videos', [YouTubeController::class, 'getAllVideos']);
    
    // Obtener detalles de un video específico
    Route::post('/video-details', [YouTubeController::class, 'getVideoDetails']);
});