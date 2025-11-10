<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;
use Illuminate\Support\Str;

echo "=== GUÍA: Nombres de archivos para imágenes ===\n\n";
echo "Guarda tus imágenes en:\n";
echo "📁 storage\\app\\public\\images\\perifericos\\\n\n";
echo "Usa estos nombres EXACTOS:\n\n";

$productos = Periferico::orderBy('id')->get();

foreach ($productos as $producto) {
    $filename = Str::slug($producto->nombre) . '-' . $producto->id . '.jpg';
    $path = "storage\\app\\public\\images\\perifericos\\{$filename}";
    
    echo sprintf("%-3d | %-35s | %s\n", 
        $producto->id,
        substr($producto->nombre, 0, 35),
        $filename
    );
}

echo "\n=== Formatos aceptados ===\n";
echo "✅ .jpg / .jpeg (recomendado)\n";
echo "✅ .png\n";
echo "✅ .webp\n";
echo "\n=== Resolución recomendada ===\n";
echo "📐 800x600 píxeles mínimo\n";
echo "📐 1200x900 píxeles ideal\n";
echo "💾 Tamaño: 50KB - 500KB\n";
