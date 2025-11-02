<?php
// Test simple para verificar el error
echo "Probando conexión...\n";

// Cargar el autoloader de Laravel
require_once __DIR__ . '/vendor/autoload.php';

// Cargar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Inicializar la aplicación
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // Probar conexión a BD
    echo "Ambiente actual: " . app()->environment() . "\n";
    
    // Verificar si la tabla environment_logs existe
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'environment_logs'");
    
    if (empty($tables)) {
        echo "ERROR: La tabla environment_logs no existe\n";
    } else {
        echo "✓ Tabla environment_logs existe\n";
        
        // Probar consulta simple
        $count = DB::table('environment_logs')->count();
        echo "✓ Registros en environment_logs: $count\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}