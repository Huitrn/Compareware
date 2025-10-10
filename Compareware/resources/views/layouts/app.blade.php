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
                    <div class="flex items-center gap-4 text-[#0d141c]">
                        <div class="size-4">
                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M36.7273 44C33.9891 44 31.6043 39.8386 30.3636 33.69C29.123 39.8386 26.7382 44 24 44C21.2618 44 18.877 39.8386 17.6364 33.69C16.3957 39.8386 14.0109 44 11.2727 44C7.25611 44 4 35.0457 4 24C4 12.9543 7.25611 4 11.2727 4C14.0109 4 16.3957 8.16144 17.6364 14.31C18.877 8.16144 21.2618 4 24 4C26.7382 4 29.123 8.16144 30.3636 14.31C31.6043 8.16144 33.9891 4 36.7273 4C40.7439 4 44 12.9543 44 24C44 35.0457 40.7439 44 36.7273 44Z"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </div>
                        <h2 class="logo-link text-[#0d141c] text-lg font-bold leading-tight tracking-[-0.015em]">CompareWare</h2>
                    </div>
                    <div class="flex items-center gap-9">
                        <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="{{ route('home') }}">Inicio</a>
                        <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="{{ route('marcas') }}">Marcas</a>
                        <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="#">Contacto</a>
                    </div>
                </div>
                <div class="flex flex-1 justify-end gap-8">
                    <label class="flex flex-col min-w-40 !h-10 max-w-64">
                        <div class="flex w-full flex-1 items-stretch rounded-lg h-full">
                            <div
                                class="text-[#49739c] flex border-none bg-[#e7edf4] items-center justify-center pl-4 rounded-l-lg border-r-0"
                                data-icon="MagnifyingGlass"
                                data-size="24px"
                                data-weight="regular"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                                    <path
                                        d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"
                                    ></path>
                                </svg>
                            </div>
                            <input
                                placeholder="Search"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] focus:outline-0 focus:ring-0 border-none bg-[#e7edf4] focus:border-none h-full placeholder:text-[#49739c] px-4 rounded-l-none border-l-0 pl-2 text-base font-normal leading-normal"
                                value=""
                            />
                        </div>
                    </label>
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
                    <div
                        class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuADwkjq3QGdj2I_v6ukQlP9YZkzMqfEOuSpSjqUDPKf22K8DnxgjfpEtnoCAf7-s9MNnTZoiYt3fNCylkLNS9XYxIsQLaJW6laqNQLUgnnyK_JHA1nzpQmXtH3c1-AiP7QciUCp7MUF4Jcgy4J-KMrYrG9XS8DjmYzMfXeL-vvMezy1bmt9FppDx3b12fn9MtFUmuBMWkfCIRny6UkqVyg9F1GJ21gM1oAhACoAfU1CIvEW-bPv83kJkl59crpi3U-bv6ApUsb-D_c");'
                    ></div>
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