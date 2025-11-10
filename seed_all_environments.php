<?php

/**
 * Script para poblar los 3 esquemas (sandbox, staging, production)
 * con datos de prueba
 * 
 * Ejecutar: php Compareware/seed_all_environments.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Periferico;
use App\Models\Marca;
use App\Models\Categoria;
use App\Services\AmazonImageService;
use Illuminate\Support\Facades\DB;
use App\Helpers\SchemaManager;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   Poblar Datos en los 3 Ambientes de CompareWare         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Esquemas a poblar
$schemas = ['sandbox', 'staging', 'public'];
$schemaLabels = [
    'sandbox' => '🧪 SANDBOX (Desarrollo)',
    'staging' => '🔧 STAGING (Pruebas)',
    'public' => '🚀 PRODUCTION'
];

// Datos a insertar
$marcasData = [
    ['nombre' => 'Logitech'],
    ['nombre' => 'Razer'],
    ['nombre' => 'HyperX'],
    ['nombre' => 'Corsair'],
    ['nombre' => 'SteelSeries'],
    ['nombre' => 'Cooler Master'],
    ['nombre' => 'ASUS'],
    ['nombre' => 'MSI']
];

$categoriasData = [
    ['nombre' => 'Mouse'],
    ['nombre' => 'Teclado'],
    ['nombre' => 'Audífonos'],
    ['nombre' => 'Webcam'],
    ['nombre' => 'Micrófono'],
    ['nombre' => 'Monitor']
];

$perifericosData = [
    // Mouses
    ['nombre' => 'Logitech G502 HERO', 'modelo' => 'G502', 'marca' => 'Logitech', 'categoria' => 'Mouse', 'precio' => 899.99, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Razer DeathAdder V2', 'modelo' => 'DeathAdder V2', 'marca' => 'Razer', 'categoria' => 'Mouse', 'precio' => 1199.99, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Logitech MX Master 3', 'modelo' => 'MX Master 3', 'marca' => 'Logitech', 'categoria' => 'Mouse', 'precio' => 1599.00, 'tipo_conectividad' => 'Bluetooth'],
    ['nombre' => 'Razer Viper Ultimate', 'modelo' => 'Viper Ultimate', 'marca' => 'Razer', 'categoria' => 'Mouse', 'precio' => 1899.00, 'tipo_conectividad' => 'Inalámbrico'],
    ['nombre' => 'SteelSeries Rival 600', 'modelo' => 'Rival 600', 'marca' => 'SteelSeries', 'categoria' => 'Mouse', 'precio' => 1299.00, 'tipo_conectividad' => 'USB'],
    
    // Teclados
    ['nombre' => 'Razer BlackWidow V3', 'modelo' => 'BlackWidow V3', 'marca' => 'Razer', 'categoria' => 'Teclado', 'precio' => 1899.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Corsair K70 RGB', 'modelo' => 'K70 RGB MK.2', 'marca' => 'Corsair', 'categoria' => 'Teclado', 'precio' => 2299.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Logitech G915 TKL', 'modelo' => 'G915 TKL', 'marca' => 'Logitech', 'categoria' => 'Teclado', 'precio' => 3499.00, 'tipo_conectividad' => 'Inalámbrico'],
    ['nombre' => 'HyperX Alloy FPS Pro', 'modelo' => 'Alloy FPS Pro', 'marca' => 'HyperX', 'categoria' => 'Teclado', 'precio' => 1599.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'SteelSeries Apex Pro', 'modelo' => 'Apex Pro', 'marca' => 'SteelSeries', 'categoria' => 'Teclado', 'precio' => 2799.00, 'tipo_conectividad' => 'USB'],
    
    // Audífonos
    ['nombre' => 'HyperX Cloud II', 'modelo' => 'Cloud II', 'marca' => 'HyperX', 'categoria' => 'Audífonos', 'precio' => 1499.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Logitech G Pro X', 'modelo' => 'G Pro X', 'marca' => 'Logitech', 'categoria' => 'Audífonos', 'precio' => 1999.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Razer BlackShark V2', 'modelo' => 'BlackShark V2', 'marca' => 'Razer', 'categoria' => 'Audífonos', 'precio' => 1699.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'SteelSeries Arctis 7', 'modelo' => 'Arctis 7', 'marca' => 'SteelSeries', 'categoria' => 'Audífonos', 'precio' => 2299.00, 'tipo_conectividad' => 'Inalámbrico'],
    ['nombre' => 'Corsair HS70 Pro', 'modelo' => 'HS70 Pro', 'marca' => 'Corsair', 'categoria' => 'Audífonos', 'precio' => 1399.00, 'tipo_conectividad' => 'Inalámbrico'],
    
    // Webcams
    ['nombre' => 'Logitech C920', 'modelo' => 'C920', 'marca' => 'Logitech', 'categoria' => 'Webcam', 'precio' => 1299.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Logitech Brio 4K', 'modelo' => 'Brio', 'marca' => 'Logitech', 'categoria' => 'Webcam', 'precio' => 3499.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Razer Kiyo', 'modelo' => 'Kiyo', 'marca' => 'Razer', 'categoria' => 'Webcam', 'precio' => 1799.00, 'tipo_conectividad' => 'USB'],
    
    // Micrófonos
    ['nombre' => 'HyperX QuadCast', 'modelo' => 'QuadCast', 'marca' => 'HyperX', 'categoria' => 'Micrófono', 'precio' => 2299.00, 'tipo_conectividad' => 'USB'],
    ['nombre' => 'Razer Seiren Mini', 'modelo' => 'Seiren Mini', 'marca' => 'Razer', 'categoria' => 'Micrófono', 'precio' => 899.00, 'tipo_conectividad' => 'USB'],
    
    // Monitores
    ['nombre' => 'ASUS TUF Gaming VG27AQ', 'modelo' => 'VG27AQ', 'marca' => 'ASUS', 'categoria' => 'Monitor', 'precio' => 7999.00, 'tipo_conectividad' => 'HDMI'],
    ['nombre' => 'MSI Optix MAG274QRF', 'modelo' => 'MAG274QRF', 'marca' => 'MSI', 'categoria' => 'Monitor', 'precio' => 8499.00, 'tipo_conectividad' => 'DisplayPort']
];

$totalStats = [
    'sandbox' => ['marcas' => 0, 'categorias' => 0, 'perifericos' => 0, 'imagenes' => 0],
    'staging' => ['marcas' => 0, 'categorias' => 0, 'perifericos' => 0, 'imagenes' => 0],
    'public' => ['marcas' => 0, 'categorias' => 0, 'perifericos' => 0, 'imagenes' => 0]
];

// Procesar cada esquema
foreach ($schemas as $schema) {
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║   " . str_pad($schemaLabels[$schema], 56) . "║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";
    
    // Cambiar al esquema
    DB::statement("SET search_path TO {$schema}");
    echo "📍 Esquema activo: {$schema}\n\n";
    
    // Verificar estado actual
    $currentMarcas = DB::table('marcas')->count();
    $currentCategorias = DB::table('categorias')->count();
    $currentPerifericos = DB::table('perifericos')->count();
    
    echo "📊 Estado actual:\n";
    echo "   Marcas: {$currentMarcas}\n";
    echo "   Categorías: {$currentCategorias}\n";
    echo "   Periféricos: {$currentPerifericos}\n\n";
    
    // Paso 1: Insertar Marcas
    echo "🏷️  Insertando Marcas...\n";
    $marcas = [];
    foreach ($marcasData as $marcaData) {
        $marca = DB::table('marcas')
            ->where('nombre', $marcaData['nombre'])
            ->first();
        
        if (!$marca) {
            $marcaId = DB::table('marcas')->insertGetId([
                'nombre' => $marcaData['nombre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $marcas[$marcaData['nombre']] = $marcaId;
            echo "   ✓ {$marcaData['nombre']} (ID: {$marcaId})\n";
            $totalStats[$schema]['marcas']++;
        } else {
            $marcas[$marcaData['nombre']] = $marca->id;
            echo "   ⏭️  {$marcaData['nombre']} (ya existe)\n";
        }
    }
    
    echo "\n";
    
    // Paso 2: Insertar Categorías
    echo "📁 Insertando Categorías...\n";
    $categorias = [];
    
    // Primero, obtener todas las categorías existentes
    $existingCats = DB::table('categorias')->get();
    foreach ($existingCats as $cat) {
        $categorias[$cat->nombre] = $cat->id;
    }
    
    foreach ($categoriasData as $catData) {
        if (isset($categorias[$catData['nombre']])) {
            echo "   ⏭️  {$catData['nombre']} (ya existe, ID: {$categorias[$catData['nombre']]})\n";
            continue;
        }
        
        try {
            $catId = DB::table('categorias')->insertGetId([
                'nombre' => $catData['nombre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $categorias[$catData['nombre']] = $catId;
            echo "   ✓ {$catData['nombre']} (ID: {$catId})\n";
            $totalStats[$schema]['categorias']++;
        } catch (\Exception $e) {
            // Si falla, buscar de nuevo
            $categoria = DB::table('categorias')
                ->where('nombre', $catData['nombre'])
                ->first();
            
            if ($categoria) {
                $categorias[$catData['nombre']] = $categoria->id;
                echo "   ⏭️  {$catData['nombre']} (encontrado tras error, ID: {$categoria->id})\n";
            } else {
                echo "   ❌ Error con {$catData['nombre']}: " . substr($e->getMessage(), 0, 60) . "\n";
            }
        }
    }
    
    echo "\n";
    
    // Paso 3: Insertar Periféricos
    echo "🖱️  Insertando Periféricos...\n";
    $insertedIds = [];
    
    foreach ($perifericosData as $periData) {
        $existing = DB::table('perifericos')
            ->where('nombre', $periData['nombre'])
            ->where('modelo', $periData['modelo'])
            ->first();
        
        if (!$existing) {
            $periId = DB::table('perifericos')->insertGetId([
                'nombre' => $periData['nombre'],
                'modelo' => $periData['modelo'],
                'marca_id' => $marcas[$periData['marca']],
                'categoria_id' => $categorias[$periData['categoria']],
                'precio' => $periData['precio'],
                'tipo_conectividad' => $periData['tipo_conectividad'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $insertedIds[] = $periId;
            echo "   ✓ {$periData['nombre']}\n";
            $totalStats[$schema]['perifericos']++;
        } else {
            $insertedIds[] = $existing->id;
            echo "   ⏭️  {$periData['nombre']} (ya existe)\n";
        }
    }
    
    echo "\n";
    echo "📦 Resumen para {$schema}:\n";
    echo "   Marcas nuevas: {$totalStats[$schema]['marcas']}\n";
    echo "   Categorías nuevas: {$totalStats[$schema]['categorias']}\n";
    echo "   Periféricos nuevos: {$totalStats[$schema]['perifericos']}\n";
    
    // Estado final
    $finalMarcas = DB::table('marcas')->count();
    $finalCategorias = DB::table('categorias')->count();
    $finalPerifericos = DB::table('perifericos')->count();
    
    echo "\n📊 Estado final:\n";
    echo "   Total Marcas: {$finalMarcas}\n";
    echo "   Total Categorías: {$finalCategorias}\n";
    echo "   Total Periféricos: {$finalPerifericos}\n";
    
    echo "\n" . str_repeat("═", 63) . "\n\n";
}

// Sincronizar imágenes solo en sandbox y staging
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   Sincronización de Imágenes                              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$imageService = app(AmazonImageService::class);

foreach (['sandbox', 'staging'] as $schema) {
    echo "🖼️  Sincronizando imágenes en {$schema}...\n";
    
    // Cambiar al esquema
    DB::statement("SET search_path TO {$schema}");
    
    // Obtener periféricos sin imagen
    $perifericosSinImagen = DB::table('perifericos')
        ->whereNull('imagen_url')
        ->limit(10)
        ->get();
    
    if ($perifericosSinImagen->count() == 0) {
        echo "   ✓ Todos los periféricos ya tienen imagen\n\n";
        continue;
    }
    
    $synced = 0;
    foreach ($perifericosSinImagen as $peri) {
        // Usar Eloquent para el modelo
        $periferico = Periferico::find($peri->id);
        if ($periferico) {
            $result = $imageService->syncPerifericoImage($periferico);
            if ($result['success']) {
                $synced++;
                $totalStats[$schema]['imagenes']++;
            }
            usleep(300000); // 0.3 segundos
        }
    }
    
    echo "   ✓ Sincronizadas: {$synced} imágenes\n\n";
}

// Resumen global
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN GLOBAL                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "┌─────────────┬─────────┬────────────┬─────────────┬──────────┐\n";
echo "│ Esquema     │ Marcas  │ Categorías │ Periféricos │ Imágenes │\n";
echo "├─────────────┼─────────┼────────────┼─────────────┼──────────┤\n";

foreach ($schemas as $schema) {
    $label = str_pad($schema, 11);
    $m = str_pad($totalStats[$schema]['marcas'], 7);
    $c = str_pad($totalStats[$schema]['categorias'], 10);
    $p = str_pad($totalStats[$schema]['perifericos'], 11);
    $i = str_pad($totalStats[$schema]['imagenes'], 8);
    
    echo "│ {$label} │ {$m} │ {$c} │ {$p} │ {$i} │\n";
}

echo "└─────────────┴─────────┴────────────┴─────────────┴──────────┘\n\n";

// Verificación final por esquema
echo "🔍 Verificación Final:\n\n";

foreach ($schemas as $schema) {
    DB::statement("SET search_path TO {$schema}");
    
    $totalMarcas = DB::table('marcas')->count();
    $totalCategorias = DB::table('categorias')->count();
    $totalPerifericos = DB::table('perifericos')->count();
    $totalConImagen = DB::table('perifericos')->whereNotNull('imagen_url')->count();
    
    echo "📍 {$schemaLabels[$schema]}:\n";
    echo "   Marcas:         {$totalMarcas}\n";
    echo "   Categorías:     {$totalCategorias}\n";
    echo "   Periféricos:    {$totalPerifericos}\n";
    echo "   Con imagen:     {$totalConImagen}\n";
    
    if ($totalPerifericos > 0) {
        $porcentaje = round(($totalConImagen / $totalPerifericos) * 100, 2);
        echo "   Cobertura:      {$porcentaje}%\n";
    }
    
    echo "\n";
}

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              POBLACIÓN COMPLETADA EXITOSAMENTE            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "📝 Próximos pasos:\n\n";
echo "1️⃣  Cambiar entre ambientes:\n";
echo "   scripts\\switch-environment.bat sandbox\n";
echo "   scripts\\switch-environment.bat staging\n";
echo "   scripts\\switch-environment.bat production\n\n";

echo "2️⃣  Iniciar servidor:\n";
echo "   php artisan serve\n\n";

echo "3️⃣  Ver comparadora:\n";
echo "   http://localhost:8000/comparadora\n\n";

echo "4️⃣  Sincronizar más imágenes:\n";
echo "   php artisan amazon:sync-images --limit=20\n\n";

echo "✅ Todos los ambientes poblados correctamente!\n";
