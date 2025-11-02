<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

echo "🔍 Periféricos en la Base de Datos:\n";
echo str_repeat("=", 50) . "\n";

$perifericos = DB::table('perifericos')
    ->select('id', 'nombre', 'marca', 'categoria_id', 'precio')
    ->limit(20)
    ->get();

foreach($perifericos as $p) {
    echo sprintf("%d: %s (%s) - $%.2f\n", 
        $p->id, 
        $p->nombre, 
        $p->marca ?? 'Sin marca', 
        $p->precio ?? 0
    );
}

echo "\n📊 Total periféricos: " . DB::table('perifericos')->count() . "\n";
echo "📂 Categorías disponibles:\n";

$categorias = DB::table('categorias')->select('id', 'nombre')->get();
foreach($categorias as $cat) {
    $count = DB::table('perifericos')->where('categoria_id', $cat->id)->count();
    echo "  - {$cat->nombre} ({$count} productos)\n";
}