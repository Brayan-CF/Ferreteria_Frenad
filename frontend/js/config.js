/**
 * ========================================
 * CONFIGURACIÓN GLOBAL
 * Ferretería Frenad - Sistema POS
 * ========================================
 */

const CONFIG = {
  // ========================================
  // API Configuration
  // ========================================
  API: {
    BASE_URL: 'http://localhost:8000/api',
    TIMEOUT: 30000, // 30 segundos
    RETRY_ATTEMPTS: 3,
    RETRY_DELAY: 1000, // 1 segundo entre reintentos
  },

  // ========================================
  // Storage Keys (localStorage)
  // ========================================
  STORAGE: {
    TOKEN: 'frenad_token',
    USER: 'frenad_user',
    THEME: 'frenad_theme',
    REMEMBER_EMAIL: 'frenad_remember_email',
    CART: 'frenad_cart',
    LAST_SYNC: 'frenad_last_sync',
  },

  // ========================================
  // Themes
  // ========================================
  THEMES: {
    LIGHT: 'light',
    DARK: 'dark',
    SYSTEM: 'system',
  },

  // ========================================
  // User Roles
  // ========================================
  ROLES: {
    ADMIN: 'Administrador',
    GERENTE: 'Gerente',
    VENDEDOR: 'Vendedor',
    BODEGUERO: 'Bodeguero',
  },

  // ========================================
  // Routes & Pages
  // ========================================
  ROUTES: {
    LOGIN: '/login.html',
    DASHBOARD: '/dashboard.html',
    POS: '/pos.html',
    PRODUCTOS: '/productos.html',
    INVENTARIO: '/inventario.html',
    CLIENTES: '/clientes.html',
    COMPRAS: '/compras.html',
    REPORTES: '/reportes.html',
  },

  // ========================================
  // Pagination
  // ========================================
  PAGINATION: {
    DEFAULT_PAGE_SIZE: 15,
    PAGE_SIZES: [10, 15, 25, 50, 100],
  },

  // ========================================
  // Formats
  // ========================================
  FORMATS: {
    CURRENCY: {
      locale: 'es-BO',
      currency: 'BOB',
      symbol: 'Bs.',
    },
    DATE: {
      locale: 'es-BO',
      options: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      },
    },
    DATETIME: {
      locale: 'es-BO',
      options: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      },
    },
  },

  // ========================================
  // Validation
  // ========================================
  VALIDATION: {
    MIN_PASSWORD_LENGTH: 6,
    MAX_PRODUCT_QUANTITY: 9999,
    MIN_PRODUCT_PRICE: 0.01,
    NIT_REGEX: /^[0-9]{7,13}$/,
    PHONE_REGEX: /^[67][0-9]{7}$/,
    EMAIL_REGEX: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  },

  // ========================================
  // Messages
  // ========================================
  MESSAGES: {
    ERROR: {
      NETWORK: 'Error de conexión. Verifica tu internet.',
      UNAUTHORIZED: 'Sesión expirada. Inicia sesión nuevamente.',
      FORBIDDEN: 'No tienes permisos para esta acción.',
      NOT_FOUND: 'Recurso no encontrado.',
      VALIDATION: 'Por favor, revisa los campos del formulario.',
      SERVER: 'Error interno del servidor. Intenta más tarde.',
      DEFAULT: 'Ha ocurrido un error inesperado.',
    },
    SUCCESS: {
      LOGIN: 'Bienvenido al sistema',
      LOGOUT: 'Sesión cerrada correctamente',
      CREATED: 'Registro creado correctamente',
      UPDATED: 'Registro actualizado correctamente',
      DELETED: 'Registro eliminado correctamente',
      SALE_COMPLETED: 'Venta completada exitosamente',
    },
    CONFIRM: {
      DELETE: '¿Estás seguro de eliminar este registro?',
      LOGOUT: '¿Deseas cerrar sesión?',
      CANCEL_SALE: '¿Cancelar la venta actual?',
      UNSAVED_CHANGES: 'Tienes cambios sin guardar. ¿Deseas salir?',
    },
  },

  // ========================================
  // Role Permissions (páginas permitidas)
  // ========================================
  PERMISSIONS: {
    Administrador: ['dashboard', 'pos', 'productos', 'inventario', 'clientes', 'compras', 'reportes', 'usuarios'],
    Gerente: ['dashboard', 'pos', 'productos', 'inventario', 'clientes', 'compras', 'reportes'],
    Vendedor: ['pos', 'clientes'],
    Bodeguero: ['inventario', 'productos'],
  },

  // ========================================
  // Default Home Page per Role
  // ========================================
  HOME_PAGE: {
    Administrador: '/dashboard.html',
    Gerente: '/dashboard.html',
    Vendedor: '/pos.html',
    Bodeguero: '/inventario.html',
  },
};

// Freeze config para prevenir modificaciones accidentales
Object.freeze(CONFIG);
Object.freeze(CONFIG.API);
Object.freeze(CONFIG.STORAGE);
Object.freeze(CONFIG.THEMES);
Object.freeze(CONFIG.ROLES);
Object.freeze(CONFIG.ROUTES);
Object.freeze(CONFIG.PAGINATION);
Object.freeze(CONFIG.FORMATS);
Object.freeze(CONFIG.VALIDATION);
Object.freeze(CONFIG.MESSAGES);
Object.freeze(CONFIG.PERMISSIONS);
Object.freeze(CONFIG.HOME_PAGE);

// Export para uso en otros módulos (si se usa ES6 modules)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = CONFIG;
}
