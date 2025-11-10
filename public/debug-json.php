<!DOCTYPE html>
<html>
<head>
    <title>Debug JSON</title>
    <style>
        body { background: #1a1a1a; color: white; padding: 20px; font-family: monospace; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .producto { margin-bottom: 30px; border: 2px solid #444; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>🔍 Debug de JSON de Productos</h1>
    
    <?php
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    use App\Models\Periferico;
    
    $productos = Periferico::with(['marca', 'categoria'])->limit(3)->get();
    
    foreach ($productos as $producto) {
        echo '<div class="producto">';
        echo '<h2>' . $producto->nombre . '</h2>';
        echo '<h3>Atributos directos:</h3>';
        echo '<pre>';
        echo 'imagen_path: ' . ($producto->imagen_path ?? 'NULL') . PHP_EOL;
        echo 'imagen_url: ' . ($producto->imagen_url ?? 'NULL') . PHP_EOL;
        echo 'imagen_source: ' . ($producto->imagen_source ?? 'NULL') . PHP_EOL;
        echo '</pre>';
        
        echo '<h3>Accessor imagen_url_completa:</h3>';
        echo '<pre>' . $producto->imagen_url_completa . '</pre>';
        
        echo '<h3>JSON completo del producto:</h3>';
        echo '<pre>' . json_encode($producto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        echo '</div>';
    }
    ?>
    
    <h2>📦 Todos los productos como array JSON (como se pasa a la vista):</h2>
    <pre><?php echo json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
</body>
</html>
