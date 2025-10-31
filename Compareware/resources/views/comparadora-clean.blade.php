<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparadora | CompareWare</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
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
        if (!isAuthenticated) return;
        
        try {
          const response = await fetch('/api/token');
          if (response.ok) {
            const data = await response.json();
            apiToken = data.token;
            localStorage.setItem('api_token', data.token);
            console.log('Token API obtenido exitosamente');
          }
        } catch (e) {
          console.log('Error obteniendo token API:', e.message);
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

          console.log('🔄 Iniciando comparación:', id1, 'vs', id2);
          
          // Mostrar loading
          document.getElementById('resultado-comparacion').innerHTML = '<div class="text-center py-8"><div class="pulse text-2xl">🔍 Comparando...</div></div>';

          try {
            const response = await fetch('/comparar-perifericos?periferico1=' + id1 + '&periferico2=' + id2);
            
            if (response.ok) {
              const data = await response.json();
              console.log('✅ Comparación exitosa:', data);
              mostrarResultadoComparacion(data);
            } else {
              console.error('❌ Error en la comparación:', response.status);
              document.getElementById('resultado-comparacion').innerHTML = '<div class="bg-red-500 text-white p-4 rounded">Error al comparar productos</div>';
            }
          } catch (error) {
            console.error('❌ Error de red:', error);
            document.getElementById('resultado-comparacion').innerHTML = '<div class="bg-red-500 text-white p-4 rounded">Error de conexión</div>';
          }
        });
      });

      function mostrarResultadoComparacion(data) {
        const resultado = document.getElementById('resultado-comparacion');
        
        const html = '<div class="bg-[#1e293b] rounded-xl p-6 mt-6 shadow-lg">' +
          '<h3 class="text-white text-2xl font-bold mb-6 text-center">📊 Resultado de la Comparación</h3>' +
          '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">' +
            '<div class="bg-[#0f172a] p-4 rounded-lg border border-blue-500">' +
              '<div class="text-center mb-4">' +
                '<h4 class="font-bold text-xl mb-2 text-blue-600">' + data.periferico1.nombre + '</h4>' +
              '</div>' +
              '<div class="space-y-2">' +
                '<div class="flex justify-between">' +
                  '<span class="text-gray-400">💰 Precio:</span>' +
                  '<span class="font-bold text-green-400">$' + parseFloat(data.periferico1.precio).toLocaleString() + '</span>' +
                '</div>' +
              '</div>' +
            '</div>' +
            '<div class="bg-[#0f172a] p-4 rounded-lg border border-purple-500">' +
              '<div class="text-center mb-4">' +
                '<h4 class="font-bold text-xl mb-2 text-purple-600">' + data.periferico2.nombre + '</h4>' +
              '</div>' +
              '<div class="space-y-2">' +
                '<div class="flex justify-between">' +
                  '<span class="text-gray-400">💰 Precio:</span>' +
                  '<span class="font-bold text-green-400">$' + parseFloat(data.periferico2.precio).toLocaleString() + '</span>' +
                '</div>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="mt-6 p-4 bg-[#0f172a] rounded-lg">' +
            '<h5 class="font-bold text-lg mb-3 text-white text-center">🏆 Análisis</h5>' +
            '<p class="text-gray-300 text-center">' + (data.comparacion || 'Comparación completada') + '</p>' +
          '</div>' +
        '</div>';
        
        resultado.innerHTML = html;
      }

      // EJECUCIÓN FORZADA PARA TESTING
      setTimeout(() => {
        console.log('🚀 EJECUCIÓN FORZADA DE actualizarSelects después de 2 segundos...');
        actualizarSelects();
      }, 2000);
      
    </script>
  </body>
</html>