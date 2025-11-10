<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparadora | CompareWare</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode: 'class' }
    </script>
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
      
      /* Utilidad para limitar texto a 2 líneas */
      .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    </style>
</head>

<body>
  <div class="relative flex size-full min-h-screen flex-col bg-slate-50 dark:bg-gray-900 group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
    <div class="layout-container flex h-full grow flex-col">
    <div class="layout-container flex h-full grow flex-col">
      <!-- Header -->
      <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#e7edf4] dark:border-b-gray-700 px-10 py-3 bg-slate-50 dark:bg-gray-800">
        <div class="flex items-center gap-4 text-[#0d141c] dark:text-white">
          <div class="size-4">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"
                fill="currentColor"
              ></path>
            </svg>
          </div>
          <a href="{{ route('home') }}" class="logo-link text-[#0d141c] dark:text-white text-lg font-bold leading-tight tracking-[-0.015em] hover:underline">CompareWare</a>
        </div>
        <div class="flex flex-1 justify-end gap-8">
          <div class="flex items-center gap-9">
            <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="{{ route('marcas') }}">Marcas</a>
            <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="{{ route('chatbot') }}">Contacto</a>
          </div>
          <div class="flex gap-2">
            <!-- Botón de tema -->
            <button
              id="theme-toggle"
              class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 w-10 bg-[#e7edf4] dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
              <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256" class="text-amber-500 dark:text-amber-300">
                <path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path>
              </svg>
            </button>

            @auth
              <!-- Usuario autenticado -->
              <span class="text-[#0d141c] dark:text-white text-sm font-medium">Hola, {{ Auth::user()->name }}</span>
              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button
                  type="submit"
                  class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-red-600 text-white gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5 hover:bg-red-700 transition-colors"
                >
                  Cerrar sesión
                </button>
              </form>
            @else
              <!-- Usuario no autenticado -->
              <a
                href="{{ route('login') }}"
                class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#0d80f2] text-white gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5 hover:bg-blue-600 transition-colors"
              >
                Iniciar sesión
              </a>
              <a
                href="{{ route('register') }}"
                class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#e7edf4] dark:bg-gray-700 text-[#0d141c] dark:text-white gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              >
                Registrarse
              </a>
            @endauth

            @auth
              @if(Auth::user()->isAdmin())
                <div class="flex gap-2">
                  <a
                    href="{{ route('admin.access') }}"
                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-purple-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-purple-700 transition-colors"
                  >
                    <span class="truncate">🎯 Área Admin</span>
                  </a>
                  <a
                    href="{{ route('panel.admin') }}"
                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-indigo-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-indigo-700 transition-colors"
                  >
                    <span class="truncate">🚀 Directo</span>
                  </a>
                </div>
              @endif
            @endauth
          </div>
        </div>
      </header>

      <div class="px-40 flex flex-1 justify-center py-5">
        <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
          
          <!-- Hero Section -->
          <div id="hero-section" class="rounded-xl p-8 mb-8 flex flex-col md:flex-row items-center justify-between shadow-lg bg-[#1e293b] dark:bg-gray-800">
            <div class="text-center md:text-left mb-6 md:mb-0">
              <h1 class="text-white dark:text-white text-4xl font-black leading-tight tracking-[-0.033em] mb-4">
                 Comparador de Periféricos
              </h1>
              <p class="text-white dark:text-gray-300 text-base font-normal leading-normal">
                Compara especificaciones, precios y encuentra el mejor periférico para ti
              </p>
            </div>
            <div class="flex items-center gap-4">
              <div class="bg-blue-500 dark:bg-blue-600 text-white p-4 rounded-full">
                <span class="text-2xl">⚡</span>
              </div>
            </div>
          </div>

          <!-- Pestañas de Categorías -->
          <div id="categorias-tabs" class="flex gap-2 mb-6 rounded-lg p-2 bg-[#324d67] dark:bg-gray-800">
            @foreach($categorias as $categoria)
              <button class="categoria-tab px-6 py-2 rounded-lg font-bold bg-[#1e293b] dark:bg-gray-700 text-white dark:text-gray-200 hover:bg-[#3b82f6] dark:hover:bg-blue-600 transition-colors" 
                      data-categoria="{{ $categoria->id }}">
                {{ $categoria->nombre }}
              </button>
            @endforeach
          </div>

          <!-- Listas de productos por categoría -->
          @foreach($categorias as $categoria)
              <div class="lista-productos" data-categoria="{{ $categoria->id }}" style="display: none;">
                <h3 class="text-white dark:text-white text-xl font-bold mb-4">Productos en {{ $categoria->nombre }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                  @foreach($productos as $producto)
                    @if($producto->categoria_id == $categoria->id)
                      <div class="card rounded-lg overflow-hidden font-semibold bg-[#1e293b] dark:bg-gray-800 border border-[#324d67] dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-300">
                        
                        <!-- Imagen del producto -->
                        <div class="relative h-48 bg-white dark:bg-gray-700 flex items-center justify-center p-4">
                          <img 
                            src="{{ $producto->imagen_url_completa }}?v={{ time() }}" 
                            alt="{{ $producto->imagen_alt ?? $producto->nombre }}"
                            class="w-full h-full object-contain"
                            onerror="this.src='https://via.placeholder.com/300x300?text={{ urlencode($producto->nombre) }}'; console.error('Error cargando:', this.src);"
                          >
                          <!-- Badge de origen de imagen -->
                          @if($producto->imagen_source)
                            <div class="absolute top-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                              @if($producto->imagen_source === 'amazon')
                                🛒 Amazon
                              @elseif($producto->imagen_source === 'local')
                                📁 Local
                              @else
                                ✏️ Manual
                              @endif
                            </div>
                          @endif
                        </div>

                        <!-- Información del producto -->
                        <div class="p-4">
                          <div class="mb-3">
                            <h4 class="text-white dark:text-gray-100 font-bold text-base line-clamp-2 mb-1">
                              {{ $producto->nombre }}
                            </h4>
                            @if($producto->modelo)
                              <p class="text-gray-400 dark:text-gray-500 text-xs">
                                Modelo: {{ $producto->modelo }}
                              </p>
                            @endif
                          </div>
                          
                          <div class="space-y-2">
                            <div class="flex items-center justify-between">
                              <span class="text-gray-400 dark:text-gray-500 text-sm">Precio:</span>
                              <span class="text-green-400 dark:text-green-500 font-bold text-lg">
                                ${{ number_format($producto->precio, 2) }}
                              </span>
                            </div>
                            
                            @if($producto->tipo_conectividad)
                              <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-400 dark:text-gray-500">🔗</span>
                                <span class="text-gray-300 dark:text-gray-400">{{ $producto->tipo_conectividad }}</span>
                              </div>
                            @endif

                            @if($producto->marca)
                              <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-400 dark:text-gray-500">🏷️</span>
                                <span class="text-blue-400 dark:text-blue-500">{{ $producto->marca->nombre }}</span>
                              </div>
                            @endif
                            
                            @if($producto->amazon_url)
                              <div class="mt-3">
                                <a href="{{ $producto->amazon_url }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 w-full justify-center px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors duration-200">
                                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.045 18.02c.072-.116.187-.124.348-.022 3.636 2.11 7.594 3.166 11.87 3.166 2.852 0 5.668-.533 8.447-1.595l.315-.14c.138-.06.234-.1.293-.13.226-.088.39-.046.525.13.12.174.09.336-.12.48-.256.19-.6.41-1.006.654-1.244.743-2.64 1.316-4.185 1.726-1.53.406-3.045.61-4.516.61-2.265 0-4.473-.38-6.624-1.135-2.16-.755-4.043-1.8-5.648-3.144-.136-.117-.18-.235-.126-.35zm23.696-1.77c-.29-.326-.73-.326-1.31 0-.58.325-1.28.742-2.1 1.248-.824.506-1.47.868-1.934 1.08-.465.212-.87.318-1.21.318-.78 0-1.67-.24-2.67-.72-1-.48-1.84-1.02-2.52-1.63-.68-.61-1.17-1.16-1.47-1.65-.3-.49-.45-.89-.45-1.2 0-.455.135-.855.405-1.2.27-.345.675-.57 1.215-.675.54-.105 1.17-.105 1.89 0 .72.105 1.47.315 2.25.63.78.315 1.485.735 2.115 1.26.63.525 1.11 1.125 1.44 1.8.33.675.495 1.425.495 2.25 0 .705-.135 1.365-.405 1.98-.27.615-.645 1.155-1.125 1.62s-1.035.84-1.665 1.125c-.63.285-1.305.465-2.025.54-.72.075-1.44.075-2.16 0-.72-.075-1.41-.24-2.07-.495-.66-.255-1.245-.6-1.755-1.035-.51-.435-.915-.96-1.215-1.575-.3-.615-.45-1.32-.45-2.115 0-1.02.225-1.935.675-2.745.45-.81 1.065-1.485 1.845-2.025.78-.54 1.665-.945 2.655-1.215.99-.27 2.01-.405 3.06-.405 1.5 0 2.88.255 4.14.765 1.26.51 2.31 1.2 3.15 2.07.84.87 1.47 1.875 1.89 3.015.42 1.14.63 2.34.63 3.6 0 1.29-.21 2.49-.63 3.6-.42 1.11-1.02 2.07-1.8 2.88-.78.81-1.71 1.44-2.79 1.89-1.08.45-2.25.675-3.51.675-1.44 0-2.79-.27-4.05-.81-1.26-.54-2.34-1.29-3.24-2.25-.9-.96-1.59-2.1-2.07-3.42-.48-1.32-.72-2.76-.72-4.32 0-1.65.255-3.165.765-4.545.51-1.38 1.23-2.565 2.16-3.555.93-.99 2.04-1.755 3.33-2.295 1.29-.54 2.7-.81 4.23-.81 1.65 0 3.165.3 4.545.9 1.38.6 2.565 1.425 3.555 2.475.99 1.05 1.755 2.28 2.295 3.69.54 1.41.81 2.925.81 4.545 0 .84-.075 1.65-.225 2.43-.15.78-.39 1.515-.72 2.205-.33.69-.75 1.32-1.26 1.89-.51.57-1.11 1.035-1.8 1.395-.69.36-1.455.54-2.295.54-.99 0-1.86-.225-2.61-.675-.75-.45-1.32-1.065-1.71-1.845-.39-.78-.585-1.68-.585-2.7z"/>
                                  </svg>
                                  Ver en Amazon
                                </a>
                              </div>
                            @endif
                          </div>
                        </div>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
            @endforeach

          <!-- Selectores para comparación -->
          <div class="bg-[#1e293b] dark:bg-gray-800 rounded-xl p-6 mb-8 shadow-lg">
            <h2 class="text-white dark:text-white text-2xl font-bold leading-tight tracking-[-0.015em] pb-4 border-b border-[#324d67] dark:border-gray-700 mb-6">
              Selecciona dos periféricos para comparar
            </h2>
            <div class="flex gap-6 mb-6">
              <div class="flex-1">
                <label for="periferico1" class="block mb-3 font-semibold text-white dark:text-gray-200 flex items-center gap-2">
                  <span class="bg-blue-500 dark:bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm">1</span>
                  Primer Periférico
                </label>
                <select id="periferico1" class="select-dark w-full rounded-xl px-4 py-3 border border-[#324d67] dark:border-gray-700 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1172d4] dark:focus:ring-blue-500 focus:border-transparent transition-all">
                  <option value="">-- Selecciona --</option>
                </select>
              </div>
              <div class="flex-1">
                <label for="periferico2" class="block mb-3 font-semibold text-white dark:text-gray-200 flex items-center gap-2">
                  <span class="bg-purple-500 dark:bg-purple-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm">2</span>
                  Segundo Periférico
                </label>
                <select id="periferico2" class="select-dark w-full rounded-xl px-4 py-3 border border-[#324d67] dark:border-gray-700 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1172d4] dark:focus:ring-blue-500 focus:border-transparent transition-all">
                  <option value="">-- Selecciona --</option>
                </select>
              </div>
            </div>
            <div class="flex justify-center">
              <button id="comparar-btn" class="bg-gradient-to-r from-[#1172d4] to-[#3b82f6] hover:from-[#0f5ebd] hover:to-[#2563eb] dark:from-blue-600 dark:to-blue-500 dark:hover:from-blue-700 dark:hover:to-blue-600 text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all">
                 Comparar Periféricos
              </button>
            </div>
          </div>

          <!-- Resultado de la comparación -->
          <div id="resultado-comparacion"></div>

        </div>
      </div>
    </div>

    <script>
      // ========== DEBUG INICIAL ==========
      console.log('✅ Script iniciado');
      console.log('📦 Total productos:', @json(count($productos)));
      
      // Variables globales con debugging
      const productos = @json($productos);
      const categorias = @json($categorias);
      let isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
      
      console.log('✅ Productos cargados:', productos.length);
      
      // Debug: Verificar URLs de imágenes
      console.log('🔍 Debug de imágenes:');
      productos.forEach((p, index) => {
        console.log(`${index + 1}. ${p.nombre}:`);
        console.log(`   - imagen_url_completa: ${p.imagen_url_completa || 'NO EXISTE'}`);
        console.log(`   - imagen_path: ${p.imagen_path || 'NO EXISTE'}`);
        console.log(`   - imagen_source: ${p.imagen_source || 'NO EXISTE'}`);
      });
      
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
            
            // Crear opciones con más información y data attribute para conversión de moneda
            const optionText = p.nombre + ' - $' + p.precio + ' MXN';
            const option1 = document.createElement('option');
            option1.value = p.id;
            option1.textContent = optionText;
            option1.dataset.priceMxn = p.precio; // Guardar precio original en MXN
            
            const option2 = document.createElement('option');
            option2.value = p.id;
            option2.textContent = optionText;
            option2.dataset.priceMxn = p.precio; // Guardar precio original en MXN
            
            select1.appendChild(option1);
            select2.appendChild(option2);
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
            '<div class="bg-[#1e293b] dark:bg-gray-800 rounded-xl p-8 text-center">' +
              '<div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>' +
              '<h3 class="text-white dark:text-white text-xl font-bold mb-2">🔍 Comparando Productos</h3>' +
              '<p class="text-gray-300 dark:text-gray-400">Obteniendo especificaciones detalladas y datos de Amazon...</p>' +
              '<div class="mt-4 bg-[#0f172a] dark:bg-gray-900 rounded-lg p-3">' +
                '<div class="text-sm text-gray-400 dark:text-gray-500">📊 Datos locales... <span class="animate-pulse">⏳</span></div>' +
                '<div class="text-sm text-green-400 dark:text-green-500">🚀 Especificaciones Detalladas (1°)... <span class="animate-pulse">⏳</span></div>' +
                '<div class="text-sm text-orange-400 dark:text-orange-500">🛒 Amazon (2°)... <span class="animate-pulse">⏳</span></div>' +
                '<div class="text-xs text-gray-500 dark:text-gray-600 mt-2">Solo 2 secciones: Especificaciones → Amazon</div>' +
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

            // 2. 🚀 PRIMERO: Obtener especificaciones técnicas de la nueva API implementada
            let nodeApiData = null;
            console.log('🚀 PRIMERA API - Consultando especificaciones avanzadas...');
            
            try {
              const product1Name = localData.periferico1.nombre;
              const product2Name = localData.periferico2.nombre;
              
              console.log('🔍 Comparando con Node API:', product1Name, 'vs', product2Name);
              
              // Actualizar loading con información PRIORITARIA del Node API
              document.getElementById('resultado-comparacion').innerHTML = 
                '<div class="bg-[#1e293b] dark:bg-gray-800 rounded-xl p-8 text-center">' +
                  '<div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-500 mx-auto mb-4"></div>' +
                  '<h3 class="text-white dark:text-white text-xl font-bold mb-2">🚀 Nueva API Implementada</h3>' +
                  '<p class="text-gray-300 dark:text-gray-400">Obteniendo especificaciones detalladas (se mostrará PRIMERO)...</p>' +
                  '<div class="mt-4 bg-[#0f172a] dark:bg-gray-900 rounded-lg p-3 space-y-2">' +
                    '<div class="text-sm text-blue-400 dark:text-blue-500">📊 Comparación local completada ✅</div>' +
                    '<div class="text-sm text-green-400 dark:text-green-500">🚀 ESPECIFICACIONES: "' + product1Name + '" vs "' + product2Name + '"</div>' +
                    '<div class="text-xs text-gray-400 dark:text-gray-500">Vista simplificada: 1° Especificaciones → 2° Amazon</div>' +
                  '</div>' +
                '</div>';
              
              const nodeResponse = await fetch(`/test-node-compare/${encodeURIComponent(product1Name)}/${encodeURIComponent(product2Name)}`);
              
              if (nodeResponse.ok) {
                nodeApiData = await nodeResponse.json();
                console.log('✅ Node API data received:', nodeApiData);
              } else {
                console.warn('⚠️ Node API not available or error:', nodeResponse.status);
              }
            } catch (nodeError) {
              console.warn('⚠️ Node API fallback to legacy APIs:', nodeError.message);
            }

            // 3. Obtener especificaciones técnicas de los productos (fallback)
            let specsData = null;
            console.log('🔧 Obteniendo especificaciones técnicas...');
            
            // 4. 🛒 SEGUNDO: Obtener datos de Amazon como información complementaria
            let amazonData = null;
            let priceHistoryData = null;
            console.log('🛒 SEGUNDA API - Obteniendo información de Amazon...');
            
            try {
              // Obtener los nombres de los productos para buscar
              const product1 = localData.periferico1;
              const product2 = localData.periferico2;
              
              console.log('🔍 Buscando especificaciones y datos de Amazon:');
              console.log('- Producto 1:', product1.nombre);
              console.log('- Producto 2:', product2.nombre);
              
              // Actualizar loading con nombres específicos
              document.getElementById('resultado-comparacion').innerHTML = 
                '<div class="bg-[#1e293b] dark:bg-gray-800 rounded-xl p-8 text-center">' +
                  '<div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-500 mx-auto mb-4"></div>' +
                  '<h3 class="text-white dark:text-white text-xl font-bold mb-2">� Consultando APIs Externas</h3>' +
                  '<p class="text-gray-300 dark:text-gray-400">Obteniendo especificaciones técnicas, precios de Amazon e historial de precios...</p>' +
                  '<div class="mt-4 bg-[#0f172a] dark:bg-gray-900 rounded-lg p-3 space-y-2">' +
                    '<div class="text-sm text-blue-400 dark:text-blue-500">📊 Comparación local completada ✅</div>' +
                    '<div class="text-sm text-green-400 dark:text-green-500">🔧 Especificaciones: "' + product1.nombre + '"</div>' +
                    '<div class="text-sm text-green-400 dark:text-green-500">� Especificaciones: "' + product2.nombre + '"</div>' +
                    '<div class="text-sm text-orange-400 dark:text-orange-500">🛒 Amazon: "' + product1.nombre + '"</div>' +
                    '<div class="text-sm text-orange-400 dark:text-orange-500">� Amazon: "' + product2.nombre + '"</div>' +
                  '</div>' +
                '</div>';
              
              // Buscar especificaciones, productos en Amazon e historial de precios en paralelo
              console.log('🆔 IDs de productos para Amazon API:', product1.id, product2.id);
              
              const [specs1Response, specs2Response, amazon1Response, amazon2Response, priceHistory1Response, priceHistory2Response] = await Promise.all([
                fetch(`/test-specs-api/${encodeURIComponent(product1.nombre)}`),
                fetch(`/test-specs-api/${encodeURIComponent(product2.nombre)}`),
                // Usar nueva API específica para productos de la BDD
                fetch(`/amazon-for-product/${product1.id}`),
                fetch(`/amazon-for-product/${product2.id}`),
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
                console.log('🔍 Estructura de specs del Producto 1:', JSON.stringify(specs1Data.result?.specifications, null, 2));
                console.log('🔍 Estructura de specs del Producto 2:', JSON.stringify(specs2Data.result?.specifications, null, 2));
                
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
              
              // Procesar datos de Amazon (nueva API específica)
              if (amazon1Response.ok && amazon2Response.ok) {
                const amazon1Data = await amazon1Response.json();
                const amazon2Data = await amazon2Response.json();
                
                console.log('✅ Datos de Amazon obtenidos exitosamente (API específica):');
                console.log('- Amazon Producto 1:', amazon1Data);
                console.log('- Amazon Producto 2:', amazon2Data);
                
                // Estructurar datos para el display (nueva estructura de API específica)
                amazonData = {
                  success: true,
                  amazon_data: {
                    // Nueva estructura: amazon_results.data.products en lugar de result.data.products
                    product1: amazon1Data.amazon_results?.data?.products?.slice(0, 3) || [],
                    product2: amazon2Data.amazon_results?.data?.products?.slice(0, 3) || []
                  },
                  db_products: {
                    product1: amazon1Data.db_product || null,
                    product2: amazon2Data.db_product || null
                  }
                };
                
                console.log('🏷️ Productos de BDD vinculados:');
                console.log('- BDD Producto 1:', amazon1Data.db_product);
                console.log('- BDD Producto 2:', amazon2Data.db_product);
              } else {
                console.warn('⚠️ Error en Amazon API específica');
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
            }            // 5. Mostrar resultado combinado con todas las APIs
            console.log('🚀 DATOS FINALES ANTES DE MOSTRAR:');
            console.log('📊 localData:', localData);
            console.log('� nodeApiData:', nodeApiData);
            console.log('�🛒 amazonData:', amazonData);
            console.log('🔧 specsData:', specsData);
            console.log('📈 priceHistoryData:', priceHistoryData);
            
            mostrarResultadoComparacion(localData, amazonData, specsData, priceHistoryData, nodeApiData);
            
          } catch (error) {
            console.error('❌ Error en la comparación:', error);
            document.getElementById('resultado-comparacion').innerHTML = 
              '<div class="bg-red-500 dark:bg-red-600 text-white p-4 rounded-lg">' +
                '<h4 class="font-bold mb-2">❌ Error en la Comparación</h4>' +
                '<p>Ocurrió un problema al obtener los datos: ' + error.message + '</p>' +
                '<div class="mt-2 text-sm opacity-80">Por favor, inténtalo de nuevo en unos momentos.</div>' +
              '</div>';
          }
        });
      });

      function mostrarResultadoComparacion(data, amazonData = null, specsData = null, priceHistoryData = null, nodeApiData = null) {
        const resultado = document.getElementById('resultado-comparacion');
        
        console.log('🎨 Generando resultado visual...');
        console.log('- Datos locales:', data);
        console.log('- Node API data:', nodeApiData);
        console.log('- Datos Amazon:', amazonData);
        console.log('- Datos Especificaciones:', specsData);
        console.log('- Datos Historial de Precios:', priceHistoryData);
        
        // Crear container principal
        const container = document.createElement('div');
        container.className = 'bg-[#1e293b] dark:bg-gray-800 rounded-xl p-6 mt-6 shadow-lg';
        
        // Título
        const title = document.createElement('h3');
        title.className = 'text-white dark:text-white text-2xl font-bold mb-4 text-center';
        title.innerHTML = '📊 Comparación Completa ' + (amazonData ? '<span class="text-orange-400 dark:text-orange-500">(🛒 + Amazon)</span>' : '');
        container.appendChild(title);
        
        // Indicador de tasa de cambio si hay datos de Amazon
        if (amazonData) {
          const exchangeRate = document.createElement('div');
          exchangeRate.className = 'text-center mb-4 text-sm text-gray-400 dark:text-gray-500 bg-gray-800/50 dark:bg-gray-900/50 p-2 rounded-lg';
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
        
        // 🚀 PRIMERA SECCIÓN: Especificaciones Técnicas Detalladas
        if (nodeApiData && nodeApiData.success) {
          const nodeApiCard = crearSeccionNodeApi(nodeApiData);
          container.appendChild(nodeApiCard);
        }
        
        // � SEGUNDO: Amazon API - Información de precios y productos
        if (amazonData && amazonData.success) {
          const amazonCard = crearSeccionAmazon(amazonData);
          container.appendChild(amazonCard);
        }
        
        // � AL FINAL: Análisis de comparación básico
        // ❌ ANÁLISIS BÁSICO ELIMINADO - Solo especificaciones detalladas + Amazon
        
        // Limpiar y mostrar
        resultado.innerHTML = '';
        resultado.appendChild(container);
        
        console.log('✅ Resultado visual generado correctamente');
      }
      
      function crearTarjetaProducto(producto, color, amazonInfo = null, specsInfo = null, priceHistoryInfo = null) {
        console.log(`🏗️ Creando tarjeta para: ${producto.nombre}`);
        console.log(`🖼️ imagen_url_completa:`, producto.imagen_url_completa);
        console.log(`🖼️ imagen_path:`, producto.imagen_path);
        console.log(`🖼️ imagen_source:`, producto.imagen_source);
        console.log(`🏗️ Amazon Info:`, amazonInfo);
        console.log(`🏗️ Specs Info:`, specsInfo);
        const card = document.createElement('div');
        card.className = 'bg-[#0f172a] dark:bg-gray-900 p-6 rounded-lg border border-' + color + '-500';
        
        // Header del producto
        const header = document.createElement('div');
        header.className = 'text-center mb-4';
        
        const name = document.createElement('h4');
        name.className = 'font-bold text-xl mb-2 text-' + color + '-400 dark:text-' + color + '-500';
        name.textContent = producto.nombre;
        header.appendChild(name);
        
        const price = document.createElement('div');
        price.className = 'text-2xl font-bold text-green-400 dark:text-green-500 mb-2';
        price.textContent = '$' + parseFloat(producto.precio).toLocaleString() + ' MXN';
        price.dataset.priceMxn = producto.precio; // Guardar precio original para conversión
        header.appendChild(price);
        
        // Badge de modelo si existe
        if (producto.modelo) {
          const model = document.createElement('div');
          model.className = 'text-sm text-gray-400 dark:text-gray-500 bg-gray-800 dark:bg-gray-700 px-3 py-1 rounded-full inline-block';
          model.textContent = '📦 ' + producto.modelo;
          header.appendChild(model);
        }
        
        card.appendChild(header);
        
        // Imagen del producto (si existe)
        console.log(`🔍 Verificando imagen: imagen_url_completa=${producto.imagen_url_completa ? 'EXISTS' : 'NULL'}, amazonPhoto=${amazonInfo && amazonInfo.length > 0 && amazonInfo[0].product_photo ? 'EXISTS' : 'NULL'}`);
        if (producto.imagen_url_completa || (amazonInfo && amazonInfo.length > 0 && amazonInfo[0].product_photo)) {
          const imageContainer = document.createElement('div');
          imageContainer.className = 'mb-4 rounded-lg overflow-hidden bg-white dark:bg-gray-800 p-4';
          
          const img = document.createElement('img');
          img.className = 'w-full h-64 object-contain mx-auto';
          
          // Prioridad: imagen_url_completa del producto, luego imagen de Amazon
          if (producto.imagen_url_completa) {
            img.src = producto.imagen_url_completa;
            img.alt = producto.imagen_alt || producto.nombre;
            console.log('🖼️ Cargando imagen propia:', producto.imagen_url_completa);
          } else if (amazonInfo && amazonInfo.length > 0 && amazonInfo[0].product_photo) {
            img.src = amazonInfo[0].product_photo;
            img.alt = amazonInfo[0].product_title || producto.nombre;
            console.log('🖼️ Cargando imagen de Amazon:', amazonInfo[0].product_photo);
          }
          
          // Badge de origen de imagen
          const sourceBadge = document.createElement('div');
          sourceBadge.className = 'text-xs text-center mt-2 text-gray-400 dark:text-gray-500';
          if (producto.imagen_source === 'amazon') {
            sourceBadge.innerHTML = '🛒 <span class="text-orange-400">Imagen de Amazon</span>';
          } else if (producto.imagen_source === 'local') {
            sourceBadge.innerHTML = '📁 <span class="text-blue-400">Imagen local</span>';
          } else if (amazonInfo && amazonInfo.length > 0) {
            sourceBadge.innerHTML = '🛒 <span class="text-orange-400">Imagen de Amazon (API)</span>';
          }
          
          // Manejo de errores de carga
          img.onerror = function() {
            console.warn('⚠️ Error cargando imagen, usando placeholder');
            img.src = 'https://via.placeholder.com/300x300?text=' + encodeURIComponent(producto.nombre);
          };
          
          imageContainer.appendChild(img);
          imageContainer.appendChild(sourceBadge);
          card.appendChild(imageContainer);
        }
        
        // Información del producto
        const info = document.createElement('div');
        info.className = 'space-y-3 mb-4';
        
        // Marca
        if (producto.marca_nombre) {
          const brand = document.createElement('div');
          brand.className = 'flex justify-between text-sm';
          brand.innerHTML = '<span class="text-gray-400 dark:text-gray-500">🏷️ Marca:</span><span class="text-white dark:text-gray-200 font-medium">' + producto.marca_nombre + '</span>';
          info.appendChild(brand);
        }
        
        // Categoría
        if (producto.categoria_nombre) {
          const category = document.createElement('div');
          category.className = 'flex justify-between text-sm';
          category.innerHTML = '<span class="text-gray-400 dark:text-gray-500">📂 Categoría:</span><span class="text-blue-300 dark:text-blue-400">' + producto.categoria_nombre.trim() + '</span>';
          info.appendChild(category);
        }
        
        // Conectividad
        if (producto.tipo_conectividad) {
          const connectivity = document.createElement('div');
          connectivity.className = 'flex justify-between text-sm';
          connectivity.innerHTML = '<span class="text-gray-400 dark:text-gray-500">🔗 Conectividad:</span><span class="text-green-300 dark:text-green-400">' + producto.tipo_conectividad + '</span>';
          info.appendChild(connectivity);
        }
        
        // Precio en pesos mexicanos
        const mxnPrice = document.createElement('div');
        mxnPrice.className = 'bg-green-900/30 dark:bg-green-900/20 border border-green-500/30 dark:border-green-600/30 p-3 rounded-lg mt-3';
        const localMxnPrice = parseFloat(producto.precio).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        mxnPrice.innerHTML = '<div class="text-center"><span class="text-gray-400 dark:text-gray-500 text-sm">💰 Precio Local</span><br><span class="text-green-400 dark:text-green-500 text-xl font-bold" data-price-mxn="' + producto.precio + '">$' + localMxnPrice + ' MXN</span></div>';
        info.appendChild(mxnPrice);
        
        card.appendChild(info);
        
        // Información de Amazon si está disponible
        if (amazonInfo && amazonInfo.length > 0) {
          console.log('📦 Mostrando info Amazon para:', producto.nombre);
          console.log('🛒 Amazon products:', amazonInfo);
          
          const amazonSection = document.createElement('div');
          amazonSection.className = 'border-t border-orange-500/30 pt-4 mt-4 bg-orange-900/10 dark:bg-orange-900/5 rounded-lg p-4';
          
          const amazonTitle = document.createElement('div');
          amazonTitle.className = 'text-orange-400 dark:text-orange-500 font-semibold mb-3 flex items-center justify-between';
          amazonTitle.innerHTML = '<span>🛒 Disponible en Amazon</span><span class="text-xs bg-orange-500 dark:bg-orange-600 text-white px-2 py-1 rounded">EN VIVO</span>';
          amazonSection.appendChild(amazonTitle);
          
          const amazonProduct = amazonInfo[0]; // Primer producto del array
          console.log('🎯 Producto Amazon seleccionado:', amazonProduct);
          
          // Título del producto Amazon
          if (amazonProduct.product_title) {
            const productTitle = document.createElement('div');
            productTitle.className = 'text-sm text-gray-300 dark:text-gray-400 mb-3 font-medium line-clamp-2 border-l-2 border-orange-500 pl-3';
            productTitle.textContent = amazonProduct.product_title.substring(0, 120) + (amazonProduct.product_title.length > 120 ? '...' : '');
            amazonSection.appendChild(productTitle);
          }
          
          // Precios Amazon - USD y MXN
          if (amazonProduct.product_price) {
            const priceConversion = convertUsdToMxn(amazonProduct.product_price);
            const amazonPriceSection = document.createElement('div');
            amazonPriceSection.className = 'bg-orange-900/30 dark:bg-orange-900/20 border border-orange-500/40 dark:border-orange-600/30 p-3 rounded-lg mb-3';
            
            if (priceConversion) {
              amazonPriceSection.innerHTML = 
                '<div class="text-center space-y-1">' +
                  '<div class="text-orange-300 dark:text-orange-400 text-sm">💰 Precio en Amazon</div>' +
                  '<div class="text-orange-400 dark:text-orange-500 font-bold text-lg">' + priceConversion.mxnFormatted + '</div>' +
                  '<div class="text-gray-400 dark:text-gray-500 text-xs">(' + priceConversion.usdFormatted + ')</div>' +
                '</div>';
            } else {
              amazonPriceSection.innerHTML = '<div class="text-center"><span class="text-orange-400 dark:text-orange-500 font-bold">' + amazonProduct.product_price + '</span></div>';
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
                  '<span class="bg-red-500 dark:bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold">' +
                  '🔥 ' + discount + '% DESCUENTO</span><br>' +
                  '<span class="text-gray-500 dark:text-gray-600 line-through text-sm">Antes: ' + originalConversion.mxnFormatted + '</span>';
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
        
        // ========================================
        // SECCIÓN DE VIDEOS DE YOUTUBE
        // ========================================
        console.log('🎬 Agregando sección de YouTube para:', producto.nombre);
        
        const youtubeSection = document.createElement('div');
        youtubeSection.className = 'border-t border-red-500/30 pt-4 mt-4 bg-red-900/10 rounded-lg p-4';
        
        const youtubeTitle = document.createElement('div');
        youtubeTitle.className = 'text-red-400 font-semibold mb-4 flex items-center justify-between cursor-pointer';
        youtubeTitle.innerHTML = 
          '<span>🎬 Reviews en Video</span>' +
          '<button class="text-xs bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors" onclick="cargarVideosYouTube(\'' + producto.nombre + '\', \'youtube_content_' + producto.id + '\')">▶ Cargar Videos</button>';
        
        const youtubeContent = document.createElement('div');
        youtubeContent.id = 'youtube_content_' + producto.id;
        youtubeContent.className = 'mt-3';
        youtubeContent.innerHTML = `
          <div class="text-center text-gray-400 py-6 bg-gray-800/30 rounded-lg">
            <div class="text-4xl mb-2">🎥</div>
            <p class="text-sm">Haz click en "Cargar Videos" para ver reviews de YouTube</p>
            <p class="text-xs text-gray-500 mt-2">Se cargarán reviews, unboxings y tutoriales</p>
          </div>
        `;
        
        youtubeSection.appendChild(youtubeTitle);
        youtubeSection.appendChild(youtubeContent);
        card.appendChild(youtubeSection);
        
        // ========================================
        // SECCIÓN DE GOOGLE SHOPPING (COMPARACIÓN DE PRECIOS)
        // ========================================
        console.log('🛒 Agregando sección de Google Shopping para:', producto.nombre);
        
        const shoppingSection = document.createElement('div');
        shoppingSection.className = 'border-t border-blue-500/30 pt-4 mt-4 bg-blue-900/10 rounded-lg p-4';
        
        const shoppingTitle = document.createElement('div');
        shoppingTitle.className = 'text-blue-400 font-semibold mb-4 flex items-center justify-between cursor-pointer';
        shoppingTitle.innerHTML = 
          '<span>🛒 Comparar Precios en Tiendas</span>' +
          '<button class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors" onclick="cargarPreciosTiendas(\'' + producto.nombre + '\', \'shopping_content_' + producto.id + '\')">💰 Ver Precios</button>';
        
        const shoppingContent = document.createElement('div');
        shoppingContent.id = 'shopping_content_' + producto.id;
        shoppingContent.className = 'mt-3';
        shoppingContent.innerHTML = `
          <div class="text-center text-gray-400 py-6 bg-gray-800/30 rounded-lg">
            <div class="text-4xl mb-2">🏪</div>
            <p class="text-sm">Haz click en "Ver Precios" para comparar en múltiples tiendas</p>
            <p class="text-xs text-gray-500 mt-2">Amazon, Mercado Libre, Liverpool, y más...</p>
          </div>
        `;
        
        shoppingSection.appendChild(shoppingTitle);
        shoppingSection.appendChild(shoppingContent);
        card.appendChild(shoppingSection);
        
        // ========================================
        // BOTÓN DE COMPRA EN AMAZON (desde BD)
        // ========================================
        if (producto.amazon_url) {
          console.log('🛒 Agregando botón de Amazon desde BD para:', producto.nombre);
          
          const amazonButtonSection = document.createElement('div');
          amazonButtonSection.className = 'border-t border-orange-500/30 pt-4 mt-4';
          
          const amazonButton = document.createElement('a');
          amazonButton.href = producto.amazon_url;
          amazonButton.target = '_blank';
          amazonButton.rel = 'noopener noreferrer';
          amazonButton.className = 'flex items-center justify-center gap-3 w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-center py-4 px-6 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-xl hover:shadow-2xl';
          amazonButton.innerHTML = `
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M.045 18.02c.072-.116.187-.124.348-.022 3.636 2.11 7.594 3.166 11.87 3.166 2.852 0 5.668-.533 8.447-1.595l.315-.14c.138-.06.234-.1.293-.13.226-.088.39-.046.525.13.12.174.09.336-.12.48-.256.19-.6.41-1.006.654-1.244.743-2.64 1.316-4.185 1.726-1.53.406-3.045.61-4.516.61-2.265 0-4.473-.38-6.624-1.135-2.16-.755-4.043-1.8-5.648-3.144-.136-.117-.18-.235-.126-.35zm23.696-1.77c-.29-.326-.73-.326-1.31 0-.58.325-1.28.742-2.1 1.248-.824.506-1.47.868-1.934 1.08-.465.212-.87.318-1.21.318-.78 0-1.67-.24-2.67-.72-1-.48-1.84-1.02-2.52-1.63-.68-.61-1.17-1.16-1.47-1.65-.3-.49-.45-.89-.45-1.2 0-.455.135-.855.405-1.2.27-.345.675-.57 1.215-.675.54-.105 1.17-.105 1.89 0 .72.105 1.47.315 2.25.63.78.315 1.485.735 2.115 1.26.63.525 1.11 1.125 1.44 1.8.33.675.495 1.425.495 2.25 0 .705-.135 1.365-.405 1.98-.27.615-.645 1.155-1.125 1.62s-1.035.84-1.665 1.125c-.63.285-1.305.465-2.025.54-.72.075-1.44.075-2.16 0-.72-.075-1.41-.24-2.07-.495-.66-.255-1.245-.6-1.755-1.035-.51-.435-.915-.96-1.215-1.575-.3-.615-.45-1.32-.45-2.115 0-1.02.225-1.935.675-2.745.45-.81 1.065-1.485 1.845-2.025.78-.54 1.665-.945 2.655-1.215.99-.27 2.01-.405 3.06-.405 1.5 0 2.88.255 4.14.765 1.26.51 2.31 1.2 3.15 2.07.84.87 1.47 1.875 1.89 3.015.42 1.14.63 2.34.63 3.6 0 1.29-.21 2.49-.63 3.6-.42 1.11-1.02 2.07-1.8 2.88-.78.81-1.71 1.44-2.79 1.89-1.08.45-2.25.675-3.51.675-1.44 0-2.79-.27-4.05-.81-1.26-.54-2.34-1.29-3.24-2.25-.9-.96-1.59-2.1-2.07-3.42-.48-1.32-.72-2.76-.72-4.32 0-1.65.255-3.165.765-4.545.51-1.38 1.23-2.565 2.16-3.555.93-.99 2.04-1.755 3.33-2.295 1.29-.54 2.7-.81 4.23-.81 1.65 0 3.165.3 4.545.9 1.38.6 2.565 1.425 3.555 2.475.99 1.05 1.755 2.28 2.295 3.69.54 1.41.81 2.925.81 4.545 0 .84-.075 1.65-.225 2.43-.15.78-.39 1.515-.72 2.205-.33.69-.75 1.32-1.26 1.89-.51.57-1.11 1.035-1.8 1.395-.69.36-1.455.54-2.295.54-.99 0-1.86-.225-2.61-.675-.75-.45-1.32-1.065-1.71-1.845-.39-.78-.585-1.68-.585-2.7z"/>
            </svg>
            <span>🛒 Comprar en Amazon</span>
          `;
          
          amazonButtonSection.appendChild(amazonButton);
          card.appendChild(amazonButtonSection);
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
      
      function crearSeccionNodeApi(nodeApiData) {
        const nodeSection = document.createElement('div');
        nodeSection.className = 'bg-green-900/20 border border-green-500/30 p-6 rounded-lg mt-6';
        
        const title = document.createElement('h5');
        title.className = 'text-green-400 text-xl font-bold mb-4 flex items-center gap-2';
        title.innerHTML = '🚀 Especificaciones Técnicas Detalladas <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">PRIMERA SECCIÓN</span>';
        nodeSection.appendChild(title);
        
        if (!nodeApiData.comparison) {
          const noData = document.createElement('div');
          noData.className = 'text-center py-4 text-gray-400';
          noData.innerHTML = '⚠️ No se pudieron obtener datos de las APIs externas';
          nodeSection.appendChild(noData);
          return nodeSection;
        }
        
        const comparison = nodeApiData.comparison;
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 gap-6';
        
        // Producto 1
        if (comparison.product1) {
          const prod1Card = crearTarjetaNodeApi(comparison.product1, 'blue', '1');
          grid.appendChild(prod1Card);
        }
        
        // Producto 2
        if (comparison.product2) {
          const prod2Card = crearTarjetaNodeApi(comparison.product2, 'purple', '2');
          grid.appendChild(prod2Card);
        }
        
        nodeSection.appendChild(grid);
        
        // Resumen de especificaciones técnicas lado a lado
        if (comparison.product1?.specs && comparison.product2?.specs) {
          const specsComparison = crearComparacionSpecs(comparison.product1.specs, comparison.product2.specs);
          nodeSection.appendChild(specsComparison);
        }
        
        return nodeSection;
      }
      
      function crearTarjetaNodeApi(productData, color, number) {
        const card = document.createElement('div');
        card.className = `bg-gray-800/50 border border-${color}-500/30 p-4 rounded-lg`;
        
        const header = document.createElement('div');
        header.className = 'mb-4';
        
        const title = document.createElement('h6');
        title.className = `text-${color}-400 font-bold text-lg mb-2 flex items-center gap-2`;
        title.innerHTML = `<span class="bg-${color}-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm">${number}</span> ${productData.name}`;
        header.appendChild(title);
        
        card.appendChild(header);
        
        // Especificaciones técnicas
        if (productData.specs && productData.specs.data) {
          const specsSection = document.createElement('div');
          specsSection.className = 'mb-4';
          
          const specsTitle = document.createElement('div');
          specsTitle.className = 'text-gray-300 font-semibold mb-2 text-sm';
          specsTitle.innerHTML = '🔧 Especificaciones Técnicas';
          specsSection.appendChild(specsTitle);
          
          const specs = productData.specs.data;
          const specsContent = document.createElement('div');
          specsContent.className = 'text-xs text-gray-400 space-y-1';
          
          // Mostrar información general
          if (specs.specs && specs.specs.general) {
            const general = specs.specs.general;
            if (general.brand) {
              specsContent.innerHTML += `<div>🏷️ Marca: <span class="text-white">${general.brand}</span></div>`;
            }
            if (general.model) {
              specsContent.innerHTML += `<div>📦 Modelo: <span class="text-white">${general.model}</span></div>`;
            }
          }
          
          specsSection.appendChild(specsContent);
          card.appendChild(specsSection);
        }
        
        // Precios de múltiples retailers
        if (productData.prices && productData.prices.items && productData.prices.items.length > 0) {
          const pricesSection = document.createElement('div');
          pricesSection.className = 'border-t border-gray-600/50 pt-3';
          
          const pricesTitle = document.createElement('div');
          pricesTitle.className = 'text-gray-300 font-semibold mb-3 text-sm';
          pricesTitle.innerHTML = '💰 Precios en Línea';
          pricesSection.appendChild(pricesTitle);
          
          const pricesList = document.createElement('div');
          pricesList.className = 'space-y-2';
          
          // Mostrar hasta 3 precios
          const prices = productData.prices.items.slice(0, 3);
          prices.forEach(priceItem => {
            const priceDiv = document.createElement('div');
            priceDiv.className = 'bg-gray-700/30 p-2 rounded text-xs flex justify-between items-center';
            
            const retailer = document.createElement('span');
            retailer.className = 'text-gray-400';
            retailer.textContent = priceItem.retailer || 'Tienda';
            
            const price = document.createElement('span');
            price.className = 'text-green-400 font-semibold';
            price.textContent = priceItem.price || 'N/A';
            
            priceDiv.appendChild(retailer);
            priceDiv.appendChild(price);
            pricesList.appendChild(priceDiv);
          });
          
          pricesSection.appendChild(pricesList);
          card.appendChild(pricesSection);
        }
        
        return card;
      }
      
      function crearComparacionSpecs(specs1, specs2) {
        const container = document.createElement('div');
        container.className = 'mt-6 border-t border-green-500/30 pt-6';
        
        const title = document.createElement('h6');
        title.className = 'text-green-300 font-bold mb-4 text-center';
        title.innerHTML = '⚖️ Comparación de Especificaciones Técnicas';
        container.appendChild(title);
        
        const table = document.createElement('div');
        table.className = 'bg-gray-800/30 rounded-lg overflow-hidden';
        
        // Headers
        const header = document.createElement('div');
        header.className = 'grid grid-cols-3 bg-gray-700/50 p-3 text-sm font-semibold';
        header.innerHTML = `
          <div class="text-gray-300">Especificación</div>
          <div class="text-blue-300 text-center">Producto 1</div>
          <div class="text-purple-300 text-center">Producto 2</div>
        `;
        table.appendChild(header);
        
        // Normalizar especificaciones (puede venir como array o como objeto)
        const normalizeSpecs = (specsData) => {
          if (!specsData) return null;
          
          // Buscar en specifications o specs
          let specs = specsData.specifications || specsData.specs;
          if (!specs) return null;
          
          // Si specs es un array, tomar el primer elemento
          if (Array.isArray(specs)) {
            console.log('📦 Specs es un array, extrayendo primer elemento');
            return specs[0] || specs;
          }
          
          // Si ya es un objeto, retornarlo directamente
          return specs;
        };
        
        const normalizedSpecs1 = normalizeSpecs(specs1);
        const normalizedSpecs2 = normalizeSpecs(specs2);
        
        console.log('✨ Specs normalizadas:');
        console.log('- Producto 1:', normalizedSpecs1);
        console.log('- Producto 2:', normalizedSpecs2);
        console.log('📊 Tipos:', typeof normalizedSpecs1, typeof normalizedSpecs2);
        
        // Extraer especificaciones comparables agrupadas por categoría
        const categoriesMap = {};
        
        // Recopilar especificaciones del producto 1
        if (normalizedSpecs1) {
          console.log('🔑 Categorías del Producto 1:', Object.keys(normalizedSpecs1));
          Object.keys(normalizedSpecs1).forEach(category => {
            console.log(`📂 Procesando categoría "${category}":`, normalizedSpecs1[category]);
            if (normalizedSpecs1[category] && typeof normalizedSpecs1[category] === 'object') {
              if (!categoriesMap[category]) {
                categoriesMap[category] = {};
              }
              Object.keys(normalizedSpecs1[category]).forEach(spec => {
                if (!categoriesMap[category][spec]) {
                  categoriesMap[category][spec] = { val1: null, val2: null };
                }
                categoriesMap[category][spec].val1 = normalizedSpecs1[category][spec];
              });
            }
          });
        }
        
        // Recopilar especificaciones del producto 2
        if (normalizedSpecs2) {
          Object.keys(normalizedSpecs2).forEach(category => {
            if (normalizedSpecs2[category] && typeof normalizedSpecs2[category] === 'object') {
              if (!categoriesMap[category]) {
                categoriesMap[category] = {};
              }
              Object.keys(normalizedSpecs2[category]).forEach(spec => {
                if (!categoriesMap[category][spec]) {
                  categoriesMap[category][spec] = { val1: null, val2: null };
                }
                categoriesMap[category][spec].val2 = normalizedSpecs2[category][spec];
              });
            }
          });
        }
        
        // Crear filas para cada categoría y especificación
        console.log('🗺️ CategoriesMap final:', categoriesMap);
        console.log('📋 Número de categorías:', Object.keys(categoriesMap).length);
        
        Object.keys(categoriesMap).forEach(category => {
          // Header de categoría
          const categoryHeader = document.createElement('div');
          categoryHeader.className = 'bg-green-800/30 p-3 text-sm font-bold text-green-300 border-b-2 border-green-500/50';
          categoryHeader.innerHTML = `${getCategoryIcon(category)} ${getCategoryName(category)}`;
          table.appendChild(categoryHeader);
          
          // Especificaciones de la categoría
          Object.keys(categoriesMap[category]).forEach(spec => {
            const { val1, val2 } = categoriesMap[category][spec];
            
            const row = document.createElement('div');
            row.className = 'grid grid-cols-3 p-3 border-b border-gray-600/30 text-xs hover:bg-gray-700/20';
            
            const specName = document.createElement('div');
            specName.className = 'text-gray-300 font-medium capitalize pl-4';
            specName.textContent = spec.replace(/_/g, ' ');
            
            const val1Div = document.createElement('div');
            val1Div.className = 'text-center text-blue-200';
            val1Div.textContent = val1 !== null && val1 !== undefined ? (typeof val1 === 'string' ? val1 : String(val1)) : 'N/A';
            
            const val2Div = document.createElement('div');
            val2Div.className = 'text-center text-purple-200';
            val2Div.textContent = val2 !== null && val2 !== undefined ? (typeof val2 === 'string' ? val2 : String(val2)) : 'N/A';
            
            row.appendChild(specName);
            row.appendChild(val1Div);
            row.appendChild(val2Div);
            table.appendChild(row);
          });
        });
        
        container.appendChild(table);
        return container;
      }
      
      function crearSeccionAmazon(amazonData) {
        const amazonSection = document.createElement('div');
        amazonSection.className = 'bg-orange-900/20 border border-orange-500/30 p-6 rounded-lg mt-6';
        
        const title = document.createElement('h5');
        title.className = 'font-bold text-lg mb-4 text-orange-400 text-center flex items-center justify-center';
        title.innerHTML = '🛒 Información de Amazon <span class="ml-2 text-xs bg-orange-500 text-white px-2 py-1 rounded">SEGUNDA SECCIÓN</span>';
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
      
      // ========================================
      // FUNCIÓN PARA CARGAR VIDEOS DE YOUTUBE
      // ========================================
      async function cargarVideosYouTube(productName, contentId) {
        console.log('🎬 Cargando videos de YouTube para:', productName);
        
        const contentDiv = document.getElementById(contentId);
        
        // Mostrar loading
        contentDiv.innerHTML = `
          <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
            <p class="text-gray-400 mt-3">Cargando videos de YouTube...</p>
          </div>
        `;
        
        try {
          const response = await fetch('/api/youtube/search', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              product_name: productName,
              search_type: 'review',
              max_results: 3
            })
          });
          
          const data = await response.json();
          console.log('✅ Respuesta de YouTube API:', data);
          
          if (data.success && data.videos && data.videos.length > 0) {
            // Crear HTML para los videos
            let videosHTML = '';
            
            // Mostrar badge si es modo demo
            if (data.using_mock_data) {
              videosHTML += `
                <div class="mb-3 text-center">
                  <span class="bg-yellow-500 text-black text-xs px-3 py-1 rounded-full font-bold">
                    🎭 MODO DEMOSTRACIÓN
                  </span>
                </div>
              `;
            }
            
            // Grid de videos
            videosHTML += '<div class="grid grid-cols-1 gap-3">';
            
            data.videos.forEach(video => {
              videosHTML += `
                <div class="bg-gray-800/50 rounded-lg overflow-hidden hover:bg-gray-800/70 transition-colors">
                  <div class="flex gap-3 p-3">
                    <!-- Thumbnail -->
                    <div class="flex-shrink-0 w-32 h-20 relative rounded overflow-hidden group cursor-pointer">
                      <img src="${video.thumbnail}" 
                           alt="${video.title}" 
                           class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                           onclick="window.open('${video.url}', '_blank')">
                      <div class="absolute inset-0 bg-black/40 flex items-center justify-center group-hover:bg-black/60 transition-colors">
                        <span class="text-white text-2xl">▶️</span>
                      </div>
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                      <h6 class="text-white text-sm font-medium mb-1 line-clamp-2 hover:text-red-400 cursor-pointer" 
                          onclick="window.open('${video.url}', '_blank')">
                        ${video.title}
                      </h6>
                      <p class="text-gray-400 text-xs mb-1">
                        📺 ${video.channel_name}
                      </p>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-xs">
                          ${new Date(video.published_at).toLocaleDateString('es-MX')}
                        </span>
                        <a href="${video.url}" 
                           target="_blank" 
                           class="text-red-400 hover:text-red-300 text-xs font-medium">
                          Ver en YouTube →
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            });
            
            videosHTML += '</div>';
            
            // Agregar botón para ver más
            videosHTML += `
              <div class="mt-4 text-center">
                <a href="https://www.youtube.com/results?search_query=${encodeURIComponent(productName + ' review')}" 
                   target="_blank"
                   class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  🔍 Ver más videos en YouTube
                </a>
              </div>
            `;
            
            contentDiv.innerHTML = videosHTML;
            
          } else {
            contentDiv.innerHTML = `
              <div class="text-center py-6 bg-gray-800/30 rounded-lg">
                <div class="text-4xl mb-2">😕</div>
                <p class="text-gray-400 text-sm">No se encontraron videos para este producto</p>
                <a href="https://www.youtube.com/results?search_query=${encodeURIComponent(productName + ' review')}" 
                   target="_blank"
                   class="inline-block mt-3 text-red-400 hover:text-red-300 text-sm">
                  Buscar en YouTube →
                </a>
              </div>
            `;
          }
          
        } catch (error) {
          console.error('❌ Error al cargar videos de YouTube:', error);
          contentDiv.innerHTML = `
            <div class="text-center py-6 bg-red-900/20 rounded-lg border border-red-500/30">
              <div class="text-4xl mb-2">⚠️</div>
              <p class="text-red-400 text-sm font-medium">Error al cargar videos</p>
              <p class="text-gray-400 text-xs mt-1">${error.message}</p>
              <button onclick="cargarVideosYouTube('${productName}', '${contentId}')" 
                      class="mt-3 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                🔄 Reintentar
              </button>
            </div>
          `;
        }
      }
      
      // ========================================
      // FUNCIÓN PARA CARGAR PRECIOS DE GOOGLE SHOPPING
      // ========================================
      async function cargarPreciosTiendas(productName, contentId) {
        console.log('🛒 Cargando precios de Google Shopping para:', productName);
        
        const contentDiv = document.getElementById(contentId);
        
        if (!contentDiv) {
          console.error('❌ No se encontró el contenedor:', contentId);
          return;
        }
        
        // Mostrar loading
        contentDiv.innerHTML = `
          <div class="text-center py-6">
            <div class="animate-spin inline-block w-12 h-12 border-4 border-blue-400 border-t-transparent rounded-full"></div>
            <p class="text-gray-400 mt-3">Buscando mejores precios en tiendas online...</p>
          </div>
        `;
        
        try {
          // Llamar a la API de Google Shopping para comparar precios
          const response = await fetch('/api/google-shopping/compare-prices', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              query: productName,
              limit: 10
            })
          });
          
          const data = await response.json();
          
          console.log('📦 Respuesta completa de Google Shopping:', JSON.stringify(data, null, 2));
          console.log('✅ Success:', data.success);
          console.log('📊 Data object:', data.data);
          console.log('🛍️ Products array:', data.data?.products);
          console.log('📏 Products length:', data.data?.products?.length);
          
          // Verificar si tenemos productos
          const hasProducts = data.success && 
                             data.data && 
                             data.data.products && 
                             Array.isArray(data.data.products) && 
                             data.data.products.length > 0;
          
          console.log('🎯 Has products:', hasProducts);
          
          if (hasProducts) {
            const products = data.data.products;
            const priceAnalysis = data.data.price_analysis;
            
            let shoppingHTML = '';
            
            // Badge de modo demo si aplica
            if (data.data.is_demo) {
              shoppingHTML += `
                <div class="mb-3 text-center">
                  <span class="inline-block bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold border border-yellow-500/30">
                    🎭 MODO DEMOSTRACIÓN
                  </span>
                </div>
              `;
            }
            
            // Mostrar análisis de precios si está disponible
            if (priceAnalysis && priceAnalysis.lowest) {
              shoppingHTML += `
                <div class="bg-gradient-to-r from-blue-900/40 to-green-900/40 rounded-lg p-4 mb-4 border border-blue-500/30">
                  <h5 class="text-white font-bold text-center mb-3">
                    <i class="fas fa-chart-line"></i> Análisis de Precios
                  </h5>
                  <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-green-900/30 rounded p-2">
                      <div class="text-green-400 text-xs mb-1">💚 Más Bajo</div>
                      <div class="text-white font-bold text-sm">$${priceAnalysis.lowest.price.toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                      <div class="text-gray-400 text-xs">${priceAnalysis.lowest.store}</div>
                    </div>
                    <div class="bg-blue-900/30 rounded p-2">
                      <div class="text-blue-400 text-xs mb-1">📊 Promedio</div>
                      <div class="text-white font-bold text-sm">$${priceAnalysis.average.toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                    </div>
                    <div class="bg-yellow-900/30 rounded p-2">
                      <div class="text-yellow-400 text-xs mb-1">💰 Ahorro</div>
                      <div class="text-white font-bold text-sm">${priceAnalysis.savings_percentage}%</div>
                      <div class="text-gray-400 text-xs">$${priceAnalysis.difference.toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>
                    </div>
                  </div>
                </div>
              `;
            }
            
            // Grid de productos
            shoppingHTML += '<div class="grid grid-cols-1 gap-3">';
            
            // Encontrar el precio más bajo
            const lowestPrice = Math.min(...products.map(p => p.price_numeric).filter(p => p > 0));
            
            products.forEach((product, index) => {
              const isBestPrice = product.price_numeric === lowestPrice && product.price_numeric > 0;
              
              shoppingHTML += `
                <div class="bg-gray-800/50 rounded-lg overflow-hidden hover:bg-gray-800/70 transition-colors border ${isBestPrice ? 'border-green-500/50' : 'border-gray-700'}">
                  <div class="flex gap-3 p-3">
                    <!-- Imagen -->
                    <div class="flex-shrink-0 w-20 h-20 relative rounded overflow-hidden group cursor-pointer">
                      <img src="${product.image || 'https://via.placeholder.com/80x80?text=Sin+Imagen'}" 
                           alt="${product.title}" 
                           class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                           onerror="this.src='https://via.placeholder.com/80x80?text=Sin+Imagen'"
                           onclick="window.open('${product.url}', '_blank')">
                      ${isBestPrice ? '<div class="absolute top-0 right-0 bg-green-500 text-white text-xs px-1 py-0.5 font-bold">¡MEJOR!</div>' : ''}
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                      <h6 class="text-white text-xs font-medium mb-1 line-clamp-2 hover:text-blue-400 cursor-pointer" 
                          onclick="window.open('${product.url}', '_blank')">
                        ${product.title}
                      </h6>
                      <div class="flex items-center gap-2 mb-1">
                        <span class="text-blue-400 text-xs">
                          <i class="fas fa-store"></i> ${product.store}
                        </span>
                        ${product.rating ? `
                          <span class="text-yellow-400 text-xs">
                            <i class="fas fa-star"></i> ${product.rating}
                          </span>
                        ` : ''}
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-green-400 font-bold text-sm">
                          ${product.price}
                        </span>
                        ${product.delivery ? `
                          <span class="text-gray-500 text-xs">
                            <i class="fas fa-shipping-fast"></i> ${product.delivery}
                          </span>
                        ` : ''}
                      </div>
                      <div class="mt-1">
                        <a href="${product.url}" 
                           target="_blank" 
                           class="text-blue-400 hover:text-blue-300 text-xs font-medium">
                          Ver en tienda →
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            });
            
            shoppingHTML += '</div>';
            
            // Botón para ver más
            shoppingHTML += `
              <div class="mt-4 text-center">
                <button onclick="cargarPreciosTiendas('${productName}', '${contentId}')" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  🔄 Actualizar Precios
                </button>
              </div>
            `;
            
            contentDiv.innerHTML = shoppingHTML;
            
          } else {
            contentDiv.innerHTML = `
              <div class="text-center py-6 bg-gray-800/30 rounded-lg">
                <div class="text-4xl mb-2">😕</div>
                <p class="text-gray-400 text-sm">No se encontraron ofertas para este producto</p>
                <button onclick="cargarPreciosTiendas('${productName}', '${contentId}')" 
                        class="mt-3 text-blue-400 hover:text-blue-300 text-sm">
                  🔄 Reintentar
                </button>
              </div>
            `;
          }
          
        } catch (error) {
          console.error('❌ Error al cargar precios de Google Shopping:', error);
          contentDiv.innerHTML = `
            <div class="text-center py-6 bg-red-900/20 rounded-lg border border-red-500/30">
              <div class="text-4xl mb-2">⚠️</div>
              <p class="text-red-400 text-sm font-medium">Error al cargar precios</p>
              <p class="text-gray-400 text-xs mt-1">${error.message}</p>
              <button onclick="cargarPreciosTiendas('${productName}', '${contentId}')" 
                      class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                🔄 Reintentar
              </button>
            </div>
          `;
        }
      }
      
      // EJECUCIÓN FORZADA PARA TESTING
      setTimeout(() => {
        console.log('🚀 EJECUCIÓN FORZADA DE actualizarSelects después de 2 segundos...');
        actualizarSelects();
      }, 2000);
      
      
      // ============================================
      // 🌍 CURRENCY EXCHANGE INTEGRATION
      // ============================================
      
      let currentCurrency = 'MXN';
      let exchangeRates = null;
      let ratesLastUpdated = null;
      
      // Obtener tasas de cambio desde el API
      async function getExchangeRates(baseCurrency = 'MXN') {
        try {
          // Si ya tenemos tasas y no han pasado más de 1 hora, usar cache
          if (exchangeRates && ratesLastUpdated && (Date.now() - ratesLastUpdated) < 3600000) {
            console.log('💰 Usando tasas de cambio en caché');
            return exchangeRates;
          }
          
          console.log(`💱 Obteniendo tasas de cambio para ${baseCurrency}...`);
          
          const response = await fetch(`/api/currency/rates?base=${baseCurrency}`);
          const data = await response.json();
          
          if (data.success) {
            exchangeRates = data.data.rates;
            ratesLastUpdated = Date.now();
            console.log('✅ Tasas de cambio obtenidas:', exchangeRates);
            return exchangeRates;
          } else {
            throw new Error(data.message || 'Error al obtener tasas de cambio');
          }
        } catch (error) {
          console.error('❌ Error al obtener tasas de cambio:', error);
          // Retornar tasas mock en caso de error
          return {
            'USD': 0.057,
            'EUR': 0.052,
            'GBP': 0.045,
            'CAD': 0.078,
            'JPY': 8.77,
            'CNY': 0.41,
            'BRL': 0.33,
            'ARS': 57.14,
            'COP': 249.29
          };
        }
      }
      
      // Convertir precio a la moneda seleccionada
      function convertPrice(priceMXN, targetCurrency) {
        if (targetCurrency === 'MXN') {
          return priceMXN;
        }
        
        if (!exchangeRates || !exchangeRates[targetCurrency]) {
          console.warn('⚠️ No hay tasa de cambio disponible para', targetCurrency);
          return priceMXN;
        }
        
        return priceMXN * exchangeRates[targetCurrency];
      }
      
      // Formatear precio con el símbolo de moneda apropiado
      function formatCurrency(amount, currency) {
        const symbols = {
          'USD': '$',
          'MXN': '$',
          'EUR': '€',
          'GBP': '£',
          'CAD': 'CA$',
          'JPY': '¥',
          'CNY': '¥',
          'BRL': 'R$',
          'ARS': '$',
          'COP': '$'
        };
        
        const decimals = currency === 'JPY' ? 0 : 2;
        const formattedAmount = amount.toLocaleString('es-MX', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        });
        
        return `${symbols[currency] || '$'}${formattedAmount} ${currency}`;
      }
      
      // Actualizar todos los precios en la página
      async function updateAllPrices(newCurrency) {
        console.log(`💱 Actualizando precios a ${newCurrency}...`);
        
        // Obtener tasas de cambio
        await getExchangeRates('MXN');
        
        // Actualizar variable global
        currentCurrency = newCurrency;
        
        // Actualizar precios de productos en selectores
        const selects = document.querySelectorAll('select[id^="periferico"]');
        selects.forEach(select => {
          Array.from(select.options).forEach(option => {
            if (option.value && option.dataset.priceMxn) {
              const priceMXN = parseFloat(option.dataset.priceMxn);
              const convertedPrice = convertPrice(priceMXN, newCurrency);
              const formattedPrice = formatCurrency(convertedPrice, newCurrency);
              
              // Actualizar texto de la opción manteniendo el nombre del producto
              const productName = option.text.split(' - ')[0];
              option.text = `${productName} - ${formattedPrice}`;
            }
          });
        });
        
        // Actualizar precios en tarjetas de comparación si existen
        const priceElements = document.querySelectorAll('[data-price-mxn]');
        priceElements.forEach(element => {
          const priceMXN = parseFloat(element.dataset.priceMxn);
          const convertedPrice = convertPrice(priceMXN, newCurrency);
          const formattedPrice = formatCurrency(convertedPrice, newCurrency);
          element.textContent = formattedPrice;
        });
        
        console.log(`✅ Precios actualizados a ${newCurrency}`);
      }
      
      // Event listener para el selector de moneda
      document.getElementById('currency-selector').addEventListener('change', async function(e) {
        const newCurrency = e.target.value;
        console.log(`🔄 Cambiando moneda a: ${newCurrency}`);
        
        // Mostrar indicador de carga (opcional)
        const originalText = e.target.options[e.target.selectedIndex].text;
        e.target.options[e.target.selectedIndex].text = '⏳ Cargando...';
        e.target.disabled = true;
        
        try {
          await updateAllPrices(newCurrency);
        } catch (error) {
          console.error('❌ Error al actualizar precios:', error);
          alert('Error al convertir moneda. Por favor intenta de nuevo.');
        } finally {
          // Restaurar selector
          e.target.options[e.target.selectedIndex].text = originalText;
          e.target.disabled = false;
        }
      });
      
      // Cargar tasas de cambio al iniciar la página
      document.addEventListener('DOMContentLoaded', async function() {
        console.log('💱 Cargando tasas de cambio iniciales...');
        await getExchangeRates('MXN');
        console.log('✅ Sistema de conversión de moneda listo');
      });
      
    </script>
    
    <!-- Theme Switcher Script -->
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
    </div>
  </div>
  </body>
</html>