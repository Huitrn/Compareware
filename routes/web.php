<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // Importa Request
use App\Models\User;         // Importa User
use App\Http\Controllers\ComparadoraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComparacionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnvironmentTestController;
use App\Http\Controllers\YouTubeController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\GoogleShoppingController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ImageController;

// Rutas de imágenes
Route::get('/images/periferico/{id}', [ImageController::class, 'show'])->name('images.show');
Route::post('/images/periferico/{id}/upload', [ImageController::class, 'upload'])->name('images.upload');

// Rutas de vistas

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/debug-specs', function () {
    return view('debug-specs');
})->name('debug-specs');

Route::get('/comparadora', function () {
    try {
        // Registrar intento de carga
        \Log::info('Intentando cargar datos para comparadora...');
        
        // Usar Eloquent para cargar relaciones
        $categorias = \App\Models\Categoria::orderBy('nombre')->get();
        $productos = \App\Models\Periferico::with(['marca', 'categoria'])
            ->orderBy('nombre')
            ->get();
        
        // Log para debug
        \Log::info('Datos cargados - Categorías: ' . $categorias->count() . ', Productos: ' . $productos->count());
        
        // Debug adicional
        if ($productos->isEmpty()) {
            \Log::warning('No se encontraron productos en la base de datos');
        }
        
        if ($categorias->isEmpty()) {
            \Log::warning('No se encontraron categorías en la base de datos');
        }
        
        return view('comparadora', compact('categorias', 'productos'));
    } catch (\Exception $e) {
        \Log::error('Error en comparadora: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        // Datos de fallback con información real de la BD
        $categorias = collect([
            (object)['id' => 1, 'nombre' => 'Audífonos'],
            (object)['id' => 2, 'nombre' => 'Teclados'],
            (object)['id' => 3, 'nombre' => 'Monitores'],
            (object)['id' => 4, 'nombre' => 'Micrófonos']
        ]);
        
        $productos = collect([
            (object)['id' => 1, 'nombre' => 'Haylou S35 ANC', 'categoria_id' => 1, 'precio' => '800.00', 'imagen_url' => null, 'marca' => null],
            (object)['id' => 2, 'nombre' => 'Skullcandy Crusher ANC 2 Inalámbricos', 'categoria_id' => 1, 'precio' => '4000.00', 'imagen_url' => null, 'marca' => null],
            (object)['id' => 3, 'nombre' => 'Producto de Prueba', 'categoria_id' => 2, 'precio' => '1500.00', 'imagen_url' => null, 'marca' => null]
        ]);
        
        \Log::info('Usando datos de fallback');
        return view('comparadora', compact('categorias', 'productos'));
    }
})->name('comparadora'); // ->middleware('auth'); // TEMPORAL: deshabilitado para testing

Route::get('/comparar-perifericos', [ComparacionController::class, 'comparar']);
Route::post('/comparar-perifericos', [ComparacionController::class, 'comparar']);

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Ruta de depuración de sesión/CSRF (solo desarrollo)
if (app()->environment('local','sandbox')) {
    Route::get('/debug-session', function(\Illuminate\Http\Request $request) {
        $request->session()->put('debug_ping', ($request->session()->get('debug_ping', 0) + 1));
        return response()->json([
            'session_id' => $request->session()->getId(),
            'debug_ping' => $request->session()->get('debug_ping'),
            'has_csrf_token' => csrf_token() !== null,
        ]);
    });
}
Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/perfil', function () {
    return view('perfil');
})->name('perfil')->middleware('auth');

Route::get('/editar', function () {
    return view('editar');
})->name('editar')->middleware('auth');
Route::get('/marcas', function () {
    return view('marcas');
})->name('marcas');

Route::post('/test-web', function () {
    return 'POST funcionando en web.php';
});

Route::get('/Hola', function(){
    return 'Hola mundo :)';
});

