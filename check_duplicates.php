<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

echo "=== Verificación Completa de Imágenes ===\n\n";

$productos = Periferico::whereNotNull('imagen_url')
    ->with('marca', 'categoria')
    ->orderBy('nombre')
    ->get();

$imageUrls = [];

foreach ($productos as $producto) {
    $url = $producto->imagen_url;
    $imageUrls[] = $url;
    
    echo sprintf("%-35s | %s\n", 
        substr($producto->nombre, 0, 35), 
        $url
    );
}

echo "\n=== Análisis de Duplicados ===\n";
$urlCounts = array_count_values($imageUrls);
$duplicates = array_filter($urlCounts, fn($count) => $count > 1);

if (empty($duplicates)) {
    echo "✅ ¡Excelente! Todas las imágenes son únicas.\n";
} else {
    echo "⚠️  Imágenes duplicadas encontradas:\n\n";
    foreach ($duplicates as $url => $count) {
        echo "Usada $count veces: $url\n";
        echo "Productos:\n";
        
        $productosConEstaUrl = Periferico::where('imagen_url', $url)->get();
        foreach ($productosConEstaUrl as $p) {
            echo "  - " . $p->nombre . "\n";
        }
        echo "\n";
    }
}

echo "\nTotal productos con imagen: " . count($imageUrls) . "\n";
echo "Imágenes únicas: " . count(array_unique($imageUrls)) . "\n";
