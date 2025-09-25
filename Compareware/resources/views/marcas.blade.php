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
              <h2 class="text-[#0d141c] text-lg font-bold leading-tight tracking-[-0.015em]">CompareWare</h2>
            </div>
            <div class="flex items-center gap-9">
              <a class="text-[#0d141c] text-sm font-medium leading-normal" href="#">Inicio</a>
              <a class="text-[#0d141c] text-sm font-medium leading-normal" href="#">Marcas</a>
              <a class="text-[#0d141c] text-sm font-medium leading-normal" href="#">Contacto</a>
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
              class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#e7edf4] text-[#0d141c] gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5"
            >
              <div class="text-[#0d141c]" data-icon="Sun" data-size="20px" data-weight="regular">
                <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                  <path
                    d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"
                  ></path>
                </svg>
              </div>
            </button>
            <div
              class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
              style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuADwkjq3QGdj2I_v6ukQlP9YZkzMqfEOuSpSjqUDPKf22K8DnxgjfpEtnoCAf7-s9MNnTZoiYt3fNCylkLNS9XYxIsQLaJW6laqNQLUgnnyK_JHA1nzpQmXtH3c1-AiP7QciUCp7MUF4Jcgy4J-KMrYrG9XS8DjmYzMfXeL-vvMezy1bmt9FppDx3b12fn9MtFUmuBMWkfCIRny6UkqVyg9F1GJ21gM1oAhACoAfU1CIvEW-bPv83kJkl59crpi3U-bv6ApUsb-D_c");'
            ></div>
          </div>
        </header>
        <div class="px-40 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <div class="flex flex-wrap justify-between gap-3 p-4"><p class="text-[#0d141c] tracking-light text-[32px] font-bold leading-tight min-w-72">Marcas</p></div>
            <div class="px-4 py-3">
              <label class="flex flex-col min-w-40 h-12 w-full">
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
                    placeholder="Buscar marca"
                    class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] focus:outline-0 focus:ring-0 border-none bg-[#e7edf4] focus:border-none h-full placeholder:text-[#49739c] px-4 rounded-l-none border-l-0 pl-2 text-base font-normal leading-normal"
                    value=""
                  />
                </div>
              </label>
            </div>
            <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-3 p-4">
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCMhwOPMYN-gZGiguDFMB1F4ZwiHg9nFGdE86M8-ZDxlSuq5TlnNJSaxFtKGE7uxMUgWbxmjNLTDJH10JHcc11y7GVrdbxnbe2Lh2dVPvKAwxnMfW06NrUU1eZmONqr5jn5aSnKTzqtBQSDg3xTyeH2Uv6vufncZxAQzlKEGiqxM4b4ARHmjg-PlnXAKYn_Mp9nGNp1dCqtSPmyCnJNi7Riurd6BVEqbXaPn60PYGJVhM0VNuTkxZ1KS58U0_MJuNy5AkZge3rugmQ");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">TechMaster</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAckuDdU8wGKpvLzty9HjkNL-lg7uX-XMzdSSFpZYYQUoOGTsDEma1q3Ks6P-tj6P-v4cNe8OcxefSylUoVQjqEGl5zpjaUDO2T2ZngP_OTHYcydjXle13GdgVh2sOvQ6GeAHe2cEYh3Wbd7dzB052SYBRjElsRreSE-shrdbHy-uYCJqGUotYBHPCRnNEm60d9jFUqapuuVED4TRG3mwIG9TAoCY74um3xRd0y72LFnsGp1Lr9C-3hMnXlllWlJ7ktS0IVH8h-7jY");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">SonicGear</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAi0vs0yy1nc1an2Rch2ZWh_OuOjclO1NNWHxI3ClVf_Ot2JrLaKjv-6BCwyjqVPEhNlbplqazZB02boRl_556o5eaaxw0WhcvqtcdW-GkanoREmsGwdMupx9MHolhQaOQPpY3GgpsP8jHSfdKglqMA6jzXVZsWFprUx4zRtM1bVDBT9-PnMZ8AxnccMy05oQf9YFH2WjKJRQdG2c2rDWHoz7za6p7OS3bdDjYBUScBbXSgDkzdpB2GKs3Ia4YyPZJIG4Pc-4GDTXA");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">ErgoTech</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCgP60L_6oN1moeDCQEilEVa9endYmodroEyYzqHTWgNeX_PmntR8xdzfmIB0NRDhCjJz8tXDpv248DhoKkio00FBMdDndeMDGreG8rJvb5nQeGHpmpe0SPHJWd1AW-1MgjhhnBd7-Zn3bILj4AbITds3bJL9wbKUvzQ76HhkFVwuSvb5uIPVB8drEQDd0JE0eJiawiNa2JKehrGG6cJZ-SEibQR3xw_tz5VPLLze4FFMg4xaTp3hHdC5XKjyHLO1cwCReyRx5k9qc");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">ClickCraft</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDNYzPtJmYdbNw2oFC3wpORSM-alsCBLA4696Nl47tNawRlkxZ3CPOClxAZ2meKRgL1Z6KF5EJ1wh_oknqQYsfhp7l4iMvW9o2hqnijgGnUK1meTd_R5CRcZIQtT197OEQOUrRwC3WPj98ZGPIjvdPJdiKmbzxUJkGi4kGyQvvIjIiIG98gHsv5pVYmwRxwpTBjjaf1_4YC5D-L7AJY-y3QSpk4QD-7etN8MPVohg8xGfrdhy9T9kBUMYCYH9lo-ePOiVQ2rTukFVU");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">AudioWave</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBrEu3xe2_q6_ZP6PrmGuDBp-W81CGBhOFRsbhE2_GbT36cnvl-Dpp4lBFsB5l-Yg2qNLj0TVNgJ9H0jvVRBSZqy7RWjuOrhZOJAM59zISpJxKTutXiVWcsusDcaq2I4thfUON_2SsiBB_Hdl7HQVdLWzbd9ktHnXzPJg2QYXRe6hpxXEWW-fGvpwcxtLXHhn8zgwI4q6kcTBUDozoydkQlMnzPC6MuwvUkJspCHoc2rFRy6mXk4_JjTCYRR_ZeO4l3S0zzMwcXIHE");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">Visionary</p>
              </div>
            </div>
          </div>
        </div>
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
  </body>
</html>