// 🐛 RUTA DE DEBUG TEMPORAL
Route::get('/debug-user', function(){
    if (!Auth::check()) {
        return 'Usuario no autenticado';
    }
    
    $user = Auth::user();
    $data = [
        'authenticated' => Auth::check(),
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'user_role' => $user->getRoleName(),
        'is_admin' => $user->isAdmin(),
        'can_access_admin' => $user->isAdmin() ? 'YES' : 'NO',
        'logout_url' => route('logout'),
        'home_url' => route('home')
    ];
    
    // Si es una petición desde navegador, mostrar interfaz
    if (request()->header('Accept') && str_contains(request()->header('Accept'), 'text/html')) {
        return view('debug-user', compact('data'));
    }
    
    return $data;
})->middleware('auth');

//  ACCESO DIRECTO AL PANEL DE ADMIN
Route::get('/panel-admin', function(){
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
    }
    
    if (!Auth::user()->isAdmin()) {
        abort(403, 'No tienes permisos de administrador.');
    }
    
    // Ir directamente a gestión de usuarios
    return redirect()->route('admin.users');
})->name('panel.admin');

// 🎯 PÁGINA DE ACCESO ADMINISTRATIVO
Route::get('/admin-access', function() {
    return view('admin-access');
})->name('admin.access');

// 🧪 RUTA DE PRUEBA SIMPLE PARA ADMIN
Route::get('/admin-test', function() {
    if (!Auth::check() || !Auth::user()->isAdmin()) {
        return 'No tienes permisos de admin';
    }
    
    $users = \App\Models\User::all();
    return 'Usuarios encontrados: ' . $users->count() . '<br>' . 
           'Roles: ' . $users->pluck('role')->unique()->implode(', ');
})->name('admin.test');

// 👨‍💼 RUTAS DE ADMINISTRACIÓN (SIMPLIFICADAS)
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/dashboard', function() {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acceso denegado');
        }
        // Redireccionar directamente a gestión de usuarios
        return redirect()->route('admin.users');
    })->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [\App\Http\Controllers\AdminController::class, 'userDetails'])->name('user.details');
    Route::patch('/users/{user}/role', [\App\Http\Controllers\AdminController::class, 'changeRole'])->name('user.role');
    Route::patch('/users/{user}/status', [\App\Http\Controllers\AdminController::class, 'toggleStatus'])->name('user.status');
    Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('user.delete');
});

// Ruta de prueba para validación de seguridad
Route::get('/test-comparacion', [App\Http\Controllers\TestComparacionController::class, 'comparar']);

// 🧪 RUTA DE PRUEBA PARA COMPARACIÓN
Route::get('/debug-comparacion', function() {
    $perifericos = DB::table('perifericos')->select('id', 'nombre')->get();
    $comparaciones = DB::table('comparaciones')->count();
    
    return response()->json([
        'perifericos' => $perifericos,
        'total_comparaciones' => $comparaciones,
        'test_url' => url('/comparar-perifericos?periferico1=1&periferico2=2')
    ]);
});

// Ruta simple para verificar datos
// TEST: Endpoint simple para verificar datos
Route::get('/test-datos', function () {
    try {
        $categorias = \App\Models\Categoria::count();
        $productos = \App\Models\Periferico::count();
        
        return response()->json([
            'categorias_count' => $categorias,
            'productos_count' => $productos,
            'status' => 'OK'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'status' => 'ERROR'
        ], 500);
    }
});

// TEST: Página simple para verificar JavaScript
Route::get('/test-selects', function () {
    try {
        $categorias = \App\Models\Categoria::all();
        $productos = \App\Models\Periferico::all();
        
        return view('test-selects', compact('categorias', 'productos'));
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'status' => 'ERROR'
        ], 500);
    }
});

