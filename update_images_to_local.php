<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "=== Actualizando productos con imágenes reales locales ===\n\n";

// Obtener lista de archivos en la carpeta
$diskPath = 'images/perifericos';
$files = Storage::disk('public')->files($diskPath);

echo "📁 Imágenes encontradas en storage: " . count($files) . "\n\n";

$productos = Periferico::all();
$stats = [
    'total' => $productos->count(),
    'updated' => 0,
    'skipped' => 0,
    'not_found' => 0
];

foreach ($productos as $producto) {
    // Generar el nombre de archivo esperado
    $expectedFilename = Str::slug($producto->nombre) . '-' . $producto->id;
    
    // Buscar el archivo (puede ser .jpg, .png, etc.)
    $foundFile = null;
    foreach ($files as $file) {
        $basename = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
        if ($basename === $expectedFilename) {
            $foundFile = $file;
            break;
        }
    }
    
    if ($foundFile) {
        // Obtener extensión
        $extension = pathinfo($foundFile, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
        
        // Actualizar producto
        $producto->update([
            'imagen_path' => $foundFile,
            'imagen_mime_type' => $mimeType,
            'imagen_source' => 'local'
        ]);
        
        echo "✅ {$producto->nombre} -> {$foundFile}\n";
        $stats['updated']++;
    } else {
        echo "⚠️  No se encontró imagen para: {$producto->nombre} (esperaba: {$expectedFilename}.*)\n";
        $stats['not_found']++;
    }
}

echo "\n=== Resumen ===\n";
echo "Total productos: {$stats['total']}\n";
echo "✅ Actualizados: {$stats['updated']}\n";
echo "⚠️  No encontrados: {$stats['not_found']}\n";

if ($stats['updated'] > 0) {
    echo "\n🎉 ¡Listo! Refresca el navegador (F5) para ver las imágenes reales.\n";
}
