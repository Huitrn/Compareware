// Tema Switcher Universal para CompareWare
const themeColors = {
  dark: {
    body: 'bg-[#111a22] text-white',
    header: 'bg-[#111a22] border-b-[#233648] text-white',
    button: 'bg-[#233648] text-white',
    navText: 'text-white',
    logoText: 'text-white',
    searchBg: 'bg-[#233648]',
    searchText: 'text-white',
    searchPlaceholder: 'placeholder-[#92adc9]',
    footerBg: 'bg-[#111a22]',
    footerText: 'text-[#92adc9]',
    cardBg: 'bg-[#233648]',
    cardText: 'text-white'
  },
  light: {
    body: 'bg-slate-50 text-[#0d141c]',
    header: 'bg-slate-50 border-b-[#e7edf4] text-[#0d141c]',
    button: 'bg-[#e7edf4] text-[#0d141c]',
    navText: 'text-[#0d141c]',
    logoText: 'text-[#0d141c]',
    searchBg: 'bg-[#e7edf4]',
    searchText: 'text-[#0d141c]',
    searchPlaceholder: 'placeholder-[#49739c]',
    footerBg: 'bg-slate-50',
    footerText: 'text-[#49739c]',
    cardBg: 'bg-white',
    cardText: 'text-[#0d141c]'
  }
};

function setTheme(theme) {
  localStorage.setItem('theme', theme);

  // Body principal
  const designRoot = document.querySelector('.group\\/design-root');
  const layoutContainer = document.querySelector('.layout-container');
  
  if (designRoot) {
    designRoot.classList.remove('bg-slate-50', 'bg-[#111a22]');
    designRoot.classList.add(...themeColors[theme].body.split(' '));
  }
  
  if (layoutContainer) {
    layoutContainer.classList.remove('bg-slate-50', 'bg-[#111a22]', 'text-[#0d141c]', 'text-white');
    layoutContainer.classList.add(...themeColors[theme].body.split(' '));
  }

  // Header
  const header = document.querySelector('header');
  if (header) {
    header.classList.remove('bg-slate-50', 'bg-[#111a22]', 'border-b-[#e7edf4]', 'border-b-[#233648]');
    header.classList.add(...themeColors[theme].header.split(' '));
  }

  // Botón de tema
  const themeButton = document.getElementById('theme-toggle');
  if (themeButton) {
    themeButton.className = themeButton.className.replace(/bg-\S+/g, '').replace(/text-\S+/g, '');
    themeButton.classList.add(...themeColors[theme].button.split(' '));
  }

  // Iconos de sol/luna
  const sunIcon = document.getElementById('icon-sun');
  const moonIcon = document.getElementById('icon-moon');
  if (sunIcon && moonIcon) {
    sunIcon.style.display = theme === 'light' ? 'inline' : 'none';
    moonIcon.style.display = theme === 'dark' ? 'inline' : 'none';
  }

  // Enlaces de navegación
  document.querySelectorAll('.nav-link, a[href]').forEach(link => {
    if (link.classList.contains('nav-link') || link.closest('nav')) {
      link.classList.remove('text-white', 'text-black', 'text-[#0d141c]');
      link.classList.add(...themeColors[theme].navText.split(' '));
    }
  });

  // Logo CompareWare
  document.querySelectorAll('h2, .logo-link').forEach(logo => {
    if (logo.textContent.includes('CompareWare') || logo.classList.contains('logo-link')) {
      logo.classList.remove('text-white', 'text-black', 'text-[#0d141c]');
      logo.classList.add(...themeColors[theme].logoText.split(' '));
    }
  });

  // Input de búsqueda
  document.querySelectorAll('input[type="text"], input[placeholder*="Search"], input[placeholder*="Buscar"]').forEach(input => {
    input.classList.remove('bg-[#e7edf4]', 'bg-[#233648]', 'text-[#0d141c]', 'text-white', 'placeholder-[#49739c]', 'placeholder-[#92adc9]');
    input.classList.add(...themeColors[theme].searchBg.split(' '));
    input.classList.add(...themeColors[theme].searchText.split(' '));
    input.classList.add(...themeColors[theme].searchPlaceholder.split(' '));
  });

  // Contenedor de búsqueda
  document.querySelectorAll('.bg-\\[\\#e7edf4\\]').forEach(element => {
    element.classList.remove('bg-[#e7edf4]', 'bg-[#233648]');
    element.classList.add(...themeColors[theme].searchBg.split(' '));
  });

  // Footer
  const footer = document.querySelector('footer');
  if (footer) {
    footer.classList.remove('bg-slate-50', 'bg-[#111a22]');
    footer.classList.add(...themeColors[theme].footerBg.split(' '));
    
    footer.querySelectorAll('a, p').forEach(element => {
      element.classList.remove('text-[#49739c]', 'text-[#92adc9]');
      element.classList.add(...themeColors[theme].footerText.split(' '));
    });
  }

  // Contenido principal
  const mainContent = document.querySelector('main');
  if (mainContent) {
    mainContent.classList.remove('bg-slate-50', 'bg-[#111a22]', 'text-[#0d141c]', 'text-white');
    mainContent.classList.add(...themeColors[theme].body.split(' '));
  }

  // Títulos y textos principales
  document.querySelectorAll('h1, h2, h3, p').forEach(element => {
    if (element.classList.contains('text-[#0d141c]')) {
      element.classList.remove('text-[#0d141c]', 'text-white');
      element.classList.add(theme === 'dark' ? 'text-white' : 'text-[#0d141c]');
    }
  });

  // Layout content container
  const contentContainer = document.querySelector('.layout-content-container');
  if (contentContainer) {
    contentContainer.classList.remove('bg-slate-50', 'bg-[#111a22]', 'text-[#0d141c]', 'text-white');
    contentContainer.classList.add(...themeColors[theme].body.split(' '));
  }
}

function initThemeSwitcher() {
  // Cargar tema guardado o usar oscuro por defecto
  const savedTheme = localStorage.getItem('theme') || 'light';
  setTheme(savedTheme);

  // Agregar event listener al botón de cambio de tema
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const currentTheme = localStorage.getItem('theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      setTheme(newTheme);
    });
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initThemeSwitcher);