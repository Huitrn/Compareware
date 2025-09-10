<?php

namespace App\Http\Controllers;

use App\Models\Periferico;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('Prueba de escritura en el log');

        $productos = Periferico::all();
        $categorias = Categoria::all();

        // API de geolocalización
        $ip = '8.8.8.8'; // IP pública de Google
        $geo = Http::get("http://ip-api.com/json/{$ip}")->json();
        Log::info('Geo:', ['response' => $geo]);

        // API de clima
        $clima = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => 'Mexico',
            'appid' => '3d8be4d12217cc70ddf091ecee614918',
            'units' => 'metric'
        ])->json();
        Log::info('Clima:', ['response' => $clima]);

        // API MercadoLibre (usuarios)
        $token = 'APP_USR-12345678-031820-X-12345678'; // Reemplaza por tu token real
        $mlUser = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('https://api.mercadolibre.com/users/me')->json();
        Log::info('MercadoLibre User:', ['response' => $mlUser]);

        return view('comparadora', compact('productos', 'categorias', 'geo', 'clima', 'mlUser'));
    }
}