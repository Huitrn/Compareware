<html>
  <head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Inter%3Awght%40400%3B500%3B700%3B900&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Comparadora | CompareWare</title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
      /* Estilos para el select en modo oscuro y claro */
      .select-dark {
        background-color: #111a22;
        color: #fff;
        border-color: #324d67;
      }
      .select-light {
        background-color: #e7edf4;
        color: #0d141c;
        border-color: #b0c4d6;
      }
    </style>
  </head>
  <body id="main-body" class="bg-[#111a22] text-white" style='font-family: Inter, "Noto Sans", sans-serif;'>
    <!-- Encabezado -->
    <header id="main-header" class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#233648] px-10 py-3 bg-[#111a22] shadow-lg">
      <div class="flex items-center gap-8">
        <div class="flex items-center gap-4">
          <a href="{{ route('welcome') }}" class="logo-link text-black text-lg font-bold leading-tight tracking-[-0.015em] hover:underline">
            CompareWare
          </a>
        </div>
        <nav class="flex items-center gap-6">
          <a class="nav-link text-black text-sm font-medium leading-normal hover:underline" href="#">Marcas</a>
          <a class="nav-link text-black text-sm font-medium leading-normal hover:underline" href="#">Contacto</a>
        </nav>
      </div>
      <div class="flex items-center gap-4">
        <!-- Botón de cambio de tema -->
        <button id="theme-toggle" class="theme-btn flex cursor-pointer items-center justify-center rounded-lg h-10 bg-[#233648] text-white gap-2 text-sm font-bold px-2.5">
          <span id="theme-icon">
            <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256" style="display:none;">
              <path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path>
            </svg>
            <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
              <path d="M228.13,158.16a8,8,0,0,0-8.94,1.73A96,96,0,0,1,96.11,36.81a8,8,0,0,0-9.67-9.67A112,112,0,1,0,229.86,167.1,8,8,0,0,0,228.13,158.16ZM128,224A96,96,0,0,1,48.11,48.11,112,112,0,0,0,207.89,207.89,96.11,96.11,0,0,1,128,224Z"></path>
            </svg>
          </span>
        </button>
        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
          style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCtVmO1pZu8P7jfJrUU-QY-bu3xMdiiGglWQv2sFdbUf2mVR_jaSHPqiEz-_sKzgHAcTeVDNKXNbgElLb6UWzQPzYNKD_iWvUHEzpwrVfA_-a19Eho9V_D3T0n_Le-uwc6e6ZcrCm-7ZwGqCRWKpgvr35ka35mr5MnJpKAWmhBJD9avopCYM4KZnC7VIyBAsJwB8pztwQg-ZzAWjcORcwiFtIPgGllelTJO7trBV3T8DcTOoGn5KC0M_oFjf2Rp-MMoa0ZBCcNIZKvm");'>
        </div>
      </div>
    </header>
    <div class="container mx-auto py-8 px-4">
      <!-- Hero Section estilo index.html -->
      <section id="hero-section" class="rounded-xl bg-[#0d80f2] p-8 mb-8 flex flex-col md:flex-row items-center justify-between shadow-lg">
        <div class="md:w-1/2 mb-6 md:mb-0">
          <h1 class="text-3xl md:text-4xl font-bold mb-4 text-white">TU DECISIÓN, NUESTRA COMPARACIÓN</h1>
          <p class="text-lg text-white/80 mb-2">Tu comparador de periféricos de confianza</p>
        </div>
        <div class="md:w-1/2 flex justify-center">
          <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80"
               alt="Gaming Setup"
               class="rounded-xl shadow-lg w-64 h-40 object-cover">
        </div>
      </section>
      <div class="w-full flex flex-col items-center pb-6">
        <h2 class="text-[32px] font-bold mb-6">Comparar Productos</h2>
        <div id="categorias-tabs" class="flex gap-2 mb-6 bg-[#111a22] rounded-lg p-2">
          @foreach($categorias as $categoria)
            <button
              class="px-6 py-2 rounded-lg font-bold categoria-tab"
              data-categoria="{{ $categoria->id }}"
              type="button"
            >
              {{ $categoria->nombre }}
            </button>
          @endforeach
        </div>
        <div id="listas-productos" class="w-full max-w-2xl mb-8">
          @foreach($categorias as $categoria)
            <div class="lista-productos" data-categoria="{{ $categoria->id }}" style="display: none;">
              @php
                $productosCat = $productos->where('categoria_id', $categoria->id);
              @endphp
              @if($productosCat->isEmpty())
                <div class="text-[#92adc9]">No hay productos en esta categoría.</div>
              @else
                <ul class="flex flex-col gap-4">
                  @foreach($productosCat as $producto)
                    <li class="card rounded-lg p-4 font-semibold">
                      {{ $producto->nombre }}
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          @endforeach
        </div>
        <div id="comparacion-form" class="w-full max-w-2xl flex flex-col gap-4 mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <label for="periferico1" class="block mb-2 font-semibold text-white">Selecciona el primer periférico</label>
              <select id="periferico1" class="select-dark w-full rounded-lg px-4 py-2 border border-[#324d67] focus:outline-none focus:ring-2 focus:ring-[#1172d4]">
                <option value="">-- Selecciona --</option>
              </select>
            </div>
            <div class="flex-1">
              <label for="periferico2" class="block mb-2 font-semibold text-white">Selecciona el segundo periférico</label>
              <select id="periferico2" class="select-dark w-full rounded-lg px-4 py-2 border border-[#324d67] focus:outline-none focus:ring-2 focus:ring-[#1172d4]">
                <option value="">-- Selecciona --</option>
              </select>
            </div>
          </div>
          <button id="comparar-btn" class="bg-[#0d80f2] text-white px-6 py-2 rounded-lg font-bold mt-4 w-full md:w-auto self-center">Comparar</button>
        </div>
        <div id="resultado-comparacion" class="w-full max-w-2xl mt-4"></div>
      </div>
    </div>
    <div class="container mx-auto py-8 px-4">
      <!-- Sección de APIs externas -->
      <section class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Resultados de APIs externas</h2>

        {{-- MercadoLibre --}}
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">MercadoLibre</h3>
          @if(isset($mercadolibre['results']))
            <ul class="list-disc pl-6">
              @foreach($mercadolibre['results'] as $item)
                <li>{{ $item['title'] }} - ${{ $item['price'] }}</li>
              @endforeach
            </ul>
          @else
            <p>No hay resultados de MercadoLibre.</p>
          @endif
        </div>

        {{-- eBay --}}
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">eBay</h3>
          @if(!empty($ebay) && isset($ebay['itemSummaries']))
            <ul class="list-disc pl-6">
              @foreach($ebay['itemSummaries'] as $item)
                <li>{{ $item['title'] ?? 'Sin título' }}</li>
              @endforeach
            </ul>
          @else
            <p>No hay resultados de eBay o falta autenticación.</p>
          @endif
        </div>

        {{-- BestBuy --}}
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">BestBuy</h3>
          @if(!empty($bestbuy) && isset($bestbuy['products']))
            <ul class="list-disc pl-6">
              @foreach($bestbuy['products'] as $item)
                <li>{{ $item['name'] ?? 'Sin nombre' }}</li>
              @endforeach
            </ul>
          @else
            <p>No hay resultados de BestBuy o falta API Key.</p>
          @endif
        </div>

        {{-- Clima --}}
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">Clima actual</h3>
          @if(isset($clima['weather'][0]['description']))
            <div>
              {{ $clima['weather'][0]['description'] }} - {{ $clima['main']['temp'] }}°C
            </div>
          @else
            <p>No se pudo obtener el clima.</p>
          @endif
        </div>

        {{-- Geolocalización --}}
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">Ubicación</h3>
          @if(isset($geo['city']))
            <div>
              {{ $geo['city'] }}, {{ $geo['country'] }}
            </div>
          @else
            <p>No se pudo obtener la ubicación.</p>
          @endif
        </div>
      </section>

      {{-- ...resto del contenido de la vista... --}}
    </div>
    <script>
      // Obtener productos y categorías desde Blade
      const productos = @json($productos->values());
      const categorias = @json($categorias->values());

      let categoriaSeleccionada = categorias.length ? categorias[0].id : null;

      // Actualiza los selects según la categoría activa
      function actualizarSelects() {
        const select1 = document.getElementById('periferico1');
        const select2 = document.getElementById('periferico2');
        select1.innerHTML = '<option value="">-- Selecciona --</option>';
        select2.innerHTML = '<option value="">-- Selecciona --</option>';
        const filtrados = productos.filter(p => p.categoria_id == categoriaSeleccionada);
        filtrados.forEach(p => {
          select1.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
          select2.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
        });
      }

      document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.categoria-tab');
        const listas = document.querySelectorAll('.lista-productos');
        // Activar la primera categoría por defecto
        if (tabs.length) {
          tabs[0].classList.add('active-tab');
          listas[0].style.display = 'block';
          categoriaSeleccionada = tabs[0].getAttribute('data-categoria');
          actualizarSelects();
        }
        tabs.forEach(tab => {
          tab.addEventListener('click', function () {
            // Quitar estilos activos
            tabs.forEach(t => t.classList.remove('active-tab'));
            listas.forEach(l => l.style.display = 'none');
            // Activar el tab y mostrar la lista correspondiente
            tab.classList.add('active-tab');
            const cat = tab.getAttribute('data-categoria');
            categoriaSeleccionada = cat;
            document.querySelector(`.lista-productos[data-categoria="${cat}"]`).style.display = 'block';
            actualizarSelects();
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
            document.getElementById('resultado-comparacion').innerHTML =
              '<div class="card rounded-lg p-4 font-semibold text-red-400">Selecciona dos periféricos distintos de la misma categoría.</div>';
            return;
          }

          // Consulta a la tabla comparaciones vía AJAX (Laravel endpoint)
          try {
            const res = await fetch(`/comparar-perifericos?periferico1=${id1}&periferico2=${id2}`);
            const data = await res.json();
            if (data.success) {
              document.getElementById('resultado-comparacion').innerHTML = `
                <div class="card rounded-lg p-6 font-semibold flex flex-col gap-4">
                  <h3 class="text-xl font-bold mb-2">Resultado de la comparación</h3>
                  <div class="flex flex-col md:flex-row gap-4">
                    <div class="card flex-1 rounded-lg p-4">
                      <h4 class="font-bold text-lg mb-2">${data.periferico1.nombre}</h4>
                      <p>${data.periferico1.descripcion ?? ''}</p>
                      <p class="mt-2">Precio: <span class="font-bold">$${data.periferico1.precio}</span></p>
                    </div>
                    <div class="card flex-1 rounded-lg p-4">
                      <h4 class="font-bold text-lg mb-2">${data.periferico2.nombre}</h4>
                      <p>${data.periferico2.descripcion ?? ''}</p>
                      <p class="mt-2">Precio: <span class="font-bold">$${data.periferico2.precio}</span></p>
                    </div>
                  </div>
                  <div class="hero-section mt-4 rounded-lg p-4">
                    <strong>Comparación:</strong> ${data.comparacion ?? 'Sin información adicional.'}
                  </div>
                </div>
              `;
            } else {
              document.getElementById('resultado-comparacion').innerHTML =
                '<div class="card rounded-lg p-4 font-semibold text-red-400">No se encontró información de comparación.</div>';
            }
          } catch (e) {
            document.getElementById('resultado-comparacion').innerHTML =
              '<div class="card rounded-lg p-4 font-semibold text-red-400">Error al consultar la comparación.</div>';
          }
        });
      });
    </script>
    <script>
      // Colores para tema claro y oscuro
      const themeColors = {
        dark: {
          body: 'bg-[#111a22] text-white',
          header: 'bg-[#111a22] border-b-[#233648] text-white',
          button: 'bg-[#233648] text-white',
          tabs: 'bg-[#111a22]',
          tabActive: 'bg-[#233648] text-white',
          tabInactive: 'bg-[#233648]/60 text-[#92adc9]',
          hero: 'bg-[#0d80f2] text-white',
          card: 'bg-[#233648] text-white',
          navText: 'text-white',
          logoText: 'text-white',
          select: 'select-dark',
        },
        light: {
          body: 'bg-slate-50 text-[#0d141c]',
          header: 'bg-slate-50 border-b-[#e7edf4] text-[#0d141c]',
          button: 'bg-[#e7edf4] text-[#0d141c]',
          tabs: 'bg-slate-50',
          tabActive: 'bg-[#0d80f2] text-white',
          tabInactive: 'bg-[#e7edf4] text-[#0d141c]',
          hero: 'bg-[#0d80f2] text-white',
          card: 'bg-[#233648] text-white',
          navText: 'text-black',
          logoText: 'text-black',
          select: 'select-light',
        }
      };

      function setTheme(theme) {
        localStorage.setItem('theme', theme);

        // Body
        document.getElementById('main-body').className = themeColors[theme].body;

        // Header
        document.getElementById('main-header').className =
          `flex items-center justify-between whitespace-nowrap border-b border-solid px-10 py-3 shadow-lg ${themeColors[theme].header}`;

        // Botón
        document.getElementById('theme-toggle').className =
          `theme-btn flex cursor-pointer items-center justify-center rounded-lg h-10 gap-2 text-sm font-bold px-2.5 ${themeColors[theme].button}`;

        // Iconos
        document.getElementById('icon-sun').style.display = theme === 'light' ? 'inline' : 'none';
        document.getElementById('icon-moon').style.display = theme === 'dark' ? 'inline' : 'none';

        // Tabs
        document.getElementById('categorias-tabs').className =
          `flex gap-2 mb-6 rounded-lg p-2 ${themeColors[theme].tabs}`;

        // Hero
        document.getElementById('hero-section').className =
          `rounded-xl p-8 mb-8 flex flex-col md:flex-row items-center justify-between shadow-lg ${themeColors[theme].hero}`;

        // Cards
        document.querySelectorAll('.card').forEach(card => {
          card.className = `card rounded-lg p-4 font-semibold ${themeColors[theme].card}`;
        });

        // Tabs activos/inactivos
        document.querySelectorAll('.categoria-tab').forEach(tab => {
          if (tab.classList.contains('active-tab')) {
            tab.className = `px-6 py-2 rounded-lg font-bold categoria-tab active-tab ${themeColors[theme].tabActive}`;
          } else {
            tab.className = `px-6 py-2 rounded-lg font-bold categoria-tab ${themeColors[theme].tabInactive}`;
          }
        });

        // Hero extra en resultado
        document.querySelectorAll('.hero-section').forEach(hero => {
          hero.className = `hero-section mt-4 rounded-lg p-4 ${themeColors[theme].hero}`;
        });

        // Botones de navegación
        document.querySelectorAll('.nav-link').forEach(link => {
          link.classList.remove('text-white', 'text-black');
          link.classList.add(themeColors[theme].navText);
        });

        // Logo
        const logoUrl = "{{ route('welcome') }}";
        const logo = document.querySelector(`.logo-link[href='${logoUrl}']`);
        if (logo) {
          logo.classList.remove('text-white', 'text-black');
          logo.classList.add(themeColors[theme].logoText);
        }

        // Selects
        document.getElementById('periferico1').className = `${themeColors[theme].select} w-full rounded-lg px-4 py-2 border focus:outline-none focus:ring-2 focus:ring-[#1172d4]`;
        document.getElementById('periferico2').className = `${themeColors[theme].select} w-full rounded-lg px-4 py-2 border focus:outline-none focus:ring-2 focus:ring-[#1172d4]`;
      }

      document.addEventListener('DOMContentLoaded', function () {
        const theme = localStorage.getItem('theme') || 'dark';
        setTheme(theme);

        document.getElementById('theme-toggle').addEventListener('click', function () {
          const newTheme = localStorage.getItem('theme') === 'dark' ? 'light' : 'dark';
          setTheme(newTheme);
        });

        document.querySelectorAll('.categoria-tab').forEach(tab => {
          tab.addEventListener('click', function () {
            document.querySelectorAll('.categoria-tab').forEach(t => t.classList.remove('active-tab'));
            tab.classList.add('active-tab');
            setTheme(localStorage.getItem('theme') || 'dark');
          });
        });
      });
    </script>
  </body>
</html>
