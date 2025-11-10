<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

$producto = Periferico::with(['marca', 'categoria'])->find(1);

echo "=== DEBUG PRODUCTO ID 1 ===" . PHP_EOL;
echo "ID: " . $producto->id . PHP_EOL;
echo "Nombre: " . $producto->nombre . PHP_EOL;
echo "imagen_path: " . ($producto->imagen_path ?? 'NULL') . PHP_EOL;
echo "imagen_blob: " . ($producto->imagen_blob ? 'EXISTS' : 'NULL') . PHP_EOL;
echo "imagen_url: " . ($producto->imagen_url ?? 'NULL') . PHP_EOL;
echo "imagen_source: " . ($producto->imagen_source ?? 'NULL') . PHP_EOL;
echo PHP_EOL;
echo "imagen_url_completa: " . $producto->imagen_url_completa . PHP_EOL;
echo PHP_EOL;

echo "=== VERIFICAR APPENDS ===" . PHP_EOL;
echo "Appends definidos: " . json_encode($producto->getAppends()) . PHP_EOL;
echo PHP_EOL;

echo "=== CONDICIONAL DE LA VISTA ===" . PHP_EOL;
echo "imagen_path || imagen_blob || imagen_url = ";
echo ($producto->imagen_path || $producto->imagen_blob || $producto->imagen_url) ? 'TRUE ✅' : 'FALSE ❌';
echo PHP_EOL;
