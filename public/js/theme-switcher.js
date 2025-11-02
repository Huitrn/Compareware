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
    cardText: 'text-white',
    // Estilos específicos para comparadora - MEJORADOS
    heroSection: 'bg-[#1e293b] text-white',
    categoryTab: 'bg-[#1e293b] text-white border-gray-600',
    resultSection: 'bg-[#0f172a] text-white',
    apiSection: 'bg-green-900/20 border-green-500/30 text-white',
    amazonSection: 'bg-orange-900/20 border-orange-500/30 text-white'
  },
  light: {
    body: 'bg-slate-50 text-[#0d141c]',
    header: 'bg-white border-b-[#e7edf4] text-[#0d141c]',
    button: 'bg-[#e7edf4] text-[#0d141c]',
    navText: 'text-[#0d141c]',
    logoText: 'text-[#0d141c]',
    searchBg: 'bg-[#e7edf4]',
    searchText: 'text-[#0d141c]',
    searchPlaceholder: 'placeholder-[#49739c]',
    footerBg: 'bg-slate-50',
    footerText: 'text-[#49739c]',
    cardBg: 'bg-white',
    cardText: 'text-[#0d141c]',
    // Estilos específicos para comparadora - MEJORADOS
    heroSection: 'bg-white text-[#0d141c]',
    categoryTab: 'bg-white text-[#0d141c] border-gray-200',
    resultSection: 'bg-white text-[#0d141c]',
    apiSection: 'bg-green-50 border-green-200 text-[#0d141c]',
    amazonSection: 'bg-orange-50 border-orange-200 text-[#0d141c]'
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

  // Títulos y textos principales - MEJORADO
  document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div, label').forEach(element => {
    // Remover clases de color existentes
    element.classList.remove(
      'text-[#0d141c]', 'text-white', 'text-black', 'text-gray-300', 
      'text-gray-400', 'text-gray-500', 'text-slate-600', 'text-slate-700'
    );
    
    // Si el elemento tiene texto y no tiene una clase de color específica, aplicar el tema
    if (element.textContent.trim() !== '' && !element.classList.contains('text-green-400') && 
        !element.classList.contains('text-blue-400') && !element.classList.contains('text-orange-400') &&
        !element.classList.contains('text-red-400') && !element.classList.contains('text-purple-400')) {
      element.classList.add(theme === 'dark' ? 'text-white' : 'text-[#0d141c]');
    }
  });

  // Layout content container
  const contentContainer = document.querySelector('.layout-content-container');
  if (contentContainer) {
    contentContainer.classList.remove('bg-slate-50', 'bg-[#111a22]', 'text-[#0d141c]', 'text-white');
    contentContainer.classList.add(...themeColors[theme].body.split(' '));
  }

  // === ESTILOS ESPECÍFICOS PARA COMPARADORA ===
  
  // Hero section
  const heroSection = document.getElementById('hero-section');
  if (heroSection) {
    heroSection.classList.remove('bg-[#1e293b]', 'bg-blue-50');
    heroSection.classList.add(...themeColors[theme].heroSection.split(' '));
  }

  // Pestañas de categorías
  document.querySelectorAll('.categoria-tab').forEach(tab => {
    tab.classList.remove('bg-[#1e293b]', 'text-white', 'bg-white', 'text-[#0d141c]');
    tab.classList.add(...themeColors[theme].categoryTab.split(' '));
  });

  // Secciones de resultados con fondo oscuro/claro
  document.querySelectorAll('.bg-\\[\\#0f172a\\], .bg-\\[\\#1e293b\\]').forEach(element => {
    element.classList.remove('bg-[#0f172a]', 'bg-[#1e293b]', 'bg-gray-50', 'bg-white');
    element.classList.add(...themeColors[theme].resultSection.split(' '));
  });

  // Secciones de API (verde)
  document.querySelectorAll('.bg-green-900\\/20').forEach(element => {
    element.classList.remove('bg-green-900/20', 'border-green-500/30', 'bg-green-50', 'border-green-200');
    element.classList.add(...themeColors[theme].apiSection.split(' '));
  });

  // Secciones de Amazon (naranja)
  document.querySelectorAll('.bg-orange-900\\/20').forEach(element => {
    element.classList.remove('bg-orange-900/20', 'border-orange-500/30', 'bg-orange-50', 'border-orange-200');
    element.classList.add(...themeColors[theme].amazonSection.split(' '));
  });

  // === CORRECCIÓN ESPECÍFICA DE TEXTOS PROBLEMÁTICOS ===
  
  // Textos en cards de productos
  document.querySelectorAll('.card, .product-card, [class*="card"]').forEach(card => {
    const textos = card.querySelectorAll('p, span, div:not([class*="bg-"]):not([class*="border"])');
    textos.forEach(texto => {
      if (texto.textContent.trim() !== '') {
        texto.classList.remove('text-white', 'text-[#0d141c]', 'text-gray-300', 'text-gray-500');
        texto.classList.add(theme === 'dark' ? 'text-white' : 'text-[#0d141c]');
      }
    });
  });

  // Textos en el hero section
  const heroTexts = document.querySelectorAll('#hero-section h1, #hero-section p, #hero-section span');
  heroTexts.forEach(text => {
    text.classList.remove('text-white', 'text-[#0d141c]');
    text.classList.add(theme === 'dark' ? 'text-white' : 'text-[#0d141c]');
  });

  // Textos de las pestañas de categorías
  document.querySelectorAll('.categoria-tab').forEach(tab => {
    tab.classList.remove('text-white', 'text-[#0d141c]');
    tab.classList.add(theme === 'dark' ? 'text-white' : 'text-[#0d141c]');
  });

  // Forzar cambio de colores según el tema
  if (theme === 'light') {
    // En modo claro, cambiar elementos con texto blanco a negro
    document.querySelectorAll('.text-white').forEach(element => {
      if (!element.closest('.bg-blue-500, .bg-green-500, .bg-red-500, .bg-purple-500')) {
        element.classList.remove('text-white');
        element.classList.add('text-[#0d141c]');
        element.style.color = '#0d141c';
      }
    });
  } else {
    // En modo oscuro, cambiar elementos con texto negro a blanco
    document.querySelectorAll('.text-\\[\\#0d141c\\], .text-black, .text-gray-900').forEach(element => {
      if (!element.closest('.bg-white, .bg-gray-100, .bg-blue-50')) {
        element.classList.remove('text-[#0d141c]', 'text-black', 'text-gray-900');
        element.classList.add('text-white');
        element.style.color = 'white';
      }
    });
  }

  // Aplicar estilos específicos a elementos problemáticos
  if (theme === 'light') {
    // En modo claro, asegurar que los textos sean oscuros
    document.querySelectorAll('div, p, span, h1, h2, h3, h4, h5, h6').forEach(element => {
      if (element.style.color === 'white' || element.style.color === '#ffffff' || element.style.color === 'rgb(255, 255, 255)') {
        element.style.color = '#0d141c';
      }
    });
  } else {
    // En modo oscuro, asegurar que los textos sean claros
    document.querySelectorAll('div, p, span, h1, h2, h3, h4, h5, h6').forEach(element => {
      if (element.style.color === 'black' || element.style.color === '#000000' || 
          element.style.color === '#0d141c' || element.style.color === 'rgb(0, 0, 0)' ||
          element.style.color === 'rgb(13, 20, 28)') {
        element.style.color = 'white';
      }
    });
  }
}

