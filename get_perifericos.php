<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERIFÉRICOS EN LA BASE DE DATOS ===\n\n";

try {
    $perifericos = \App\Models\Periferico::select('id', 'nombre', 'marca', 'categoria', 'precio')
        ->orderBy('categoria')
        ->orderBy('marca')
        ->get();

    if ($perifericos->count() > 0) {
        $categorias = $perifericos->groupBy('categoria');
        
        foreach ($categorias as $categoria => $productos) {
            echo "📂 CATEGORÍA: " . strtoupper($categoria) . "\n";
            echo str_repeat("-", 50) . "\n";
            
            foreach ($productos as $producto) {
                echo sprintf("  %d: %s (%s) - $%.2f\n", 
                    $producto->id, 
                    $producto->nombre, 
                    $producto->marca, 
                    $producto->precio
                );
            }
            echo "\n";
        }
        
        echo "\nTOTAL PRODUCTOS: " . $perifericos->count() . "\n";
    } else {
        echo "❌ No hay periféricos en la base de datos\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}