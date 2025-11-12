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
    protected $securityLogger;

    public function __construct()
    {
        // Hacer SecurityLogger opcional
        try {
            $this->securityLogger = app(SecurityLogger::class);
        } catch (\Exception $e) {
            $this->securityLogger = null;
        }
        
        // Aplicar middlewares de seguridad solo si existen
        // $this->middleware(['sql.security:strict', 'rate.limit']);
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegisterForm()
    {
        return view('registro');
    }

    /**
     * Registro de usuario con validación y seguridad avanzada
     */
    public function register(\Illuminate\Http\Request $request)
    {
        try {
            // Validación manual simple
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|same:password'
            ]);
            
            // DEBUG: Log para depurar registro
            \Log::info('REGISTER DEBUG', [
                'name' => $request->name,
                'email' => $request->email,
                'password_length' => strlen($request->password ?? ''),
                'has_password_confirmation' => !empty($request->password_confirmation)
            ]);
            
            // Rate limiting específico para registro (opcional)
            $key = 'register:' . $request->ip();
            try {
                if (RateLimiter::tooManyAttempts($key, 3)) {
                    $this->logSecurityEvent('REGISTRATION_RATE_LIMIT', [
                        'ip' => $request->ip(),
                        'attempts' => RateLimiter::attempts($key)
                    ], 'MEDIUM');

                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Too many registration attempts',
                            'retry_after' => RateLimiter::availableIn($key)
                        ], 429);
                    }
                    
                    return back()->withErrors([
                        'email' => 'Demasiados intentos de registro. Inténtalo más tarde.'
                    ]);
                }

                RateLimiter::hit($key, 300); // 5 minutos
            } catch (\Exception $e) {
                // Rate limiter no disponible, continuar
            }

            // Crear usuario con datos del request
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // El mutador del modelo se encarga del hash
                'role' => 'user' // Siempre user, no aceptar desde request
            ]);

            // Log de registro exitoso
            $this->logSecurityEvent('USER_REGISTRATION', [
                'user_id' => $user->id,
                'email' => $user->email,
                'success' => true
            ], 'LOW');

            // Crear token con expiración
            $token = $user->createToken('auth-token', ['*'], now()->addHours(2));

            // Manejar respuesta web o JSON según el request
            if ($request->expectsJson()) {
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
            }

            // Autenticar usuario automáticamente para web
            auth()->login($user);
            
            return redirect(route('home'))->with('success', '¡Registro exitoso! Bienvenido a CompareWare.');

        } catch (\Exception $e) {
            $this->logSecurityEvent('REGISTRATION_ERROR', [
                'error' => $e->getMessage(),
                'input_data' => $request->except(['password'])
            ], 'HIGH');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en el registro',
                    'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
                ], 500);
            }

            return back()->withErrors([
                'email' => 'Error interno del servidor. Por favor, inténtalo más tarde.',
            ])->withInput();
        }
    }

    /**
     * Login con protección contra ataques de fuerza bruta
     */
    public function login(SecureAuthRequest $request)
    {
        // Rate limiting por IP y email (opcional)
        $ipKey = 'login:ip:' . $request->ip();
        $emailKey = 'login:email:' . $request->email;

        try {
            // Verificar rate limiting
            if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
                $this->logSecurityEvent('BRUTE_FORCE_ATTACK', [
                    'email' => $email,
                    'ip_attempts' => RateLimiter::attempts($ipKey),
                    'email_attempts' => RateLimiter::attempts($emailKey)
                ], 'HIGH');

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Too many login attempts',
                        'retry_after' => max(RateLimiter::availableIn($ipKey), RateLimiter::availableIn($emailKey))
                    ], 429);
                }
                
                return back()->withErrors([
                    'email' => 'Demasiados intentos de login. Inténtalo más tarde.'
                ]);
            }
        } catch (\Exception $e) {
            // Rate limiter no disponible, continuar
        }

        try {
            // Buscar usuario - usar email directo del request
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Incrementar rate limiting incluso para usuarios inexistentes
                try {
                    RateLimiter::hit($ipKey, 300);
                    RateLimiter::hit($emailKey, 600);
                } catch (\Exception $e) {
                    // Rate limiter no disponible
                }
                
                $this->logSecurityEvent('USER_ENUMERATION', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ], 'MEDIUM');

                // Manejar respuesta web o JSON según el request
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El usuario no está registrado en nuestro sistema.',
                        'action' => 'register',
                        'register_url' => route('register')
                    ], 422);
                }

                return back()->withErrors([
                    'email' => 'El usuario no está registrado en nuestro sistema.'
                ])->withInput()->with([
                    'show_register_link' => true,
                    'register_url' => route('register')
                ]);
            }

            // Verificar si la cuenta está bloqueada
            if (method_exists($user, 'isLocked') && $user->isLocked()) {
                $this->logSecurityEvent('LOCKED_ACCOUNT_ACCESS', [
                    'user_id' => $user->id,
                    'email' => $request->email
                ], 'MEDIUM');

                return response()->json([
                    'error' => 'Account locked',
                    'message' => 'Account temporarily locked due to failed attempts'
                ], 423);
            }

            // Verificar contraseña
            if (!Hash::check($request->password, $user->password)) {
                // Incrementar intentos fallidos
                if (method_exists($user, 'incrementFailedAttempts')) {
                    $user->incrementFailedAttempts();
                }

                try {
                    RateLimiter::hit($ipKey, 300);
                    RateLimiter::hit($emailKey, 600);
                } catch (\Exception $e) {
                    // Rate limiter no disponible
                }

                $this->logSecurityEvent('AUTHENTICATION_FAILURE', [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'reason' => 'invalid_password'
                ], 'MEDIUM');

                return $this->loginFailureResponse('Credenciales inválidas');
            }

            // Login exitoso
            try {
                RateLimiter::clear($ipKey);
                RateLimiter::clear($emailKey);
            } catch (\Exception $e) {
                // Rate limiter no disponible
            }

            // Registrar login exitoso
            if (method_exists($user, 'recordLogin')) {
                $user->recordLogin();
            }

            // Crear token con abilities específicas
            $abilities = $this->getTokenAbilities($user);
            $token = $user->createToken('auth-token', $abilities, now()->addHours(2));

            $this->logSecurityEvent('AUTHENTICATION_SUCCESS', [
                'user_id' => $user->id,
                'email' => $request->email
            ], 'LOW');

            // Manejar respuesta web o JSON según el request
            if ($request->expectsJson()) {
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
            }

            // Autenticar usuario para sesión web
            auth()->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Redirigir según el rol del usuario
            $redirectRoute = $this->getRedirectRoute($user);
            
            return redirect()->intended($redirectRoute)->with('success', '¡Bienvenido de vuelta!');

        } catch (\Exception $e) {
            $this->logSecurityEvent('AUTHENTICATION_ERROR', [
                'error' => $e->getMessage(),
                'email' => $email ?? 'unknown'
            ], 'HIGH');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en el login',
                    'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
                ], 500);
            }

            return back()->withErrors([
                'email' => 'Error interno del servidor. Por favor, inténtalo más tarde.',
            ])->withInput();
        }
    }

    /**
     * Logout seguro con revocación de token
     */
    public function logout()
    {
        $user = auth()->user();
        
        if ($user) {
            // Revocar el token actual
            $user->currentAccessToken()?->delete();
            
            $this->logSecurityEvent('USER_LOGOUT', [
                'user_id' => $user->id
            ], 'LOW');
        }

        // Cerrar sesión web si existe
        if (auth()->check()) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
            ]);
        }

        return redirect('/')->with('success', 'Sesión cerrada exitosamente.');
    }

    /**
     * Revocar todos los tokens (logout desde todos los dispositivos)
     */
    public function logoutAll(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user) {
            $user->tokens()->delete();
            
            $this->logSecurityEvent('USER_LOGOUT_ALL', [
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

    /**
     * Helper seguro para logging
     */
    private function logSecurityEvent(string $event, array $data, string $severity)
    {
        try {
            if (isset($this->securityLogger) && $this->securityLogger) {
                $this->securityLogger->logSecurityEvent($event, $data, $severity);
            } else {
                // Fallback a logging normal
                \Log::info("Security Event: {$event}", $data);
            }
        } catch (\Exception $e) {
            // Si falla el logging de seguridad, usar log normal como fallback
            \Log::error("Security logging failed: " . $e->getMessage(), $data);
        }
    }

    /**
     * Obtener ruta de redirección según el rol del usuario
     */
    private function getRedirectRoute(User $user): string
    {
        // Obtener el nombre del rol
        $roleName = $user->getRoleName();

        // Redirigir según el rol
        switch (strtolower($roleName)) {
            case 'admin':
            case 'administrador':
                return route('admin.users');
                
            case 'supervisor':
            case 'supervisión':
                return route('supervisor.dashboard');
                
            case 'developer':
            case 'desarrollador':
                return route('developer.dashboard');
                
            case 'user':
            case 'usuario':
            default:
                return route('home');
        }
    }
}