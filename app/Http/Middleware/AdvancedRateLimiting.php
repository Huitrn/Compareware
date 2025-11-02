<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class AdvancedRateLimiting
{
    /**
     * Configuraciones de rate limiting por ruta
     */
    private $routeConfigs = [
        'auth.login' => [
            'requests' => 5,        // 5 intentos
            'window' => 60,         // por minuto
            'block_duration' => 300 // bloqueo por 5 minutos tras exceder
        ],
        'auth.register' => [
            'requests' => 3,
            'window' => 300,        // 3 registros por 5 minutos
            'block_duration' => 600
        ],
        'api.perifericos.store' => [
            'requests' => 10,
            'window' => 60,
            'block_duration' => 120
        ],
        'api.perifericos.update' => [
            'requests' => 15,
            'window' => 60,
            'block_duration' => 120
        ],
        'api.perifericos.index' => [
            'requests' => 100,
            'window' => 60,
            'block_duration' => 60
        ],
        'api.perifericos.show' => [
            'requests' => 60,
            'window' => 60,
            'block_duration' => 60
        ],
        'default' => [
            'requests' => 60,
            'window' => 60,
            'block_duration' => 120
        ]
    ];

    /**
     * Rate limiting más agresivo para IPs sospechosas
     */
    private $suspiciousIpLimits = [
        'requests' => 10,
        'window' => 300,        // 10 requests por 5 minutos
        'block_duration' => 3600 // 1 hora de bloqueo
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->getClientIp($request);
        $route = $this->getRouteIdentifier($request);
        
        // Verificar si la IP está en lista negra
        if ($this->isBlacklisted($ip)) {
            return $this->respondBlacklisted($ip);
        }

        // Verificar si la IP es sospechosa
        $isSuspicious = $this->isSuspiciousIp($ip);
        
        // Obtener configuración de rate limiting
        $config = $isSuspicious 
            ? $this->suspiciousIpLimits 
            : ($this->routeConfigs[$route] ?? $this->routeConfigs['default']);

        // Verificar rate limit
        $rateLimitResult = $this->checkRateLimit($ip, $route, $config);
        
        if (!$rateLimitResult['allowed']) {
            return $this->respondRateLimited($rateLimitResult, $ip, $route);
        }

        // Continuar con la request
        $response = $next($request);

        // Agregar headers informativos
        return $this->addRateLimitHeaders($response, $rateLimitResult);
    }

    /**
     * Obtiene la IP real del cliente (considerando proxies)
     */
    private function getClientIp(Request $request): string
    {
        // Verificar headers de proxy en orden de prioridad
        $ipSources = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',           // Nginx
            'HTTP_X_FORWARDED_FOR',     // Standard proxy header
            'HTTP_CLIENT_IP',           // Proxy header
            'REMOTE_ADDR'               // Standard IP
        ];

        foreach ($ipSources as $source) {
            $ip = $request->server($source);
            if ($ip && $this->isValidIp($ip)) {
                // Si es X-Forwarded-For, tomar la primera IP
                if ($source === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                if ($this->isValidIp($ip) && !$this->isPrivateIp($ip)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    /**
     * Valida si es una IP válida
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Verifica si es IP privada
     */
    private function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Obtiene identificador de ruta
     */
    private function getRouteIdentifier(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        // Fallback a método + URI
        return strtolower($request->method()) . ':' . $request->path();
    }

    /**
     * Verifica si una IP está en lista negra
     */
    private function isBlacklisted(string $ip): bool
    {
        $blacklistKey = "rate_limit:blacklist:$ip";
        return Cache::has($blacklistKey);
    }

    /**
     * Verifica si una IP es sospechosa
     */
    private function isSuspiciousIp(string $ip): bool
    {
        $suspiciousKey = "rate_limit:suspicious:$ip";
        return Cache::get($suspiciousKey, false);
    }

    /**
     * Marca una IP como sospechosa
     */
    private function markAsSuspicious(string $ip, int $duration = 3600): void
    {
        $suspiciousKey = "rate_limit:suspicious:$ip";
        Cache::put($suspiciousKey, true, $duration);
    }

    /**
     * Agrega IP a lista negra temporal
     */
    private function blacklistIp(string $ip, int $duration): void
    {
        $blacklistKey = "rate_limit:blacklist:$ip";
        Cache::put($blacklistKey, [
            'blocked_at' => now()->toISOString(),
            'duration' => $duration,
            'reason' => 'Rate limit exceeded'
        ], $duration);

        // Log del bloqueo
        Log::channel('security')->warning('IP Blacklisted', [
            'ip' => $ip,
            'duration' => $duration,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Verifica rate limit para IP y ruta específica
     */
    private function checkRateLimit(string $ip, string $route, array $config): array
    {
        $cacheKey = "rate_limit:$ip:$route";
        $windowStart = now()->subSeconds($config['window']);
        
        // Obtener requests actuales
        $currentRequests = Cache::get($cacheKey, []);
        
        // Filtrar requests dentro de la ventana de tiempo
        $validRequests = array_filter($currentRequests, function($timestamp) use ($windowStart) {
            return Carbon::parse($timestamp)->isAfter($windowStart);
        });

        $requestCount = count($validRequests);
        $allowed = $requestCount < $config['requests'];
        
        if ($allowed) {
            // Agregar nueva request
            $validRequests[] = now()->toISOString();
            Cache::put($cacheKey, $validRequests, $config['window']);
        } else {
            // Excedió el límite
            $this->handleRateLimitExceeded($ip, $route, $config);
        }

        return [
            'allowed' => $allowed,
            'current_requests' => $requestCount,
            'limit' => $config['requests'],
            'window' => $config['window'],
            'reset_time' => now()->addSeconds($config['window'])->toISOString(),
            'remaining' => max(0, $config['requests'] - $requestCount)
        ];
    }

    /**
     * Maneja cuando se excede el rate limit
     */
    private function handleRateLimitExceeded(string $ip, string $route, array $config): void
    {
        // Bloquear IP temporalmente
        $this->blacklistIp($ip, $config['block_duration']);
        
        // Marcar como sospechosa si excede límites repetidamente
        $exceedanceKey = "rate_limit:exceedance:$ip";
        $exceedances = Cache::get($exceedanceKey, 0) + 1;
        Cache::put($exceedanceKey, $exceedances, 3600); // 1 hora
        
        if ($exceedances >= 3) {
            $this->markAsSuspicious($ip, 7200); // 2 horas
        }

        // Log del evento
        Log::channel('security')->warning('Rate Limit Exceeded', [
            'ip' => $ip,
            'route' => $route,
            'exceedances' => $exceedances,
            'config' => $config,
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Respuesta para IP en lista negra
     */
    private function respondBlacklisted(string $ip): JsonResponse
    {
        $blacklistInfo = Cache::get("rate_limit:blacklist:$ip");
        
        return response()->json([
            'error' => 'IP temporarily blocked',
            'code' => 'IP_BLACKLISTED',
            'message' => 'Your IP has been temporarily blocked due to excessive requests',
            'blocked_at' => $blacklistInfo['blocked_at'] ?? null,
            'retry_after' => $blacklistInfo['duration'] ?? null,
            'timestamp' => now()->toISOString()
        ], 429);
    }

    /**
     * Respuesta para rate limit excedido
     */
    private function respondRateLimited(array $rateLimitResult, string $ip, string $route): JsonResponse
    {
        return response()->json([
            'error' => 'Rate limit exceeded',
            'code' => 'RATE_LIMIT_EXCEEDED',
            'message' => 'Too many requests. Please slow down.',
            'current_requests' => $rateLimitResult['current_requests'],
            'limit' => $rateLimitResult['limit'],
            'window_seconds' => $rateLimitResult['window'],
            'reset_time' => $rateLimitResult['reset_time'],
            'timestamp' => now()->toISOString()
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $rateLimitResult['limit'],
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => Carbon::parse($rateLimitResult['reset_time'])->timestamp,
            'Retry-After' => $rateLimitResult['window']
        ]);
    }

    /**
     * Agregar headers de rate limit a response exitosa
     */
    private function addRateLimitHeaders(Response $response, array $rateLimitResult): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $rateLimitResult['limit'],
            'X-RateLimit-Remaining' => $rateLimitResult['remaining'],
            'X-RateLimit-Reset' => Carbon::parse($rateLimitResult['reset_time'])->timestamp
        ]);
    }

    /**
     * Método público para verificar estado de IP (para uso en otros middlewares)
     */
    public function getIpStatus(string $ip): array
    {
        return [
            'is_blacklisted' => $this->isBlacklisted($ip),
            'is_suspicious' => $this->isSuspiciousIp($ip),
            'exceedance_count' => Cache::get("rate_limit:exceedance:$ip", 0)
        ];
    }

    /**
     * Método público para limpiar rate limits (para testing o admin)
     */
    public function clearRateLimits(string $ip = null): void
    {
        if ($ip) {
            $keys = [
                "rate_limit:blacklist:$ip",
                "rate_limit:suspicious:$ip", 
                "rate_limit:exceedance:$ip"
            ];
            
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            // Limpiar todo (usar con cuidado)
            Cache::flush();
        }
    }
}