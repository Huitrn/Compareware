<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SimularAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar si hay una sesión simulada o usuario real
        if (!auth()->check() && !session('usuario_simulado')) {
            return response()->json([
                'error' => 'Acceso denegado',
                'mensaje' => 'Esta ruta requiere autenticación',
                'codigo' => 401,
                'accion_requerida' => 'Inicia sesión primero',
                'rutas_sugeridas' => [
                    '/simular-login' => 'Para simular login',
                    '/publica' => 'Ruta pública disponible'
                ]
            ], 401);
        }
        
        return $next($request);
    }
}