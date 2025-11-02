<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SecurityLogger;

class ApiExternaController extends Controller
{
    protected SecurityLogger $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
        
        // Aplicar middlewares de seguridad
        $this->middleware(['sql.security:strict', 'rate.limit']);
    }

    /**
     * Sanitizar parámetros de búsqueda
     */
    private function sanitizeSearchQuery($query, $default = ''): string
    {
        if (is_null($query) || !is_string($query)) {
            return $default;
        }

        // Limitar longitud
        if (strlen($query) > 100) {
            $query = substr($query, 0, 100);
        }

        // Remover caracteres peligrosos
        $dangerous_patterns = [
            '/[\'";\\\\]/',
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b/i',
            '/(-{2,}|\/\*|\*\/|\#)/',
            '/<[^>]*>/',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/[<>&\'"()]/',
        ];

        $originalQuery = $query;
        foreach ($dangerous_patterns as $pattern) {
            $query = preg_replace($pattern, '', $query);
        }

        // Log si se detecta intento malicioso
        if ($query !== $originalQuery) {
            $this->securityLogger->logSecurityEvent('MALICIOUS_SEARCH_QUERY', [
                'controller' => 'ApiExternaController',
                'original_query' => $originalQuery,
                'sanitized_query' => $query,
                'ip' => request()->ip()
            ], 'HIGH');
        }

        return trim($query) ?: $default;
    }

    /**
     * Validar IP address
     */
    private function validateIpAddress($ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        $this->securityLogger->logSecurityEvent('INVALID_IP_PROVIDED', [
            'provided_ip' => $ip,
            'client_ip' => request()->ip()
        ], 'MEDIUM');
        
        return request()->ip(); // Fallback a IP real del cliente
    }

    // 1. MercadoLibre: Buscar periféricos - SEGURO
    public function buscarMercadoLibre(Request $request)
    {
        $query = $this->sanitizeSearchQuery($request->get('q'), 'teclado');
        
        $this->securityLogger->logSecurityEvent('EXTERNAL_API_SEARCH', [
            'api' => 'MercadoLibre',
            'query' => $query,
            'ip' => request()->ip()
        ], 'LOW');

        try {
            $response = Http::timeout(10)->get("https://api.mercadolibre.com/sites/MLA/search", [
                'q' => $query
            ]);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'External API error'], 500);
        }
    }

    // 2. eBay: Buscar periféricos - SEGURO
    public function buscarEbay(Request $request)
    {
        $query = $this->sanitizeSearchQuery($request->get('q'), 'keyboard');
        
        $this->securityLogger->logSecurityEvent('EXTERNAL_API_SEARCH', [
            'api' => 'eBay',
            'query' => $query,
            'ip' => request()->ip()
        ], 'LOW');

        try {
            $response = Http::timeout(10)->get("https://api.ebay.com/buy/browse/v1/item_summary/search", [
                'q' => $query
            ]);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'External API error'], 500);
        }
    }

    // 3. Best Buy: Buscar productos (requiere API Key) - SEGURO
    public function buscarBestBuy(Request $request)
    {
        $query = $this->sanitizeSearchQuery($request->get('q'), 'mouse');
        $apiKey = env('BESTBUY_API_KEY', 'TU_API_KEY'); // Usar variable de entorno
        
        $this->securityLogger->logSecurityEvent('EXTERNAL_API_SEARCH', [
            'api' => 'BestBuy',
            'query' => $query,
            'ip' => request()->ip()
        ], 'LOW');

        try {
            $response = Http::timeout(10)->get("https://api.bestbuy.com/v1/products((search=$query))", [
                'apiKey' => $apiKey,
                'format' => 'json'
            ]);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'External API error'], 500);
        }
    }

    // 4. OpenWeatherMap: Clima actual - SEGURO
    public function clima(Request $request)
    {
        $city = $this->sanitizeSearchQuery($request->get('city'), 'Mexico');
        $apiKey = env('OPENWEATHER_API_KEY', '3d8be4d12217cc70ddf091ecee614918');
        
        $this->securityLogger->logSecurityEvent('WEATHER_API_REQUEST', [
            'city' => $city,
            'ip' => request()->ip()
        ], 'LOW');

        try {
            $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Weather API error'], 500);
        }
    }

    // 5. IP Geolocation: Ubicación por IP - SEGURO
    public function geolocalizacion(Request $request)
    {
        $ip = $this->validateIpAddress($request->get('ip', $request->ip()));
        
        $this->securityLogger->logSecurityEvent('GEOLOCATION_REQUEST', [
            'target_ip' => $ip,
            'client_ip' => request()->ip()
        ], 'LOW');

        try {
            $response = Http::timeout(10)->get("http://ip-api.com/json/$ip");
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Geolocation API error'], 500);
        }
    }
}
