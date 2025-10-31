<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Especificaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🔧 Debug Especificaciones Ultra-Detalladas</h1>
        
        <div class="mb-6">
            <button id="testButton" class="bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-lg font-semibold">
                🧪 Probar Especificaciones Sony WH-1000XM5
            </button>
        </div>
        
        <div id="results" class="space-y-4"></div>
        
        <div id="rawData" class="mt-8 p-4 bg-gray-800 rounded-lg">
            <h3 class="text-xl font-bold mb-4">📄 Datos Raw (JSON)</h3>
            <pre id="jsonOutput" class="text-xs overflow-x-auto"></pre>
        </div>
    </div>

    <script>
        document.getElementById('testButton').addEventListener('click', async function() {
            console.log('🚀 Iniciando prueba de especificaciones...');
            
            const resultsDiv = document.getElementById('results');
            const jsonOutput = document.getElementById('jsonOutput');
            
            try {
                resultsDiv.innerHTML = '<div class="text-yellow-400">⏳ Cargando especificaciones...</div>';
                
                const response = await fetch('/test-specs-api/Sony WH-1000XM5 premium wireless headphones ANC');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('📦 Datos recibidos:', data);
                
                // Mostrar JSON raw
                jsonOutput.textContent = JSON.stringify(data, null, 2);
                
                // Procesar y mostrar especificaciones
                if (data.result && data.result.specifications) {
                    const specs = data.result.specifications;
                    const aboutProduct = data.result.about_this_product;
                    
                    let html = '<h2 class="text-2xl font-bold text-green-400 mb-4">✅ Especificaciones Cargadas Exitosamente</h2>';
                    
                    // Mostrar categorías de especificaciones
                    html += '<div class="mb-6">';
                    html += '<h3 class="text-lg font-semibold text-blue-400 mb-3">📂 Categorías de Especificaciones:</h3>';
                    Object.keys(specs).forEach(category => {
                        const specCount = Object.keys(specs[category]).length;
                        html += `<div class="mb-2 p-2 bg-gray-800 rounded">
                            <span class="font-medium text-green-300">${category}</span>
                            <span class="text-gray-400 text-sm ml-2">(${specCount} especificaciones)</span>
                        </div>`;
                    });
                    html += '</div>';
                    
                    // Mostrar primera categoría como ejemplo
                    const firstCategory = Object.keys(specs)[0];
                    if (firstCategory) {
                        html += `<div class="mb-6">`;
                        html += `<h3 class="text-lg font-semibold text-yellow-400 mb-3">🔍 Ejemplo - ${firstCategory}:</h3>`;
                        Object.entries(specs[firstCategory]).forEach(([spec, value]) => {
                            html += `<div class="mb-1 text-sm">
                                <span class="text-gray-400">${spec}:</span>
                                <span class="text-white ml-2">${value}</span>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    // Mostrar "Sobre este artículo" si existe
                    if (aboutProduct && aboutProduct.length > 0) {
                        html += '<div class="mb-6">';
                        html += '<h3 class="text-lg font-semibold text-purple-400 mb-3">📖 Sobre este artículo:</h3>';
                        aboutProduct.forEach(feature => {
                            html += `<div class="mb-3 p-3 bg-gray-800 rounded">
                                <div class="font-medium text-purple-300 mb-1">${feature.title}</div>
                                <div class="text-gray-300 text-sm">${feature.description}</div>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<div class="text-red-400">❌ No se encontraron especificaciones en la respuesta</div>';
                }
                
            } catch (error) {
                console.error('❌ Error al cargar especificaciones:', error);
                resultsDiv.innerHTML = `<div class="text-red-400">❌ Error: ${error.message}</div>`;
            }
        });
    </script>
</body>
</html>