<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentDetector
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detectar ambiente basado en el dominio
        $this->detectEnvironmentByDomain($request);
        
        // Agregar headers de ambiente
        $response = $next($request);
        
        // Solo agregar headers en ambientes de desarrollo/staging
        if (!app()->environment('production')) {
            $response->headers->set('X-Environment', app()->environment());
            $response->headers->set('X-App-Version', config('app.version', '1.0.0'));
            $response->headers->set('X-Debug-Mode', config('app.debug') ? 'enabled' : 'disabled');
        }
        
        // Log de acceso por ambiente
        $this->logAccess($request);
        
        return $response;
    }

    /**
     * Detectar ambiente basado en el dominio
     */
    protected function detectEnvironmentByDomain(Request $request): void
    {
        $host = $request->getHost();
        
        // Mapeo de dominios a ambientes
        $domainEnvironmentMap = [
            'sandbox.compareware.local' => 'sandbox',
            'localhost' => 'sandbox',
            '127.0.0.1' => 'sandbox',
            'staging.compareware.com' => 'staging',
            'staging-api.compareware.com' => 'staging',
            'compareware.com' => 'production',
            'api.compareware.com' => 'production',
            'www.compareware.com' => 'production'
        ];

        // Si el dominio coincide con un ambiente específico, agregarlo al contexto
        if (isset($domainEnvironmentMap[$host])) {
            $detectedEnv = $domainEnvironmentMap[$host];
            
            // Agregar al contexto de la request
            $request->attributes->set('detected_environment', $detectedEnv);
            
            // Validar que coincida con el ambiente configurado
            if (app()->environment() !== $detectedEnv) {
                Log::channel('security')->warning('Environment mismatch detected', [
                    'configured_env' => app()->environment(),
                    'detected_env' => $detectedEnv,
                    'host' => $host,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }
        }
    }

    /**
     * Log de acceso específico por ambiente
     */
    protected function logAccess(Request $request): void
    {
        $environment = app()->environment();
        $logData = [
            'environment' => $environment,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ];

        // Log diferenciado por ambiente
        switch ($environment) {
            case 'sandbox':
                // En sandbox, log todo para debugging
                Log::channel('user_activity')->debug('Request processed', $logData);
                break;
                
            case 'staging':
                // En staging, log requests importantes
                if ($this->isImportantRequest($request)) {
                    Log::channel('user_activity')->info('Important request processed', $logData);
                }
                break;
                
            case 'production':
                // En production, solo log errores y requests críticos
                if ($this->isCriticalRequest($request)) {
                    Log::channel('user_activity')->warning('Critical request processed', $logData);
                }
                break;
        }
    }

    /**
     * Determinar si es una request importante (para staging)
     */
    protected function isImportantRequest(Request $request): bool
    {
        $importantPaths = [
            '/api/',
            '/admin/',
            '/login',
            '/register',
            '/comparar'
        ];

        foreach ($importantPaths as $path) {
            if (str_contains($request->getPathInfo(), $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si es una request crítica (para production)
     */
    protected function isCriticalRequest(Request $request): bool
    {
        $criticalPaths = [
            '/api/payment',
            '/api/user/delete',
            '/admin/users',
            '/admin/system'
        ];

        foreach ($criticalPaths as $path) {
            if (str_contains($request->getPathInfo(), $path)) {
                return true;
            }
        }

        // También considerar requests con errores 4xx/5xx como críticas
        return $request->attributes->has('error_code');
    }
}