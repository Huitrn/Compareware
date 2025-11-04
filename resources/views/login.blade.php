<html>
  <head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Inter%3Awght%40400%3B500%3B700%3B900&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Stitch Design</title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
      // Configurar Tailwind para usar dark mode basado en clase
      tailwind.config = {
        darkMode: 'class'
      }
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
            <a href="{{ route('home') }}" class="logo-link text-[#0d141c] dark:text-white text-lg font-bold leading-tight tracking-[-0.015em] hover:underline">
            CompareWare
            </a>
          </div>
          <div class="flex flex-1 justify-end gap-8">
            <div class="flex items-center gap-9">
              <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="{{ route('home') }}">Inicio</a>
              <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="{{ route('marcas') }}">Marcas</a>
              <a class="nav-link text-[#0d141c] dark:text-gray-300 text-sm font-medium leading-normal hover:text-blue-600 dark:hover:text-blue-400" href="#">Contacto</a>
            </div>
            <div class="flex gap-2">
              <button
                id="theme-toggle"
                class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 w-10 bg-[#e7edf4] dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
              >
                <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256" class="text-amber-500 dark:text-amber-300">
                  <path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path>
                </svg>
              </button>
          <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
          style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCtVmO1pZu8P7jfJrUU-QY-bu3xMdiiGglWQv2sFdbUf2mVR_jaSHPqiEz-_sKzgHAcTeVDNKXNbgElLb6UWzQPzYNKD_iWvUHEzpwrVfA_-a19Eho9V_D3T0n_Le-uwc6e6ZcrCm-7ZwGqCRWKpgvr35ka35mr5MnJpKAWmhBJD9avopCYM4KZnC7VIyBAsJwB8pztwQg-ZzAWjcORcwiFtIPgGllelTJO7trBV3T8DcTOoGn5KC0M_oFjf2Rp-MMoa0ZBCcNIZKvm");'>
        </div>
            </div>
        </header>
        <!-- Contenido principal -->
<div class="px-40 flex flex-1 justify-center py-5 items-center bg-slate-50 dark:bg-gray-900">
  <div class="layout-content-container flex flex-col w-[512px] max-w-[512px] py-5 max-w-[960px] flex-1 justify-center items-center">
            <h2 class="text-[#0d141c] dark:text-white tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5">Iniciar sesión en CompareWare</h2>
            
            <!-- Mostrar errores de validación -->
            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4 w-full">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Mostrar mensaje de éxito -->
            @if (session('success'))
                <div class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4 w-full">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Mostrar enlace a registro si el usuario no existe -->
            @if (session('show_register_link'))
                <div class="bg-blue-100 dark:bg-blue-900/50 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded mb-4 w-full">
                    ¿No tienes una cuenta? 
                    <a href="{{ session('register_url') }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">Regístrate aquí</a>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 px-4 py-3 w-full items-center">
              @csrf
              <input
                type="email"
                name="email"
                placeholder="Correo electrónico"
                value="{{ old('email') }}"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#cedbe8] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('email') border-red-500 @enderror"
                required
              />
              <input
                type="password"
                name="password"
                placeholder="Contraseña"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] dark:text-white focus:outline-0 focus:ring-0 border border-[#cedbe8] dark:border-gray-600 bg-slate-50 dark:bg-gray-800 focus:border-[#cedbe8] dark:focus:border-blue-500 h-14 placeholder:text-[#49739c] dark:placeholder:text-gray-400 p-[15px] text-base font-normal leading-normal @error('password') border-red-500 @enderror"
                required
              />

              <div class="flex items-center gap-4 bg-slate-50 dark:bg-gray-900 px-4 min-h-14 justify-between w-full">
                <p class="text-[#0d141c] dark:text-white text-base font-normal leading-normal flex-1 truncate">Recordarme</p>
                <div class="shrink-0">
                  <label
                    class="relative flex h-[31px] w-[51px] cursor-pointer items-center rounded-full border-none bg-[#e7edf4] dark:bg-gray-700 p-0.5 has-[:checked]:justify-end has-[:checked]:bg-[#0d80f2]"
                  >
                    <div class="h-full w-[27px] rounded-full bg-white dark:bg-gray-300" style="box-shadow: rgba(0, 0, 0, 0.15) 0px 3px 8px, rgba(0, 0, 0, 0.06) 0px 3px 1px;"></div>
                    <input type="checkbox" name="remember" class="invisible absolute" />
                  </label>
                </div>
              </div>

      <button
        type="submit"
        class="flex min-w-[180px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 px-6 flex-1 bg-[#0d80f2] text-slate-50 text-lg font-bold leading-normal tracking-[0.015em] mt-16"
        style="margin-top: 48px;"
      >
        <span class="truncate">Iniciar sesión</span>
      </button>
            </form>
          </div>
        </div>

            
            <p class="text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal pb-3 pt-1 px-4 underline">¿Olvidaste tu contraseña?</p>
            <p class="text-[#49739c] dark:text-gray-400 text-sm font-normal leading-normal pb-3 pt-1 px-4 text-center">
              ¿No tienes una cuenta?
              <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Regístrate</a>
            </p>

            </div>
        </div>
      </div>
    </div>

<script src="{{ asset('js/theme-switcher.js') }}"></script>
  </body>
</html>
