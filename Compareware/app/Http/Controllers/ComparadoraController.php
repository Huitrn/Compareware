<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ComparadoraController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', 'teclado');

        // MercadoLibre
        $mercadolibre = Http::get('https://api.mercadolibre.com/sites/MLA/search', [
            'q' => $query
        ])->json();

        // eBay y BestBuy (deja vacío si no tienes credenciales)
        $ebay = [];
        $bestbuy = [];

        // Clima
        $clima = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => 'Mexico',
            'appid' => '3d8be4d12217cc70ddf091ecee614918', 
            'units' => 'metric'
        ])->json();

        // Geolocalización
        $geo = Http::get("http://ip-api.com/json/{$request->ip()}")->json();
            dd($geo);
        return view('Comparadora', compact('mercadolibre', 'ebay', 'bestbuy', 'clima', 'geo'));
    }
}