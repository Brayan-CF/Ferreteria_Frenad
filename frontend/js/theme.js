/**
 * ========================================
 * THEME MANAGER
 * Ferretería Frenad - Sistema POS
 * ========================================
 * Gestión de temas: claro, oscuro y sistema
 */

class ThemeManager {
  constructor() {
    this.currentTheme = this.getSavedTheme();
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Escuchar cambios en las preferencias del sistema
    this.mediaQuery.addEventListener('change', (e) => {
      if (this.currentTheme === CONFIG.THEMES.SYSTEM) {
        this.applyTheme(CONFIG.THEMES.SYSTEM);
      }
    });
  }

  /**
   * Inicializa el tema
   */
  init() {
    this.applyTheme(this.currentTheme);
    this.renderThemeToggle();
  }

  /**
   * Obtiene el tema guardado o usa sistema por defecto
   */
  getSavedTheme() {
    return localStorage.getItem(CONFIG.STORAGE.THEME) || CONFIG.THEMES.SYSTEM;
  }

  /**
   * Guarda el tema seleccionado
   */
  saveTheme(theme) {
    localStorage.setItem(CONFIG.STORAGE.THEME, theme);
  }

  /**
   * Obtiene el tema efectivo (considerando preferencia del sistema)
   */
  getEffectiveTheme() {
    if (this.currentTheme === CONFIG.THEMES.SYSTEM) {
      return this.mediaQuery.matches ? CONFIG.THEMES.DARK : CONFIG.THEMES.LIGHT;
    }
    return this.currentTheme;
  }

  /**
   * Aplica el tema al documento
   */
  applyTheme(theme) {
    this.currentTheme = theme;
    this.saveTheme(theme);

    const effectiveTheme = this.getEffectiveTheme();
    
    // Aplicar al documento
    document.documentElement.setAttribute('data-theme', effectiveTheme);
    
    // Actualizar meta theme-color para móviles
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
      metaThemeColor.content = effectiveTheme === CONFIG.THEMES.DARK ? '#1a1a2e' : '#ffffff';
    }

    // Actualizar botones del toggle si existen
    this.updateToggleButtons();
  }

  /**
   * Cambia al siguiente tema (ciclo: light -> dark -> system)
   */
  toggleTheme() {
    const themes = [CONFIG.THEMES.LIGHT, CONFIG.THEMES.DARK, CONFIG.THEMES.SYSTEM];
    const currentIndex = themes.indexOf(this.currentTheme);
    const nextIndex = (currentIndex + 1) % themes.length;
    this.applyTheme(themes[nextIndex]);
  }

  /**
   * Establece un tema específico
   */
  setTheme(theme) {
    if (Object.values(CONFIG.THEMES).includes(theme)) {
      this.applyTheme(theme);
    }
  }

  /**
   * Renderiza el toggle de tema en el header
   */
  renderThemeToggle() {
    const container = document.querySelector('.theme-toggle');
    if (!container) return;

    container.innerHTML = `
      <button type="button" class="theme-toggle-btn" data-theme="light" title="Tema claro">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
      </button>
      <button type="button" class="theme-toggle-btn" data-theme="dark" title="Tema oscuro">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
      <button type="button" class="theme-toggle-btn" data-theme="system" title="Tema del sistema">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
          <line x1="8" y1="21" x2="16" y2="21"></line>
          <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
      </button>
    `;

    // Event listeners para cada botón
    container.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        this.setTheme(btn.dataset.theme);
      });
    });

    this.updateToggleButtons();
  }

  /**
   * Actualiza el estado activo de los botones del toggle
   */
  updateToggleButtons() {
    const buttons = document.querySelectorAll('.theme-toggle-btn');
    buttons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.theme === this.currentTheme);
    });
  }

  /**
   * Verifica si el tema actual es oscuro
   */
  isDark() {
    return this.getEffectiveTheme() === CONFIG.THEMES.DARK;
  }

  /**
   * Verifica si el tema actual es claro
   */
  isLight() {
    return this.getEffectiveTheme() === CONFIG.THEMES.LIGHT;
  }

  /**
   * Verifica si está usando preferencia del sistema
   */
  isSystem() {
    return this.currentTheme === CONFIG.THEMES.SYSTEM;
  }
}

// Crear instancia global
const themeManager = new ThemeManager();

// Inicializar tema lo antes posible para evitar flash
(function() {
  const savedTheme = localStorage.getItem(CONFIG.STORAGE.THEME) || CONFIG.THEMES.SYSTEM;
  let effectiveTheme = savedTheme;
  
  if (savedTheme === CONFIG.THEMES.SYSTEM) {
    effectiveTheme = window.matchMedia('(prefers-color-scheme: dark)').matches 
      ? CONFIG.THEMES.DARK 
      : CONFIG.THEMES.LIGHT;
  }
  
  document.documentElement.setAttribute('data-theme', effectiveTheme);
})();

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  themeManager.init();
});

// Export para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { ThemeManager, themeManager };
}
