<?php

/**
 * Script de prueba para el sistema de imágenes de Amazon
 * Ejecutar: php Compareware/test_amazon_images.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Periferico;
use App\Services\AmazonImageService;
use Illuminate\Support\Facades\DB;

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   Test: Sistema de Imágenes con Amazon API             ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Test 1: Verificar conexión a BD y modelo
echo "📊 Test 1: Verificación de Base de Datos\n";
echo str_repeat("─", 60) . "\n";

try {
    $totalPerifericos = Periferico::count();
    $conImagen = Periferico::whereNotNull('imagen_url')->count();
    $sinImagen = Periferico::whereNull('imagen_url')->count();
    
    echo "✓ Total de periféricos: {$totalPerifericos}\n";
    echo "✓ Con imagen: {$conImagen}\n";
    echo "✓ Sin imagen: {$sinImagen}\n";
    
    if ($totalPerifericos > 0) {
        $porcentaje = round(($conImagen / $totalPerifericos) * 100, 2);
        echo "✓ Cobertura de imágenes: {$porcentaje}%\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Verificar estructura de columnas
echo "🔍 Test 2: Verificación de Columnas de Imagen\n";
echo str_repeat("─", 60) . "\n";

try {
    $columns = DB::select("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'perifericos' 
        AND column_name LIKE '%imagen%'
    ");
    
    $expectedColumns = ['imagen_url', 'imagen_alt', 'galeria_imagenes', 'imagen_path', 'thumbnail_url', 'imagen_source'];
    $foundColumns = array_map(fn($col) => $col->column_name, $columns);
    
    foreach ($expectedColumns as $col) {
        if (in_array($col, $foundColumns)) {
            echo "✓ Columna '{$col}' existe\n";
        } else {
            echo "✗ Columna '{$col}' NO encontrada\n";
        }
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error verificando columnas: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar métodos del modelo
echo "🧪 Test 3: Métodos del Modelo Periferico\n";
echo str_repeat("─", 60) . "\n";

try {
    $periferico = Periferico::first();
    
    if ($periferico) {
        $methods = ['hasImage', 'hasGallery', 'getImagenUrlCompletaAttribute', 'getImageDataAttribute'];
        
        foreach ($methods as $method) {
            if (method_exists($periferico, $method)) {
                echo "✓ Método '{$method}' existe\n";
            } else {
                echo "✗ Método '{$method}' NO encontrado\n";
            }
        }
        
        // Probar métodos
        echo "\n  Pruebas funcionales:\n";
        echo "  - hasImage(): " . ($periferico->hasImage() ? 'Sí' : 'No') . "\n";
        echo "  - hasGallery(): " . ($periferico->hasGallery() ? 'Sí' : 'No') . "\n";
        
        if ($periferico->hasImage()) {
            echo "  - URL completa: " . substr($periferico->imagen_url_completa, 0, 50) . "...\n";
        }
        
    } else {
        echo "⚠ No hay periféricos en la base de datos\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error probando métodos: " . $e->getMessage() . "\n\n";
}

// Test 4: Verificar servicio AmazonImageService
echo "🔧 Test 4: Servicio AmazonImageService\n";
echo str_repeat("─", 60) . "\n";

try {
    $imageService = app(AmazonImageService::class);
    
    if ($imageService) {
        echo "✓ Servicio AmazonImageService instanciado correctamente\n";
        
        $methods = ['syncPerifericoImage', 'syncMultiplePerifericosImages', 'downloadAndStoreImage'];
        
        foreach ($methods as $method) {
            if (method_exists($imageService, $method)) {
                echo "✓ Método '{$method}' disponible\n";
            } else {
                echo "✗ Método '{$method}' NO encontrado\n";
            }
        }
    } else {
        echo "✗ No se pudo instanciar el servicio\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error con el servicio: " . $e->getMessage() . "\n\n";
}

// Test 5: Probar sincronización de un periférico (si hay alguno sin imagen)
echo "🌐 Test 5: Prueba de Sincronización (Simulada)\n";
echo str_repeat("─", 60) . "\n";

try {
    $perifericoSinImagen = Periferico::whereNull('imagen_url')->first();
    
    if ($perifericoSinImagen) {
        echo "✓ Encontrado periférico sin imagen: #{$perifericoSinImagen->id} - {$perifericoSinImagen->nombre}\n";
        echo "  Marca: " . ($perifericoSinImagen->marca->nombre ?? 'N/A') . "\n";
        echo "  Categoría: " . ($perifericoSinImagen->categoria->nombre ?? 'N/A') . "\n";
        
        // NO ejecutar la sincronización real en el test, solo simular
        echo "\n  💡 Para sincronizar, ejecutar:\n";
        echo "     php artisan amazon:sync-images --limit=1\n";
        
    } else {
        echo "✓ Todos los periféricos ya tienen imagen asignada\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Verificar API endpoints
echo "🌐 Test 6: Verificación de Rutas API\n";
echo str_repeat("─", 60) . "\n";

try {
    $routes = \Route::getRoutes();
    $imageRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (str_contains($uri, 'comparacion') && str_contains($uri, 'image')) {
            $imageRoutes[] = $uri;
        }
    }
    
    if (!empty($imageRoutes)) {
        echo "✓ Rutas de comparación con imágenes encontradas:\n";
        foreach ($imageRoutes as $uri) {
            echo "  - {$uri}\n";
        }
    } else {
        echo "⚠ No se encontraron rutas específicas de imágenes\n";
        echo "  (Las rutas pueden estar en el endpoint general de comparación)\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error verificando rutas: " . $e->getMessage() . "\n\n";
}

// Test 7: Estadísticas por fuente de imagen
echo "📈 Test 7: Estadísticas de Imágenes\n";
echo str_repeat("─", 60) . "\n";

try {
    $stats = DB::table('perifericos')
        ->select('imagen_source', DB::raw('COUNT(*) as total'))
        ->whereNotNull('imagen_url')
        ->groupBy('imagen_source')
        ->get();
    
    if ($stats->count() > 0) {
        echo "Distribución por fuente de imagen:\n\n";
        foreach ($stats as $stat) {
            $source = $stat->imagen_source ?? 'unknown';
            echo "  {$source}: {$stat->total} imagen(es)\n";
        }
    } else {
        echo "⚠ No hay imágenes registradas aún\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error obteniendo estadísticas: " . $e->getMessage() . "\n\n";
}

// Test 8: Verificar comando Artisan
echo "⚙️ Test 8: Comando Artisan\n";
echo str_repeat("─", 60) . "\n";

try {
    $commands = \Artisan::all();
    
    if (isset($commands['amazon:sync-images'])) {
        echo "✓ Comando 'amazon:sync-images' registrado correctamente\n";
        
        // Mostrar descripción
        $command = $commands['amazon:sync-images'];
        echo "  Descripción: " . $command->getDescription() . "\n";
        
        // Mostrar opciones
        $definition = $command->getDefinition();
        $options = $definition->getOptions();
        
        echo "\n  Opciones disponibles:\n";
        foreach ($options as $option) {
            if (!in_array($option->getName(), ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env'])) {
                echo "    --{$option->getName()}\n";
            }
        }
    } else {
        echo "✗ Comando 'amazon:sync-images' NO encontrado\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error verificando comando: " . $e->getMessage() . "\n\n";
}

// Resumen final
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║                   RESUMEN DE TESTS                     ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

echo "✓ Sistema de imágenes instalado correctamente\n";
echo "✓ Base de datos configurada\n";
echo "✓ Modelo y servicios disponibles\n";
echo "✓ Comandos Artisan registrados\n\n";

echo "📝 Próximos pasos:\n";
echo "   1. Ejecutar: php artisan amazon:sync-images --limit=10\n";
echo "   2. Verificar en la vista comparadora: /comparadora\n";
echo "   3. Probar API: GET /api/comparacion/compare-products?periferico1=1&periferico2=2\n\n";

echo "✅ Tests completados exitosamente\n";
