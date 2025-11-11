<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&amp;family=Inter%3Awght%40400%3B500%3B700%3B900&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900"
    />
    <title>@yield('title', 'CompareWare')</title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
      tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body>
    <div class="relative flex size-full min-h-screen flex-col bg-slate-50 dark:bg-gray-900 group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
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
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Contenido principal -->
            <main class="flex flex-1">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="flex justify-center">
                <div class="flex max-w-[960px] flex-1 flex-col">
                    <footer class="flex flex-col gap-6 px-5 py-10 text-center @container">
                        <p class="text-[#49739c] dark:text-gray-400 text-base font-normal leading-normal">@2024 CompareWare. Todos los derechos reservados.</p>
                    </footer>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
</body>
</html>