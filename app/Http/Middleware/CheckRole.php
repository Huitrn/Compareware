<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  El slug del rol requerido
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder a esta sección.');
        }

        $user = auth()->user();

        // Verificar si el usuario tiene el rol requerido
        if (!$user->userRole || $user->userRole->slug !== $role) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        // Verificar si el rol está activo
        if (!$user->userRole->is_active) {
            abort(403, 'Su rol ha sido desactivado.');
        }

        return $next($request);
    }
}
