<!DOCTYPE html>
<html>
<head>
    <title>Test Simple de Imágenes</title>
    <style>
        body { background: #1a1a1a; color: white; padding: 20px; }
        .test-card { 
            background: #2a2a2a; 
            border: 2px solid #444; 
            padding: 20px; 
            margin: 20px 0;
            border-radius: 8px;
        }
        img { 
            max-width: 300px; 
            border: 2px solid red; 
            background: white;
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test de Carga de Imágenes</h1>
    
    <?php
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    use App\Models\Periferico;
    
    $productos = Periferico::with(['marca', 'categoria'])->limit(3)->get();
    
    foreach ($productos as $producto) {
        echo '<div class="test-card">';
        echo '<h2>' . $producto->nombre . '</h2>';
        echo '<p><strong>imagen_path:</strong> ' . ($producto->imagen_path ?? 'NULL') . '</p>';
        echo '<p><strong>imagen_source:</strong> ' . ($producto->imagen_source ?? 'NULL') . '</p>';
        echo '<p><strong>imagen_url_completa:</strong> ' . $producto->imagen_url_completa . '</p>';
        
        echo '<h3>Imagen:</h3>';
        echo '<img src="' . $producto->imagen_url_completa . '" alt="' . $producto->nombre . '">';
        
        echo '</div>';
    }
    ?>
    
    <script>
        document.querySelectorAll('img').forEach((img, i) => {
            console.log(`Imagen ${i + 1}: ${img.src}`);
            img.onload = () => {
                console.log(`✅ Imagen ${i + 1} cargada exitosamente`);
                img.style.borderColor = 'green';
            };
            img.onerror = (e) => {
                console.error(`❌ Error cargando imagen ${i + 1}:`, img.src, e);
                img.style.borderColor = 'red';
                img.parentElement.innerHTML += '<p style="color: red;">❌ ERROR: La imagen no se pudo cargar</p>';
            };
        });
    </script>
</body>
</html>
