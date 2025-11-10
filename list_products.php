<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

echo "=== Productos en la Base de Datos ===\n\n";

$productos = Periferico::with('marca', 'categoria')
    ->orderBy('nombre')
    ->get();

foreach ($productos as $producto) {
    $marca = $producto->marca ? $producto->marca->nombre : 'Sin marca';
    $categoria = $producto->categoria ? $producto->categoria->nombre : 'Sin categoría';
    $imagen = $producto->imagen_url ? 'SÍ' : 'NO';
    
    echo sprintf(
        "ID: %-3s | %-30s | Marca: %-15s | Cat: %-15s | Img: %s\n",
        $producto->id,
        substr($producto->nombre, 0, 30),
        substr($marca, 0, 15),
        substr($categoria, 0, 15),
        $imagen
    );
}

echo "\nTotal: " . $productos->count() . " productos\n";
echo "Con imagen: " . Periferico::whereNotNull('imagen_url')->count() . "\n";
