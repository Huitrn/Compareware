<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA DE LA TABLA PERIFÉRICOS ===\n\n";

try {
    // Obtener estructura de la tabla
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'perifericos' ORDER BY ordinal_position");
    
    echo "COLUMNAS:\n";
    foreach ($columns as $col) {
        echo "- " . $col->column_name . " (" . $col->data_type . ")\n";
    }
    
    echo "\n=== PRIMEROS 10 REGISTROS ===\n\n";
    
    // Obtener algunos registros
    $perifericos = DB::table('perifericos')->limit(10)->get();
    
    if ($perifericos->count() > 0) {
        foreach ($perifericos as $index => $producto) {
            echo ($index + 1) . ". ";
            foreach ($producto as $key => $value) {
                if (in_array($key, ['id', 'nombre', 'precio'])) {
                    echo $key . ": " . $value . " | ";
                }
            }
            echo "\n";
        }
    } else {
        echo "No hay registros en la tabla periféricos\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}