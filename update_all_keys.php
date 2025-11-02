<?php
// Script para actualizar APP_KEY en todos los archivos de ambiente
require_once __DIR__ . '/vendor/autoload.php';

echo "Actualizando APP_KEY en todos los archivos de ambiente...\n\n";

$envFiles = ['.env.sandbox', '.env.staging', '.env.production'];

foreach ($envFiles as $envFile) {
    if (!file_exists($envFile)) {
        echo "⚠️  {$envFile} no existe, saltando...\n";
        continue;
    }
    
    echo "Procesando {$envFile}...\n";
    
    // Generar clave única para cada ambiente
    $key = base64_encode(random_bytes(32));
    
    // Leer contenido actual
    $envContent = file_get_contents($envFile);
    
    // Reemplazar APP_KEY
    $pattern = '/^APP_KEY=.*/m';
    $replacement = "APP_KEY=base64:{$key}";
    
    $newEnvContent = preg_replace($pattern, $replacement, $envContent);
    
    if ($newEnvContent !== $envContent) {
        // Crear backup
        file_put_contents($envFile . '.backup', $envContent);
        
        // Escribir nueva configuración
        file_put_contents($envFile, $newEnvContent);
        
        echo "  ✅ APP_KEY actualizada\n";
        echo "  ✅ Backup creado como {$envFile}.backup\n";
    } else {
        echo "  ❌ No se pudo actualizar\n";
    }
    echo "\n";
}

echo "✅ Todas las claves de ambiente actualizadas!\n";