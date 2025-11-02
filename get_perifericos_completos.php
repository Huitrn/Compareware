<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PERIFÉRICOS COMPLETOS CON MARCAS Y CATEGORÍAS ===\n\n";

try {
    // Obtener periféricos con sus relaciones
    $perifericos = DB::table('perifericos as p')
        ->join('marcas as m', 'p.marca_id', '=', 'm.id')
        ->join('categorias as c', 'p.categoria_id', '=', 'c.id')
        ->select('p.id', 'p.nombre', 'p.modelo', 'p.precio', 'p.tipo_conectividad', 'm.nombre as marca', 'c.nombre as categoria')
        ->orderBy('c.nombre')
        ->orderBy('m.nombre')
        ->get();
    
    if ($perifericos->count() > 0) {
        $categorias = $perifericos->groupBy('categoria');
        
        foreach ($categorias as $categoria => $productos) {
            echo "📂 CATEGORÍA: " . strtoupper($categoria) . "\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($productos as $producto) {
                echo sprintf("  %d: %s %s (%s) - $%.2f [%s]\n", 
                    $producto->id, 
                    $producto->marca,
                    $producto->nombre, 
                    $producto->modelo ?? 'Sin modelo',
                    $producto->precio,
                    $producto->tipo_conectividad ?? 'N/A'
                );
            }
            echo "\n";
        }
        
        echo "TOTAL PRODUCTOS: " . $perifericos->count() . "\n\n";
        
        // Mostrar resumen por categoría
        echo "=== RESUMEN POR CATEGORÍA ===\n";
        foreach ($categorias as $categoria => $productos) {
            echo "- " . $categoria . ": " . $productos->count() . " productos\n";
        }

    } else {
        echo "❌ No hay periféricos en la base de datos\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}