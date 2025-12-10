/**
 * ========================================
 * AUTH MODULE
 * Ferretería Frenad - Sistema POS
 * ========================================
 * Gestión de autenticación y sesión de usuario
 */

class Auth {
  constructor() {
    this.user = null;
    this.loadUser();
  }

  // ========================================
  // USER SESSION
  // ========================================

  /**
   * Carga el usuario desde localStorage
   */
  loadUser() {
    try {
      const userData = localStorage.getItem(CONFIG.STORAGE.USER);
      if (userData) {
        this.user = JSON.parse(userData);
      }
    } catch (error) {
      console.error('Error loading user:', error);
      this.user = null;
    }
  }

  /**
   * Guarda el usuario en localStorage
   */
  saveUser(user) {
    this.user = user;
    localStorage.setItem(CONFIG.STORAGE.USER, JSON.stringify(user));
  }

  /**
   * Limpia la sesión del usuario
   */
  clearSession() {
    this.user = null;
    localStorage.removeItem(CONFIG.STORAGE.TOKEN);
    localStorage.removeItem(CONFIG.STORAGE.USER);
    localStorage.removeItem(CONFIG.STORAGE.CART);
  }

  // ========================================
  // AUTHENTICATION
  // ========================================

  /**
   * Inicia sesión
   */
  async login(email, password, remember = false) {
    const response = await api.login(email, password);

    if (response.success) {
      // Guardar token (puede venir en diferentes formatos)
      const token = response.data.token || response.data.access_token;
      api.setToken(token);

      // Guardar usuario (viene en response.data.usuario)
      const user = response.data.usuario || response.data.user;
      if (user) {
        // Normalizar estructura del usuario
        const normalizedUser = {
          id: user.id,
          nombre: user.nombre || user.name,
          email: user.email,
          rol: {
            nombre: Array.isArray(user.roles) ? user.roles[0] : (user.rol?.nombre || user.role || 'Vendedor')
          },
          activo: user.activo !== undefined ? user.activo : true
        };
        this.saveUser(normalizedUser);
      }
      
      // Recordar email si se seleccionó
      if (remember) {
        localStorage.setItem(CONFIG.STORAGE.REMEMBER_EMAIL, email);
      } else {
        localStorage.removeItem(CONFIG.STORAGE.REMEMBER_EMAIL);
      }

      return {
        success: true,
        message: response.data.message || CONFIG.MESSAGES.SUCCESS.LOGIN,
        user: this.user,
      };
    }

    return response;
  }

  /**
   * Cierra sesión
   */
  async logout() {
    try {
      // Intentar cerrar sesión en el servidor
      await api.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }

    // Limpiar sesión local siempre
    this.clearSession();

    // Redirigir a login
    window.location.href = CONFIG.ROUTES.LOGIN;
  }

  /**
   * Verifica si el usuario está autenticado
   */
  isAuthenticated() {
    const token = api.getToken();
    const hasUser = !!this.user;
    
    // DEBUG
    console.log('isAuthenticated check:', { token: token ? 'exists' : 'missing', hasUser, user: this.user });
    
    return !!token && hasUser;
  }

  /**
   * Obtiene el usuario actual
   */
  getUser() {
    return this.user;
  }

  /**
   * Obtiene el nombre completo del usuario
   */
  getUserName() {
    if (!this.user) return '';
    return this.user.nombre || this.user.name || 'Usuario';
  }

  /**
   * Obtiene el rol del usuario
   */
  getUserRole() {
    if (!this.user) return '';
    return this.user.rol?.nombre || this.user.role || '';
  }

  /**
   * Obtiene las iniciales del usuario
   */
  getUserInitials() {
    const name = this.getUserName();
    if (!name) return '?';
    
    const parts = name.split(' ');
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }

  // ========================================
  // AUTHORIZATION
  // ========================================

  /**
   * Verifica si el usuario tiene un rol específico
   */
  hasRole(role) {
    const userRole = this.getUserRole();
    return userRole === role;
  }

  /**
   * Verifica si el usuario tiene alguno de los roles especificados
   */
  hasAnyRole(roles) {
    const userRole = this.getUserRole();
    return roles.includes(userRole);
  }

  /**
   * Verifica si el usuario tiene permiso para acceder a una página
   */
  canAccess(page) {
    const userRole = this.getUserRole();
    const permissions = CONFIG.PERMISSIONS[userRole] || [];
    return permissions.includes(page);
  }

  /**
   * Obtiene la página de inicio según el rol
   */
  getHomePage() {
    const userRole = this.getUserRole();
    return CONFIG.HOME_PAGE[userRole] || CONFIG.ROUTES.DASHBOARD;
  }

  /**
   * Verifica si el usuario es administrador
   */
  isAdmin() {
    return this.hasRole(CONFIG.ROLES.ADMIN);
  }

  /**
   * Verifica si el usuario es gerente
   */
  isGerente() {
    return this.hasRole(CONFIG.ROLES.GERENTE);
  }

  /**
   * Verifica si el usuario es vendedor
   */
  isVendedor() {
    return this.hasRole(CONFIG.ROLES.VENDEDOR);
  }

  /**
   * Verifica si el usuario es bodeguero
   */
  isBodeguero() {
    return this.hasRole(CONFIG.ROLES.BODEGUERO);
  }

  // ========================================
  // PROTECTION
  // ========================================

  /**
   * Protege una página (redirige si no está autenticado)
   */
  requireAuth() {
    if (!this.isAuthenticated()) {
      window.location.href = CONFIG.ROUTES.LOGIN;
      return false;
    }
    return true;
  }

  /**
   * Protege una página con verificación de rol
   */
  requireRole(roles) {
    if (!this.requireAuth()) return false;

    const allowedRoles = Array.isArray(roles) ? roles : [roles];
    
    if (!this.hasAnyRole(allowedRoles)) {
      // Redirigir a su página de inicio
      window.location.href = this.getHomePage();
      return false;
    }

    return true;
  }

  /**
   * Verifica permisos de página actual
   */
  checkPagePermission() {
    const currentPage = window.location.pathname.split('/').pop().replace('.html', '');
    
    // Páginas públicas
    if (['login', 'index', ''].includes(currentPage)) {
      return true;
    }

    // Verificar autenticación
    if (!this.requireAuth()) {
      return false;
    }

    // Verificar permiso de página
    if (!this.canAccess(currentPage)) {
      window.location.href = this.getHomePage();
      return false;
    }

    return true;
  }

  // ========================================
  // TOKEN REFRESH
  // ========================================

  /**
   * Refresca el token de autenticación
   */
  async refreshToken() {
    const response = await api.refreshToken();

    if (response.success) {
      const token = response.data.token || response.data.access_token;
      api.setToken(token);
      return true;
    }

    // Si falla el refresh, cerrar sesión
    this.clearSession();
    window.location.href = CONFIG.ROUTES.LOGIN;
    return false;
  }

  // ========================================
  // REMEMBER EMAIL
  // ========================================

  /**
   * Obtiene el email recordado
   */
  getRememberedEmail() {
    return localStorage.getItem(CONFIG.STORAGE.REMEMBER_EMAIL) || '';
  }
}

// Crear instancia global
const auth = new Auth();

// Export para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Auth, auth };
}
