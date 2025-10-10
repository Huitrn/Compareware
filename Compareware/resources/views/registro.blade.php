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
    <div class="relative flex size-full min-h-screen flex-col bg-white group/design-root overflow-x-hidden" style='font-family: Inter, "Noto Sans", sans-serif;'>
      <div class="layout-container flex h-full grow flex-col">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f0f2f5] px-10 py-3">
          <div class="flex items-center gap-4 text-[#111418]">
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
          <div class="flex flex-1 justify-end gap-8">
            <div class="flex items-center gap-9">
              <a class="nav-link text-[#111418] text-sm font-medium leading-normal" href="{{ route('home') }}">Inicio</a>
              <a class="nav-link text-[#111418] text-sm font-medium leading-normal" href="{{ route('marcas') }}">Marcas</a>
            </div>
            <div class="flex gap-2">
              <button
                id="theme-toggle"
                class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-[#f0f2f5] text-[#111418] gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-0 px-2.5"
              >
                <div class="text-[#111418]" data-icon="Sun" data-size="20px" data-weight="regular">
                  <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"
                    ></path>
                  </svg>
                </div>
              </button>
              <button
                onclick="window.location.href='{{ route('login') }}'"
                class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em]"
              >
                <span class="truncate">Iniciar Sesión</span>
              </button>
            </div>
          </div>
        </header>
        <div class="px-40 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col w-[512px] max-w-[512px] py-5 max-w-[960px] flex-1">
            <h2 class="text-[#111418] tracking-light text-[28px] font-bold leading-tight px-4 text-center pb-3 pt-5">Regístrate en CompareWare</h2>
            <form id="registerForm">
              <input
                type="text"
                id="name"
                name="name"
                placeholder="Nombre de usuario"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-0 border border-[#dbe0e6] bg-white focus:border-[#dbe0e6] h-14 placeholder:text-[#60758a] p-[15px] text-base font-normal leading-normal"
                required
              />
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Correo electrónico"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-0 border border-[#dbe0e6] bg-white focus:border-[#dbe0e6] h-14 placeholder:text-[#60758a] p-[15px] text-base font-normal leading-normal"
                required
              />
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Contraseña"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-0 border border-[#dbe0e6] bg-white focus:border-[#dbe0e6] h-14 placeholder:text-[#60758a] p-[15px] text-base font-normal leading-normal"
                required
              />
              <button
                type="submit"
                class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 flex-1 bg-[#0d80f2] text-white text-sm font-bold leading-normal tracking-[0.015em]"
              >
                <span class="truncate">Regístrate</span>
              </button>
            </form>
            <div id="registerResult" class="px-4 py-2 text-red-600"></div>
            <p class="text-[#60758a] text-sm font-normal leading-normal pb-3 pt-1 px-4 text-center">O regístrate con</p>
            <div class="flex justify-center">
              <div class="flex flex-1 gap-3 flex-wrap px-4 py-3 max-w-[480px] justify-center">
                <button
                  class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em] grow"
                >
                  <span class="truncate">Regístrate con Google</span>
                </button>
                <button
                  class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em] grow"
                >
                  <span class="truncate">Regístrate con Facebook</span>
                </button>
              </div>
            </div>
            <p class="text-[#60758a] text-sm font-normal leading-normal pb-3 pt-1 px-4 text-center underline">
           ¿Ya tienes una cuenta?
            <a href="/login" class="text-blue-600 hover:underline">Inicia sesión</a>
              </p>
        </div>
      </div>
    </div>
  </div>
  <script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  
  // Validaciones básicas
  if (name.length < 2) {
    document.getElementById('registerResult').className = 'px-4 py-2 text-red-600';
    document.getElementById('registerResult').innerText = 'El nombre debe tener al menos 2 caracteres';
    return;
  }
  
  if (password.length < 6) {
    document.getElementById('registerResult').className = 'px-4 py-2 text-red-600';
    document.getElementById('registerResult').innerText = 'La contraseña debe tener al menos 6 caracteres';
    return;
  }

  const response = await fetch('http://localhost:4000/api/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ name, email, password, role: 'user' })
  });

  const result = await response.json();
  const resultDiv = document.getElementById('registerResult');
  
  if (response.ok) {
    resultDiv.className = 'px-4 py-2 text-green-600';
    resultDiv.innerText = result.message || '¡Registro exitoso!';
    // Limpiar formulario
    document.getElementById('registerForm').reset();
    // Opcional: redirigir al login después de unos segundos
    setTimeout(() => {
      window.location.href = '/login';
    }, 2000);
  } else {
    resultDiv.className = 'px-4 py-2 text-red-600';
    resultDiv.innerText = result.error || result.message || 'Error al registrar';
  }
});
</script>
<script src="{{ asset('js/theme-switcher.js') }}"></script>
  </body>
</html>
