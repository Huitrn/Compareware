<?php

/**
 * Script de Demostración: Insertar Productos y Sincronizar Imágenes
 * Ejecutar: php Compareware/demo_imagenes_amazon.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Periferico;
use App\Models\Marca;
use App\Models\Categoria;
use App\Services\AmazonImageService;
use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   Demo: Sistema de Imágenes Amazon - CompareWare         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Verificar esquema actual
$currentSchema = DB::select("SELECT current_schema()")[0]->current_schema;
echo "📍 Esquema actual: {$currentSchema}\n\n";

// Paso 1: Verificar/Crear Marcas
echo "📦 Paso 1: Verificando Marcas\n";
echo str_repeat("─", 60) . "\n";

$marcasData = [
    ['nombre' => 'Logitech'],
    ['nombre' => 'Razer'],
    ['nombre' => 'HyperX'],
    ['nombre' => 'Corsair']
];

$marcas = [];
foreach ($marcasData as $marcaData) {
    $marca = Marca::firstOrCreate(
        ['nombre' => $marcaData['nombre']]
    );
    $marcas[$marca->nombre] = $marca;
    echo "  ✓ {$marca->nombre} (ID: {$marca->id})\n";
}

echo "\n";

// Paso 2: Verificar/Crear Categorías
echo "📁 Paso 2: Verificando Categorías\n";
echo str_repeat("─", 60) . "\n";

$categoriasData = [
    ['nombre' => 'Mouse'],
    ['nombre' => 'Teclado'],
    ['nombre' => 'Audífonos'],
    ['nombre' => 'Webcam']
];

$categorias = [];
foreach ($categoriasData as $catData) {
    $categoria = Categoria::firstOrCreate(
        ['nombre' => $catData['nombre']]
    );
    $categorias[$categoria->nombre] = $categoria;
    echo "  ✓ {$categoria->nombre} (ID: {$categoria->id})\n";
}

echo "\n";

// Paso 3: Insertar Periféricos de Ejemplo
echo "🖱️ Paso 3: Insertando Periféricos de Ejemplo\n";
echo str_repeat("─", 60) . "\n";

$perifericosData = [
    // Mouses
    [
        'nombre' => 'Logitech G502 HERO',
        'modelo' => 'G502',
        'marca' => 'Logitech',
        'categoria' => 'Mouse',
        'precio' => 899.99,
        'tipo_conectividad' => 'USB'
    ],
    [
        'nombre' => 'Razer DeathAdder V2',
        'modelo' => 'DeathAdder V2',
        'marca' => 'Razer',
        'categoria' => 'Mouse',
        'precio' => 1199.99,
        'tipo_conectividad' => 'USB'
    ],
    [
        'nombre' => 'Logitech MX Master 3',
        'modelo' => 'MX Master 3',
        'marca' => 'Logitech',
        'categoria' => 'Mouse',
        'precio' => 1599.00,
        'tipo_conectividad' => 'Bluetooth'
    ],
    
    // Teclados
    [
        'nombre' => 'Razer BlackWidow V3',
        'modelo' => 'BlackWidow V3',
        'marca' => 'Razer',
        'categoria' => 'Teclado',
        'precio' => 1899.00,
        'tipo_conectividad' => 'USB'
    ],
    [
        'nombre' => 'Corsair K70 RGB',
        'modelo' => 'K70 RGB MK.2',
        'marca' => 'Corsair',
        'categoria' => 'Teclado',
        'precio' => 2299.00,
        'tipo_conectividad' => 'USB'
    ],
    
    // Audífonos
    [
        'nombre' => 'HyperX Cloud II',
        'modelo' => 'Cloud II',
        'marca' => 'HyperX',
        'categoria' => 'Audífonos',
        'precio' => 1499.00,
        'tipo_conectividad' => 'USB'
    ],
    [
        'nombre' => 'Logitech G Pro X',
        'modelo' => 'G Pro X',
        'marca' => 'Logitech',
        'categoria' => 'Audífonos',
        'precio' => 1999.00,
        'tipo_conectividad' => 'USB'
    ],
    
    // Webcams
    [
        'nombre' => 'Logitech C920',
        'modelo' => 'C920',
        'marca' => 'Logitech',
        'categoria' => 'Webcam',
        'precio' => 1299.00,
        'tipo_conectividad' => 'USB'
    ]
];

$insertedIds = [];
$skipped = 0;

foreach ($perifericosData as $periData) {
    // Verificar si ya existe
    $existing = Periferico::where('nombre', $periData['nombre'])
                          ->where('modelo', $periData['modelo'])
                          ->first();
    
    if ($existing) {
        echo "  ⏭️  Ya existe: {$periData['nombre']}\n";
        $insertedIds[] = $existing->id;
        $skipped++;
        continue;
    }
    
    // Crear nuevo
    $periferico = Periferico::create([
        'nombre' => $periData['nombre'],
        'modelo' => $periData['modelo'],
        'marca_id' => $marcas[$periData['marca']]->id,
        'categoria_id' => $categorias[$periData['categoria']]->id,
        'precio' => $periData['precio'],
        'tipo_conectividad' => $periData['tipo_conectividad']
    ]);
    
    $insertedIds[] = $periferico->id;
    echo "  ✓ Creado: {$periferico->nombre} (ID: {$periferico->id})\n";
}

echo "\n  📊 Total: " . count($perifericosData) . " productos\n";
echo "  ✅ Nuevos: " . (count($perifericosData) - $skipped) . "\n";
echo "  ⏭️  Existentes: {$skipped}\n";

echo "\n";

// Paso 4: Sincronizar Imágenes
echo "🖼️ Paso 4: Sincronizando Imágenes desde Amazon\n";
echo str_repeat("─", 60) . "\n";

$imageService = app(AmazonImageService::class);

$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'skipped' => 0
];

echo "Procesando periféricos...\n\n";

foreach ($insertedIds as $index => $id) {
    $periferico = Periferico::with(['marca', 'categoria'])->find($id);
    
    if (!$periferico) {
        continue;
    }
    
    $stats['total']++;
    
    echo sprintf(
        "  [%d/%d] %s... ",
        $index + 1,
        count($insertedIds),
        substr($periferico->nombre, 0, 35)
    );
    
    // Sincronizar imagen
    $result = $imageService->syncPerifericoImage($periferico);
    
    if (isset($result['skipped']) && $result['skipped']) {
        echo "⏭️  OMITIDO\n";
        $stats['skipped']++;
    } elseif ($result['success']) {
        echo "✅ OK\n";
        $stats['success']++;
    } else {
        $error = $result['error'] ?? 'Error desconocido';
        echo "❌ FALLÓ ({$error})\n";
        $stats['failed']++;
    }
    
    // Pausa para evitar rate limiting
    usleep(500000); // 0.5 segundos
}

echo "\n";

// Paso 5: Mostrar Resultados
echo "📊 Paso 5: Resultados de Sincronización\n";
echo str_repeat("─", 60) . "\n";

echo sprintf("  Total procesados: %d\n", $stats['total']);
echo sprintf("  ✅ Exitosos:      %d\n", $stats['success']);
echo sprintf("  ⏭️  Omitidos:      %d\n", $stats['skipped']);
echo sprintf("  ❌ Fallidos:      %d\n", $stats['failed']);

if ($stats['total'] > 0) {
    $successRate = round(($stats['success'] / $stats['total']) * 100, 2);
    echo sprintf("\n  🎯 Tasa de éxito: %.2f%%\n", $successRate);
}

echo "\n";

// Paso 6: Mostrar Productos con Imágenes
echo "🖼️ Paso 6: Productos con Imágenes\n";
echo str_repeat("─", 60) . "\n";

$productosConImagen = Periferico::whereNotNull('imagen_url')
                                 ->with(['marca', 'categoria'])
                                 ->get();

if ($productosConImagen->count() > 0) {
    foreach ($productosConImagen as $p) {
        echo "  ✓ {$p->nombre}\n";
        echo "    URL: " . substr($p->imagen_url, 0, 60) . "...\n";
        echo "    Fuente: " . ($p->imagen_source ?? 'N/A') . "\n";
        echo "    Marca: " . ($p->marca->nombre ?? 'N/A') . "\n";
        echo "\n";
    }
} else {
    echo "  ⚠️  No hay productos con imágenes aún\n\n";
}

// Paso 7: Instrucciones Finales
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                 DEMOSTRACIÓN COMPLETADA                   ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "📝 Próximos pasos:\n\n";

echo "1️⃣  Ver productos en la base de datos:\n";
echo "   php artisan tinker\n";
echo "   >>> Periferico::with('marca', 'categoria')->get()\n\n";

echo "2️⃣  Sincronizar más imágenes:\n";
echo "   php artisan amazon:sync-images --limit=20\n\n";

echo "3️⃣  Ver en el navegador:\n";
echo "   http://localhost:8000/comparadora\n\n";

echo "4️⃣  Probar API:\n";
echo "   GET /api/comparacion/compare-products?periferico1=1&periferico2=2\n\n";

echo "5️⃣  Consultar estadísticas:\n";
$totalPerifericos = Periferico::count();
$conImagen = Periferico::whereNotNull('imagen_url')->count();
$sinImagen = Periferico::whereNull('imagen_url')->count();

echo "   Total de productos: {$totalPerifericos}\n";
echo "   Con imagen: {$conImagen}\n";
echo "   Sin imagen: {$sinImagen}\n\n";

echo "✅ Demo completada exitosamente!\n";
