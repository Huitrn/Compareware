<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ComparisonHistory;
use App\Models\Periferico;

echo "=== Limpiando comparaciones inválidas del historial ===\n";

// Obtener todas las comparaciones
$comparisons = ComparisonHistory::with(['periferico1', 'periferico2'])->get();

$invalidCount = 0;
$validCount = 0;

foreach ($comparisons as $comparison) {
    // Verificar si los periféricos existen
    if (!$comparison->periferico1 || !$comparison->periferico2) {
        echo "❌ Comparación ID {$comparison->id}: Periférico no encontrado\n";
        $comparison->delete();
        $invalidCount++;
        continue;
    }
    
    // Verificar si son de la misma categoría
    if ($comparison->periferico1->categoria_id !== $comparison->periferico2->categoria_id) {
        $cat1 = $comparison->periferico1->categoria->nombre ?? 'N/A';
        $cat2 = $comparison->periferico2->categoria->nombre ?? 'N/A';
        
        echo "❌ Comparación ID {$comparison->id}: {$comparison->periferico1->nombre} ({$cat1}) vs {$comparison->periferico2->nombre} ({$cat2})\n";
        $comparison->delete();
        $invalidCount++;
    } else {
        $validCount++;
    }
}

echo "\n🎉 ¡Limpieza completada!\n";
echo "✅ Comparaciones válidas: {$validCount}\n";
echo "🗑️  Comparaciones eliminadas: {$invalidCount}\n";
