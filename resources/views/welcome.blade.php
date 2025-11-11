<html>
  <head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Inter%3Awght%40400%3B500%3B700%3B900&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Compareware</title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
      tailwind.config = { darkMode: 'class' }
    </script>
  </head>
  <body>
    <div class="relative flex size-full min-h-screen flex-col bg-slate-50 dark:bg-gray-900 group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
      <div class="layout-container flex h-full grow flex-col">
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
              <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="#">Contacto</a>
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
                <a href="{{ route('perfil') }}" class="text-[#0d141c] dark:text-white text-sm font-medium hover:text-purple-600 dark:hover:text-purple-400 transition-colors cursor-pointer">
                  Hola, {{ Auth::user()->name }}
                </a>
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
                  </div>
                @endif

                @if(Auth::user()->isSupervisor())
                  <a
                    href="{{ route('supervisor.dashboard') }}"
                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-green-700 transition-colors"
                  >
                    <span class="truncate">📊 Panel Supervisor</span>
                  </a>
                @endif

                @if(Auth::user()->isDeveloper())
                  <a
                    href="{{ route('developer.dashboard') }}"
                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-amber-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-amber-700 transition-colors"
                  >
                    <span class="truncate">🔧 Panel Developer</span>
                  </a>
                @endif
              @endauth
            </div>
          </div>
        </header>
        <div class="px-40 flex flex-1 justify-center py-5 bg-slate-50 dark:bg-gray-900">
          <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <div class="@container">
              <div class="@[480px]:p-4">
                <div
                  class="flex min-h-[480px] flex-col gap-6 bg-cover bg-center bg-no-repeat @[480px]:gap-8 @[480px]:rounded-lg items-center justify-center p-4"
                  style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.4) 100%), url("https://lh3.googleusercontent.com/aida-public/AB6AXuAqs_WcrZK6iojAXfHtqMhfC5Ye3YK8XFx6T2QqgImd6pXr8yW6IkxvcYhUXQ7kyU3EZYNLVbMUZTbhElomVL99mjECqqn59iAFwyqMH-l146lL5EVkrjvZqLfszoemCfOiDJ5nURlFt1ulnOEMrJxzV1XUP5BTVzv7uP3BTL_tueAwqr4Yw_TbAX9RSFSEYqmCqVgwab4gnMpFUjGAFATgkRQPViqqmcrQuMEKtlO1KY_jNAsiYgfvcAijccwleezZayzE9pcWZH8");'
                >
                  <div class="flex flex-col gap-2 text-center">
                    <h1
                      class="text-white text-4xl font-black leading-tight tracking-[-0.033em] @[480px]:text-5xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em]"
                    >
                      Compara y elige los mejores periféricos
                    </h1>
                    <h2 class="text-white text-sm font-normal leading-normal @[480px]:text-base @[480px]:font-normal @[480px]:leading-normal">
                      Encuentra los periféricos perfectos para tu setup. Compara especificaciones, precios y opiniones de usuarios para tomar la mejor decisión.
                    </h2>
                  </div>
                <button
  onclick="window.location.href='{{ route('comparadora') }}'"
  class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 @[480px]:h-12 @[480px]:px-5 bg-[#0d80f2] text-slate-50 text-sm font-bold leading-normal tracking-[0.015em] @[480px]:text-base @[480px]:font-bold @[480px]:leading-normal @[480px]:tracking-[0.015em]"
>
  <span class="truncate">Comenzar a comparar</span>
</button>
                </div>
              </div>
            </div>
            <div class="flex flex-col gap-10 px-4 py-10 @container">
              <div class="flex flex-col gap-4">
                <h1
                  class="text-[#0d141c] dark:text-white tracking-light text-[32px] font-bold leading-tight @[480px]:text-4xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em] max-w-[720px]"
                >
                  Cómo funciona
                </h1>
                <p class="text-[#0d141c] dark:text-gray-300 text-base font-normal leading-normal max-w-[720px]">Descubre lo fácil que es encontrar los periféricos ideales con CompareWare.</p>
              </div>
              <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-3 p-0">
                <div class="flex flex-1 gap-3 rounded-lg border border-[#cedbe8] dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-4 flex-col">
                  <div class="text-[#0d141c]" data-icon="MagnifyingGlass" data-size="24px" data-weight="regular">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                      <path
                        d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"
                      ></path>
                    </svg>
                  </div>
                  <div class="flex flex-col gap-1">
                    <h2 class="text-[#0d141c] text-base font-bold leading-tight">Compara</h2>
                    <p class="text-[#49739c] text-sm font-normal leading-normal">Compara diferentes periféricos lado a lado para ver sus especificaciones y características.</p>
                  </div>
                </div>
                <div class="flex flex-1 gap-3 rounded-lg border border-[#cedbe8] bg-slate-50 p-4 flex-col">
                  <div class="text-[#0d141c] dark:text-white" data-icon="ListBullets" data-size="24px" data-weight="regular">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                      <path
                        d="M80,64a8,8,0,0,1,8-8H216a8,8,0,0,1,0,16H88A8,8,0,0,1,80,64Zm136,56H88a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Zm0,64H88a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16ZM44,52A12,12,0,1,0,56,64,12,12,0,0,0,44,52Zm0,64a12,12,0,1,0,12,12A12,12,0,0,0,44,116Zm0,64a12,12,0,1,0,12,12A12,12,0,0,0,44,180Z"
                      ></path>
                    </svg>
                  </div>
                  <div class="flex flex-col gap-1">
                    <h2 class="text-[#0d141c] dark:text-white text-base font-bold leading-tight">Analiza</h2>
                    <p class="text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal">Analiza las opiniones de otros usuarios y las calificaciones para obtener una visión completa.</p>
                  </div>
                </div>
                <div class="flex flex-1 gap-3 rounded-lg border border-[#cedbe8] dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-4 flex-col">
                  <div class="text-[#0d141c] dark:text-white" data-icon="UsersThree" data-size="24px" data-weight="regular">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                      <path
                        d="M244.8,150.4a8,8,0,0,1-11.2-1.6A51.6,51.6,0,0,0,192,128a8,8,0,0,1-7.37-4.89,8,8,0,0,1,0-6.22A8,8,0,0,1,192,112a24,24,0,1,0-23.24-30,8,8,0,1,1-15.5-4A40,40,0,1,1,219,117.51a67.94,67.94,0,0,1,27.43,21.68A8,8,0,0,1,244.8,150.4ZM190.92,212a8,8,0,1,1-13.84,8,57,57,0,0,0-98.16,0,8,8,0,1,1-13.84-8,72.06,72.06,0,0,1,33.74-29.92,48,48,0,1,1,58.36,0A72.06,72.06,0,0,1,190.92,212ZM128,176a32,32,0,1,0-32-32A32,32,0,0,0,128,176ZM72,120a8,8,0,0,0-8-8A24,24,0,1,1,87.24,82a8,8,0,1,0,15.5-4A40,40,0,1,0,37,117.51,67.94,67.94,0,0,0,9.6,139.19a8,8,0,1,0,12.8,9.61A51.6,51.6,0,0,1,64,128,8,8,0,0,0,72,120Z"
                      ></path>
                    </svg>
                  </div>
                  <div class="flex flex-col gap-1">
                    <h2 class="text-[#0d141c] dark:text-white text-base font-bold leading-tight">Decide</h2>
                    <p class="text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal">Toma una decisión informada y elige el periférico que mejor se adapte a tus necesidades.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <footer class="flex justify-center bg-slate-50 dark:bg-gray-800">
          <div class="flex max-w-[960px] flex-1 flex-col">
            <footer class="flex flex-col gap-6 px-5 py-10 text-center @container">
              <div class="flex flex-wrap items-center justify-center gap-6 @[480px]:flex-row @[480px]:justify-around">
                <a class="nav-link text-[#49739c] dark:text-gray-400 text-base font-normal leading-normal min-w-40 hover:text-blue-600 dark:hover:text-blue-400" href="{{ route('marcas') }}">Marcas</a>
                <a class="nav-link text-[#49739c] dark:text-gray-400 text-base font-normal leading-normal min-w-40 hover:text-blue-600 dark:hover:text-blue-400" href="#">Contacto</a>
              </div>
              <p class="text-[#49739c] dark:text-gray-400 text-base font-normal leading-normal">© 2025 CompareWare. Todos los derechos reservados.</p>
            </footer>
          </div>
        </footer>
      </div>
    </div>
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
  </body>
</html>
