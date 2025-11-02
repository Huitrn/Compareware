<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Middlewares globales
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // <-- Agrega esta línea
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
        'simular.auth' => \App\Http\Middleware\SimularAuth::class,
        
        // 🛡️ MIDDLEWARES DE SEGURIDAD PERSONALIZADOS
        'sql.security' => \App\Http\Middleware\SQLSecurityMiddleware::class,
        'rate.limit' => \App\Http\Middleware\AdvancedRateLimiting::class,
        'secure.auth' => \App\Http\Middleware\SecureAuthentication::class,
        
        // 👨‍💼 MIDDLEWARE DE ADMINISTRACIÓN
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ];
}