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
</head>
<body>
    <div class="relative flex size-full min-h-screen flex-col bg-slate-50 group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
        <div class="layout-container flex h-full grow flex-col">
            <!-- Header -->
            <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#e7edf4] px-10 py-3">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-4 text-[#0d141c] hover:opacity-80 transition-opacity">
                        <div class="size-4">
                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </div>
                        <h2 class="logo-link text-[#0d141c] text-lg font-bold leading-tight tracking-[-0.015em]">CompareWare</h2>
                    </a>
                    <div class="flex items-center gap-9">
                        @auth
                            @if(Auth::user()->role === 'admin')
                                <a class="nav-link text-purple-600 text-sm font-bold leading-normal hover:text-purple-800" href="{{ route('admin.dashboard') }}">🔧 Admin</a>
                            @endif
                        @endauth
                        <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="#">Contacto</a>
                    </div>
                </div>
                <div class="flex flex-1 justify-end gap-4 items-center">
                    @auth
                        <!-- Usuario autenticado -->
                        <span class="text-[#0d141c] text-sm font-medium">Hola, {{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
                        
                        @if(Auth::user()->role === 'admin')
                            <!-- Botón de Admin Panel -->
                            <a href="{{ route('admin.dashboard') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg text-lg font-bold transition-colors flex items-center gap-2 border-2 border-white shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Admin
                            </a>
                        @endif
                        
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cerrar sesión
                            </button>
                        </form>
                    @else
                        <!-- Usuario no autenticado -->
                        <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Iniciar sesión
                        </a>
                        <a href="{{ route('register') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Registrarse
                        </a>
                    @endauth
                    
                    <!-- Botón de tema -->
                    <button
                        id="theme-toggle"
                        class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#e7edf4] text-[#0d141c] gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5"
                    >
                        <span id="theme-icon">
                            <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                                <path
                                    d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"
                                ></path>
                            </svg>
                            <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256" style="display:none;">
                                <path
                                    d="M233.54,142.23a8,8,0,0,0-8-2A88.08,88.08,0,0,1,114.27,29a8,8,0,0,0-10-10A104.84,104.84,0,0,0,56,192a103.09,103.09,0,0,0,24,5.28,104.84,104.84,0,0,0,153.54-55A8,8,0,0,0,233.54,142.23ZM152,184a88.15,88.15,0,0,1-88-88,89.68,89.68,0,0,1,.78-12.22,104.11,104.11,0,0,0,99.29,99.29A89.68,89.68,0,0,1,152,184Z"
                                ></path>
                            </svg>
                        </span>
                    </button>
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
                        <div class="flex flex-wrap items-center justify-center gap-6 @[480px]:flex-row @[480px]:justify-around">
                            <a class="text-[#49739c] text-base font-normal leading-normal min-w-40" href="#">Política de privacidad</a>
                            <a class="text-[#49739c] text-base font-normal leading-normal min-w-40" href="#">Términos de servicio</a>
                            <a class="text-[#49739c] text-base font-normal leading-normal min-w-40" href="#">Contacto</a>
                        </div>
                        <p class="text-[#49739c] text-base font-normal leading-normal">@2024 CompareWare. Todos los derechos reservados.</p>
                    </footer>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
</body>
</html>