<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComparadoraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComparacionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/comparadora', function () {
    return view('comparadora');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/registro', function () {
    return view('registro');
});

Route::get('/perfil', function () {
    return view('perfil');
});

Route::get('/editar', function () {
    return view('editar');
});

Route::get('/marcas', function () {
    return view('marcas');
});

Route::get('/comparadora', [ComparadoraController::class, 'index'])->name('comparadora');

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/comparadora', [ProductoController::class, 'index'])->name('comparadora');

Route::get('/comparar-perifericos', [ComparacionController::class, 'comparar']);