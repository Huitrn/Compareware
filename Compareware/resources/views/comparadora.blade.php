<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparadora | CompareWare</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      body {
        transition: background-color 0.3s, color 0.3s;
      }
      
      .bg-dark { background-color: #0d141c; }
      .text-light { color: #ffffff; }
      .card-dark { background-color: #1e293b; border-color: #374151; }
      .select-dark {
        background-color: #1e293b;
        border-color: #374151;
        color: #ffffff;
      }
      .select-dark option {
        background-color: #1e293b;
        color: #ffffff;
      }
      
      .bg-light { background-color: #ffffff; }
      .text-dark { color: #000000; }
      .card-light { background-color: #f8fafc; border-color: #e5e7eb; }
      .select-light {
        background-color: #ffffff;
        border-color: #d1d5db;
        color: #000000;
      }
      
      .active-tab {
        background-color: #3b82f6 !important;
        color: white !important;
      }
      
      .categoria-tab {
        cursor: pointer;
        transition: all 0.2s;
      }
      
      .categoria-tab:hover {
        background-color: #1d4ed8;
        color: white;
      }
      
      /* Estilos específicos para especificaciones detalladas */
      .specs-section {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
      }
      
      .spec-long-text {
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
      }
      
      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
      }
      
      .pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
      
      .lista-productos {
        display: none;
      }
    </style>
</head>

<body class="bg-dark text-light min-h-screen">
    <div class="layout-container flex h-full grow flex-col">
      <!-- Header -->
      <header id="main-header" class="flex items-center justify-between whitespace-nowrap border-b border-solid px-10 py-3 shadow-lg bg-[#0d141c] border-[#324d67]">
        <div class="flex items-center gap-4 text-white">
          <a href="{{ route('home') }}" class="logo-link text-white text-lg font-bold leading-tight tracking-[-0.015em] hover:underline">
            CompareWare
          </a>
        </div>
        <div class="flex flex-1 justify-end gap-8">
          <a class="nav-link text-white text-sm font-medium leading-normal hover:underline" href="{{ route('marcas') }}">Marcas</a>
        </div>
        <div class="flex gap-2 items-center">
          @auth
          <span class="text-white text-sm font-medium">Hola, {{ Auth::user()->name }}</span>
          
          @if(Auth::user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
              <span>⚡</span>
              Admin
            </a>
          @endif
          
          <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              Cerrar Sesión
            </button>
          </form>
        @else
          <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Iniciar Sesión
          </a>
          <a href="{{ route('register') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Registrarse
          </a>
        @endauth
        
        <!-- Botón cambiar tema -->
        <button id="theme-toggle" class="theme-btn flex cursor-pointer items-center justify-center rounded-lg h-10 gap-2 text-sm font-bold px-2.5 bg-[#324d67] text-white hover:bg-[#1172d4]">
          <span id="icon-sun" style="display: none;">☀️</span>
          <span id="icon-moon">🌙</span>
        </button>
        </div>
      </header>

      <div class="px-40 flex flex-1 justify-center py-5">
        <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
          
          <!-- Hero Section -->
          <div id="hero-section" class="rounded-xl p-8 mb-8 flex flex-col md:flex-row items-center justify-between shadow-lg bg-[#1e293b]">
            <div class="text-center md:text-left mb-6 md:mb-0">
              <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em] mb-4">
                🔍 Comparador de Periféricos
              </h1>
              <p class="text-white text-base font-normal leading-normal">
                Compara especificaciones, precios y encuentra el mejor periférico para ti
              </p>
            </div>
            <div class="flex items-center gap-4">
              <div class="bg-blue-500 text-white p-4 rounded-full">
                <span class="text-2xl">⚡</span>
              </div>
            </div>
          </div>

          <!-- Pestañas de Categorías -->
          <div id="categorias-tabs" class="flex gap-2 mb-6 rounded-lg p-2 bg-[#324d67]">
            @foreach($categorias as $categoria)
              <button class="categoria-tab px-6 py-2 rounded-lg font-bold bg-[#1e293b] text-white hover:bg-[#3b82f6]" 
                      data-categoria="{{ $categoria->id }}">
                {{ $categoria->nombre }}
              </button>
            @endforeach
          </div>

          <!-- Listas de productos por categoría -->
          @foreach($categorias as $categoria)
              <div class="lista-productos" data-categoria="{{ $categoria->id }}" style="display: none;">
                <h3 class="text-white text-xl font-bold mb-4">Productos en {{ $categoria->nombre }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                  @foreach($productos as $producto)
                    @if($producto->categoria_id == $categoria->id)
                      <div class="card rounded-lg p-4 font-semibold bg-[#1e293b] border border-[#324d67]">
                        <div class="flex items-center gap-3 mb-2">
                          <div class="bg-blue-500 p-2 rounded-lg">
                            <span class="text-white">{{ $producto->nombre }}</span>
                          </div>
                        </div>
                        <p class="text-white text-sm">Precio: ${{ $producto->precio }}</p>
                        <p class="text-gray-300 text-xs">{{ $producto->tipo_conectividad }}</p>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
            @endforeach

          <!-- Selectores para comparación -->
          <div class="bg-[#1e293b] rounded-xl p-6 mb-8 shadow-lg">
            <h2 class="text-white text-2xl font-bold leading-tight tracking-[-0.015em] pb-4 border-b border-[#324d67] mb-6">
              Selecciona dos periféricos para comparar
            </h2>
            <div class="flex gap-6 mb-6">
              <div class="flex-1">
                <label for="periferico1" class="block mb-3 font-semibold text-white flex items-center gap-2">
                  <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm">1</span>
                  Primer Periférico
                </label>
                <select id="periferico1" class="select-dark w-full rounded-xl px-4 py-3 border border-[#324d67] focus:outline-none focus:ring-2 focus:ring-[#1172d4] focus:border-transparent transition-all">
                  <option value="">-- Selecciona --</option>
                </select>
              </div>
              <div class="flex-1">
                <label for="periferico2" class="block mb-3 font-semibold text-white flex items-center gap-2">
                  <span class="bg-purple-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm">2</span>
                  Segundo Periférico
                </label>
                <select id="periferico2" class="select-dark w-full rounded-xl px-4 py-3 border border-[#324d67] focus:outline-none focus:ring-2 focus:ring-[#1172d4] focus:border-transparent transition-all">
                  <option value="">-- Selecciona --</option>
                </select>
              </div>
            </div>
            <div class="flex justify-center">
              <button id="comparar-btn" class="bg-gradient-to-r from-[#1172d4] to-[#3b82f6] hover:from-[#0f5ebd] hover:to-[#2563eb] text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all">
                🔍 Comparar Periféricos
              </button>
            </div>
          </div>

          <!-- Resultado de la comparación -->
          <div id="resultado-comparacion"></div>

        </div>
      </div>
    </div>

    <script>
      // Variables globales con debugging
      const productos = @json($productos);
      const categorias = @json($categorias);
      let isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
      
      // Variables globales
      let categoriaSeleccionada = null;
      let apiToken = null;
      
      // Tasa de cambio USD a MXN (aproximada - se puede obtener de una API en el futuro)
      const USD_TO_MXN_RATE = 18.50; // Tasa aproximada actualizada
      
      // Función para convertir USD a MXN
      function convertUsdToMxn(usdPriceString) {
        try {
          // Extraer el número del string (ej: "$15.96" -> 15.96)
          const cleanPrice = usdPriceString.replace(/[$,]/g, '');
          const usdPrice = parseFloat(cleanPrice);
          
          if (isNaN(usdPrice)) return null;
          
          const mxnPrice = usdPrice * USD_TO_MXN_RATE;
          return {
            usd: usdPrice,
            mxn: mxnPrice,
            usdFormatted: '$' + usdPrice.toFixed(2) + ' USD',
            mxnFormatted: '$' + mxnPrice.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MXN'
          };
        } catch (e) {
          console.warn('Error convirtiendo precio:', usdPriceString, e);
          return null;
        }
      }
      
      // Debug inmediato
      console.log('🔍 DEBUGGING COMPARADORA:');
      console.log('Productos recibidos:', productos);
      console.log('Categorías recibidas:', categorias);
      console.log('Total productos:', productos ? productos.length : 0);
      console.log('Total categorías:', categorias ? categorias.length : 0);

      // Actualiza los selects según la categoría activa
      function actualizarSelects() {
        console.log('🔄 EJECUTANDO actualizarSelects...');
        console.log('⏰ Timestamp:', new Date().toLocaleTimeString());
        
        const select1 = document.getElementById('periferico1');
        const select2 = document.getElementById('periferico2');
        
        console.log('🔍 Búsqueda de elementos:');
        console.log('- select1 encontrado:', !!select1);
        console.log('- select2 encontrado:', !!select2);
        
        if (!select1 || !select2) {
          console.error('❌ ERROR CRÍTICO: No se encontraron los elementos select!');
          return;
        }
        
        console.log('📊 Categoría seleccionada:', categoriaSeleccionada);
        console.log('📦 Total productos:', productos ? productos.length : 0);
        
        // Limpiar selects
        select1.innerHTML = '<option value="">-- Selecciona un periférico --</option>';
        select2.innerHTML = '<option value="">-- Selecciona un periférico --</option>';
        
        if (!productos || productos.length === 0) {
          console.warn('⚠️ No hay productos disponibles');
          return;
        }
        
        // Filtrar productos por categoría
        let filtrados = productos;
        if (categoriaSeleccionada) {
          filtrados = productos.filter(p => {
            const match = parseInt(p.categoria_id) === parseInt(categoriaSeleccionada);
            console.log('🔍 Producto "' + p.nombre + '": categoria_id=' + p.categoria_id + ', buscando=' + categoriaSeleccionada + ', coincide=' + match);
            return match;
          });
        }
        
        console.log('✅ Productos filtrados:', filtrados.length);
        
        if (filtrados.length === 0) {
          console.warn('⚠️ No hay productos en esta categoría');
          select1.innerHTML += '<option value="" disabled>-- Sin productos en esta categoría --</option>';
          select2.innerHTML += '<option value="" disabled>-- Sin productos en esta categoría --</option>';
        } else {
          console.log('➕ Agregando productos a los selects...');
          filtrados.forEach((p, index) => {
            console.log('  ' + (index + 1) + '. Agregando: ' + p.nombre + ' (ID: ' + p.id + ')');
            
            // Crear opciones con más información
            const optionText = p.nombre + ' - $' + p.precio;
            select1.innerHTML += '<option value="' + p.id + '">' + optionText + '</option>';
            select2.innerHTML += '<option value="' + p.id + '">' + optionText + '</option>';
          });
          console.log('✅ SELECTS ACTUALIZADOS EXITOSAMENTE');
          console.log('🔢 Total opciones en select1:', select1.options.length);
          console.log('🔢 Total opciones en select2:', select2.options.length);
        }
        
        console.log('🏁 FIN DE actualizarSelects');
      }

      // Función para obtener token API
      async function getApiToken() {
        console.log('🔑 Intentando obtener token API...');
        console.log('- Usuario autenticado:', isAuthenticated);
        
        if (!isAuthenticated) {
          console.warn('⚠️ Usuario no autenticado, saltando obtención de token');
          return;
        }
        
        try {
          console.log('📡 Haciendo petición a /api/token...');
          const response = await fetch('/api/token');
          
          console.log('📊 Respuesta del token:', response.status, response.statusText);
          
          if (response.ok) {
            const data = await response.json();
            apiToken = data.token;
            localStorage.setItem('api_token', data.token);
            console.log('✅ Token API obtenido exitosamente');
            console.log('🔑 Token (primeros 20 chars):', apiToken.substring(0, 20) + '...');
          } else {
            console.error('❌ Error al obtener token:', response.status, response.statusText);
            const errorText = await response.text();
            console.error('📄 Respuesta de error:', errorText);
          }
        } catch (e) {
          console.error('💥 Excepción obteniendo token API:', e.message);
        }
      }

      document.addEventListener('DOMContentLoaded', async function () {
        console.log('🚀 DOM Cargado - Inicializando comparadora...');
        
        // Obtener token API si está autenticado
        await getApiToken();
        
        const tabs = document.querySelectorAll('.category-tab, .categoria-tab');
        const listas = document.querySelectorAll('.lista-productos');
        
        console.log('🔍 Elementos encontrados:');
        console.log('- Pestañas:', tabs.length);
        console.log('- Listas:', listas.length);
        
        // Activar la primera categoría por defecto
        if (tabs.length) {
          console.log('✅ Activando primera categoría...');
          tabs[0].classList.add('active-tab');
          if (listas[0]) listas[0].style.display = 'block';
          categoriaSeleccionada = tabs[0].getAttribute('data-categoria');
          console.log('📌 Categoría activada:', categoriaSeleccionada);
          
          // Cargar productos
          actualizarSelects();
        } else {
          console.error('❌ No se encontraron pestañas de categorías!');
        }

        // Manejar clicks en las pestañas
        tabs.forEach(tab => {
          tab.addEventListener('click', function () {
            console.log('🖱️ Click en pestaña:', tab.getAttribute('data-categoria'));
            
            // Quitar estilos activos
            tabs.forEach(t => t.classList.remove('active-tab'));
            listas.forEach(l => l.style.display = 'none');
            
            // Activar el tab y mostrar la lista correspondiente
            tab.classList.add('active-tab');
            const cat = tab.getAttribute('data-categoria');
            categoriaSeleccionada = cat;
            
            const lista = document.querySelector('.lista-productos[data-categoria="' + cat + '"]');
            if (lista) lista.style.display = 'block';
            
            actualizarSelects();
            
            // Limpiar resultados
            document.getElementById('resultado-comparacion').innerHTML = '';
            document.getElementById('periferico1').value = '';
            document.getElementById('periferico2').value = '';
          });
        });

        // Comparar periféricos
        document.getElementById('comparar-btn').addEventListener('click', async function () {
          const id1 = document.getElementById('periferico1').value;
          const id2 = document.getElementById('periferico2').value;
          
          if (!id1 || !id2 || id1 === id2) {
            alert('Por favor selecciona dos periféricos diferentes para comparar.');
            return;
          }

          console.log('🔄 Iniciando comparación con Amazon:', id1, 'vs', id2);
          
          // Mostrar loading mejorado
          document.getElementById('resultado-comparacion').innerHTML = 
            '<div class="bg-[#1e293b] rounded-xl p-8 text-center">' +
              '<div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>' +
              '<h3 class="text-white text-xl font-bold mb-2">🔍 Comparando Productos</h3>' +
              '<p class="text-gray-300">Obteniendo datos locales y en tiempo real de Amazon...</p>' +
              '<div class="mt-4 bg-[#0f172a] rounded-lg p-3">' +
                '<div class="text-sm text-gray-400">📊 Datos locales... <span class="animate-pulse">⏳</span></div>' +
                '<div class="text-sm text-gray-400">🛒 API de Amazon... <span class="animate-pulse">⏳</span></div>' +
                '<div class="text-xs text-gray-500 mt-2">Conexión directa con Amazon</div>' +
              '</div>' +
            '</div>';

          try {
            // 1. Obtener comparación local
            console.log('📊 Obteniendo comparación local...');
            const localResponse = await fetch('/comparar-perifericos?periferico1=' + id1 + '&periferico2=' + id2);
            const localData = await localResponse.json();
            
            if (!localResponse.ok || !localData.success) {
              throw new Error('Error en la comparación local');
            }

            console.log('✅ Comparación local exitosa:', localData);

            // 2. Obtener especificaciones técnicas de los productos
            let specsData = null;
            console.log('🔧 Obteniendo especificaciones técnicas...');
            
            // 3. Obtener datos enriquecidos de Amazon usando endpoint de prueba
            let amazonData = null;
            let priceHistoryData = null;
            console.log('🛒 Obteniendo datos de Amazon...');
            
            try {
              // Obtener los nombres de los productos para buscar
              const product1 = localData.periferico1;
              const product2 = localData.periferico2;
              
              console.log('🔍 Buscando especificaciones y datos de Amazon:');
              console.log('- Producto 1:', product1.nombre);
              console.log('- Producto 2:', product2.nombre);
              
              // Actualizar loading con nombres específicos
              document.getElementById('resultado-comparacion').innerHTML = 
                '<div class="bg-[#1e293b] rounded-xl p-8 text-center">' +
                  '<div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-500 mx-auto mb-4"></div>' +
                  '<h3 class="text-white text-xl font-bold mb-2">� Consultando APIs Externas</h3>' +
                  '<p class="text-gray-300">Obteniendo especificaciones técnicas, precios de Amazon e historial de precios...</p>' +
                  '<div class="mt-4 bg-[#0f172a] rounded-lg p-3 space-y-2">' +
                    '<div class="text-sm text-blue-400">📊 Comparación local completada ✅</div>' +
                    '<div class="text-sm text-green-400">🔧 Especificaciones: "' + product1.nombre + '"</div>' +
                    '<div class="text-sm text-green-400">� Especificaciones: "' + product2.nombre + '"</div>' +
                    '<div class="text-sm text-orange-400">🛒 Amazon: "' + product1.nombre + '"</div>' +
                    '<div class="text-sm text-orange-400">� Amazon: "' + product2.nombre + '"</div>' +
                  '</div>' +
                '</div>';
              
              // Buscar especificaciones, productos en Amazon e historial de precios en paralelo
              const [specs1Response, specs2Response, amazon1Response, amazon2Response, priceHistory1Response, priceHistory2Response] = await Promise.all([
                fetch(`/test-specs-api/${encodeURIComponent(product1.nombre)}`),
                fetch(`/test-specs-api/${encodeURIComponent(product2.nombre)}`),
                fetch(`/test-amazon-api/${encodeURIComponent(product1.nombre)}`),
                fetch(`/test-amazon-api/${encodeURIComponent(product2.nombre)}`),
                fetch(`/test-price-history/${encodeURIComponent(product1.id || product1.nombre)}`),
                fetch(`/test-price-history/${encodeURIComponent(product2.id || product2.nombre)}`)
              ]);

              console.log('📊 Respuestas de APIs:');
              console.log('- Especificaciones 1:', specs1Response.status, specs1Response.statusText);
              console.log('- Especificaciones 2:', specs2Response.status, specs2Response.statusText);
              console.log('- Amazon Producto 1:', amazon1Response.status, amazon1Response.statusText);
              console.log('- Amazon Producto 2:', amazon2Response.status, amazon2Response.statusText);
              console.log('- Historial Precios 1:', priceHistory1Response.status, priceHistory1Response.statusText);
              console.log('- Historial Precios 2:', priceHistory2Response.status, priceHistory2Response.statusText);
              
              // Procesar especificaciones técnicas
              if (specs1Response.ok && specs2Response.ok) {
                const specs1Data = await specs1Response.json();
                const specs2Data = await specs2Response.json();
                
                console.log('✅ Especificaciones obtenidas exitosamente:');
                console.log('- Especificaciones Producto 1:', specs1Data);
                console.log('- Especificaciones Producto 2:', specs2Data);
                
                specsData = {
                  success: true,
                  specs_data: {
                    product1: specs1Data.result,
                    product2: specs2Data.result
                  }
                };
              } else {
                console.warn('⚠️ Error en Especificaciones API');
              }
              
              // Procesar datos de Amazon
              if (amazon1Response.ok && amazon2Response.ok) {
                const amazon1Data = await amazon1Response.json();
                const amazon2Data = await amazon2Response.json();
                
                console.log('✅ Datos de Amazon obtenidos exitosamente:');
                console.log('- Amazon Producto 1:', amazon1Data);
                console.log('- Amazon Producto 2:', amazon2Data);
                
                // Estructurar datos para el display
                amazonData = {
                  success: true,
                  amazon_data: {
                    product1: amazon1Data.result?.data?.products?.slice(0, 3) || [],
                    product2: amazon2Data.result?.data?.products?.slice(0, 3) || []
                  }
                };
              } else {
                console.warn('⚠️ Error en Amazon API');
                console.warn('- Response 1:', amazon1Response.status);
                console.warn('- Response 2:', amazon2Response.status);
              }
              
            } catch (amazonError) {
              console.error('💥 Excepción consultando APIs externas:', amazonError.message);
              console.error(amazonError);
            }
            
              // Procesar historial de precios independientemente de Amazon
            try {
              if (priceHistory1Response.ok && priceHistory2Response.ok) {
                const priceHistory1Data = await priceHistory1Response.json();
                const priceHistory2Data = await priceHistory2Response.json();
                
                console.log('✅ Historial de precios obtenido exitosamente:');
                console.log('- Historial Producto 1 RAW:', priceHistory1Data);
                console.log('- Historial Producto 2 RAW:', priceHistory2Data);
                
                // Los datos están en .result, no directamente en la respuesta
                priceHistoryData = {
                  success: true,
                  price_history: {
                    product1: priceHistory1Data.result || priceHistory1Data,
                    product2: priceHistory2Data.result || priceHistory2Data
                  }
                };
                
                console.log('📈 Price History Data procesada:', priceHistoryData);
              } else {
                console.warn('⚠️ Error en Price History API');
                console.warn('- Response 1:', priceHistory1Response.status);
                console.warn('- Response 2:', priceHistory2Response.status);
              }
            } catch (priceHistoryError) {
              console.error('💥 Excepción consultando Price History API:', priceHistoryError.message);
              console.error(priceHistoryError);
            }            // 4. Mostrar resultado combinado con todas las APIs
            console.log('🚀 DATOS FINALES ANTES DE MOSTRAR:');
            console.log('📊 localData:', localData);
            console.log('🛒 amazonData:', amazonData);
            console.log('🔧 specsData:', specsData);
            console.log('📈 priceHistoryData:', priceHistoryData);
            
            mostrarResultadoComparacion(localData, amazonData, specsData, priceHistoryData);
            
          } catch (error) {
            console.error('❌ Error en la comparación:', error);
            document.getElementById('resultado-comparacion').innerHTML = 
              '<div class="bg-red-500 text-white p-4 rounded-lg">' +
                '<h4 class="font-bold mb-2">❌ Error en la Comparación</h4>' +
                '<p>Ocurrió un problema al obtener los datos: ' + error.message + '</p>' +
                '<div class="mt-2 text-sm opacity-80">Por favor, inténtalo de nuevo en unos momentos.</div>' +
              '</div>';
          }
        });
      });

      function mostrarResultadoComparacion(data, amazonData = null, specsData = null, priceHistoryData = null) {
        const resultado = document.getElementById('resultado-comparacion');
        
        console.log('🎨 Generando resultado visual...');
        console.log('- Datos locales:', data);
        console.log('- Datos Amazon:', amazonData);
        console.log('- Datos Especificaciones:', specsData);
        console.log('- Datos Historial de Precios:', priceHistoryData);
        
        // Crear container principal
        const container = document.createElement('div');
        container.className = 'bg-[#1e293b] rounded-xl p-6 mt-6 shadow-lg';
        
        // Título
        const title = document.createElement('h3');
        title.className = 'text-white text-2xl font-bold mb-4 text-center';
        title.innerHTML = '📊 Comparación Completa ' + (amazonData ? '<span class="text-orange-400">(🛒 + Amazon)</span>' : '');
        container.appendChild(title);
        
        // Indicador de tasa de cambio si hay datos de Amazon
        if (amazonData) {
          const exchangeRate = document.createElement('div');
          exchangeRate.className = 'text-center mb-4 text-sm text-gray-400 bg-gray-800/50 p-2 rounded-lg';
          exchangeRate.innerHTML = '💱 Tipo de cambio: 1 USD = $' + USD_TO_MXN_RATE.toFixed(2) + ' MXN (aproximado)';
          container.appendChild(exchangeRate);
        }
        
        // Grid de productos
        const productsGrid = document.createElement('div');
        productsGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-6 mb-6';
        
        // Producto 1
        console.log('🔍 CREANDO TARJETA PRODUCTO 1:');
        console.log('- Producto:', data.periferico1?.nombre);
        console.log('- Price History Data:', priceHistoryData?.price_history?.product1);
        
        const prod1Card = crearTarjetaProducto(
          data.periferico1, 
          'blue', 
          amazonData?.amazon_data?.product1, 
          specsData?.specs_data?.product1,
          priceHistoryData?.price_history?.product1
        );
        productsGrid.appendChild(prod1Card);
        
        // Separador VS
        const vsDiv = document.createElement('div');
        vsDiv.className = 'flex items-center justify-center col-span-full md:col-span-1 md:hidden';
        vsDiv.innerHTML = '<div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2 rounded-full font-bold text-lg shadow-lg">VS</div>';
        productsGrid.appendChild(vsDiv);
        
        // Producto 2
        console.log('🔍 CREANDO TARJETA PRODUCTO 2:');
        console.log('- Producto:', data.periferico2?.nombre);
        console.log('- Price History Data:', priceHistoryData?.price_history?.product2);
        
        const prod2Card = crearTarjetaProducto(
          data.periferico2, 
          'purple', 
          amazonData?.amazon_data?.product2, 
          specsData?.specs_data?.product2,
          priceHistoryData?.price_history?.product2
        );
        productsGrid.appendChild(prod2Card);
        
        container.appendChild(productsGrid);
        
        // Análisis de comparación (ahora incluye datos de Amazon y especificaciones)
        const analysisCard = crearAnalisisComparacion(data, amazonData, specsData);
        container.appendChild(analysisCard);
        
        // Si hay datos de Amazon, mostrar comparación enriquecida
        if (amazonData && amazonData.success) {
          const amazonCard = crearSeccionAmazon(amazonData);
          container.appendChild(amazonCard);
        }
        
        // Limpiar y mostrar
        resultado.innerHTML = '';
        resultado.appendChild(container);
        
        console.log('✅ Resultado visual generado correctamente');
      }
      
      function crearTarjetaProducto(producto, color, amazonInfo = null, specsInfo = null, priceHistoryInfo = null) {
        console.log(`🏗️ Creando tarjeta para: ${producto.nombre}`);
        console.log(`🏗️ Amazon Info:`, amazonInfo);
        console.log(`🏗️ Specs Info:`, specsInfo);
        const card = document.createElement('div');
        card.className = 'bg-[#0f172a] p-6 rounded-lg border border-' + color + '-500';
        
        // Header del producto
        const header = document.createElement('div');
        header.className = 'text-center mb-4';
        
        const name = document.createElement('h4');
        name.className = 'font-bold text-xl mb-2 text-' + color + '-400';
        name.textContent = producto.nombre;
        header.appendChild(name);
        
        const price = document.createElement('div');
        price.className = 'text-2xl font-bold text-green-400 mb-2';
        price.textContent = '$' + parseFloat(producto.precio).toLocaleString();
        header.appendChild(price);
        
        // Badge de modelo si existe
        if (producto.modelo) {
          const model = document.createElement('div');
          model.className = 'text-sm text-gray-400 bg-gray-800 px-3 py-1 rounded-full inline-block';
          model.textContent = '📦 ' + producto.modelo;
          header.appendChild(model);
        }
        
        card.appendChild(header);
        
        // Información del producto
        const info = document.createElement('div');
        info.className = 'space-y-3 mb-4';
        
        // Marca
        if (producto.marca_nombre) {
          const brand = document.createElement('div');
          brand.className = 'flex justify-between text-sm';
          brand.innerHTML = '<span class="text-gray-400">🏷️ Marca:</span><span class="text-white font-medium">' + producto.marca_nombre + '</span>';
          info.appendChild(brand);
        }
        
        // Categoría
        if (producto.categoria_nombre) {
          const category = document.createElement('div');
          category.className = 'flex justify-between text-sm';
          category.innerHTML = '<span class="text-gray-400">📂 Categoría:</span><span class="text-blue-300">' + producto.categoria_nombre.trim() + '</span>';
          info.appendChild(category);
        }
        
        // Conectividad
        if (producto.tipo_conectividad) {
          const connectivity = document.createElement('div');
          connectivity.className = 'flex justify-between text-sm';
          connectivity.innerHTML = '<span class="text-gray-400">🔗 Conectividad:</span><span class="text-green-300">' + producto.tipo_conectividad + '</span>';
          info.appendChild(connectivity);
        }
        
        // Precio en pesos mexicanos
        const mxnPrice = document.createElement('div');
        mxnPrice.className = 'bg-green-900/30 border border-green-500/30 p-3 rounded-lg mt-3';
        const localMxnPrice = parseFloat(producto.precio).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        mxnPrice.innerHTML = '<div class="text-center"><span class="text-gray-400 text-sm">💰 Precio Local</span><br><span class="text-green-400 text-xl font-bold">$' + localMxnPrice + ' MXN</span></div>';
        info.appendChild(mxnPrice);
        
        card.appendChild(info);
        
        // Información de Amazon si está disponible
        if (amazonInfo && amazonInfo.length > 0) {
          console.log('📦 Mostrando info Amazon para:', producto.nombre);
          console.log('🛒 Amazon products:', amazonInfo);
          
          const amazonSection = document.createElement('div');
          amazonSection.className = 'border-t border-orange-500/30 pt-4 mt-4 bg-orange-900/10 rounded-lg p-4';
          
          const amazonTitle = document.createElement('div');
          amazonTitle.className = 'text-orange-400 font-semibold mb-3 flex items-center justify-between';
          amazonTitle.innerHTML = '<span>🛒 Disponible en Amazon</span><span class="text-xs bg-orange-500 text-white px-2 py-1 rounded">EN VIVO</span>';
          amazonSection.appendChild(amazonTitle);
          
          const amazonProduct = amazonInfo[0]; // Primer producto del array
          console.log('🎯 Producto Amazon seleccionado:', amazonProduct);
          
          // Título del producto Amazon
          if (amazonProduct.product_title) {
            const productTitle = document.createElement('div');
            productTitle.className = 'text-sm text-gray-300 mb-3 font-medium line-clamp-2 border-l-2 border-orange-500 pl-3';
            productTitle.textContent = amazonProduct.product_title.substring(0, 120) + (amazonProduct.product_title.length > 120 ? '...' : '');
            amazonSection.appendChild(productTitle);
          }
          
          // Precios Amazon - USD y MXN
          if (amazonProduct.product_price) {
            const priceConversion = convertUsdToMxn(amazonProduct.product_price);
            const amazonPriceSection = document.createElement('div');
            amazonPriceSection.className = 'bg-orange-900/30 border border-orange-500/40 p-3 rounded-lg mb-3';
            
            if (priceConversion) {
              amazonPriceSection.innerHTML = 
                '<div class="text-center space-y-1">' +
                  '<div class="text-orange-300 text-sm">💰 Precio en Amazon</div>' +
                  '<div class="text-orange-400 font-bold text-lg">' + priceConversion.mxnFormatted + '</div>' +
                  '<div class="text-gray-400 text-xs">(' + priceConversion.usdFormatted + ')</div>' +
                '</div>';
            } else {
              amazonPriceSection.innerHTML = '<div class="text-center"><span class="text-orange-400 font-bold">' + amazonProduct.product_price + '</span></div>';
            }
            
            amazonSection.appendChild(amazonPriceSection);
            
            // Mostrar precio original si existe descuento
            if (amazonProduct.product_original_price && amazonProduct.product_original_price !== amazonProduct.product_price) {
              const originalConversion = convertUsdToMxn(amazonProduct.product_original_price);
              if (originalConversion && priceConversion) {
                const discount = Math.round(((originalConversion.mxn - priceConversion.mxn) / originalConversion.mxn) * 100);
                const discountBadge = document.createElement('div');
                discountBadge.className = 'text-center mt-2';
                discountBadge.innerHTML = 
                  '<span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">' +
                  '🔥 ' + discount + '% DESCUENTO</span><br>' +
                  '<span class="text-gray-500 line-through text-sm">Antes: ' + originalConversion.mxnFormatted + '</span>';
                amazonSection.appendChild(discountBadge);
              }
            }
          }
          
          // Información adicional en grid
          const infoGrid = document.createElement('div');
          infoGrid.className = 'grid grid-cols-2 gap-2 mb-3 text-xs';
          
          // Calificación
          if (amazonProduct.product_star_rating) {
            const ratingDiv = document.createElement('div');
            ratingDiv.className = 'bg-yellow-900/30 border border-yellow-500/30 p-2 rounded text-center';
            ratingDiv.innerHTML = '<div class="text-yellow-400 font-bold">⭐ ' + amazonProduct.product_star_rating + '</div><div class="text-gray-400">' + amazonProduct.product_num_ratings + ' reseñas</div>';
            infoGrid.appendChild(ratingDiv);
          }
          
          // Ventas/popularidad
          if (amazonProduct.sales_volume) {
            const salesDiv = document.createElement('div');
            salesDiv.className = 'bg-blue-900/30 border border-blue-500/30 p-2 rounded text-center';
            // Traducir términos de volumen de ventas
            let salesText = amazonProduct.sales_volume;
            salesText = salesText.replace(/bought in past month/g, 'comprados este mes');
            salesText = salesText.replace(/K\+/g, 'K+');
            
            salesDiv.innerHTML = '<div class="text-blue-400 font-bold">📈 Popular</div><div class="text-gray-400">' + salesText + '</div>';
            infoGrid.appendChild(salesDiv);
          }
          
          // Disponibilidad Prime
          if (amazonProduct.is_prime) {
            const primeDiv = document.createElement('div');
            primeDiv.className = 'bg-blue-900/30 border border-blue-500/30 p-2 rounded text-center';
            primeDiv.innerHTML = '<div class="text-blue-400 font-bold">📦 Prime</div><div class="text-gray-400">Envío rápido</div>';
            infoGrid.appendChild(primeDiv);
          }
          
          // Insignias especiales
          if (amazonProduct.is_amazon_choice) {
            const choiceDiv = document.createElement('div');
            choiceDiv.className = 'bg-orange-900/30 border border-orange-500/30 p-2 rounded text-center';
            choiceDiv.innerHTML = '<div class="text-orange-400 font-bold">🏆 Recomendado</div><div class="text-gray-400">Amazon\'s Choice</div>';
            infoGrid.appendChild(choiceDiv);
          }
          
          if (amazonProduct.is_best_seller) {
            const bestSellerDiv = document.createElement('div');
            bestSellerDiv.className = 'bg-red-900/30 border border-red-500/30 p-2 rounded text-center';
            bestSellerDiv.innerHTML = '<div class="text-red-400 font-bold">🔥 Más Vendido</div><div class="text-gray-400">Best Seller</div>';
            infoGrid.appendChild(bestSellerDiv);
          }
          
          if (infoGrid.children.length > 0) {
            amazonSection.appendChild(infoGrid);
          }
          
          // Información de envío
          if (amazonProduct.delivery) {
            const deliveryDiv = document.createElement('div');
            deliveryDiv.className = 'text-xs text-gray-300 bg-gray-800/50 p-2 rounded mb-3';
            // Traducir algunos términos comunes de envío
            let deliveryText = amazonProduct.delivery;
            deliveryText = deliveryText.replace(/FREE delivery/g, 'Envío GRATIS');
            deliveryText = deliveryText.replace(/Or fastest delivery/g, 'O envío más rápido');
            deliveryText = deliveryText.replace(/on \$\d+ of items shipped by Amazon/g, 'en pedidos elegibles');
            
            deliveryDiv.innerHTML = '<span class="text-green-400">🚚</span> ' + deliveryText;
            amazonSection.appendChild(deliveryDiv);
          }
          
          // Enlace a Amazon
          if (amazonProduct.product_url) {
            const amazonLink = document.createElement('a');
            amazonLink.href = amazonProduct.product_url;
            amazonLink.target = '_blank';
            amazonLink.className = 'block bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-center py-3 px-4 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg';
            amazonLink.innerHTML = '🛒 Comprar en Amazon';
            amazonSection.appendChild(amazonLink);
          }
          
          card.appendChild(amazonSection);
        } else {
          console.log('⚠️ No hay datos de Amazon para:', producto.nombre);
        }
        
        // Sección de Especificaciones Técnicas si está disponible
        if (specsInfo && specsInfo.specifications) {
          console.log('🔧 Mostrando especificaciones técnicas para:', producto.nombre);
          console.log('📋 Especificaciones completas:', specsInfo);
          console.log('📋 Categorías de especificaciones:', Object.keys(specsInfo.specifications));
          console.log('📋 About this product:', specsInfo.about_this_product);
          
          const specsSection = document.createElement('div');
          specsSection.className = 'specs-section border-t border-green-500/30 pt-4 mt-4 bg-green-900/10 rounded-lg p-4';
          
          const specsTitle = document.createElement('div');
          specsTitle.className = 'text-green-400 font-semibold mb-3 flex items-center justify-between';
          specsTitle.innerHTML = '<span>🔧 Especificaciones Técnicas</span><span class="text-xs bg-green-500 text-white px-2 py-1 rounded">DETALLADAS</span>';
          specsSection.appendChild(specsTitle);
          
          // Crear acordeón de especificaciones por categoría
          for (const [category, specs] of Object.entries(specsInfo.specifications)) {
            console.log(`📂 Procesando categoría: ${category}`, specs);
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'mb-3 bg-gray-800/50 rounded-lg overflow-hidden';
            
            // Header de la categoría
            const categoryHeader = document.createElement('div');
            categoryHeader.className = 'bg-gray-700/50 p-3 cursor-pointer hover:bg-gray-600/50 transition-colors flex items-center justify-between';
            categoryHeader.innerHTML = 
              '<span class="text-green-300 font-medium">' + getCategoryIcon(category) + ' ' + getCategoryName(category) + '</span>' +
              '<span class="text-gray-400 text-sm">▼</span>';
            
            // Contenido de especificaciones (inicialmente visible)
            const categoryContent = document.createElement('div');
            categoryContent.className = 'p-3 space-y-2';
            
            for (const [spec, value] of Object.entries(specs)) {
              if (value && value !== null) {
                const specRow = document.createElement('div');
                
                // Si la especificación es muy larga (más de 80 caracteres), usar layout vertical
                const isLongSpec = value.toString().length > 80;
                
                if (isLongSpec) {
                  specRow.className = 'mb-3 p-2 bg-gray-800/30 rounded';
                  specRow.innerHTML = 
                    '<div class="text-gray-400 text-xs font-medium mb-1">' + getSpecName(spec) + ':</div>' +
                    '<div class="spec-long-text text-white text-sm leading-relaxed">' + value + '</div>';
                } else {
                  specRow.className = 'flex justify-between text-sm py-1';
                  specRow.innerHTML = 
                    '<span class="text-gray-400 flex-shrink-0 mr-2">' + getSpecName(spec) + ':</span>' +
                    '<span class="text-white font-medium text-right">' + value + '</span>';
                }
                
                categoryContent.appendChild(specRow);
              }
            }
            
            // Toggle functionality
            categoryHeader.addEventListener('click', function() {
              const isVisible = categoryContent.style.display !== 'none';
              categoryContent.style.display = isVisible ? 'none' : 'block';
              const arrow = categoryHeader.querySelector('span:last-child');
              arrow.textContent = isVisible ? '▶' : '▼';
            });
            
            categoryDiv.appendChild(categoryHeader);
            categoryDiv.appendChild(categoryContent);
            specsSection.appendChild(categoryDiv);
          }
          
          // Agregar sección "Sobre este artículo" si está disponible
          if (specsInfo.about_this_product && specsInfo.about_this_product.length > 0) {
            const aboutSection = document.createElement('div');
            aboutSection.className = 'border-t border-blue-500/30 pt-4 mt-4 bg-blue-900/10 rounded-lg p-4';
            
            const aboutTitle = document.createElement('div');
            aboutTitle.className = 'text-blue-400 font-semibold mb-4 flex items-center';
            aboutTitle.innerHTML = '<span>📖 Sobre este artículo</span>';
            aboutSection.appendChild(aboutTitle);
            
            specsInfo.about_this_product.forEach(feature => {
              const featureDiv = document.createElement('div');
              featureDiv.className = 'mb-4 p-3 bg-gray-800/30 rounded-lg';
              
              const featureTitle = document.createElement('div');
              featureTitle.className = 'text-blue-300 font-medium mb-2 flex items-center';
              featureTitle.innerHTML = '• ' + feature.title;
              
              const featureDescription = document.createElement('div');
              featureDescription.className = 'text-gray-300 text-sm leading-relaxed';
              featureDescription.textContent = feature.description;
              
              featureDiv.appendChild(featureTitle);
              featureDiv.appendChild(featureDescription);
              aboutSection.appendChild(featureDiv);
            });
            
            specsSection.appendChild(aboutSection);
          }
          
          card.appendChild(specsSection);
        } else {
          console.log('⚠️ No hay especificaciones técnicas para:', producto.nombre);
        }
        
        // Agregar sección de historial de precios si está disponible
        console.log('🔍 Verificando datos de historial de precios para:', producto.nombre);
        console.log('📊 Datos de precio historial:', priceHistoryInfo);
        
        if (priceHistoryInfo) {
          console.log('✅ Creando sección de historial de precios para:', producto.nombre);
          const priceHistorySection = document.createElement('div');
          priceHistorySection.className = 'border-t border-purple-500/30 pt-4 mt-4';
          
          const priceHistoryTitle = document.createElement('div');
          priceHistoryTitle.className = 'text-purple-400 font-semibold mb-4 flex items-center justify-between cursor-pointer';
          priceHistoryTitle.innerHTML = 
            '<span>📈 Historial de Precios & Tendencias</span>' +
            '<span>▼</span>';
          
          const priceHistoryContent = document.createElement('div');
          priceHistoryContent.className = 'space-y-4';
          
          // Información de precio actual y tendencia
          if (priceHistoryInfo.current_price) {
            const currentPriceDiv = document.createElement('div');
            currentPriceDiv.className = 'bg-purple-900/20 p-4 rounded-lg';
            
            // Calcular tendencia simple basada en los primeros y últimos precios
            const priceHistory = priceHistoryInfo.price_history || [];
            let trendAction = 'OBSERVAR';
            let trendMessage = 'Precio estable';
            
            if (priceHistory.length > 1) {
              const firstPrice = priceHistory[0].price;
              const lastPrice = priceHistory[priceHistory.length - 1].price;
              const change = ((lastPrice - firstPrice) / firstPrice) * 100;
              
              if (change < -10) {
                trendAction = 'COMPRA_AHORA';
                trendMessage = 'El precio ha bajado significativamente';
              } else if (change < -5) {
                trendAction = 'COMPRA_PRONTO';
                trendMessage = 'Buen momento para comprar';
              } else if (change > 10) {
                trendAction = 'ESPERAR';
                trendMessage = 'El precio está alto, considera esperar';
              }
            }
            
            const priceWithTrend = getPriceTrendIndicator(trendAction);
            currentPriceDiv.innerHTML = `
              <div class="flex justify-between items-center mb-2">
                <span class="text-purple-300 font-medium">Precio Actual:</span>
                <span class="text-white font-bold text-lg">$${priceHistoryInfo.current_price.toFixed(2)} USD</span>
              </div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-purple-300">Tendencia:</span>
                <span class="${getTrendColor(trendAction)}">${priceWithTrend}</span>
              </div>
              <div class="text-sm text-gray-300 mt-3 p-2 bg-gray-800/40 rounded">
                <strong>Recomendación:</strong> ${trendMessage}
              </div>
              <div class="text-xs text-gray-400 mt-2">
                Basado en ${priceHistory.length} días de historial
              </div>
            `;
            
            priceHistoryContent.appendChild(currentPriceDiv);
          }
          
          // Estadísticas de volatilidad
          if (priceHistoryInfo.price_history && priceHistoryInfo.price_history.length > 0) {
            const prices = priceHistoryInfo.price_history.map(item => item.price);
            const maxPrice = Math.max(...prices);
            const minPrice = Math.min(...prices);
            const avgPrice = prices.reduce((sum, price) => sum + price, 0) / prices.length;
            const priceRange = maxPrice - minPrice;
            const volatilityPercent = (priceRange / avgPrice) * 100;
            
            const volatilityDiv = document.createElement('div');
            volatilityDiv.className = 'bg-blue-900/20 p-4 rounded-lg';
            
            volatilityDiv.innerHTML = `
              <div class="text-blue-300 font-medium mb-3">📊 Análisis de Volatilidad (${priceHistoryInfo.period_days} días)</div>
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-400">Precio Más Alto:</span>
                  <span class="text-green-400 font-medium">$${maxPrice.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Precio Más Bajo:</span>
                  <span class="text-red-400 font-medium">$${minPrice.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Precio Promedio:</span>
                  <span class="text-blue-400 font-medium">$${avgPrice.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Variación:</span>
                  <span class="text-yellow-400 font-medium">${volatilityPercent.toFixed(1)}%</span>
                </div>
              </div>
              <div class="mt-3 text-xs text-gray-400">
                Rango de precios: $${priceRange.toFixed(2)} USD
              </div>
            `;
            
            priceHistoryContent.appendChild(volatilityDiv);
          }
          
          // Canvas para el gráfico de Chart.js
          const chartContainer = document.createElement('div');
          chartContainer.className = 'bg-gray-900/40 p-4 rounded-lg';
          
          const chartTitle = document.createElement('div');
          chartTitle.className = 'text-gray-300 font-medium mb-3 text-center';
          chartTitle.textContent = '📈 Gráfico de Tendencia de Precios';
          
          const canvas = document.createElement('canvas');
          canvas.id = `priceChart_${producto.id}`;
          canvas.width = 400;
          canvas.height = 200;
          
          chartContainer.appendChild(chartTitle);
          chartContainer.appendChild(canvas);
          priceHistoryContent.appendChild(chartContainer);
          
          // Crear el gráfico después de agregar el canvas al DOM
          setTimeout(() => {
            if (priceHistoryInfo.price_history && priceHistoryInfo.price_history.length > 0) {
              createPriceChart(canvas.id, priceHistoryInfo.price_history, priceHistoryInfo.current_price);
            }
          }, 100);
          
          // Toggle functionality
          priceHistoryTitle.addEventListener('click', function() {
            const isVisible = priceHistoryContent.style.display !== 'none';
            priceHistoryContent.style.display = isVisible ? 'none' : 'block';
            const arrow = priceHistoryTitle.querySelector('span:last-child');
            arrow.textContent = isVisible ? '▶' : '▼';
          });
          
          priceHistorySection.appendChild(priceHistoryTitle);
          priceHistorySection.appendChild(priceHistoryContent);
          card.appendChild(priceHistorySection);
        } else {
          console.log('⚠️ No hay datos de historial de precios para:', producto.nombre);
        }
        
        return card;
      }
      
      function crearAnalisisComparacion(data, amazonData = null, specsData = null) {
        const analysis = document.createElement('div');
        analysis.className = 'bg-[#0f172a] p-6 rounded-lg border border-gray-600';
        
        const title = document.createElement('h5');
        title.className = 'font-bold text-lg mb-4 text-white text-center';
        title.innerHTML = '🏆 Análisis Completo de Comparación';
        analysis.appendChild(title);
        
        // Comparación de precios locales
        const precio1 = parseFloat(data.periferico1.precio);
        const precio2 = parseFloat(data.periferico2.precio);
        const diferencia = Math.abs(precio1 - precio2);
        
        const priceAnalysis = document.createElement('div');
        priceAnalysis.className = 'mb-4 p-4 rounded-lg bg-green-900/30 border border-green-500/30';
        
        let comparisonText = '';
        const diferenciaFormatted = diferencia.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        if (precio1 < precio2) {
          comparisonText = '💚 <strong>' + data.periferico1.nombre + '</strong> es <strong>$' + diferenciaFormatted + ' MXN</strong> más barato localmente';
        } else if (precio2 < precio1) {
          comparisonText = '💚 <strong>' + data.periferico2.nombre + '</strong> es <strong>$' + diferenciaFormatted + ' MXN</strong> más barato localmente';
        } else {
          comparisonText = '🤝 Ambos productos tienen el mismo precio local';
        }
        
        priceAnalysis.innerHTML = '<div class="text-green-300 text-center font-medium">' + comparisonText + '</div>';
        analysis.appendChild(priceAnalysis);
        
        // Análisis con Amazon si está disponible
        if (amazonData && amazonData.amazon_data) {
          const amazonAnalysis = document.createElement('div');
          amazonAnalysis.className = 'mb-4 p-4 rounded-lg bg-orange-900/30 border border-orange-500/30';
          amazonAnalysis.innerHTML = '<div class="text-orange-300 font-semibold mb-3 text-center">🛒 Comparación con Amazon</div>';
          
          const amazonGrid = document.createElement('div');
          amazonGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
          
          // Producto 1 vs Amazon
          if (amazonData.amazon_data.product1 && amazonData.amazon_data.product1.length > 0) {
            const amazon1 = amazonData.amazon_data.product1[0];
            if (amazon1.product_price) {
              const amazonPrice1 = convertUsdToMxn(amazon1.product_price);
              if (amazonPrice1) {
                const localVsAmazon1 = precio1 - amazonPrice1.mxn;
                const comparison1 = document.createElement('div');
                comparison1.className = 'bg-gray-800/50 p-3 rounded';
                
                let compText1 = '';
                if (localVsAmazon1 < 0) {
                  compText1 = '<div class="text-green-400">📈 ' + data.periferico1.nombre + ' es $' + Math.abs(localVsAmazon1).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN más barato que Amazon</div>';
                } else if (localVsAmazon1 > 0) {
                  compText1 = '<div class="text-red-400">📉 Amazon es $' + localVsAmazon1.toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN más barato</div>';
                } else {
                  compText1 = '<div class="text-blue-400">🟰 Mismo precio que Amazon</div>';
                }
                
                comparison1.innerHTML = '<div class="text-gray-300 text-sm mb-1">' + data.periferico1.nombre + '</div>' + compText1;
                amazonGrid.appendChild(comparison1);
              }
            }
          }
          
          // Producto 2 vs Amazon
          if (amazonData.amazon_data.product2 && amazonData.amazon_data.product2.length > 0) {
            const amazon2 = amazonData.amazon_data.product2[0];
            if (amazon2.product_price) {
              const amazonPrice2 = convertUsdToMxn(amazon2.product_price);
              if (amazonPrice2) {
                const localVsAmazon2 = precio2 - amazonPrice2.mxn;
                const comparison2 = document.createElement('div');
                comparison2.className = 'bg-gray-800/50 p-3 rounded';
                
                let compText2 = '';
                if (localVsAmazon2 < 0) {
                  compText2 = '<div class="text-green-400">📈 ' + data.periferico2.nombre + ' es $' + Math.abs(localVsAmazon2).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN más barato que Amazon</div>';
                } else if (localVsAmazon2 > 0) {
                  compText2 = '<div class="text-red-400">📉 Amazon es $' + localVsAmazon2.toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN más barato</div>';
                } else {
                  compText2 = '<div class="text-blue-400">🟰 Mismo precio que Amazon</div>';
                }
                
                comparison2.innerHTML = '<div class="text-gray-300 text-sm mb-1">' + data.periferico2.nombre + '</div>' + compText2;
                amazonGrid.appendChild(comparison2);
              }
            }
          }
          
          amazonAnalysis.appendChild(amazonGrid);
          analysis.appendChild(amazonAnalysis);
        }
        
        // Análisis de especificaciones técnicas si están disponibles
        if (specsData && specsData.specs_data) {
          const specsAnalysis = document.createElement('div');
          specsAnalysis.className = 'mb-4 p-4 rounded-lg bg-green-900/30 border border-green-500/30';
          specsAnalysis.innerHTML = '<div class="text-green-300 font-semibold mb-3 text-center">🔧 Comparación de Especificaciones</div>';
          
          const specsGrid = document.createElement('div');
          specsGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
          
          // Comparar categorías importantes
          const spec1 = specsData.specs_data.product1?.specifications;
          const spec2 = specsData.specs_data.product2?.specifications;
          
          if (spec1 && spec2) {
            // Comparar audio si existe
            if (spec1.audio && spec2.audio) {
              const audioComp = document.createElement('div');
              audioComp.className = 'bg-gray-800/50 p-3 rounded';
              audioComp.innerHTML = '<div class="text-green-300 text-sm font-bold mb-2">🎵 Audio</div>' +
                generateSpecComparison(spec1.audio, spec2.audio, data.periferico1.nombre, data.periferico2.nombre);
              specsGrid.appendChild(audioComp);
            }
            
            // Comparar conectividad
            if (spec1.connectivity && spec2.connectivity) {
              const connComp = document.createElement('div');
              connComp.className = 'bg-gray-800/50 p-3 rounded';
              connComp.innerHTML = '<div class="text-green-300 text-sm font-bold mb-2">🔗 Conectividad</div>' +
                generateSpecComparison(spec1.connectivity, spec2.connectivity, data.periferico1.nombre, data.periferico2.nombre);
              specsGrid.appendChild(connComp);
            }
            
            // Comparar batería/poder
            if (spec1.power && spec2.power) {
              const powerComp = document.createElement('div');
              powerComp.className = 'bg-gray-800/50 p-3 rounded';
              powerComp.innerHTML = '<div class="text-green-300 text-sm font-bold mb-2">🔋 Energía</div>' +
                generateSpecComparison(spec1.power, spec2.power, data.periferico1.nombre, data.periferico2.nombre);
              specsGrid.appendChild(powerComp);
            }
            
            // Comparar características
            if (spec1.features && spec2.features) {
              const featComp = document.createElement('div');
              featComp.className = 'bg-gray-800/50 p-3 rounded';
              featComp.innerHTML = '<div class="text-green-300 text-sm font-bold mb-2">⭐ Características</div>' +
                generateSpecComparison(spec1.features, spec2.features, data.periferico1.nombre, data.periferico2.nombre);
              specsGrid.appendChild(featComp);
            }
          }
          
          specsAnalysis.appendChild(specsGrid);
          analysis.appendChild(specsAnalysis);
        }
        
        // Análisis adicional si existe
        if (data.comparacion) {
          const additionalAnalysis = document.createElement('div');
          additionalAnalysis.className = 'p-4 bg-blue-900/30 border border-blue-500/30 rounded-lg';
          additionalAnalysis.innerHTML = '<div class="text-blue-300"><strong>💡 Análisis Técnico:</strong></div><div class="text-gray-300 mt-2">' + data.comparacion + '</div>';
          analysis.appendChild(additionalAnalysis);
        }
        
        return analysis;
      }
      
      function crearSeccionAmazon(amazonData) {
        const amazonSection = document.createElement('div');
        amazonSection.className = 'bg-orange-900/20 border border-orange-500/30 p-6 rounded-lg mt-6';
        
        const title = document.createElement('h5');
        title.className = 'font-bold text-lg mb-4 text-orange-400 text-center flex items-center justify-center';
        title.innerHTML = '🛒 Información Adicional de Amazon <span class="ml-2 text-xs bg-orange-500 text-white px-2 py-1 rounded">EN TIEMPO REAL</span>';
        amazonSection.appendChild(title);
        
        if (amazonData.comparison && amazonData.comparison.analysis) {
          const analysis = document.createElement('div');
          analysis.className = 'text-gray-300 text-center p-4 bg-[#0f172a] rounded-lg';
          analysis.innerHTML = '<strong>🔍 Análisis de Amazon:</strong><br>' + amazonData.comparison.analysis;
          amazonSection.appendChild(analysis);
        }
        
        return amazonSection;
      }

      // Funciones auxiliares para especificaciones técnicas
      function getCategoryIcon(category) {
        const icons = {
          'general': '📋',
          'audio': '🎵',
          'connectivity': '🔗',
          'power': '🔋',
          'features': '⭐',
          'sensor': '🖱️',
          'switches': '⌨️'
        };
        return icons[category] || '📄';
      }
      
      function getCategoryName(category) {
        const names = {
          'general': 'General',
          'audio': 'Audio',
          'connectivity': 'Conectividad',
          'power': 'Alimentación',
          'features': 'Características',
          'sensor': 'Sensor',
          'switches': 'Switches'
        };
        return names[category] || category.charAt(0).toUpperCase() + category.slice(1);
      }
      
      function getSpecName(spec) {
        const names = {
          // Especificaciones generales
          'brand': 'Marca', 'manufacturer': 'Fabricante',
          'model': 'Modelo', 'model_number': 'Número de Modelo',
          'type': 'Tipo', 'product_category': 'Categoría de Producto',
          'weight': 'Peso', 'dimensions': 'Dimensiones',
          'color': 'Color', 'build_materials': 'Materiales de Construcción',
          
          // Audio Performance - Especificaciones Ultra-Detalladas
          'driver_configuration': 'Configuración de Drivers',
          'frequency_response_detailed': 'Respuesta de Frecuencia Detallada',
          'acoustic_engineering': 'Ingeniería Acústica',
          'impedance_specifications': 'Especificaciones de Impedancia',
          'sensitivity_performance': 'Rendimiento de Sensibilidad',
          'distortion_analysis': 'Análisis de Distorsión',
          'dynamic_range_snr': 'Rango Dinámico y SNR',
          'power_handling_thermal': 'Manejo de Potencia Térmica',
          'diaphragm_technology': 'Tecnología del Diafragma',
          
          // Noise Control - Ultra-Detallado
          'active_noise_cancellation': 'Cancelación Activa de Ruido',
          'microphone_array_anc': 'Array de Micrófonos ANC',
          'frequency_response_anc': 'Respuesta de Frecuencia ANC',
          'adaptive_algorithms': 'Algoritmos Adaptativos',
          'transparency_modes': 'Modos de Transparencia',
          'wind_noise_reduction': 'Reducción de Ruido de Viento',
          'call_noise_suppression': 'Supresión de Ruido en Llamadas',
          
          // Connectivity - Ultra-Detallado
          'primary_connection': 'Conexión Primaria',
          'bluetooth_specifications': 'Especificaciones Bluetooth',
          'supported_profiles': 'Perfiles Soportados',
          'audio_codecs_detailed': 'Códecs de Audio Detallados',
          'transmission_specs': 'Especificaciones de Transmisión',
          'multidevice_capability': 'Capacidad Multi-dispositivo',
          'pairing_technology': 'Tecnología de Emparejamiento',
          'wireless_range_detailed': 'Rango Inalámbrico Detallado',
          
          // Power Management - Ultra-Detallado
          'battery_specifications': 'Especificaciones de Batería',
          'playback_duration_detailed': 'Duración de Reproducción Detallada',
          'charging_specifications': 'Especificaciones de Carga',
          'power_consumption_analysis': 'Análisis de Consumo de Energía',
          'standby_performance': 'Rendimiento en Standby',
          'call_battery_performance': 'Rendimiento de Batería en Llamadas',
          'temperature_management': 'Gestión Térmica',
          'battery_indicators': 'Indicadores de Batería',
          
          // Especificaciones básicas mantenidas
          'frequency_response': 'Respuesta de Frecuencia',
          'impedance': 'Impedancia',
          'sensitivity': 'Sensibilidad',
          'driver_size': 'Tamaño del Driver',
          'noise_cancellation': 'Cancelación de Ruido',
          'connection_type': 'Tipo de Conexión',
          'bluetooth_version': 'Versión Bluetooth',
          'wireless_range': 'Alcance Inalámbrico',
          'cable_length': 'Longitud del Cable',
          'battery_life': 'Duración de Batería',
          'charging_time': 'Tiempo de Carga',
          'power_consumption': 'Consumo de Energía',
          'charging_port': 'Puerto de Carga',
          'microphone': 'Micrófono',
          'controls': 'Controles',
          'compatibility': 'Compatibilidad',
          'special_features': 'Características Especiales',
          
          // Mouse especificaciones
          'sensor_type': 'Tipo de Sensor',
          'dpi': 'DPI',
          'tracking_speed': 'Velocidad de Seguimiento',
          'acceleration': 'Aceleración',
          'battery_type': 'Tipo de Batería',
          'buttons': 'Botones',
          'scroll_wheel': 'Rueda de Desplazamiento',
          'ergonomic': 'Ergonómico',
          
          // Teclado especificaciones
          'layout': 'Distribución',
          'size': 'Tamaño',
          'switch_type': 'Tipo de Switch',
          'key_travel': 'Recorrido de Tecla',
          'actuation_force': 'Fuerza de Activación',
          'lifespan': 'Vida Útil',
          'backlight': 'Retroiluminación',
          'media_keys': 'Teclas Multimedia',
          'anti_ghosting': 'Anti-ghosting',
          'polar_pattern': 'Patrón Polar',
          'max_spl': 'SPL Máximo',
          'signal_noise_ratio': 'Relación Señal/Ruido',
          'mute_button': 'Botón de Silencio',
          'headphone_monitoring': 'Monitoreo de Auriculares',
          'mount_type': 'Tipo de Montaje',
          'plug_and_play': 'Plug and Play'
        };
        return names[spec] || spec.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      }
      
      function generateSpecComparison(spec1, spec2, product1Name, product2Name) {
        let html = '';
        
        // Comparar especificaciones comunes
        for (const [key, value1] of Object.entries(spec1)) {
          if (spec2[key] && value1 && spec2[key]) {
            const value2 = spec2[key];
            const specName = getSpecName(key);
            
            if (value1 === value2) {
              html += '<div class="text-xs mb-1"><span class="text-gray-400">' + specName + ':</span> <span class="text-blue-300">Igual</span> (' + value1 + ')</div>';
            } else {
              html += '<div class="text-xs mb-1"><span class="text-gray-400">' + specName + ':</span><br>' +
                      '<span class="text-blue-300">• ' + product1Name.split(' ')[0] + ':</span> ' + value1 + '<br>' +
                      '<span class="text-purple-300">• ' + product2Name.split(' ')[0] + ':</span> ' + value2 + '</div>';
            }
          }
        }
        
        return html || '<div class="text-gray-500 text-xs">No hay especificaciones comparables</div>';
      }
      
      // Funciones auxiliares para el historial de precios
      function getPriceTrendIndicator(action) {
        switch(action) {
          case 'COMPRA_AHORA':
            return '🟢 EXCELENTE MOMENTO PARA COMPRAR';
          case 'COMPRA_PRONTO':
            return '🟡 BUEN MOMENTO PARA COMPRAR';
          case 'ESPERAR':
            return '🔴 CONSIDERA ESPERAR';
          case 'OBSERVAR':
            return '🔵 MANTENTE ATENTO';
          default:
            return '⚪ PRECIO ESTABLE';
        }
      }
      
      function getTrendColor(action) {
        switch(action) {
          case 'COMPRA_AHORA':
            return 'text-green-400 font-bold';
          case 'COMPRA_PRONTO':
            return 'text-yellow-400 font-medium';
          case 'ESPERAR':
            return 'text-red-400 font-medium';
          case 'OBSERVAR':
            return 'text-blue-400 font-medium';
          default:
            return 'text-gray-400';
        }
      }
      
      function createPriceChart(canvasId, priceHistory, currentPrice) {
        try {
          const canvas = document.getElementById(canvasId);
          if (!canvas) {
            console.warn('Canvas no encontrado:', canvasId);
            return;
          }
          
          const ctx = canvas.getContext('2d');
          
          // Preparar datos para Chart.js
          const labels = priceHistory.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('es-MX', { month: 'short', day: 'numeric' });
          });
          
          const prices = priceHistory.map(item => item.price);
          
          // Detectar tendencia para colorear la línea
          const isUptrend = prices[prices.length - 1] > prices[0];
          const lineColor = isUptrend ? 'rgb(239, 68, 68)' : 'rgb(34, 197, 94)';
          const gradientColor = isUptrend ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)';
          
          new Chart(ctx, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Precio ($MXN)',
                data: prices,
                borderColor: lineColor,
                backgroundColor: gradientColor,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: lineColor,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: 'rgba(15, 23, 42, 0.9)',
                  titleColor: '#f1f5f9',
                  bodyColor: '#e2e8f0',
                  borderColor: lineColor,
                  borderWidth: 1,
                  callbacks: {
                    label: function(context) {
                      return `Precio: $${context.parsed.y.toFixed(2)} MXN`;
                    }
                  }
                }
              },
              scales: {
                x: {
                  ticks: {
                    color: '#94a3b8'
                  },
                  grid: {
                    color: 'rgba(148, 163, 184, 0.1)'
                  }
                },
                y: {
                  ticks: {
                    color: '#94a3b8',
                    callback: function(value) {
                      return '$' + value.toFixed(0);
                    }
                  },
                  grid: {
                    color: 'rgba(148, 163, 184, 0.1)'
                  }
                }
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });
          
        } catch (error) {
          console.error('Error creando gráfico de precios:', error);
        }
      }
      
      // EJECUCIÓN FORZADA PARA TESTING
      setTimeout(() => {
        console.log('🚀 EJECUCIÓN FORZADA DE actualizarSelects después de 2 segundos...');
        actualizarSelects();
      }, 2000);
      
    </script>
  </body>
</html>