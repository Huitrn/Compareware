<?php

use App\Http\Controllers\ApiPerifericoController;
use App\Http\Controllers\ApiExternaController;

Route::get('/perifericos', [ApiPerifericoController::class, 'index']);
Route::get('/perifericos/{id}', [ApiPerifericoController::class, 'show']);
Route::get('/comparar', [ApiPerifericoController::class, 'comparar']);
Route::get('/categorias', [ApiPerifericoController::class, 'categorias']);
Route::post('/perifericos/{id}/comentarios', [ApiPerifericoController::class, 'comentar']);

// Rutas para APIs externas 

Route::get('/externo/mercadolibre', [ApiExternaController::class, 'buscarMercadoLibre']);
Route::get('/externo/ebay', [ApiExternaController::class, 'buscarEbay']);
Route::get('/externo/bestbuy', [ApiExternaController::class, 'buscarBestBuy']);
Route::get('/externo/clima', [ApiExternaController::class, 'clima']);
Route::get('/externo/geolocalizacion', [ApiExternaController::class, 'geolocalizacion']);