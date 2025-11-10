<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\YouTubeController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\GoogleShoppingController;
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

// ============================================
// RUTAS DE COMPARACIÓN CON IMÁGENES
// ============================================
Route::prefix('comparacion')->group(function () {
    // Obtener comparación con imágenes incluidas
    Route::get('/{id}/with-images', [App\Http\Controllers\ComparacionController::class, 'getComparisonWithImages']);
    
    // Obtener datos de comparación en formato JSON
    Route::get('/compare-products', [App\Http\Controllers\ComparacionController::class, 'compareProductsWithImages']);
});

// ============================================
// RUTAS DE CURRENCY API
// ============================================
Route::prefix('currency')->group(function () {
    // Convertir entre dos monedas
    Route::post('/convert', [CurrencyController::class, 'convert']);
    
    // Convertir a multiples monedas
    Route::post('/convert-multiple', [CurrencyController::class, 'convertMultiple']);
    
    // Obtener todas las tasas de cambio
    Route::get('/rates', [CurrencyController::class, 'getRates']);
    
    // Obtener monedas soportadas
    Route::get('/supported', [CurrencyController::class, 'getSupportedCurrencies']);
});

// ============================================
// RUTAS DE GOOGLE SHOPPING API
// ============================================
Route::prefix('google-shopping')->group(function () {
    // Buscar productos en Google Shopping
    Route::post('/search', [GoogleShoppingController::class, 'searchProducts']);
    
    // Obtener detalles de un producto específico
    Route::post('/product-details', [GoogleShoppingController::class, 'getProductDetails']);
    
    // Comparar precios entre múltiples tiendas
    Route::post('/compare-prices', [GoogleShoppingController::class, 'comparePrices']);
});