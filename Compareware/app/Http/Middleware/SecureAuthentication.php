<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use App\Services\SecurityLogger;

class SecureAuthentication
{
    protected $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        // Verificar autenticación básica
        if (!$this->attemptAuthentication($request, $guards)) {
            return $this->respondUnauthorized('Authentication required');
        }

        $user = Auth::user();
        $token = $request->bearerToken();

        // Validaciones de seguridad adicionales
        $securityChecks = [
            'token_validation' => $this->validateToken($token),
            'user_status' => $this->validateUserStatus($user),
            'ip_validation' => $this->validateIpAddress($request, $user),
            'token_rotation' => $this->checkTokenRotation($token),
            'concurrent_sessions' => $this->checkConcurrentSessions($user),
            'suspicious_activity' => $this->checkSuspiciousActivity($request, $user)
        ];

        // Procesar resultados de validación
        foreach ($securityChecks as $check => $result) {
            if (!$result['valid']) {
                $this->logSecurityFailure($check, $result, $request, $user);
                
                if ($result['block']) {
                    return $this->respondSecurityBlock($result['reason'], $check);
                }
            }
        }

        // Log de acceso exitoso
        $this->logSuccessfulAccess($request, $user);

        // Actualizar metadatos de la sesión
        $this->updateSessionMetadata($request, $user);

