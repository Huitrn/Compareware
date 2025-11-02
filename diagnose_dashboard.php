<?php
// Script de diagnóstico detallado
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 DIAGNÓSTICO DETALLADO DEL DASHBOARD\n";
echo "=====================================\n\n";

try {
    // 1. Verificar autoloader
    echo "1. Verificando autoloader...\n";
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception("❌ Autoloader no encontrado");
    }
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✅ Autoloader cargado\n";

    // 2. Verificar bootstrap
    echo "2. Verificando bootstrap...\n";
    if (!file_exists(__DIR__ . '/bootstrap/app.php')) {
        throw new Exception("❌ Bootstrap no encontrado");
    }
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "   ✅ Bootstrap cargado\n";

    // 3. Inicializar kernel
    echo "3. Inicializando kernel...\n";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "   ✅ Kernel inicializado\n";

    // 4. Verificar conexión DB
    echo "4. Verificando conexión a base de datos...\n";
    $connection = DB::connection();
    $dbName = $connection->getDatabaseName();
    echo "   ✅ Conectado a: {$dbName}\n";

    // 5. Verificar tabla environment_logs
    echo "5. Verificando tabla environment_logs...\n";
    $tableExists = DB::getSchemaBuilder()->hasTable('environment_logs');
    if (!$tableExists) {
        throw new Exception("❌ Tabla environment_logs no existe");
    }
    echo "   ✅ Tabla existe\n";

    // 6. Verificar estructura de la tabla
    echo "6. Verificando estructura de tabla...\n";
    $columns = DB::getSchemaBuilder()->getColumnListing('environment_logs');
    echo "   Columnas: " . implode(', ', $columns) . "\n";

    // 7. Probar consulta básica
    echo "7. Probando consulta básica...\n";
    $count = DB::table('environment_logs')->count();
    echo "   ✅ Registros encontrados: {$count}\n";

    // 8. Probar modelo EnvironmentLog
    echo "8. Probando modelo EnvironmentLog...\n";
    $modelPath = __DIR__ . '/app/Models/EnvironmentLog.php';
    if (!file_exists($modelPath)) {
        throw new Exception("❌ Modelo EnvironmentLog no encontrado");
    }
    
    // Probar instanciación del modelo
    $environmentLog = new \App\Models\EnvironmentLog();
    echo "   ✅ Modelo instanciado correctamente\n";

    // 9. Probar controlador
    echo "9. Probando controlador EnvironmentTestController...\n";
    $controllerPath = __DIR__ . '/app/Http/Controllers/EnvironmentTestController.php';
    if (!file_exists($controllerPath)) {
        throw new Exception("❌ Controlador no encontrado");
    }
    
    $controller = new \App\Http\Controllers\EnvironmentTestController();
    echo "   ✅ Controlador instanciado\n";

    // 10. Simular request al dashboard
    echo "10. Simulando request al dashboard...\n";
    
    // Crear mock request
    $request = \Illuminate\Http\Request::create('/environment/dashboard', 'GET');
    \Illuminate\Support\Facades\Request::swap($request);
    
    // Intentar ejecutar método dashboard
    $response = $controller->dashboard();
    
    if ($response instanceof \Illuminate\Http\Response || 
        $response instanceof \Illuminate\View\View ||
        $response instanceof \Illuminate\Contracts\View\View) {
        echo "   ✅ Dashboard ejecutado exitosamente\n";
    } else {
        echo "   ⚠️ Respuesta inesperada: " . gettype($response) . "\n";
    }

    echo "\n✅ TODOS LOS DIAGNÓSTICOS PASARON\n";
    echo "El problema podría estar en el routing o en el servidor web.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR ENCONTRADO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ ERROR FATAL:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}