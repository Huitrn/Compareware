<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "=== Creación de Imágenes Placeholder Profesionales ===\n\n";

$productos = Periferico::with('marca', 'categoria')
    ->whereNull('imagen_path')
    ->get();

if ($productos->isEmpty()) {
    echo "✅ Todos los productos ya tienen imágenes locales\n";
    exit(0);
}

echo "📦 Productos sin imagen local: {$productos->count()}\n\n";

$stats = [
    'total' => $productos->count(),
    'success' => 0,
    'failed' => 0
];

// Colores por categoría
$categoryColors = [
    'Mouse' => ['bg' => '3B82F6', 'fg' => 'FFFFFF'],          // Azul
    'Teclado' => ['bg' => '10B981', 'fg' => 'FFFFFF'],        // Verde
    'Audífonos' => ['bg' => 'F59E0B', 'fg' => 'FFFFFF'],      // Naranja
    'Monitor' => ['bg' => '8B5CF6', 'fg' => 'FFFFFF'],        // Púrpura
    'Webcam' => ['bg' => 'EC4899', 'fg' => 'FFFFFF'],         // Rosa
    'Micrófono' => ['bg' => 'EF4444', 'fg' => 'FFFFFF'],      // Rojo
    'default' => ['bg' => '6B7280', 'fg' => 'FFFFFF']         // Gris
];

foreach ($productos as $producto) {
    echo "🎨 Creando placeholder para: {$producto->nombre}...\n";
    
    try {
        // Obtener color según categoría
        $categoria = $producto->categoria ? $producto->categoria->nombre : 'default';
        $colors = $categoryColors[$categoria] ?? $categoryColors['default'];
        
        // Preparar texto para la imagen
        $marca = $producto->marca ? $producto->marca->nombre : '';
        $nombre = $producto->nombre;
        
        // Acortar nombre si es muy largo
        if (strlen($nombre) > 30) {
            $nombre = substr($nombre, 0, 27) . '...';
        }
        
        // URL de placeholder profesional (via.placeholder.com o placehold.co)
        $placeholderUrl = "https://placehold.co/800x600/{$colors['bg']}/{$colors['fg']}/png?text=" . 
                          urlencode($marca . "\n" . $nombre);
        
        echo "   🔗 URL: $placeholderUrl\n";
        
        // Descargar imagen
        $response = Http::timeout(30)->get($placeholderUrl);
        
        if (!$response->successful()) {
            echo "   ❌ Error al descargar\n";
            $stats['failed']++;
            continue;
        }
        
        // Generar nombre de archivo
        $filename = Str::slug($producto->nombre) . '-' . $producto->id . '.png';
        $path = 'images/perifericos/' . $filename;
        
        // Guardar archivo
        Storage::disk('public')->put($path, $response->body());
        
        // Actualizar producto
        $producto->update([
            'imagen_path' => $path,
            'imagen_mime_type' => 'image/png',
            'imagen_source' => 'local',
            'imagen_alt' => $producto->nombre
        ]);
        
        echo "   ✅ Guardada: storage/$path\n";
        $stats['success']++;
        
    } catch (\Exception $e) {
        echo "   ❌ Error: {$e->getMessage()}\n";
        $stats['failed']++;
    }
}

echo "\n=== Resumen ===\n";
echo "Total: {$stats['total']}\n";
echo "✅ Exitosas: {$stats['success']}\n";
echo "❌ Fallidas: {$stats['failed']}\n";
