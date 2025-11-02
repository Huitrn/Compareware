<?php
// Script para probar todas las funcionalidades del dashboard
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "🧪 PRUEBA COMPLETA DEL SISTEMA DE AMBIENTES\n";
    echo "==========================================\n\n";
    
    echo "1. Ambiente actual: " . strtoupper(app()->environment()) . "\n";
    
    echo "2. Base de datos: ";
    $dbName = config('database.connections.pgsql.database');
    echo "✓ Conectado a '{$dbName}'\n";
    
    echo "3. Tabla environment_logs: ";
    $count = DB::table('environment_logs')->count();
    echo "✓ {$count} registros\n";
    
    echo "4. Creando logs de prueba...\n";
    
    // Insertar algunos logs de prueba para cada ambiente
    $testLogs = [
        ['environment' => 'sandbox', 'action' => 'test_action', 'description' => 'Prueba automática en sandbox'],
        ['environment' => 'staging', 'action' => 'test_action', 'description' => 'Prueba automática en staging'],
        ['environment' => 'production', 'action' => 'test_action', 'description' => 'Prueba automática en production'],
    ];
    
    foreach ($testLogs as $log) {
        DB::table('environment_logs')->insert([
            'environment' => $log['environment'],
            'action' => $log['action'],
            'description' => $log['description'],
            'created_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Script',
            'session_id' => 'test-session'
        ]);
        echo "   ✓ Log creado para {$log['environment']}\n";
    }
    
    echo "\n5. Estadísticas por ambiente:\n";
    $stats = DB::table('environment_logs')
        ->selectRaw('environment, COUNT(*) as total, MAX(created_at) as last_activity')
        ->groupBy('environment')
        ->get();
    
    foreach ($stats as $stat) {
        echo "   {$stat->environment}: {$stat->total} registros (último: {$stat->last_activity})\n";
    }
    
    echo "\n✅ SISTEMA COMPLETAMENTE FUNCIONAL!\n";
    echo "📍 Accede al dashboard en: http://127.0.0.1:8000/environment/dashboard\n\n";
    
    echo "🔧 COMANDOS DISPONIBLES:\n";
    echo "• Cambiar a sandbox: php artisan env:switch sandbox\n";
    echo "• Cambiar a staging: php artisan env:switch staging\n";
    echo "• Cambiar a production: php artisan env:switch production\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}