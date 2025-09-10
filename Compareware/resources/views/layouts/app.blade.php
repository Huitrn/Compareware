
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CompareWare')</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?display=swap&amp;family=Inter%3Awght%40400%3B500%3B700%3B900&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-[#0d141c]" style='font-family: Inter, "Noto Sans", sans-serif;'>
    <header class="flex items-center justify-between whitespace-nowrap border-b border-solid px-10 py-3 shadow-lg bg-slate-50 border-b-[#e7edf4] text-[#0d141c]">
        <nav class="flex gap-8">
            <a href="/" class="font-bold">Inicio</a>
            <a href="/marca" class="font-bold">Marca</a>
            <a href="/contacto" class="font-bold">Contacto</a>
        </nav>
        <button id="theme-toggle" class="flex cursor-pointer items-center justify-center rounded-lg h-10 bg-[#e7edf4] text-[#0d141c] gap-2 text-sm font-bold px-2.5">
            <span id="theme-icon">
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                    <path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path>
                </svg>
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256" style="display:none;">
                    <path d="M228.13,158.16a8,8,0,0,0-8.94,1.73A96,96,0,0,1,96.11,36.81a8,8,0,0,0-9.67-9.67A112,112,0,1,0,229.86,167.1,8,8,0,0,0,228.13,158.16ZM128,224A96,96,0,0,1,48.11,48.11,112,112,0,0,0,207.89,207.89,96.11,96.11,0,0,1,128,224Z"></path>
                </svg>
            </span>
        </button>
        <!-- Aquí puedes agregar el avatar si lo necesitas -->
    </header>
    <main>
        @yield('content')
    </main>
    <script>
      const themeColors = {
        dark: {
          body: 'bg-[#111a22] text-white',
          header: 'bg-[#111a22] border-b-[#233648] text-white',
          button: 'bg-[#233648] text-white',
        },
        light: {
          body: 'bg-slate-50 text-[#0d141c]',
          header: 'bg-slate-50 border-b-[#e7edf4] text-[#0d141c]',
          button: 'bg-[#e7edf4] text-[#0d141c]',
        }
      };

      function setTheme(theme) {
        localStorage.setItem('theme', theme);
        document.body.className = themeColors[theme].body;
        const header = document.querySelector('header');
        if (header) {
          header.className = `flex items-center justify-between whitespace-nowrap border-b border-solid px-10 py-3 shadow-lg ${themeColors[theme].header}`;
        }
        const themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
          themeBtn.className = `flex cursor-pointer items-center justify-center rounded-lg h-10 gap-2 text-sm font-bold px-2.5 ${themeColors[theme].button}`;
        }
        document.getElementById('icon-sun').style.display = theme === 'light' ? 'inline' : 'none';
        document.getElementById('icon-moon').style.display = theme === 'dark' ? 'inline' : 'none';
      }

      document.addEventListener('DOMContentLoaded', function () {
        const theme = localStorage.getItem('theme') || 'light';
        setTheme(theme);

        document.getElementById('theme-toggle').addEventListener('click', function () {
          const newTheme = localStorage.getItem('theme') === 'dark' ? 'light' : 'dark';
          setTheme(newTheme);
        });
      });
    </script>
</body>
</html>