<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Log;

class ComparadoraController extends Controller
{
    protected SecurityLogger $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
        
        // Aplicar middlewares de seguridad
        $this->middleware(['sql.security:strict', 'rate.limit']);
    }

    /**
     * Sanitizar input para prevenir SQL injection y XSS
     */
    private function sanitizeInput($input): string
    {
        if (is_null($input)) {
            return '';
        }

        $input = (string) $input;

        // Detectar y remover patrones peligrosos
        $dangerous_patterns = [
            '/[\'";\\\\]/',           // Comillas y backslashes
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b/i',
            '/(-{2,}|\/\*|\*\/|\#)/', // Comentarios SQL
            '/<[^>]*>/',              // Tags HTML
            '/javascript:/i',          // JavaScript
            '/on\w+\s*=/i',           // Event handlers
        ];

        $originalInput = $input;
        foreach ($dangerous_patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        // Log si se detecta intento malicioso
        if ($input !== $originalInput) {
            $this->securityLogger->logSecurityEvent('MALICIOUS_INPUT_DETECTED', [
                'controller' => 'ComparadoraController',
                'original_input' => $originalInput,
                'sanitized_input' => $input,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ], 'HIGH');
        }

        return trim($input);
    }

    /**
     * Validar entrada numérica
     */
    private function validateNumeric($input): ?int
    {
        if (is_null($input) || $input === '') {
            return null;
        }

        // Solo permitir números
        if (!is_numeric($input) || $input < 0 || $input > 999999) {
            $this->securityLogger->logSecurityEvent('INVALID_NUMERIC_INPUT', [
                'input' => $input,
                'ip' => request()->ip()
            ], 'MEDIUM');
            return null;
        }

        return (int) $input;
    }
    // Método para mostrar usuario por ID - SEGURO
    public function mostrarUsuario($id)
    {
        // Validar que el ID sea un número entero positivo
        if (!is_numeric($id) || $id <= 0 || $id > 999999) {
            $this->securityLogger->logSecurityEvent('INVALID_USER_ID', [
                'provided_id' => $id,
                'ip' => request()->ip()
            ], 'MEDIUM');

            return response()->json(['error' => 'Invalid user ID'], 400);
        }

        $validatedId = (int) $id;

        $this->securityLogger->logSecurityEvent('USER_PROFILE_ACCESS', [
            'user_id' => $validatedId,
            'ip' => request()->ip()
        ], 'LOW');
        
        return response()->json([
            'mensaje' => 'Mostrando usuario',
            'id_usuario' => $validatedId,
            'url_completa' => request()->url()
        ]);
    }
    
    // Método con múltiples parámetros
    public function mostrarPerfilUsuario($id)
    {
        return response()->json([
            'mensaje' => 'Mostrando perfil del usuario',
            'id_usuario' => $id,
            'ruta' => 'usuario/' . $id . '/perfil'
        ]);
    }

    // Método con parámetros de consulta (query parameters) - SEGURO
    public function buscarPorFiltros(Request $request)
    {
        // Validar y sanitizar todos los inputs
        $categoria = $this->sanitizeInput($request->query('categoria'));
        $marca = $this->sanitizeInput($request->query('marca'));
        $precio_min = $this->validateNumeric($request->query('precio_min'));
        $precio_max = $this->validateNumeric($request->query('precio_max'));
        
        return response()->json([
            'mensaje' => 'Búsqueda de productos',
            'parametros_recibidos' => [
                'categoria' => $categoria,
                'marca' => $marca,
                'precio_min' => $precio_min,
                'precio_max' => $precio_max
            ],
            'query_string_completa' => $request->getQueryString(),
            'url_completa' => $request->fullUrl()
        ]);
    }
    
    // Método para filtrar periféricos
    public function filtrarPerifericos(Request $request)
    {
        // Obtener todos los query parameters
        $filtros = $request->all();
        
        // Parámetros específicos con valores por defecto
        $tipo = $request->query('tipo', 'todos');
        $ordenar = $request->query('ordenar', 'nombre');
        $limite = $request->query('limite', 10);
        
        return response()->json([
            'mensaje' => 'Filtros aplicados a periféricos',
            'tipo_filtro' => $tipo,
            'ordenar_por' => $ordenar,
            'limite_resultados' => $limite,
            'todos_los_filtros' => $filtros,
            'tiene_filtros' => !empty($filtros)
        ]);
    }
    
    // Método para listar usuarios con paginación
    public function listarUsuarios(Request $request)
    {
        // Query parameters con validación
        $pagina = $request->query('pagina', 1);
        $por_pagina = $request->query('por_pagina', 5);
        $rol = $request->query('rol');
        $activo = $request->query('activo');
        
        return response()->json([
            'mensaje' => 'Lista de usuarios',
            'paginacion' => [
                'pagina_actual' => (int)$pagina,
                'elementos_por_pagina' => (int)$por_pagina,
                'offset' => ((int)$pagina - 1) * (int)$por_pagina
            ],
            'filtros' => [
                'rol' => $rol,
                'activo' => $activo === 'true' ? true : ($activo === 'false' ? false : null)
            ],
            'url_siguiente' => $request->fullUrlWithQuery(['pagina' => (int)$pagina + 1]),
            'parametros_query' => $request->query()
        ]);
    }
    
    // Método para búsqueda avanzada con múltiples parámetros
    public function busquedaAvanzada(Request $request)
    {
        // Obtener parámetros específicos
        $termino = $request->query('q'); // término de búsqueda
        $categoria = $request->query('categoria');
        $marca = $request->query('marca');
        $precio_min = $request->query('precio_min');
        $precio_max = $request->query('precio_max');
        $disponible = $request->query('disponible');
        $ordenar = $request->query('sort', 'nombre');
        $direccion = $request->query('order', 'asc');
        
        // Validar parámetros
        $errores = [];
        if ($precio_min && !is_numeric($precio_min)) {
            $errores[] = 'precio_min debe ser un número';
        }
        if ($precio_max && !is_numeric($precio_max)) {
            $errores[] = 'precio_max debe ser un número';
        }
        
        return response()->json([
            'mensaje' => 'Búsqueda avanzada de productos',
            'criterios_busqueda' => [
                'termino' => $termino,
                'categoria' => $categoria,
                'marca' => $marca,
                'rango_precio' => [
                    'minimo' => $precio_min ? (float)$precio_min : null,
                    'maximo' => $precio_max ? (float)$precio_max : null
                ],
                'disponible' => $disponible === 'true' ? true : ($disponible === 'false' ? false : null),
                'ordenamiento' => [
                    'campo' => $ordenar,
                    'direccion' => $direccion
                ]
            ],
            'tiene_errores' => !empty($errores),
            'errores' => $errores,
            'total_parametros' => count($request->query()),
            'query_string' => $request->getQueryString()
        ]);
    }

    // Método para redirigir productos con parámetros preservados
    public function redirigirProductos(Request $request)
    {
        // Preservar query parameters en la redirección
        $queryParams = $request->query();
        
        return redirect()->to('/productos-nuevos')
                       ->withQuery($queryParams)
                       ->with('mensaje', 'Has sido redirigido desde la URL antigua');
    }

    // Método para redirigir perfil de usuario
    public function redirigirPerfil(Request $request)
    {
        // Redirección condicional
        $userId = $request->query('id');
        
        if ($userId) {
            return redirect()->route('usuario.mostrar', ['id' => $userId])
                           ->with('info', 'Redirigido desde perfil antiguo');
        }
        
        return redirect()->to('/usuarios')
                       ->with('warning', 'ID de usuario no especificado');
    }

    // Método para redirigir búsqueda antigua
    public function redirigirBusqueda(Request $request)
    {
        // Convertir parámetros antiguos a nuevos
        $parametrosAntiguos = $request->query();
        $parametrosNuevos = [];
        
        // Mapear parámetros antiguos a nuevos nombres
        if (isset($parametrosAntiguos['cat'])) {
            $parametrosNuevos['categoria'] = $parametrosAntiguos['cat'];
        }
        if (isset($parametrosAntiguos['brand'])) {
            $parametrosNuevos['marca'] = $parametrosAntiguos['brand'];
        }
        if (isset($parametrosAntiguos['search'])) {
            $parametrosNuevos['q'] = $parametrosAntiguos['search'];
        }
        
        return redirect()->to('/api/search')
                       ->withQuery($parametrosNuevos)
                       ->with('success', 'Búsqueda actualizada al nuevo formato');
    }

    // Método para redirección con validación
    public function redirigirConValidacion(Request $request)
    {
        $tipo = $request->query('tipo');
        
        switch ($tipo) {
            case 'usuario':
                return redirect()->route('listar.usuarios');
            case 'producto':
                return redirect()->route('productos.nuevos');
            case 'busqueda':
                return redirect()->route('busqueda.avanzada');
            default:
                return redirect()->to('/')
                               ->with('error', 'Tipo de redirección no válido');
        }
    }
    
// ========== MÉTODOS PARA RUTAS PÚBLICAS ==========

public function rutaPublica(Request $request)
{
    return response()->json([
        'mensaje' => 'Esta es una ruta PÚBLICA',
        'acceso' => 'Sin restricciones',
        'usuario_autenticado' => auth()->check(),
        'ip_visitante' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'timestamp' => now()
    ]);
}

public function informacionPublica(Request $request)
{
    return response()->json([
        'mensaje' => 'Información pública del sistema',
        'version' => '1.0',
        'funcionalidades_publicas' => [
            'Catálogo de productos',
            'Búsqueda básica',
            'Información de la empresa',
            'Contacto'
        ],
        'estadisticas' => [
            'productos_disponibles' => 150,
            'categorias' => 8,
            'usuarios_registrados' => '1000+'
        ],
        'acceso_desde' => $request->fullUrl()
    ]);
}

public function catalogoPublico(Request $request)
{
    $categoria = $request->query('categoria');
    
    return response()->json([
        'mensaje' => 'Catálogo público de productos',
        'categoria_filtro' => $categoria,
        'productos_muestra' => [
            [
                'id' => 1,
                'nombre' => 'Mouse Gaming RGB',
                'precio' => 1299.99,
                'disponible' => true
            ],
            [
                'id' => 2,
                'nombre' => 'Teclado Mecánico',
                'precio' => 2499.99,
                'disponible' => true
            ]
        ],
        'nota' => 'Para ver detalles completos, inicia sesión',
        'acceso_publico' => true
    ]);
}

// ========== MÉTODOS PARA RUTAS PRIVADAS ==========

public function rutaPrivada(Request $request)
{
    $user = auth()->user();
    
    return response()->json([
        'mensaje' => 'Esta es una ruta PRIVADA',
        'acceso' => 'Solo usuarios autenticados',
        'usuario_actual' => [
            'id' => $user ? $user->id : null,
            'nombre' => $user ? $user->name : null,
            'email' => $user ? $user->email : null
        ],
        'permisos' => 'Usuario autenticado',
        'session_id' => session()->getId(),
        'timestamp' => now()
    ]);
}

public function dashboard(Request $request)
{
    $user = auth()->user();
    
    return response()->json([
        'mensaje' => 'Dashboard privado del usuario',
        'bienvenida' => 'Bienvenido, ' . ($user ? $user->name : 'Usuario'),
        'estadisticas_personales' => [
            'comparaciones_realizadas' => 5,
            'productos_favoritos' => 12,
            'comentarios_escritos' => 3
        ],
        'acciones_disponibles' => [
            'Ver perfil completo',
            'Gestionar favoritos',
            'Historial de comparaciones',
            'Configuración de cuenta'
        ],
        'ultimo_acceso' => now()->subHours(2)
    ]);
}

public function perfilPrivado(Request $request)
{
    $user = auth()->user();
    
    return response()->json([
        'mensaje' => 'Perfil privado del usuario',
        'datos_usuario' => [
            'id' => $user ? $user->id : null,
            'nombre_completo' => $user ? $user->name : null,
            'email' => $user ? $user->email : null,
            'fecha_registro' => $user ? $user->created_at : null
        ],
        'configuracion_privada' => [
            'notificaciones_email' => true,
            'perfil_publico' => false,
            'compartir_comparaciones' => true
        ],
        'datos_sensibles' => 'Solo visible para el usuario autenticado'
    ]);
}

public function adminPanel(Request $request)
{
    $user = auth()->user();
    
    return response()->json([
        'mensaje' => 'Panel de administración',
        'usuario' => $user ? $user->name : 'Desconocido',
        'advertencia' => 'Esta sección requiere permisos de administrador',
        'funciones_admin' => [
            'Gestionar usuarios',
            'Moderar comentarios',
            'Estadísticas del sistema',
            'Configuración global'
        ],
        'nivel_acceso' => 'Administrador'
    ]);
}

public function superPrivada(Request $request)
{
    $user = auth()->user();
    
    return response()->json([
        'mensaje' => 'Ruta súper privada',
        'descripcion' => 'Requiere autenticación Y verificación de email',
        'usuario' => $user ? $user->name : null,
        'email_verificado' => $user ? $user->hasVerifiedEmail() : false,
        'nivel_seguridad' => 'Máximo',
        'contenido_exclusivo' => 'Datos altamente sensibles'
    ]);
}

// ========== MÉTODOS PARA SIMULACIÓN DE LOGIN/LOGOUT ==========

public function simularLogin(Request $request)
{
    // Simular autenticación (solo para pruebas)
    session(['usuario_simulado' => true]);
    
    return response()->json([
        'mensaje' => 'Login simulado exitoso',
        'accion' => 'Usuario autenticado temporalmente',
        'nota' => 'Esta es una simulación para pruebas',
        'siguiente_paso' => 'Ahora puedes acceder a rutas privadas',
        'rutas_disponibles' => [
            '/privada',
            '/dashboard',
            '/perfil-privado'
        ]
    ]);
}

public function simularLogout(Request $request)
{
    // Simular cierre de sesión
    session()->forget('usuario_simulado');
    
    return response()->json([
        'mensaje' => 'Logout simulado exitoso',
        'accion' => 'Sesión cerrada',
        'estado' => 'Usuario no autenticado',
        'acceso_limitado' => 'Solo rutas públicas disponibles'
    ]);
}
}