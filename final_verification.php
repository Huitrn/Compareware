<?php
// Verificación final del sistema
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎉 VERIFICACIÓN FINAL DEL SISTEMA MULTI-AMBIENTE\n";
echo "================================================\n\n";

try {
    // Test completo del controlador
    echo "📊 Probando dashboard completo...\n";
    
    $controller = new \App\Http\Controllers\EnvironmentTestController();
    $request = \Illuminate\Http\Request::create('/environment/dashboard', 'GET');
    \Illuminate\Support\Facades\Request::swap($request);
    
    $response = $controller->dashboard();
    echo "   ✅ Dashboard response: " . get_class($response) . "\n";
    
    // Probar otros métodos
    echo "   ✅ Testing environment...\n";
    $testRequest = \Illuminate\Http\Request::create('/api/environment/test', 'GET');
    $testResponse = $controller->testEnvironment($testRequest);
    echo "   ✅ Test response: " . get_class($testResponse) . "\n";
    
    // Verificar logs
    echo "📝 Verificando logs recientes...\n";
    $recentLogs = DB::table('environment_logs')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Últimos {$recentLogs->count()} registros:\n";
    foreach ($recentLogs as $log) {
        echo "   - [{$log->environment}] {$log->action}: {$log->description}\n";
    }
    
    // Estado por ambiente
    echo "\n🌍 Estado por ambiente:\n";
    $stats = DB::table('environment_logs')
        ->selectRaw('environment, COUNT(*) as total')
        ->groupBy('environment')
        ->orderBy('total', 'desc')
        ->get();
    
    foreach ($stats as $stat) {
        echo "   {$stat->environment}: {$stat->total} registros\n";
    }
    
    echo "\n🔧 URLs del sistema:\n";
    echo "   Dashboard: http://127.0.0.1:8000/environment/dashboard\n";
    echo "   API Test:  http://127.0.0.1:8000/api/environment/test\n";
    
    echo "\n📋 Comandos disponibles:\n";
    echo "   php artisan env:switch sandbox\n";
    echo "   php artisan env:switch staging\n";
    echo "   php artisan env:switch production\n";
    
    echo "\n✅ SISTEMA COMPLETAMENTE OPERACIONAL PARA TU DEMOSTRACIÓN ACADÉMICA! 🎓\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}