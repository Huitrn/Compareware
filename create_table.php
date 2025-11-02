<?php
// Script para crear la tabla environment_logs
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Inicializar la aplicación
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "Conectando a la base de datos...\n";
    echo "Ambiente actual: " . app()->environment() . "\n";
    
    // Leer el SQL
    $sql = file_get_contents(__DIR__ . '/create_environment_logs.sql');
    
    // Ejecutar las queries
    DB::unprepared($sql);
    
    echo "✅ Tabla environment_logs creada exitosamente!\n";
    
    // Verificar que se creó
    $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'Compareware' AND table_name = 'environment_logs'");
    
    if (!empty($tables)) {
        echo "✅ Tabla verificada en la base de datos\n";
        
        // Insertar registro de prueba
        DB::table('environment_logs')->insert([
            'environment' => app()->environment(),
            'action' => 'table_created',
            'description' => 'Tabla environment_logs creada correctamente',
            'created_at' => now()
        ]);
        
        echo "✅ Registro de prueba insertado\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}