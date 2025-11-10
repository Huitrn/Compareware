<?php

/*
|--------------------------------------------------------------------------
| Rutas Protegidas por Roles - Ejemplo de Implementación
|--------------------------------------------------------------------------
|
| Este archivo muestra cómo implementar rutas protegidas por roles
| en tu aplicación. Copia estas rutas a routes/web.php o routes/api.php
|
*/

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\DeveloperController;

// ============================================================================
// RUTAS PARA ADMINISTRADORES
// ============================================================================

Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Gestión de Usuarios
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Gestión de Roles
    Route::get('/roles', [AdminController::class, 'roles'])->name('roles.index');
    Route::get('/roles/create', [AdminController::class, 'createRole'])->name('roles.create');
    Route::post('/roles', [AdminController::class, 'storeRole'])->name('roles.store');
    Route::get('/roles/{id}/edit', [AdminController::class, 'editRole'])->name('roles.edit');
    Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{id}', [AdminController::class, 'deleteRole'])->name('roles.delete');
    
    // Gestión de Productos
    Route::resource('products', 'ProductController');
    
    // Configuración del Sistema
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    
    // Logs del Sistema
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::delete('/logs', [AdminController::class, 'clearLogs'])->name('logs.clear');
    
    // Reportes
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    
    // Estadísticas
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
});

// ============================================================================
// RUTAS PARA SUPERVISORES
// ============================================================================

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    
    // Gestión de Productos
    Route::get('/products', [SupervisorController::class, 'products'])->name('products.index');
    Route::post('/products/{id}/approve', [SupervisorController::class, 'approveProduct'])->name('products.approve');
    Route::post('/products/{id}/reject', [SupervisorController::class, 'rejectProduct'])->name('products.reject');
    
    // Ver Usuarios (solo lectura y edición básica)
    Route::get('/users', [SupervisorController::class, 'users'])->name('users.index');
    Route::get('/users/{id}/edit', [SupervisorController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [SupervisorController::class, 'updateUser'])->name('users.update');
    
    // Reportes
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('reports');
    
    // Estadísticas
    Route::get('/statistics', [SupervisorController::class, 'statistics'])->name('statistics');
    
    // Gestión de Categorías y Marcas
    Route::resource('categories', 'CategoryController')->except(['destroy']);
    Route::resource('brands', 'BrandController')->except(['destroy']);
});

// ============================================================================
// RUTAS PARA DESARROLLADORES
// ============================================================================

Route::middleware(['auth', 'role:desarrollador'])->prefix('developer')->name('developer.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DeveloperController::class, 'dashboard'])->name('dashboard');
    
    // Logs del Sistema
    Route::get('/logs', [DeveloperController::class, 'logs'])->name('logs');
    Route::delete('/logs', [DeveloperController::class, 'clearLogs'])->name('logs.clear');
    
    // Gestión de Caché
    Route::get('/cache', [DeveloperController::class, 'cachePanel'])->name('cache');
    Route::post('/cache/clear', [DeveloperController::class, 'clearCache'])->name('cache.clear');
    
    // Pruebas de API
    Route::get('/api-tester', [DeveloperController::class, 'apiTester'])->name('api-tester');
    Route::post('/api-tester/test', [DeveloperController::class, 'testApi'])->name('api-tester.test');
    
    // Ver Configuración
    Route::get('/config', [DeveloperController::class, 'viewConfig'])->name('config');
    
    // Información de Base de Datos
    Route::get('/database', [DeveloperController::class, 'databaseInfo'])->name('database');
    
    // Herramientas de Debugging
    Route::get('/debug', [DeveloperController::class, 'debugTools'])->name('debug');
    Route::post('/debug/execute', [DeveloperController::class, 'executeCommand'])->name('debug.execute');
    
    // Ver Reportes (solo lectura)
    Route::get('/reports', [DeveloperController::class, 'reports'])->name('reports');
});

// ============================================================================
// RUTAS CON PERMISOS ESPECÍFICOS (en lugar de roles completos)
// ============================================================================

// Requiere permiso específico de gestión de productos
Route::middleware(['auth', 'permission:manage_products'])->group(function () {
    Route::get('/products', 'ProductController@index')->name('products.index');
    Route::get('/products/create', 'ProductController@create')->name('products.create');
    Route::post('/products', 'ProductController@store')->name('products.store');
});

