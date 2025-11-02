<?php
// Script para generar una APP_KEY válida
require_once __DIR__ . '/vendor/autoload.php';

echo "Generando nueva APP_KEY para Laravel...\n";

// Generar una clave base64 de 32 bytes (256 bits)
$key = base64_encode(random_bytes(32));

echo "Nueva APP_KEY generada:\n";
echo "APP_KEY=base64:{$key}\n\n";

// Leer el archivo .env actual
$envContent = file_get_contents('.env');

// Buscar y reemplazar la APP_KEY
$pattern = '/^APP_KEY=.*/m';
$replacement = "APP_KEY=base64:{$key}";

$newEnvContent = preg_replace($pattern, $replacement, $envContent);

if ($newEnvContent !== $envContent) {
    // Crear backup
    file_put_contents('.env.backup', $envContent);
    
    // Escribir nueva configuración
    file_put_contents('.env', $newEnvContent);
    
    echo "✅ APP_KEY actualizada en .env\n";
    echo "✅ Backup creado como .env.backup\n";
} else {
    echo "❌ No se pudo actualizar el archivo .env\n";
}