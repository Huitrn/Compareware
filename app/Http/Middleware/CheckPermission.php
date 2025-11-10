<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions  Los permisos requeridos (puede ser uno o varios)
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder a esta sección.');
        }

        $user = auth()->user();

        // Verificar si el usuario tiene un rol
        if (!$user->userRole) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        // Verificar si el rol está activo
        if (!$user->userRole->is_active) {
            abort(403, 'Su rol ha sido desactivado.');
        }

        // Verificar si tiene alguno de los permisos requeridos
        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'No tiene los permisos necesarios para realizar esta acción.');
        }

        return $next($request);
    }
}
