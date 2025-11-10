<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

$conImagen = Periferico::whereNotNull('imagen_path')->count();
$total = Periferico::count();

echo "=== Estado de Imágenes Locales ===\n\n";
echo "Total productos: $total\n";
echo "Con imagen local (archivo): $conImagen\n";
echo "Sin imagen local: " . ($total - $conImagen) . "\n\n";

if ($conImagen > 0) {
    echo "Productos con imagen local:\n";
    $productos = Periferico::whereNotNull('imagen_path')->get(['id', 'nombre', 'imagen_path']);
    foreach ($productos as $p) {
        echo "  - {$p->nombre} -> storage/{$p->imagen_path}\n";
    }
}