// TEST: Comparadora sin middleware de autenticación
Route::get('/comparadora-test', function () {
    try {
        \Log::info('Intentando cargar datos para comparadora-test...');
        
        $categorias = DB::table('categorias')->orderBy('nombre')->get();
        $productos = DB::table('perifericos')->orderBy('nombre')->get();
        
        \Log::info('Datos cargados - Categorías: ' . $categorias->count() . ', Productos: ' . $productos->count());
        
        $categorias = collect($categorias);
        $productos = collect($productos);
        
        return view('comparadora', compact('categorias', 'productos'));
    } catch (\Exception $e) {
        \Log::error('Error en comparadora-test: ' . $e->getMessage());
        
        $categorias = collect([
            (object)['id' => 1, 'nombre' => 'Audífonos'],
            (object)['id' => 2, 'nombre' => 'Teclados'],
        ]);
        $productos = collect([
            (object)['id' => 1, 'nombre' => 'Producto Test', 'categoria_id' => 1, 'precio' => 100],
        ]);
        
        return view('comparadora', compact('categorias', 'productos'));
    }
});

// TEST: Comparadora limpia sin errores de sintaxis
Route::get('/comparadora-clean', function () {
    try {
        \Log::info('Intentando cargar datos para comparadora-clean...');
        
        $categorias = DB::table('categorias')->orderBy('nombre')->get();
        $productos = DB::table('perifericos')->orderBy('nombre')->get();
        
        \Log::info('Datos cargados - Categorías: ' . $categorias->count() . ', Productos: ' . $productos->count());
        
        $categorias = collect($categorias);
        $productos = collect($productos);
        
        return view('comparadora-clean', compact('categorias', 'productos'));
    } catch (\Exception $e) {
        \Log::error('Error en comparadora-clean: ' . $e->getMessage());
        
        $categorias = collect([
            (object)['id' => 1, 'nombre' => 'Audífonos'],
            (object)['id' => 2, 'nombre' => 'Teclados'],
        ]);
        $productos = collect([
            (object)['id' => 1, 'nombre' => 'Producto Test', 'categoria_id' => 1, 'precio' => 100],
        ]);
        
        return view('comparadora', compact('categorias', 'productos'));
    }
});

// TEST: Verificar estado de autenticación
Route::get('/debug-auth', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::user() ? [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email
        ] : null,
        'session_id' => session()->getId()
    ]);
});

