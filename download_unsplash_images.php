<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "=== Descarga de Imágenes desde Unsplash ===\n\n";

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

foreach ($productos as $producto) {
    echo "📥 Procesando: {$producto->nombre}...\n";
    
    try {
        // Generar búsqueda basada en categoría
        $query = $producto->categoria ? $producto->categoria->nombre : 'technology';
        
        // Mapear categorías a términos de búsqueda
        $searchTerms = [
            'Mouse' => 'computer mouse gaming',
            'Teclado' => 'mechanical keyboard gaming',
            'Audífonos' => 'headphones gaming',
            'Monitor' => 'gaming monitor screen',
            'Webcam' => 'webcam camera',
            'Micrófono' => 'microphone studio'
        ];
        
        $searchQuery = $searchTerms[$query] ?? 'gaming peripheral';
        
        // Unsplash API (sin necesidad de API key para URLs básicas)
        $unsplashUrl = "https://source.unsplash.com/800x600/?{$searchQuery}";
        
        echo "   🔍 Buscando imagen de: $searchQuery\n";
        
        // Descargar imagen
        $response = Http::timeout(30)->get($unsplashUrl);
        
        if (!$response->successful()) {
            echo "   ❌ Error al descargar\n";
            $stats['failed']++;
            continue;
        }
        
        // Generar nombre de archivo
        $filename = Str::slug($producto->nombre) . '-' . $producto->id . '.jpg';
        $path = 'images/perifericos/' . $filename;
        
        // Guardar archivo
        Storage::disk('public')->put($path, $response->body());
        
        // Actualizar producto
        $producto->update([
            'imagen_path' => $path,
            'imagen_mime_type' => 'image/jpeg',
            'imagen_source' => 'local',
            'imagen_alt' => $producto->nombre . ' - Imagen representativa'
        ]);
        
        echo "   ✅ Guardada: storage/$path\n";
        $stats['success']++;
        
        // Pequeña pausa para no saturar
        sleep(1);
        
    } catch (\Exception $e) {
        echo "   ❌ Error: {$e->getMessage()}\n";
        $stats['failed']++;
    }
}

echo "\n=== Resumen ===\n";
echo "Total: {$stats['total']}\n";
echo "✅ Exitosas: {$stats['success']}\n";
echo "❌ Fallidas: {$stats['failed']}\n";
