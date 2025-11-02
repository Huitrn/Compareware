<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Selects</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .debug {
            background: #f4f4f4;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .console-output {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            max-height: 400px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <h1>🔍 Test de Selects - Comparadora</h1>
    
    <div class="debug">
        <h3>📊 Datos desde Laravel:</h3>
        <p><strong>Categorías:</strong> {{ $categorias->count() }}</p>
        <p><strong>Productos:</strong> {{ $productos->count() }}</p>
    </div>
    
    <div class="debug">
        <h3>🏷️ Categorías disponibles:</h3>
        @foreach($categorias as $cat)
            <button onclick="filtrarPorCategoria({{ $cat->id }})" style="margin: 5px; padding: 10px;">
                {{ $cat->nombre }} (ID: {{ $cat->id }})
            </button>
        @endforeach
    </div>
    
    <div>
        <h3>🛒 Seleccionar Periféricos:</h3>
        <label>Periférico 1:</label>
        <select id="periferico1">
            <option value="">-- Selecciona un periférico --</option>
        </select>
        
        <label>Periférico 2:</label>
        <select id="periferico2">
            <option value="">-- Selecciona un periférico --</option>
        </select>
    </div>
    
    <div class="debug">
        <h3>🖥️ Consola de Debug:</h3>
        <div id="console-output" class="console-output">
            <div>🚀 Cargando...</div>
        </div>
    </div>

    <script>
        // Variables globales
        let categorias = @json($categorias);
        let productos = @json($productos);
        let categoriaSeleccionada = null;
        
        // Función para mostrar mensajes en la consola visual
        function log(mensaje) {
            const console_div = document.getElementById('console-output');
            const time = new Date().toLocaleTimeString();
            console_div.innerHTML += `<div>[${time}] ${mensaje}</div>`;
            console_div.scrollTop = console_div.scrollHeight;
            
            // También mostrar en consola real
            console.log(mensaje);
        }
        
        // Función para actualizar los selects
        function actualizarSelects() {
            log('🔄 Ejecutando actualizarSelects...');
            
            const select1 = document.getElementById('periferico1');
            const select2 = document.getElementById('periferico2');
            
            if (!select1 || !select2) {
                log('❌ ERROR: No se encontraron los elementos select!');
                return;
            }
            
            log(`✅ Selects encontrados correctamente`);
            log(`📊 Categoría seleccionada: ${categoriaSeleccionada}`);
            log(`📦 Total productos: ${productos ? productos.length : 0}`);
            
            // Limpiar selects
            select1.innerHTML = '<option value="">-- Selecciona un periférico --</option>';
            select2.innerHTML = '<option value="">-- Selecciona un periférico --</option>';
            
            if (!productos || productos.length === 0) {
                log('⚠️ No hay productos disponibles');
                return;
            }
            
            // Filtrar productos si hay categoría seleccionada
            let filtrados = productos;
            if (categoriaSeleccionada) {
                filtrados = productos.filter(p => {
                    const match = parseInt(p.categoria_id) === parseInt(categoriaSeleccionada);
                    log(`🔍 Producto "${p.nombre}": categoria_id=${p.categoria_id}, buscando=${categoriaSeleccionada}, coincide=${match}`);
                    return match;
                });
            }
            
            log(`✅ Productos filtrados: ${filtrados.length}`);
            
            if (filtrados.length === 0) {
                log('⚠️ No hay productos en esta categoría');
                select1.innerHTML += '<option value="" disabled>-- Sin productos en esta categoría --</option>';
                select2.innerHTML += '<option value="" disabled>-- Sin productos en esta categoría --</option>';
            } else {
                log('➕ Agregando productos a los selects...');
                filtrados.forEach((p, index) => {
                    log(`  ${index + 1}. Agregando: ${p.nombre} (ID: ${p.id})`);
                    
                    const optionText = `${p.nombre} - $${p.precio}`;
                    select1.innerHTML += `<option value="${p.id}">${optionText}</option>`;
                    select2.innerHTML += `<option value="${p.id}">${optionText}</option>`;
                });
                log('✅ Selects actualizados exitosamente');
                log(`🔢 Total opciones en select1: ${select1.options.length}`);
                log(`🔢 Total opciones en select2: ${select2.options.length}`);
            }
        }
        
        // Función para filtrar por categoría
        function filtrarPorCategoria(catId) {
            log(`🏷️ Filtrando por categoría: ${catId}`);
            categoriaSeleccionada = catId;
            actualizarSelects();
        }
        
        // Inicialización cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            log('🚀 DOM Cargado - Inicializando test...');
            log('📊 Datos recibidos:');
            log(`  - Categorías: ${categorias ? categorias.length : 0}`);
            log(`  - Productos: ${productos ? productos.length : 0}`);
            
            // Mostrar todos los datos
            if (categorias && categorias.length > 0) {
                log('🏷️ Lista de categorías:');
                categorias.forEach(cat => {
                    log(`  - ${cat.nombre} (ID: ${cat.id})`);
                });
            }
            
            if (productos && productos.length > 0) {
                log('📦 Lista de productos:');
                productos.forEach(prod => {
                    log(`  - ${prod.nombre} (ID: ${prod.id}, Categoría: ${prod.categoria_id})`);
                });
            }
            
            // Cargar todos los productos inicialmente
            log('🔄 Cargando todos los productos...');
            actualizarSelects();
        });
    </script>
</body>
</html>