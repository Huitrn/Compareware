<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ComparisonHistory;
use App\Models\User;
use App\Models\Periferico;

echo "=== Creando comparaciones de prueba ===\n\n";

$user = User::first();
if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit(1);
}

$perifericos = Periferico::limit(6)->get();
if ($perifericos->count() < 4) {
    echo "❌ Se necesitan al menos 4 periféricos en la base de datos\n";
    exit(1);
}

// Crear 3 comparaciones de ejemplo
$comparisons = [
    [
        'periferico1_id' => $perifericos[0]->id,
        'periferico2_id' => $perifericos[1]->id,
    ],
    [
        'periferico1_id' => $perifericos[2]->id,
        'periferico2_id' => $perifericos[3]->id,
    ],
    [
        'periferico1_id' => $perifericos[4]->id ?? $perifericos[0]->id,
        'periferico2_id' => $perifericos[5]->id ?? $perifericos[1]->id,
    ],
];

foreach ($comparisons as $index => $comparison) {
    ComparisonHistory::create([
        'user_id' => $user->id,
        'periferico1_id' => $comparison['periferico1_id'],
        'periferico2_id' => $comparison['periferico2_id'],
        'comparison_data' => [
            'test' => true,
            'created_by_seed' => true
        ],
        'ip_address' => '127.0.0.1'
    ]);
    
    echo "✅ Comparación " . ($index + 1) . " creada\n";
}

echo "\n🎉 ¡Listo! {$user->name} ahora tiene " . ComparisonHistory::where('user_id', $user->id)->count() . " comparaciones en su historial\n";
echo "\n📍 Ve a http://127.0.0.1:8000/perfil para ver el historial\n";
