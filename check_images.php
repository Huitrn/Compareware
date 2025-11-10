<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

echo "=== Verificación de Imágenes ===\n\n";

$productos = Periferico::whereNotNull('imagen_url')->limit(5)->get();

foreach ($productos as $producto) {
    echo "Producto: {$producto->nombre}\n";
    echo "URL: {$producto->imagen_url}\n";
    echo "Source: {$producto->imagen_source}\n";
    echo "Alt: {$producto->imagen_alt}\n";
    echo "---\n\n";
}

echo "\nTotal con imagen: " . Periferico::whereNotNull('imagen_url')->count() . "\n";
