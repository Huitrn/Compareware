<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Periferico;
use App\Policies\UserPolicy;
use App\Policies\PerifericoPolicy;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Periferico::class => PerifericoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 🚪 GATES PERSONALIZADOS PARA CONTROL GRANULAR

        // Gate para acceso administrativo
        Gate::define('admin-access', function (User $user) {
            $hasAccess = $user->isAdmin();
            
            if (!$hasAccess) {
                Log::channel('security')->warning('Unauthorized admin access attempt', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'ip' => request()->ip(),
                    'route' => request()->route()?->getName()
                ]);
            }

            return $hasAccess;
        });

        // Gate para acciones sensibles
        Gate::define('sensitive-action', function (User $user, string $action) {
            $allowed = $user->isAdmin();
            
            Log::channel('audit')->info('Sensitive action attempt', [
                'user_id' => $user->id,
                'action' => $action,
                'allowed' => $allowed,
                'ip' => request()->ip()
            ]);

            return $allowed;
        });

        // Gate para gestión de usuarios
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // Gate para acceso a reportes de seguridad
        Gate::define('view-security-reports', function (User $user) {
            return $user->isAdmin();
        });

        // Gate para modificar configuración del sistema
        Gate::define('modify-system-config', function (User $user) {
            $canModify = $user->isAdmin() && $user->role === 'admin';
            
            if (!$canModify) {
                Log::channel('security')->critical('System config modification attempt', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'ip' => request()->ip()
                ]);
            }

            return $canModify;
        });

        // Gate para acciones de desarrollo (solo en desarrollo)
        Gate::define('development-actions', function (User $user) {
            if (app()->environment('production')) {
                return false;
            }
            
            return $user->isAdmin();
        });

        // Gate para operaciones masivas
        Gate::define('bulk-operations', function (User $user) {
            $canPerform = $user->isAdmin();
            
            Log::channel('audit')->info('Bulk operation attempt', [
                'user_id' => $user->id,
                'allowed' => $canPerform,
                'ip' => request()->ip()
            ]);

            return $canPerform;
        });

        // Gate para acceso API externo
        Gate::define('external-api-access', function (User $user) {
            return $user->isAdmin() || $user->role === 'api_user';
        });

        // Gate condicional basado en IP
        Gate::define('admin-ip-check', function (User $user) {
            if (!$user->isAdmin()) {
                return false;
            }

            $allowedIps = config('auth.admin_allowed_ips', []);
            if (empty($allowedIps)) {
                return true; // Si no hay restricción de IP
            }

            $currentIp = request()->ip();
            $isAllowed = in_array($currentIp, $allowedIps);

            if (!$isAllowed) {
                Log::channel('security')->critical('Admin access from unauthorized IP', [
                    'user_id' => $user->id,
                    'ip' => $currentIp,
                    'allowed_ips' => $allowedIps
                ]);
            }

            return $isAllowed;
        });

        // Gate para horario laboral (opcional)
        Gate::define('business-hours-access', function (User $user) {
            if ($user->isAdmin()) {
                return true; // Admins siempre pueden acceder
            }

            $businessHours = config('auth.business_hours');
            if (!$businessHours['enabled']) {
                return true;
            }

            $now = now();
            $currentHour = $now->hour;
            $currentDay = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday

            // Verificar día de la semana
            if (!in_array($currentDay, $businessHours['allowed_days'])) {
                return false;
            }

            // Verificar hora
            return $currentHour >= $businessHours['start_hour'] && 
                   $currentHour <= $businessHours['end_hour'];
        });

        // Gate para verificar estado de sesión
        Gate::define('valid-session', function (User $user) {
            // Verificar si hay demasiadas sesiones activas
            $maxSessions = config('auth.max_concurrent_sessions', 5);
            if ($maxSessions > 0) {
                $activeSessions = $user->tokens()->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })->count();

                if ($activeSessions > $maxSessions) {
                    Log::channel('security')->warning('Excessive concurrent sessions', [
                        'user_id' => $user->id,
                        'active_sessions' => $activeSessions,
                        'max_allowed' => $maxSessions
                    ]);
                    return false;
                }
            }

            return true;
        });

        // Super admin gate (para acciones críticas)
        Gate::define('super-admin', function (User $user) {
            $isSuperAdmin = $user->isAdmin() && $user->email === config('auth.super_admin_email');
            
            if (request()->has('super_admin_action')) {
                Log::channel('security')->info('Super admin action attempted', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'is_super_admin' => $isSuperAdmin,
                    'action' => request('super_admin_action'),
                    'ip' => request()->ip()
                ]);
            }

            return $isSuperAdmin;
        });

        // Gate before hook - ejecutado antes de todos los gates
        Gate::before(function (User $user, string $ability) {
            // Log de todos los intentos de autorización
            Log::channel('audit')->debug('Authorization check', [
                'user_id' => $user->id,
                'ability' => $ability,
                'ip' => request()->ip(),
                'route' => request()->route()?->getName()
            ]);

            // Super admin bypass (opcional)
            if ($user->email === config('auth.super_admin_email') && 
                config('auth.super_admin_bypass', false)) {
                return true;
            }

            // No return value = continue with normal gate evaluation
        });

        // Gate after hook - ejecutado después de la evaluación
        Gate::after(function (User $user, string $ability, bool $result, $arguments = []) {
            // Log de resultados de autorización para habilidades críticas
            $criticalAbilities = ['admin-access', 'sensitive-action', 'modify-system-config'];
            
            if (in_array($ability, $criticalAbilities)) {
                Log::channel('security')->info('Critical authorization result', [
                    'user_id' => $user->id,
                    'ability' => $ability,
                    'result' => $result,
                    'arguments' => $arguments,
                    'ip' => request()->ip()
                ]);
            }
        });
    }
}