// Función para corregir textos problemáticos después del cambio de tema
function forceTextCorrection(theme) {
  setTimeout(() => {
    console.log(`🎨 Aplicando corrección de textos para tema: ${theme}`);
    
    // Seleccionar todos los elementos con texto
    const allTextElements = document.querySelectorAll('*');
    
    allTextElements.forEach(element => {
      // Solo procesar elementos con texto directo (no contenedores)
      if (element.children.length === 0 && element.textContent.trim() !== '') {
        const computedStyle = window.getComputedStyle(element);
        const currentColor = computedStyle.color;
        
        if (theme === 'light') {
          // En modo claro, cambiar textos blancos o muy claros a negro
          if (currentColor === 'rgb(255, 255, 255)' || 
              currentColor === 'white' ||
              element.classList.contains('text-white') ||
              element.classList.contains('text-gray-300')) {
            
            element.classList.remove('text-white', 'text-gray-300', 'text-gray-400');
            element.classList.add('text-[#0d141c]');
            element.style.color = '#0d141c';
          }
        } else {
          // En modo oscuro, cambiar textos negros o muy oscuros a blanco
          if (currentColor === 'rgb(13, 20, 28)' || 
              currentColor === 'rgb(0, 0, 0)' ||
              currentColor === 'black' ||
              currentColor === '#0d141c' ||
              element.classList.contains('text-[#0d141c]') ||
              element.classList.contains('text-black') ||
              element.classList.contains('text-gray-900') ||
              element.classList.contains('text-gray-800') ||
              element.classList.contains('text-gray-700')) {
            
            element.classList.remove('text-[#0d141c]', 'text-black', 'text-gray-700', 'text-gray-800', 'text-gray-900');
            element.classList.add('text-white');
            element.style.color = 'white';
          }
        }
      }
    });

    // === CORRECCIÓN ESPECÍFICA ADICIONAL PARA TEMA OSCURO ===
    if (theme === 'dark') {
      // Corregir elementos específicos que suelen tener problemas
      
      // Títulos y textos principales
      document.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(title => {
        if (!title.classList.contains('text-white')) {
          title.classList.remove('text-[#0d141c]', 'text-black', 'text-gray-700');
          title.classList.add('text-white');
          title.style.color = 'white';
        }
      });

      // Párrafos y spans
      document.querySelectorAll('p, span').forEach(text => {
        if (text.textContent.trim() !== '' && 
            !text.classList.contains('text-blue-400') && 
            !text.classList.contains('text-green-400') &&
            !text.classList.contains('text-orange-400') &&
            !text.classList.contains('text-red-400')) {
          
          text.classList.remove('text-[#0d141c]', 'text-black', 'text-gray-700');
          text.classList.add('text-white');
          text.style.color = 'white';
        }
      });

      // Elementos con clases específicas problemáticas
      document.querySelectorAll('.text-\\[\\#0d141c\\]').forEach(element => {
        element.classList.remove('text-[#0d141c]');
        element.classList.add('text-white');
        element.style.color = 'white';
      });

      // Forzar textos en cards y contenedores
      document.querySelectorAll('div, section, article').forEach(container => {
        if (container.children.length === 0 && container.textContent.trim() !== '') {
          container.classList.remove('text-[#0d141c]', 'text-black');
          container.classList.add('text-white');
          container.style.color = 'white';
        }
      });
    }
    
    console.log(`✅ Corrección de textos completada para tema: ${theme}`);
  }, 100); // Pequeño delay para asegurar que los estilos se hayan aplicado
}

function initThemeSwitcher() {
  // Cargar tema guardado o usar claro por defecto
  const savedTheme = localStorage.getItem('theme') || 'light';
  setTheme(savedTheme);
  forceTextCorrection(savedTheme);

  // Agregar event listener al botón de cambio de tema
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const currentTheme = localStorage.getItem('theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      setTheme(newTheme);
      forceTextCorrection(newTheme);
    });
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initThemeSwitcher);