        return $next($request);
    }

    /**
     * Intentar autenticación usando guards
     */
    private function attemptAuthentication(Request $request, array $guards): bool
    {
        if (empty($guards)) {
            $guards = ['sanctum'];
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validar token Sanctum específicamente
     */
    private function validateToken(?string $token): array
    {
        if (!$token) {
            return ['valid' => false, 'block' => true, 'reason' => 'Missing authentication token'];
        }

        // Buscar token en BD
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return ['valid' => false, 'block' => true, 'reason' => 'Invalid token'];
        }

        // Verificar expiración
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return ['valid' => false, 'block' => true, 'reason' => 'Expired token'];
        }

        // Verificar si el token ha sido revocado
        if (!$accessToken->tokenable) {
            return ['valid' => false, 'block' => true, 'reason' => 'Revoked token'];
        }

        // Verificar abilities del token
        if ($accessToken->abilities && !in_array('*', $accessToken->abilities)) {
            $requiredAbility = $this->getRequiredAbility(request());
            if ($requiredAbility && !in_array($requiredAbility, $accessToken->abilities)) {
                return ['valid' => false, 'block' => true, 'reason' => 'Insufficient token permissions'];
            }
        }

        return ['valid' => true, 'token' => $accessToken];
    }

    /**
     * Validar estado del usuario
     */
    private function validateUserStatus($user): array
    {
        if (!$user) {
            return ['valid' => false, 'block' => true, 'reason' => 'User not found'];
        }

        // Verificar si el usuario está activo
        if (method_exists($user, 'is_active') && !$user->is_active) {
            return ['valid' => false, 'block' => true, 'reason' => 'User account disabled'];
        }

        // Verificar si la cuenta está bloqueada por intentos fallidos
        if (method_exists($user, 'isLocked') && $user->isLocked()) {
            return ['valid' => false, 'block' => true, 'reason' => 'Account locked due to failed attempts'];
        }

        // Verificar verificación de email si es requerida
        if (config('auth.email_verification_required', false) && !$user->hasVerifiedEmail()) {
            return ['valid' => false, 'block' => true, 'reason' => 'Email verification required'];
        }

        return ['valid' => true];
    }

    /**
     * Validar dirección IP
     */
    private function validateIpAddress(Request $request, $user): array
    {
        $currentIp = $request->ip();
        
        // Verificar IP blacklist global
        if ($this->isIpBlacklisted($currentIp)) {
            return ['valid' => false, 'block' => true, 'reason' => 'IP address blacklisted'];
        }

        // Verificar geolocalización sospechosa (opcional)
        if (config('auth.geo_validation', false)) {
            $geoCheck = $this->validateGeolocation($currentIp, $user);
            if (!$geoCheck['valid']) {
                return $geoCheck;
            }
        }

        return ['valid' => true];
    }

    /**
     * Verificar rotación de tokens
     */
    private function checkTokenRotation(?string $token): array
    {
        if (!$token || !config('auth.token_rotation', true)) {
            return ['valid' => true];
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return ['valid' => true]; // Ya se maneja en validateToken
        }

        // Si el token tiene más de 30 minutos, sugerir rotación (no bloquear)
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
        if ($accessToken->last_used_at && $accessToken->last_used_at->isBefore($thirtyMinutesAgo)) {
            return [
                'valid' => true, 
                'rotation_needed' => true, 
                'message' => 'Token rotation recommended'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Verificar sesiones concurrentes
     */
    private function checkConcurrentSessions($user): array
    {
        $maxSessions = config('auth.max_concurrent_sessions', 5);
        
        if ($maxSessions <= 0) {
            return ['valid' => true];
        }

        // Contar tokens activos del usuario
        $activeTokens = PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', Carbon::now());
            })
            ->count();

        if ($activeTokens > $maxSessions) {
            return [
                'valid' => false, 
                'block' => false, // No bloquear, solo alertar
                'reason' => 'Too many concurrent sessions',
                'active_sessions' => $activeTokens
            ];
        }

        return ['valid' => true];
    }

    /**
     * Detectar actividad sospechosa
     */
    private function checkSuspiciousActivity(Request $request, $user): array
    {
        $cacheKey = "user_activity:{$user->id}";
        $activity = Cache::get($cacheKey, [
            'request_count' => 0,
            'last_reset' => Carbon::now(),
            'unique_ips' => [],
            'user_agents' => []
        ]);

        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();

        // Reset contador cada hora
        if (Carbon::parse($activity['last_reset'])->addHour()->isPast()) {
            $activity = [
                'request_count' => 0,
                'last_reset' => Carbon::now(),
                'unique_ips' => [],
                'user_agents' => []
            ];
        }

        $activity['request_count']++;
        
        if (!in_array($currentIp, $activity['unique_ips'])) {
            $activity['unique_ips'][] = $currentIp;
        }
        
        if (!in_array($currentUserAgent, $activity['user_agents'])) {
            $activity['user_agents'][] = $currentUserAgent;
        }

        Cache::put($cacheKey, $activity, 3600);

        // Detectar patrones sospechosos
        $suspicious = false;
        $reasons = [];

        // Demasiadas requests por hora
        if ($activity['request_count'] > 1000) {
            $suspicious = true;
            $reasons[] = 'Excessive requests per hour';
        }

        // Múltiples IPs
        if (count($activity['unique_ips']) > 10) {
            $suspicious = true;
            $reasons[] = 'Multiple IP addresses';
        }

        // Múltiples User-Agents
        if (count($activity['user_agents']) > 5) {
            $suspicious = true;
            $reasons[] = 'Multiple user agents';
        }

        if ($suspicious) {
            return [
                'valid' => false,
                'block' => false, // Solo alertar por ahora
                'reason' => implode(', ', $reasons),
                'activity' => $activity
            ];
        }

        return ['valid' => true];
    }

    /**
     * Verificar si IP está en blacklist
     */
    private function isIpBlacklisted(string $ip): bool
    {
        return Cache::has("blacklist_ip:{$ip}");
    }

    /**
     * Obtener ability requerida para la ruta actual
     */
    private function getRequiredAbility(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) return null;

        // Mapear rutas a abilities
        $routeAbilities = [
            'admin.*' => 'admin',
            '*.store' => 'create',
            '*.update' => 'update', 
            '*.destroy' => 'delete',
            '*.index' => 'read',
            '*.show' => 'read'
        ];

        $routeName = $route->getName();
        foreach ($routeAbilities as $pattern => $ability) {
            if (fnmatch($pattern, $routeName)) {
                return $ability;
            }
        }

        return null;
    }

    /**
     * Log de fallo de seguridad
     */
    private function logSecurityFailure(string $check, array $result, Request $request, $user): void
    {
        $this->securityLogger->logSecurityEvent('AUTHENTICATION_FAILURE', [
            'check_type' => $check,
            'failure_reason' => $result['reason'],
            'user_id' => $user?->id,
            'will_block' => $result['block'] ?? false,
            'additional_data' => $result
        ], $result['block'] ? 'HIGH' : 'MEDIUM');
    }

    /**
     * Log de acceso exitoso
     */
    private function logSuccessfulAccess(Request $request, $user): void
    {
        Log::channel('audit')->info('Successful Authentication', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Actualizar metadatos de sesión
     */
    private function updateSessionMetadata(Request $request, $user): void
    {
        // Actualizar último acceso del usuario
        $user->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip' => $request->ip()
        ]);

        // Actualizar último uso del token si existe
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $accessToken->forceFill(['last_used_at' => Carbon::now()])->save();
            }
        }
    }

    /**
     * Respuesta de no autorizado
     */
    private function respondUnauthorized(string $message): Response
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $message,
            'code' => 'AUTH_REQUIRED',
            'timestamp' => Carbon::now()->toISOString()
        ], 401);
    }

    /**
     * Respuesta de bloqueo de seguridad
     */
    private function respondSecurityBlock(string $reason, string $checkType): Response
    {
        return response()->json([
            'error' => 'Access Denied',
            'message' => 'Access denied for security reasons',
            'code' => 'SECURITY_BLOCK',
            'check_type' => $checkType,
            'reason' => $reason,
            'timestamp' => Carbon::now()->toISOString()
        ], 403);
    }
}