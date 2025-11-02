<?php
// Servidor simple para probar Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Inicializar la aplicación
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "Iniciando servidor Laravel en puerto 8000...\n";
    echo "Accede a: http://127.0.0.1:8000/environment/dashboard\n";
    echo "Presiona Ctrl+C para detener\n\n";
    
    // Simular servidor (esto es solo informativo)
    system('php -S 127.0.0.1:8000 -t public');

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}