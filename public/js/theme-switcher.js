// 🎨 Tema Switcher para CompareWare
// Usa el sistema de dark mode de Tailwind CSS con clase 'dark' en <html>

function setTheme(theme) {
  // Guardar preferencia en localStorage
  localStorage.setItem('theme', theme);
  
  // Aplicar o remover clase 'dark' en el elemento HTML
  const htmlElement = document.documentElement;
  
  if (theme === 'dark') {
    htmlElement.classList.add('dark');
  } else {
    htmlElement.classList.remove('dark');
  }
  
  // Actualizar ícono del botón de tema
  updateThemeIcon(theme);
  
  console.log(`✅ Tema cambiado a: ${theme}`);
}

function updateThemeIcon(theme) {
  // Actualizar ícono dentro del botón de tema
  const themeIcon = document.getElementById('theme-icon');
  if (themeIcon) {
    // Mantener las clases de color que ya tiene
    themeIcon.className = 'text-amber-500 dark:text-amber-300';
    
    if (theme === 'dark') {
      // Icono de luna para modo oscuro
      themeIcon.innerHTML = `<path d="M233.54,142.23a8,8,0,0,0-8-2,88.08,88.08,0,0,1-109.8-109.8,8,8,0,0,0-10-10,104.84,104.84,0,0,0-52.91,37A104,104,0,0,0,136,224a103.09,103.09,0,0,0,62.52-20.88,104.84,104.84,0,0,0,37-52.91A8,8,0,0,0,233.54,142.23ZM188.9,190.34A88,88,0,0,1,65.66,67.11a89,89,0,0,1,31.4-26A106,106,0,0,0,96,56A104.11,104.11,0,0,0,200,160a106,106,0,0,0,14.92-1.06A89,89,0,0,1,188.9,190.34Z"></path>`;
    } else {
      // Icono de sol para modo claro
      themeIcon.innerHTML = `<path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path>`;
    }
  }
}

function initThemeSwitcher() {
  // Cargar tema guardado o usar claro por defecto
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
