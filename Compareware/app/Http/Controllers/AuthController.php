<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecureAuthRequest;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected SecurityLogger $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
        
        // Aplicar middlewares de seguridad
        $this->middleware(['sql.security:strict', 'rate.limit']);
    }

    /**
     * Registro de usuario con validación y seguridad avanzada
     */
    public function register(SecureAuthRequest $request): JsonResponse
    {
        try {
            // Rate limiting específico para registro
            $key = 'register:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $this->securityLogger->logSecurityEvent('REGISTRATION_RATE_LIMIT', [
                    'ip' => $request->ip(),
                    'attempts' => RateLimiter::attempts($key)
                ], 'MEDIUM');

                return response()->json([
                    'error' => 'Too many registration attempts',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }

            RateLimiter::hit($key, 300); // 5 minutos

            // Crear usuario con datos validados
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'), // Auto-hasheado por mutator
                'role' => 'user' // Siempre user, no aceptar desde request
            ]);

            // Log de registro exitoso
            $this->securityLogger->logSecurityEvent('USER_REGISTRATION', [
                'user_id' => $user->id,
                'email' => $user->email,
                'success' => true
            ], 'LOW');

            // Crear token con expiración
            $token = $user->createToken('auth-token', ['*'], now()->addHours(2));

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'token' => $token->plainTextToken,
                'expires_at' => now()->addHours(2)->toISOString()
            ], 201);

        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent('REGISTRATION_ERROR', [
                'error' => $e->getMessage(),
                'input_data' => $request->except(['password'])
            ], 'HIGH');

            return response()->json([
                'success' => false,
                'message' => 'Error en el registro',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Login con protección contra ataques de fuerza bruta
     */
    public function login(SecureAuthRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $password = $request->validated('password');
        
        // Rate limiting por IP y email
        $ipKey = 'login:ip:' . $request->ip();
        $emailKey = 'login:email:' . $email;

        // Verificar rate limiting
        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
            $this->securityLogger->logSecurityEvent('BRUTE_FORCE_ATTACK', [
                'email' => $email,
                'ip_attempts' => RateLimiter::attempts($ipKey),
                'email_attempts' => RateLimiter::attempts($emailKey)
            ], 'HIGH');

            return response()->json([
                'error' => 'Too many login attempts',
                'retry_after' => max(RateLimiter::availableIn($ipKey), RateLimiter::availableIn($emailKey))
            ], 429);
        }

        try {
            // Buscar usuario
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Incrementar rate limiting incluso para usuarios inexistentes
                RateLimiter::hit($ipKey, 300);
                RateLimiter::hit($emailKey, 600);
                
                $this->securityLogger->logSecurityEvent('USER_ENUMERATION', [
                    'email' => $email,
                    'ip' => $request->ip()
                ], 'MEDIUM');

                return $this->loginFailureResponse('Credenciales inválidas');
            }

            // Verificar si la cuenta está bloqueada
            if (method_exists($user, 'isLocked') && $user->isLocked()) {
                $this->securityLogger->logSecurityEvent('LOCKED_ACCOUNT_ACCESS', [
                    'user_id' => $user->id,
                    'email' => $email
                ], 'MEDIUM');

                return response()->json([
                    'error' => 'Account locked',
                    'message' => 'Account temporarily locked due to failed attempts'
                ], 423);
            }

            // Verificar contraseña
            if (!Hash::check($password, $user->password)) {
                // Incrementar intentos fallidos
                if (method_exists($user, 'incrementFailedAttempts')) {
                    $user->incrementFailedAttempts();
                }

                RateLimiter::hit($ipKey, 300);
                RateLimiter::hit($emailKey, 600);

                $this->securityLogger->logSecurityEvent('AUTHENTICATION_FAILURE', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'reason' => 'invalid_password'
                ], 'MEDIUM');

                return $this->loginFailureResponse('Credenciales inválidas');
            }

            // Login exitoso
            RateLimiter::clear($ipKey);
            RateLimiter::clear($emailKey);

            // Registrar login exitoso
            if (method_exists($user, 'recordLogin')) {
                $user->recordLogin();
            }

            // Crear token con abilities específicas
            $abilities = $this->getTokenAbilities($user);
            $token = $user->createToken('auth-token', $abilities, now()->addHours(2));

            $this->securityLogger->logSecurityEvent('AUTHENTICATION_SUCCESS', [
                'user_id' => $user->id,
                'email' => $email
            ], 'LOW');

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'token' => $token->plainTextToken,
                'expires_at' => now()->addHours(2)->toISOString(),
                'abilities' => $abilities
            ]);

        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent('AUTHENTICATION_ERROR', [
                'error' => $e->getMessage(),
                'email' => $email
            ], 'HIGH');

            return response()->json([
                'success' => false,
                'message' => 'Error en el login',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Logout seguro con revocación de token
     */
    public function logout(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user) {
            // Revocar el token actual
            $user->currentAccessToken()?->delete();
            
            $this->securityLogger->logSecurityEvent('USER_LOGOUT', [
                'user_id' => $user->id
            ], 'LOW');
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Revocar todos los tokens (logout desde todos los dispositivos)
     */
    public function logoutAll(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user) {
            $user->tokens()->delete();
            
            $this->securityLogger->logSecurityEvent('USER_LOGOUT_ALL', [
                'user_id' => $user->id
            ], 'MEDIUM');
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesiones cerradas en todos los dispositivos'
        ]);
    }

    /**
     * Obtener abilities para el token basado en el rol del usuario
     */
    private function getTokenAbilities(User $user): array
    {
        $baseAbilities = ['read'];

        if ($user->isAdmin()) {
            return ['*']; // Todos los permisos para admin
        }

        return array_merge($baseAbilities, ['create', 'update:own']);
    }

    /**
     * Respuesta estandardizada para fallos de login
     */
    private function loginFailureResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => $message
        ], 401);
    }
}