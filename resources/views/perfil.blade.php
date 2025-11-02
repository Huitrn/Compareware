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
  </head>
  <body>
    <div class="relative flex size-full min-h-screen flex-col bg-slate-50 group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
      <div class="layout-container flex h-full grow flex-col">
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
            <a href="{{ route('home') }}" class="logo-link text-[#0d141c] text-lg font-bold leading-tight tracking-[-0.015em] hover:underline">
                CompareWare
            </a>
            </div>
            <div class="flex items-center gap-9">
              <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="{{ route('home') }}">Inicio</a>
              <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="{{ route('comparadora') }}">Comparar</a>
              <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="{{ route('marcas') }}">Marcas</a>
              <a class="nav-link text-[#0d141c] text-sm font-medium leading-normal" href="#">Comunidad</a>
            </div>
          </div>
          <div class="flex flex-1 justify-end gap-8">
            <button
              id="theme-toggle"
              class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#e7edf4] text-[#0d141c] gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5"
            >
              <div class="text-[#0d141c]" data-icon="Sun" data-size="20px" data-weight="regular">
                <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                  <path
                    d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"
                  ></path>
                </svg>
              </div>
            </button>
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
            <div
              class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
              style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuB2HH1-PTm-a8D46oKTxcPnQxcRNEp7ZZ2WFCHIAPWBR_NtCBicTIQ9CxvSJ1MtYRc6HiWqzTFZ-aqMov_sZ46x99ozCZow_t7x4_NbASWA4gOCnJXW9RgmSxwv28zJS8ViHqiMuZRn2XksT7U3d2FxE_LVo2kx9sjE-b7D2ll4zLjlGXyWBVl6LP8jiOu57tS5TzOnUWnSF19Q8mXouJtY9gSUg4olsqg4KXhZGju8DOIO3O0pnIHWacXYyRHDPzOWoyVNtFJ-8IA");'
            ></div>
          </div>
        </header>
        <div class="px-40 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <div class="flex flex-wrap justify-between gap-3 p-4"><p class="text-[#0d141c] tracking-light text-[32px] font-bold leading-tight min-w-72">Mi Perfil</p></div>
            <div class="flex p-4 @container">
              <div class="flex w-full flex-col gap-4 @[520px]:flex-row @[520px]:justify-between @[520px]:items-center">
                <div class="flex gap-4">
                  <div
                    class="bg-center bg-no-repeat aspect-square bg-cover rounded-full min-h-32 w-32"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBhCGjwtPwYCtn6sLLUOUKPrLS_W6UB8wECiFIP3BtlFoYQ5rKjoP18xQcOU8zgi0Zh9-Ec5g5j3fbCDzn397F3kp1rHRBKlTsDIHijsv7J6ZSQaFC0s4BoWqdSszSrm_Vu1HsEr5a_N8g1e2LSB0W8X6AHh6oAusTbM4xEUjmcKAyDfuBet-5hvF-9IrqDNl91w-YZwdhlZ19_sAMJ8TPWFxqmhXjMJ2-cXl67Y7sqjLtR2dGL9X-oKpUFOQV4PsMWW0dCuMshr7Y");'
                  ></div>
                  <div class="flex flex-col justify-center">
                    <p class="text-[#0d141c] text-[22px] font-bold leading-tight tracking-[-0.015em]">Lucía García</p>
                    <p class="text-[#49739c] text-base font-normal leading-normal">lucia.garcia@email.com</p>
                  </div>
                </div>
              </div>
            </div>
            <h2 class="text-[#0d141c] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">Favoritos</h2>
            <div class="px-4 py-3 @container">
              <div class="flex overflow-hidden rounded-lg border border-[#cedbe8] bg-slate-50">
                <table class="flex-1">
                  <thead>
                    <tr class="bg-slate-50">
                      <th class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-120 px-4 py-3 text-left text-[#0d141c] w-[400px] text-sm font-medium leading-normal">
                        Producto
                      </th>
                      <th class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-240 px-4 py-3 text-left text-[#0d141c] w-[400px] text-sm font-medium leading-normal">
                        Descripción
                      </th>
                      <th class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-360 px-4 py-3 text-left text-[#0d141c] w-60 text-[#49739c] text-sm font-medium leading-normal">
                        Acciones
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-t border-t-[#cedbe8]">
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-120 h-[72px] px-4 py-2 w-[400px] text-[#0d141c] text-sm font-normal leading-normal">
                        Teclado Mecánico
                      </td>
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-240 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        Teclado mecánico con interruptores azules
                      </td>
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-360 h-[72px] px-4 py-2 w-60 text-[#49739c] text-sm font-bold leading-normal tracking-[0.015em]">
                        Ver detalles | Eliminar
                      </td>
                    </tr>
                    <tr class="border-t border-t-[#cedbe8]">
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-120 h-[72px] px-4 py-2 w-[400px] text-[#0d141c] text-sm font-normal leading-normal">
                        Mouse Gamer
                      </td>
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-240 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        Mouse gamer con alta sensibilidad y botones programables
                      </td>
                      <td class="table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-360 h-[72px] px-4 py-2 w-60 text-[#49739c] text-sm font-bold leading-normal tracking-[0.015em]">
                        Ver detalles | Eliminar
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <style>
                          @container(max-width:120px){.table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-120{display: none;}}
                @container(max-width:240px){.table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-240{display: none;}}
                @container(max-width:360px){.table-364eb7a1-db52-408b-865a-051b0f17b8cc-column-360{display: none;}}
              </style>
            </div>
            <h2 class="text-[#0d141c] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">Historial de Comparaciones</h2>
            <div class="px-4 py-3 @container">
              <div class="flex overflow-hidden rounded-lg border border-[#cedbe8] bg-slate-50">
                <table class="flex-1">
                  <thead>
                    <tr class="bg-slate-50">
                      <th class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-120 px-4 py-3 text-left text-[#0d141c] w-[400px] text-sm font-medium leading-normal">Fecha</th>
                      <th class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-240 px-4 py-3 text-left text-[#0d141c] w-[400px] text-sm font-medium leading-normal">
                        Periféricos Comparados
                      </th>
                      <th class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-360 px-4 py-3 text-left text-[#0d141c] w-60 text-[#49739c] text-sm font-medium leading-normal">
                        Resultado
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-t border-t-[#cedbe8]">
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-120 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        2024-07-26
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-240 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        Teclado mecánico vs. Teclado de membrana
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-360 h-[72px] px-4 py-2 w-60 text-[#49739c] text-sm font-bold leading-normal tracking-[0.015em]">
                        Ver detalles
                      </td>
                    </tr>
                    <tr class="border-t border-t-[#cedbe8]">
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-120 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        2024-07-20
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-240 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        Mouse gamer vs. Mouse ergonómico
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-360 h-[72px] px-4 py-2 w-60 text-[#49739c] text-sm font-bold leading-normal tracking-[0.015em]">
                        Ver detalles
                      </td>
                    </tr>
                    <tr class="border-t border-t-[#cedbe8]">
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-120 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        2024-07-15
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-240 h-[72px] px-4 py-2 w-[400px] text-[#49739c] text-sm font-normal leading-normal">
                        Auriculares con cable vs. Auriculares inalámbricos
                      </td>
                      <td class="table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-360 h-[72px] px-4 py-2 w-60 text-[#49739c] text-sm font-bold leading-normal tracking-[0.015em]">
                        Ver detalles
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <style>
                          @container(max-width:120px){.table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-120{display: none;}}
                @container(max-width:240px){.table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-240{display: none;}}
                @container(max-width:360px){.table-4d3ef65b-6fe2-4498-bf61-c40524823f88-column-360{display: none;}}
              </style>
            </div>
            <h2 class="text-[#0d141c] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">Configuración</h2>
            <div class="flex items-center gap-4 bg-slate-50 px-4 min-h-14 justify-between">
              <p class="text-[#0d141c] text-base font-normal leading-normal flex-1 truncate">Modo Oscuro</p>
              <div class="shrink-0">
                <label
                  class="relative flex h-[31px] w-[51px] cursor-pointer items-center rounded-full border-none bg-[#e7edf4] p-0.5 has-[:checked]:justify-end has-[:checked]:bg-[#0d80f2]"
                >
                  <div class="h-full w-[27px] rounded-full bg-white" style="box-shadow: rgba(0, 0, 0, 0.15) 0px 3px 8px, rgba(0, 0, 0, 0.06) 0px 3px 1px;"></div>
                  <input type="checkbox" class="invisible absolute" />
                </label>
              </div>
            </div>
            <div class="flex items-center gap-4 bg-slate-50 px-4 min-h-14 justify-between">
              <p class="text-[#0d141c] text-base font-normal leading-normal flex-1 truncate">Editar Perfil</p>
              <div class="shrink-0">
                <div class="text-[#0d141c] flex size-7 items-center justify-center" data-icon="PencilSimple" data-size="24px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"
                    ></path>
                  </svg>
                </div>
              </div>
            </div>
            <div class="flex px-4 py-3 justify-start">
              <button
                class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#e7edf4] text-[#0d141c] text-sm font-bold leading-normal tracking-[0.015em]"
              >
                <span class="truncate">Cerrar Sesión</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
  </body>
</html>
