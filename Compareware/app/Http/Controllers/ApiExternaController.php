<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiExternaController extends Controller
{
    // 1. MercadoLibre: Buscar periféricos
    public function buscarMercadoLibre(Request $request)
    {
        $query = $request->get('q', 'teclado');
        $response = Http::get("https://api.mercadolibre.com/sites/MLA/search", [
            'q' => $query
        ]);
        return $response->json();
    }

    // 2. eBay: Buscar periféricos
    public function buscarEbay(Request $request)
    {
        $query = $request->get('q', 'keyboard');
        $response = Http::get("https://api.ebay.com/buy/browse/v1/item_summary/search", [
            'q' => $query
        ]);
        return $response->json();
    }

    // 3. Best Buy: Buscar productos (requiere API Key)
    public function buscarBestBuy(Request $request)
    {
        $query = $request->get('q', 'mouse');
        $apiKey = 'TU_API_KEY'; // Reemplaza por tu API Key
        $response = Http::get("https://api.bestbuy.com/v1/products((search=$query))", [
            'apiKey' => $apiKey,
            'format' => 'json'
        ]);
        return $response->json();
    }

    // 4. OpenWeatherMap: Clima actual
    public function clima(Request $request)
    {
        $city = $request->get('city', 'Mexico');
        $apiKey = '3d8be4d12217cc70ddf091ecee614918'; // Reemplaza por tu API Key
        $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);
        return $response->json();
    }

    // 5. IP Geolocation: Ubicación por IP
    public function geolocalizacion(Request $request)
    {
        $ip = $request->get('ip', $request->ip());
        $response = Http::get("http://ip-api.com/json/$ip");
        return $response->json();
    }
}
