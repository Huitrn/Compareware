<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

echo "=== PRODUCTOS EN LA BASE DE DATOS ===\n\n";
echo "Total: " . Periferico::count() . " productos\n\n";

$productos = Periferico::limit(10)->get(['nombre']);

echo "Primeros 10 productos:\n";
foreach ($productos as $producto) {
    echo "- {$producto->nombre}\n";
}
