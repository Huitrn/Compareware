<?php
// Test directo de carga de imagen
$imagePath = __DIR__ . '/storage/images/perifericos/logitech-g502-hero-1.jpg';

echo "<h1>Test de Imagen Directa</h1>";

echo "<h2>1. Verificación de archivo:</h2>";
echo "Ruta completa: " . $imagePath . "<br>";
echo "Existe: " . (file_exists($imagePath) ? 'SÍ ✅' : 'NO ❌') . "<br>";
if (file_exists($imagePath)) {
    echo "Tamaño: " . filesize($imagePath) . " bytes<br>";
    echo "Tipo MIME: " . mime_content_type($imagePath) . "<br>";
}

echo "<h2>2. Imagen con ruta relativa:</h2>";
echo '<img src="/storage/images/perifericos/logitech-g502-hero-1.jpg" style="max-width: 300px; border: 2px solid red;">';

echo "<h2>3. Imagen con ruta absoluta:</h2>";
echo '<img src="http://127.0.0.1:8000/storage/images/perifericos/logitech-g502-hero-1.jpg" style="max-width: 300px; border: 2px solid blue;">';

echo "<h2>4. Listar archivos en storage:</h2>";
$storageDir = __DIR__ . '/storage/images/perifericos/';
if (is_dir($storageDir)) {
    $files = scandir($storageDir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}

echo "<script>
const imgs = document.querySelectorAll('img');
imgs.forEach((img, i) => {
    img.onload = () => console.log('✅ Imagen ' + (i+1) + ' cargada');
    img.onerror = () => console.error('❌ Error cargando imagen ' + (i+1) + ':', img.src);
});
</script>";
