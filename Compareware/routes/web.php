<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // Importa Request
use App\Models\User;         // Importa User
use App\Http\Controllers\ComparadoraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComparacionController;

// Rutas de vistas

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/comparadora', function () {
    $categorias = \App\Models\Categoria::all();
    $productos = \App\Models\Periferico::all();
    return view('comparadora', compact('categorias', 'productos'));
})->name('comparadora');

Route::get('/comparar-perifericos', [ComparacionController::class, 'comparar']);

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/registro', function () {
    return view('registro');
})->name('registro');

Route::get('/perfil', function () {
    return view('perfil');
})->name('perfil');

Route::get('/editar', function () {
    return view('editar');
})->name('editar');
Route::get('/marcas', function () {
    return view('marcas');
})->name('marcas');

Route::post('/test-web', function () {
    return 'POST funcionando en web.php';
});

Route::get('/Hola', function(){
    return 'Hola mundo :)';
});