// Requiere permiso de edición O eliminación de productos (cualquiera de los dos)
Route::middleware(['auth', 'permission:edit_products,delete_products'])->group(function () {
    Route::get('/products/{id}/edit', 'ProductController@edit')->name('products.edit');
    Route::put('/products/{id}', 'ProductController@update')->name('products.update');
    Route::delete('/products/{id}', 'ProductController@destroy')->name('products.destroy');
});

// Requiere permiso de ver usuarios
Route::middleware(['auth', 'permission:view_users'])->group(function () {
    Route::get('/users', 'UserController@index')->name('users.index');
});

// Requiere permiso de gestión de chatbot (disponible para Admin, Supervisor y Developer)
Route::middleware(['auth', 'permission:manage_chatbot'])->group(function () {
    Route::get('/chatbot/config', 'ChatbotController@config')->name('chatbot.config');
    Route::put('/chatbot/config', 'ChatbotController@updateConfig')->name('chatbot.config.update');
});

// ============================================================================
// RUTAS PÚBLICAS O AUTENTICADAS (sin restricción de rol)
// ============================================================================

Route::middleware('auth')->group(function () {
    // Estas rutas están disponibles para todos los usuarios autenticados
    Route::get('/profile', 'ProfileController@show')->name('profile.show');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');
    Route::get('/notifications', 'NotificationController@index')->name('notifications.index');
});

// ============================================================================
// EJEMPLOS DE VERIFICACIÓN EN CONTROLADORES
// ============================================================================

/*
class ProductController extends Controller
{
    public function index()
    {
        // Verificar si el usuario tiene permiso
        if (!auth()->user()->hasPermission('manage_products')) {
            abort(403, 'No tiene permiso para gestionar productos.');
        }
        
        $products = Product::all();
        return view('products.index', compact('products'));
    }
    
    public function destroy($id)
    {
        // Verificar si es admin O tiene permiso específico
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete_products')) {
            abort(403, 'No tiene permiso para eliminar productos.');
        }
        
        $product = Product::findOrFail($id);
        $product->delete();
        
        return redirect()->back()->with('success', 'Producto eliminado.');
    }
}
*/

// ============================================================================
// EJEMPLOS DE VERIFICACIÓN EN VISTAS BLADE
// ============================================================================

/*
<!-- Mostrar solo para administradores -->
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
        Panel de Administración
    </a>
@endif

<!-- Mostrar solo para supervisores -->
@if(auth()->user()->isSupervisor())
    <a href="{{ route('supervisor.dashboard') }}" class="btn btn-info">
        Panel de Supervisión
    </a>
@endif

<!-- Mostrar solo para desarrolladores -->
@if(auth()->user()->isDeveloper())
    <a href="{{ route('developer.dashboard') }}" class="btn btn-warning">
        Panel de Desarrollo
    </a>
@endif

<!-- Mostrar si tiene permiso específico -->
@if(auth()->user()->hasPermission('manage_products'))
    <button class="btn btn-success">Gestionar Productos</button>
@endif

<!-- Mostrar si tiene alguno de varios permisos -->
@if(auth()->user()->hasAnyPermission(['edit_products', 'delete_products']))
    <div class="admin-actions">
        @if(auth()->user()->hasPermission('edit_products'))
            <button class="btn btn-primary">Editar</button>
        @endif
        
        @if(auth()->user()->hasPermission('delete_products'))
            <button class="btn btn-danger">Eliminar</button>
        @endif
    </div>
@endif

<!-- Mostrar nombre del rol -->
<div class="user-info">
    <p>Usuario: {{ auth()->user()->name }}</p>
    <p>Rol: {{ auth()->user()->getRoleName() }}</p>
</div>
*/

// ============================================================================
// REDIRECCIÓN SEGÚN ROL DESPUÉS DEL LOGIN
// ============================================================================

/*
// En App\Http\Controllers\Auth\LoginController.php

protected function authenticated(Request $request, $user)
{
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    
    if ($user->isSupervisor()) {
        return redirect()->route('supervisor.dashboard');
    }
    
    if ($user->isDeveloper()) {
        return redirect()->route('developer.dashboard');
    }
    
    return redirect('/'); // Usuario sin rol específico
}
*/
