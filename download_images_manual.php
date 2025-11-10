<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Periferico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "=== Descarga Manual de Imágenes Locales ===\n\n";

// URLs de imágenes reales para productos específicos
$productImages = [
    // Logitech
    'Logitech G502 HERO' => 'https://m.media-amazon.com/images/I/61mpMH5TzkL._AC_SX679_.jpg',
    'Logitech MX Master 3' => 'https://m.media-amazon.com/images/I/61ni3t1ryQL._AC_SX679_.jpg',
    'Logitech G915 TKL' => 'https://m.media-amazon.com/images/I/71cF26SSStL._AC_SX679_.jpg',
    'Logitech G Pro X' => 'https://m.media-amazon.com/images/I/61j9BmNa7WL._AC_SX679_.jpg',
    'Logitech C920' => 'https://m.media-amazon.com/images/I/61XXQc3K7HL._AC_SX679_.jpg',
    'Logitech Brio 4K' => 'https://m.media-amazon.com/images/I/71k8geW-ltL._AC_SX679_.jpg',
    
    // Razer
    'Razer DeathAdder V2' => 'https://m.media-amazon.com/images/I/61gcJL3TOZL._AC_SX679_.jpg',
    'Razer Viper Ultimate' => 'https://m.media-amazon.com/images/I/71exqp0kXNL._AC_SX679_.jpg',
    'Razer BlackWidow V3' => 'https://m.media-amazon.com/images/I/71kwl9p9BrL._AC_SX679_.jpg',
    'Razer BlackShark V2' => 'https://m.media-amazon.com/images/I/61oU76YCZvL._AC_SX679_.jpg',
    'Razer Kiyo' => 'https://m.media-amazon.com/images/I/61HcNLKYrYL._AC_SX679_.jpg',
    'Razer Seiren Mini' => 'https://m.media-amazon.com/images/I/51MEp8vgP-L._AC_SX679_.jpg',
    
    // Corsair
    'Corsair K70 RGB' => 'https://m.media-amazon.com/images/I/81LhJGuoXOL._AC_SX679_.jpg',
    'Corsair HS70 Pro' => 'https://m.media-amazon.com/images/I/61yVBleN8GL._AC_SX679_.jpg',
    
    // SteelSeries
    'SteelSeries Rival 600' => 'https://m.media-amazon.com/images/I/61HXm8D8EcL._AC_SX679_.jpg',
    'SteelSeries Apex Pro' => 'https://m.media-amazon.com/images/I/81yFR8MfyML._AC_SX679_.jpg',
    'SteelSeries Arctis 7' => 'https://m.media-amazon.com/images/I/61mOKr6lYbL._AC_SX679_.jpg',
    
    // HyperX
    'HyperX Alloy FPS Pro' => 'https://m.media-amazon.com/images/I/71xg0N4qKlL._AC_SX679_.jpg',
    'HyperX Cloud II' => 'https://m.media-amazon.com/images/I/61CGYwdVF0L._AC_SX679_.jpg',
    'HyperX QuadCast' => 'https://m.media-amazon.com/images/I/61c2bCEt-LL._AC_SX679_.jpg',
    
    // ASUS
    'ASUS TUF Gaming VG27AQ' => 'https://m.media-amazon.com/images/I/81tJJhpB7WL._AC_SX679_.jpg',
    
    // MSI
    'MSI Optix MAG274QRF' => 'https://m.media-amazon.com/images/I/81D8pNFmWzL._AC_SX679_.jpg',
];

$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'skipped' => 0
];

foreach ($productImages as $productName => $imageUrl) {
    $stats['total']++;
    
    // Buscar producto en la base de datos
    $producto = Periferico::where('nombre', 'like', "%$productName%")->first();
    
    if (!$producto) {
        echo "⚠️  Producto no encontrado: $productName\n";
        $stats['skipped']++;
        continue;
    }
    
    // Si ya tiene imagen local, saltar
    if ($producto->imagen_path && !empty($producto->imagen_path)) {
        echo "⏭️  Ya tiene imagen local: {$producto->nombre}\n";
        $stats['skipped']++;
        continue;
    }
    
    echo "📥 Descargando: {$producto->nombre}...\n";
    
    try {
        // Descargar imagen
        $response = Http::timeout(30)->get($imageUrl);
        
        if (!$response->successful()) {
            echo "   ❌ Error al descargar (HTTP {$response->status()})\n";
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
            'imagen_source' => 'local'
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
echo "⏭️  Omitidas: {$stats['skipped']}\n";
echo "❌ Fallidas: {$stats['failed']}\n";
