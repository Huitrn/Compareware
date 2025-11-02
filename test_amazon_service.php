<?php
// Test del servicio Amazon API
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DIAGNÓSTICO DE AMAZON API SERVICE\n";
echo "===================================\n\n";

try {
    // Verificar configuración
    echo "1. Verificando configuración...\n";
    $apiKey = config('services.rapidapi.key');
    $apiHost = config('services.rapidapi.host');  
    $baseUrl = config('services.rapidapi.base_url');
    
    echo "   API Key: " . ($apiKey ? substr($apiKey, 0, 10) . "..." : "❌ NO CONFIGURADA") . "\n";
    echo "   API Host: " . ($apiHost ?: "❌ NO CONFIGURADA") . "\n";
    echo "   Base URL: " . ($baseUrl ?: "❌ NO CONFIGURADA") . "\n\n";
    
    // Instanciar servicio
    echo "2. Instanciando servicio Amazon...\n";
    $amazonService = app(\App\Services\AmazonApiService::class);
    echo "   ✅ Servicio instanciado correctamente\n\n";
    
    // Test búsqueda simple
    echo "3. Probando búsqueda simple...\n";
    echo "   Buscando: 'auriculares'\n";
    
    $result = $amazonService->searchProducts('auriculares');
    
    if (isset($result['success']) && $result['success']) {
        echo "   ✅ Búsqueda exitosa!\n";
        echo "   Productos encontrados: " . count($result['products'] ?? []) . "\n";
        
        if (!empty($result['products'])) {
            $product = $result['products'][0];
            echo "   Primer producto: " . ($product['product_title'] ?? 'Sin título') . "\n";
            echo "   Precio: " . ($product['product_price'] ?? 'Sin precio') . "\n";
        }
    } else {
        echo "   ❌ Error en la búsqueda:\n";
        echo "   " . ($result['error'] ?? 'Error desconocido') . "\n";
    }

} catch (\Exception $e) {
    echo "❌ EXCEPCIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n🔧 POSIBLES SOLUCIONES:\n";
    echo "1. Verificar que RAPIDAPI_KEY esté configurada correctamente en .env\n";
    echo "2. Verificar que la clave de RapidAPI tenga acceso a Real Time Amazon Data API\n";
    echo "3. Verificar conectividad a internet\n";
    echo "4. Revisar límites de API en RapidAPI\n";
}