/**
 * ========================================
 * UTILITIES
 * Ferretería Frenad - Sistema POS
 * ========================================
 * Funciones de utilidad generales
 */

const Utils = {
  // ========================================
  // FORMATEO
  // ========================================

  /**
   * Formatea un número como moneda
   */
  formatCurrency(value) {
    const num = parseFloat(value) || 0;
    return `${CONFIG.FORMATS.CURRENCY.symbol} ${num.toFixed(2)}`;
  },

  /**
   * Formatea un número con separadores de miles
   */
  formatNumber(value, decimals = 0) {
    const num = parseFloat(value) || 0;
    return num.toLocaleString(CONFIG.FORMATS.CURRENCY.locale, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  },

  /**
   * Formatea una fecha
   */
  formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString(CONFIG.FORMATS.DATE.locale, CONFIG.FORMATS.DATE.options);
  },

  /**
   * Formatea fecha y hora
   */
  formatDateTime(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString(CONFIG.FORMATS.DATETIME.locale, CONFIG.FORMATS.DATETIME.options);
  },

  /**
   * Formatea fecha para inputs (YYYY-MM-DD)
   */
  formatDateInput(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
  },

  /**
   * Obtiene la fecha actual en formato YYYY-MM-DD
   */
  getCurrentDate() {
    return new Date().toISOString().split('T')[0];
  },

  /**
   * Obtiene el primer día del mes actual
   */
  getFirstDayOfMonth() {
    const date = new Date();
    return new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
  },

  /**
   * Obtiene el último día del mes actual
   */
  getLastDayOfMonth() {
    const date = new Date();
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
  },

  // ========================================
  // VALIDACIONES
  // ========================================

  /**
   * Valida un email
   */
  isValidEmail(email) {
    return CONFIG.VALIDATION.EMAIL_REGEX.test(email);
  },

  /**
   * Valida un NIT
   */
  isValidNIT(nit) {
    return CONFIG.VALIDATION.NIT_REGEX.test(nit);
  },

  /**
   * Valida un teléfono
   */
  isValidPhone(phone) {
    return CONFIG.VALIDATION.PHONE_REGEX.test(phone);
  },

  /**
   * Valida que un campo no esté vacío
   */
  isNotEmpty(value) {
    return value !== null && value !== undefined && String(value).trim() !== '';
  },

  /**
   * Valida longitud mínima
   */
  hasMinLength(value, min) {
    return String(value).length >= min;
  },

  /**
   * Valida que sea un número positivo
   */
  isPositiveNumber(value) {
    const num = parseFloat(value);
    return !isNaN(num) && num > 0;
  },

  // ========================================
  // DOM HELPERS
  // ========================================

  /**
   * Selecciona un elemento del DOM
   */
  $(selector, parent = document) {
    return parent.querySelector(selector);
  },

  /**
   * Selecciona múltiples elementos del DOM
   */
  $$(selector, parent = document) {
    return parent.querySelectorAll(selector);
  },

  /**
   * Crea un elemento HTML con atributos y contenido
   */
  createElement(tag, attributes = {}, content = '') {
    const element = document.createElement(tag);
    
    Object.entries(attributes).forEach(([key, value]) => {
      if (key === 'className') {
        element.className = value;
      } else if (key === 'dataset') {
        Object.entries(value).forEach(([dataKey, dataValue]) => {
          element.dataset[dataKey] = dataValue;
        });
      } else if (key.startsWith('on') && typeof value === 'function') {
        element.addEventListener(key.substring(2).toLowerCase(), value);
      } else {
        element.setAttribute(key, value);
      }
    });

    if (content) {
      if (typeof content === 'string') {
        element.innerHTML = content;
      } else if (content instanceof HTMLElement) {
        element.appendChild(content);
      }
    }

    return element;
  },

  /**
   * Muestra un elemento
   */
  show(element) {
    if (element) element.classList.remove('hidden');
  },

  /**
   * Oculta un elemento
   */
  hide(element) {
    if (element) element.classList.add('hidden');
  },

  /**
   * Toggle de visibilidad
   */
  toggle(element) {
    if (element) element.classList.toggle('hidden');
  },

  /**
   * Agrega clase a elemento
   */
  addClass(element, className) {
    if (element) element.classList.add(className);
  },

  /**
   * Remueve clase de elemento
   */
  removeClass(element, className) {
    if (element) element.classList.remove(className);
  },

  // ========================================
  // NOTIFICACIONES
  // ========================================

  /**
   * Muestra una notificación toast
   */
  showToast(message, type = 'info', duration = 3000) {
    // Crear contenedor si no existe
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
      `;
      document.body.appendChild(container);
    }

    // Crear toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = `
      min-width: 300px;
      animation: slideIn 0.3s ease;
    `;
    
    // Icono según tipo
    const icons = {
      success: '✓',
      danger: '✕',
      warning: '⚠',
      info: 'ℹ',
    };

    toast.innerHTML = `
      <span class="alert-icon">${icons[type] || icons.info}</span>
      <span class="alert-message">${message}</span>
    `;

    container.appendChild(toast);

    // Auto-remover después del tiempo especificado
    setTimeout(() => {
      toast.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  /**
   * Muestra toast de éxito
   */
  showSuccess(message) {
    this.showToast(message, 'success');
  },

  /**
   * Muestra toast de error
   */
  showError(message) {
    this.showToast(message, 'danger', 5000);
  },

  /**
   * Muestra toast de advertencia
   */
  showWarning(message) {
    this.showToast(message, 'warning');
  },

  /**
   * Muestra toast de información
   */
  showInfo(message) {
    this.showToast(message, 'info');
  },

  // ========================================
  // MODALES
  // ========================================

  /**
   * Abre un modal
   */
  openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  },

  /**
   * Cierra un modal
   */
  closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  },

  /**
   * Cierra todos los modales
   */
  closeAllModals() {
    document.querySelectorAll('.modal-backdrop.active').forEach(modal => {
      modal.classList.remove('active');
    });
    document.body.style.overflow = '';
  },

  /**
   * Modal de confirmación
   */
  async confirm(message, title = 'Confirmar') {
    return new Promise((resolve) => {
      // Crear modal de confirmación
      const modalId = 'confirm-modal-' + Date.now();
      const modal = document.createElement('div');
      modal.id = modalId;
      modal.className = 'modal-backdrop';
      modal.innerHTML = `
        <div class="modal modal-sm">
          <div class="modal-header">
            <h3 class="modal-title">${title}</h3>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-action="cancel">Cancelar</button>
            <button type="button" class="btn btn-primary" data-action="confirm">Confirmar</button>
          </div>
        </div>
      `;

      document.body.appendChild(modal);
      
      // Activar modal
      setTimeout(() => modal.classList.add('active'), 10);

      // Event listeners
      modal.addEventListener('click', (e) => {
        const action = e.target.dataset.action;
        if (action === 'confirm') {
          resolve(true);
        } else if (action === 'cancel' || e.target === modal) {
          resolve(false);
        } else {
          return;
        }
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
      });
    });
  },

  // ========================================
  // LOADING
  // ========================================

  /**
   * Muestra overlay de carga
   */
  showLoading(message = 'Cargando...') {
    let overlay = document.getElementById('loading-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'loading-overlay';
      overlay.className = 'loading-overlay';
      overlay.innerHTML = `
        <div class="spinner"></div>
        <p class="loading-message" style="margin-top: 16px; color: var(--text-primary);">${message}</p>
      `;
      document.body.appendChild(overlay);
    } else {
      overlay.querySelector('.loading-message').textContent = message;
    }
    overlay.classList.add('active');
  },

  /**
   * Oculta overlay de carga
   */
  hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
      overlay.classList.remove('active');
    }
  },

  // ========================================
  // FORMULARIOS
  // ========================================

  /**
   * Serializa un formulario a objeto
   */
  serializeForm(form) {
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
      // Manejar campos con mismo nombre (arrays)
      if (data[key]) {
        if (!Array.isArray(data[key])) {
          data[key] = [data[key]];
        }
        data[key].push(value);
      } else {
        data[key] = value;
      }
    });

    return data;
  },

  /**
   * Llena un formulario con datos
   */
  fillForm(form, data) {
    Object.entries(data).forEach(([key, value]) => {
      const field = form.querySelector(`[name="${key}"]`);
      if (field) {
        if (field.type === 'checkbox') {
          field.checked = !!value;
        } else if (field.type === 'radio') {
          const radio = form.querySelector(`[name="${key}"][value="${value}"]`);
          if (radio) radio.checked = true;
        } else {
          field.value = value ?? '';
        }
      }
    });
  },

  /**
   * Limpia un formulario
   */
  clearForm(form) {
    form.reset();
    // Limpiar clases de error
    form.querySelectorAll('.form-control').forEach(field => {
      field.classList.remove('error');
    });
    form.querySelectorAll('.form-error').forEach(error => {
      error.textContent = '';
    });
  },

  /**
   * Muestra errores de validación en el formulario
   */
  showFormErrors(form, errors) {
    // Limpiar errores previos
    form.querySelectorAll('.form-control').forEach(field => {
      field.classList.remove('error');
    });
    form.querySelectorAll('.form-error').forEach(error => {
      error.textContent = '';
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, messages]) => {
      const input = form.querySelector(`[name="${field}"]`);
      const errorElement = form.querySelector(`[data-error="${field}"]`);
      
      if (input) {
        input.classList.add('error');
      }
      if (errorElement) {
        errorElement.textContent = Array.isArray(messages) ? messages[0] : messages;
      }
    });
  },

  // ========================================
  // DEBOUNCE / THROTTLE
  // ========================================

  /**
   * Debounce - Retrasa la ejecución hasta que deje de llamarse
   */
  debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Throttle - Limita la ejecución a una vez por intervalo
   */
  throttle(func, limit = 300) {
    let inThrottle;
    return function executedFunction(...args) {
      if (!inThrottle) {
        func(...args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  },

  // ========================================
  // STRINGS
  // ========================================

  /**
   * Capitaliza la primera letra
   */
  capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  },

  /**
   * Trunca un string
   */
  truncate(str, length = 50) {
    if (!str || str.length <= length) return str;
    return str.substring(0, length) + '...';
  },

  /**
   * Genera un ID único
   */
  generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  },

  // ========================================
  // LOCAL STORAGE HELPERS
  // ========================================

  /**
   * Guarda un objeto en localStorage
   */
  setStorage(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (error) {
      console.error('Storage error:', error);
      return false;
    }
  },

  /**
   * Obtiene un objeto de localStorage
   */
  getStorage(key, defaultValue = null) {
    try {
      const item = localStorage.getItem(key);
      return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
      console.error('Storage error:', error);
      return defaultValue;
    }
  },

  /**
   * Elimina un item de localStorage
   */
  removeStorage(key) {
    localStorage.removeItem(key);
  },

  // ========================================
  // URL HELPERS
  // ========================================

  /**
   * Obtiene parámetros de la URL
   */
  getUrlParams() {
    return Object.fromEntries(new URLSearchParams(window.location.search));
  },

  /**
   * Obtiene un parámetro específico de la URL
   */
  getUrlParam(name) {
    return new URLSearchParams(window.location.search).get(name);
  },

  /**
   * Actualiza parámetros de la URL sin recargar
   */
  updateUrlParams(params) {
    const url = new URL(window.location);
    Object.entries(params).forEach(([key, value]) => {
      if (value === null || value === undefined || value === '') {
        url.searchParams.delete(key);
      } else {
        url.searchParams.set(key, value);
      }
    });
    window.history.replaceState({}, '', url);
  },
};

// Agregar estilos para animaciones de toast
const toastStyles = document.createElement('style');
toastStyles.textContent = `
  @keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
  }
`;
document.head.appendChild(toastStyles);

// Export para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Utils;
}