// TEST: Probar Amazon API sin autenticación (solo para desarrollo)
Route::get('/test-amazon-api/{term?}', function ($term = 'wireless headphones') {
    try {
        $amazonService = app(\App\Services\AmazonApiService::class);
        
        $result = $amazonService->searchProducts($term);
        
        return response()->json([
            'success' => true,
            'search_term' => $term,
            'result' => $result,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// TEST PAGE: Página de prueba para integración Amazon
Route::get('/test-amazon-integration', function () {
    return response()->file(public_path('../test_amazon_integration.html'));
});

// TEST PAGE: Página de prueba para URLs reales de Amazon
Route::get('/test-real-amazon-urls', function () {
    return response()->file(public_path('../test_real_amazon_urls.html'));
});

// NEW: Buscar productos Amazon específicos basados en periféricos de la BDD
Route::get('/amazon/{productId}', function ($productId) {
    try {
        // Obtener producto específico de la base de datos
        $periferico = DB::table('perifericos as p')
            ->join('marcas as m', 'p.marca_id', '=', 'm.id')
            ->join('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->select('p.id', 'p.nombre', 'p.modelo', 'p.precio', 'm.nombre as marca', 'c.nombre as categoria')
            ->where('p.id', $productId)
            ->first();
        
        if (!$periferico) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado en la base de datos'
            ], 404);
        }
        
        $amazonService = app(\App\Services\AmazonApiService::class);
        
        // Usar el nuevo método específico para productos de la BDD
        $result = $amazonService->findDatabaseProduct($periferico->nombre, $periferico->marca);
        
        return response()->json([
            'success' => true,
            'db_product' => [
                'id' => $periferico->id,
                'name' => $periferico->nombre,
                'brand' => $periferico->marca,
                'model' => $periferico->modelo,
                'price_mxn' => $periferico->precio,
                'category' => $periferico->categoria
            ],
            'amazon_results' => $result,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// TEST: Probar Product Specs API sin autenticación (solo para desarrollo)
Route::get('/test-specs-api/{product?}', function ($product = 'Sony WH-1000XM4') {
    try {
        $specsService = app(\App\Services\ProductSpecsService::class);
        
        $result = $specsService->getProductSpecs($product);
        
        return response()->json([
            'success' => true,
            'product_name' => $product,
            'result' => $result,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// TEST: Comparar especificaciones de dos productos
Route::get('/test-specs-compare/{product1?}/{product2?}', function ($product1 = 'Sony WH-1000XM4', $product2 = 'Bose QuietComfort 35') {
    try {
        $specsService = app(\App\Services\ProductSpecsService::class);
        
        $result = $specsService->compareSpecs($product1, $product2);
        
        return response()->json([
            'success' => true,
            'products' => [$product1, $product2],
            'result' => $result,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// TEST: Node API - obtener especificaciones
Route::get('/test-node-specs/{product}', function ($product) {
    $node = app(\App\Services\NodeCompareClient::class);
    return response()->json($node->getSpecs($product));
});

// TEST: Node API - comparar dos productos
Route::get('/test-node-compare/{p1}/{p2}', function ($p1, $p2) {
    $node = app(\App\Services\NodeCompareClient::class);
    return response()->json($node->compare($p1, $p2));
});

// TEST: Auto-login para desarrollo
Route::get('/auto-login', function () {
    try {
        // Buscar o crear usuario de prueba
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Usuario Test',
                'password' => \Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Hacer login automático
        \Auth::login($user);
        
        return redirect('/comparadora')->with('success', 'Login automático exitoso');
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error en auto-login: ' . $e->getMessage(),
            'status' => 'ERROR'
        ], 500);
    }
});

// Ruta para debug de datos de la comparadora
Route::get('/debug-vista-comparadora', function () {
    try {
        // Probar diferentes métodos de obtención de datos
        $categorias_model = \App\Models\Categoria::all();
        $productos_model = \App\Models\Periferico::all();
        
        $categorias_db = DB::table('categorias')->get();
        $productos_db = DB::table('perifericos')->get();
        
        return response()->json([
            'status' => 'success',
            'database_connection' => DB::connection()->getPdo() ? 'OK' : 'FAILED',
            'models' => [
                'categorias_count' => $categorias_model->count(),
                'productos_count' => $productos_model->count(),
                'categorias_sample' => $categorias_model->take(3)->toArray(),
                'productos_sample' => $productos_model->take(3)->toArray(),
            ],
            'db_queries' => [
                'categorias_count' => $categorias_db->count(),
                'productos_count' => $productos_db->count(),
                'categorias_sample' => $categorias_db->take(3)->toArray(),
                'productos_sample' => $productos_db->take(3)->toArray(),
            ],
            'tables_exist' => [
                'categorias' => DB::getSchemaBuilder()->hasTable('categorias'),
                'perifericos' => DB::getSchemaBuilder()->hasTable('perifericos'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// ===== PRICE HISTORY API ROUTES =====
Route::get('/test-price-history/{product}', function ($product) {
    try {
        $priceHistoryService = new \App\Services\PriceHistoryService();
        
        // Obtener historial de precios (últimos 90 días por defecto)
        $history = $priceHistoryService->getPriceHistory($product, 90);
        
        return response()->json([
            'success' => true,
            'result' => $history,
            'endpoint' => 'price-history',
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'endpoint' => 'price-history'
        ]);
    }
});

Route::get('/test-price-trends/{product}', function ($product) {
    try {
        $priceHistoryService = new \App\Services\PriceHistoryService();
        
        // Obtener análisis de tendencias
        $trends = $priceHistoryService->getPriceTrends($product);
        
        return response()->json([
            'success' => true,
            'result' => $trends,
            'endpoint' => 'price-trends',
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'endpoint' => 'price-trends'
        ]);
    }
});

Route::get('/test-price-comparison/{product1}/{product2}', function ($product1, $product2) {
    try {
        $priceHistoryService = new \App\Services\PriceHistoryService();
        
        // Obtener tendencias para ambos productos
        $trends1 = $priceHistoryService->getPriceTrends($product1);
        $trends2 = $priceHistoryService->getPriceTrends($product2);
        
        // Crear comparación de precios
        $comparison = [
            'product1' => [
                'name' => $product1,
                'trends' => $trends1
            ],
            'product2' => [
                'name' => $product2,
                'trends' => $trends2
            ],
            'comparison_insights' => [
                'better_deal' => $trends1['success'] && $trends2['success'] ? 
                    ($trends1['analysis']['current_price'] < $trends2['analysis']['current_price'] ? $product1 : $product2) : 
                    'Unable to determine',
                'price_difference' => $trends1['success'] && $trends2['success'] ? 
                    abs($trends1['analysis']['current_price'] - $trends2['analysis']['current_price']) : 0,
                'savings_opportunity' => $trends1['success'] && $trends2['success'] ? 
                    max($trends1['analysis']['price_alerts']['savings_potential'], 
                        $trends2['analysis']['price_alerts']['savings_potential']) : 0
            ]
        ];
        
        return response()->json([
            'success' => true,
            'result' => $comparison,
            'endpoint' => 'price-comparison',
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'endpoint' => 'price-comparison'
        ]);
    }
});

// API Token para usuarios autenticados
Route::middleware('auth')->get('/api/token', function (Request $request) {
    $user = $request->user();
    
    // Eliminar tokens anteriores del usuario
    $user->tokens()->delete();
    
    // Crear nuevo token
    $token = $user->createToken('amazon-api-token')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email', 'role'])
    ]);
});

// ========================================
// RUTAS DE TESTING DE AMBIENTES
// ========================================

// Dashboard de pruebas de ambientes
Route::get('/environment/dashboard', [EnvironmentTestController::class, 'dashboard'])
    ->name('environment.dashboard');

// Probar funcionalidades del ambiente actual
Route::post('/environment/test', [EnvironmentTestController::class, 'testEnvironment'])
    ->name('environment.test');

// Comparar configuraciones de ambientes
Route::get('/environment/compare', [EnvironmentTestController::class, 'compareEnvironments'])
    ->name('environment.compare');

// Cambiar ambiente usando rutas web (alternativa al comando artisan)
Route::get('/environment/switch/{environment}', function($environment) {
    if (!in_array($environment, ['sandbox', 'staging', 'production'])) {
        abort(404, 'Ambiente no válido');
    }
    
    try {
        // Ejecutar comando artisan para cambiar ambiente
        Artisan::call('env:switch', [
            'environment' => $environment,
            '--force' => true
        ]);
        
        return redirect('/environment/dashboard')
            ->with('success', "Ambiente cambiado exitosamente a: {$environment}");
            
    } catch (\Exception $e) {
        return redirect('/environment/dashboard')
            ->with('error', "Error al cambiar ambiente: {$e->getMessage()}");
    }
})->name('environment.switch');

// ============================================
// RUTAS DE PRUEBA PARA YOUTUBE API
// ============================================
Route::get('/test/youtube', [YouTubeController::class, 'testView'])->name('test.youtube');

// ============================================
// RUTAS DE PRUEBA PARA CURRENCY EXCHANGE API
// ============================================
Route::get('/test/currency', [CurrencyController::class, 'testView'])->name('test.currency');

// ============================================
// RUTAS DE PRUEBA PARA GOOGLE SHOPPING API
// ============================================
Route::get('/test/google-shopping', [GoogleShoppingController::class, 'testView'])->name('test.google-shopping');

// Chatbot OpenAI (RapidAPI)
Route::post('/chatbot/message', [ChatbotController::class, 'chat'])->name('chatbot.message');

// Ruta para el chatbot
Route::get('/chatbot', function () {
    return view('chatbot');
})->name('chatbot');