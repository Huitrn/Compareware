<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;

echo "=== Verificación de URLs de Amazon ===\n\n";

$productos = Periferico::whereNotNull('amazon_url')
    ->limit(5)
    ->get(['id', 'nombre', 'amazon_url', 'amazon_asin']);

if ($productos->isEmpty()) {
    echo "❌ No hay productos con amazon_url\n";
} else {
    echo "✅ Productos con URL de Amazon:\n\n";
    foreach ($productos as $p) {
        echo "ID: {$p->id}\n";
        echo "Producto: {$p->nombre}\n";
        echo "ASIN: {$p->amazon_asin}\n";
        echo "URL: " . substr($p->amazon_url, 0, 100) . "...\n";
        echo "---\n\n";
    }
}

$total = Periferico::count();
$conAmazon = Periferico::whereNotNull('amazon_url')->count();

echo "Total productos: $total\n";
echo "Con URL de Amazon: $conAmazon\n";
echo "Sin URL de Amazon: " . ($total - $conAmazon) . "\n";
