/**
 * ========================================
 * API CLIENT
 * Ferretería Frenad - Sistema POS
 * ========================================
 * Cliente HTTP para comunicación con el backend Laravel
 */

class APIClient {
  constructor() {
    this.baseURL = CONFIG.API.BASE_URL;
    this.timeout = CONFIG.API.TIMEOUT;
  }

  // ========================================
  // TOKEN MANAGEMENT
  // ========================================

  /**
   * Obtiene el token de autenticación
   */
  getToken() {
    return localStorage.getItem(CONFIG.STORAGE.TOKEN);
  }

  /**
   * Guarda el token de autenticación
   */
  setToken(token) {
    localStorage.setItem(CONFIG.STORAGE.TOKEN, token);
  }

  /**
   * Elimina el token de autenticación
   */
  removeToken() {
    localStorage.removeItem(CONFIG.STORAGE.TOKEN);
  }

  // ========================================
  // HEADERS
  // ========================================

  /**
   * Genera los headers para las peticiones
   */
  getHeaders(includeAuth = true, isFormData = false) {
    const headers = {
      'Accept': 'application/json',
    };

    if (!isFormData) {
      headers['Content-Type'] = 'application/json';
    }

    if (includeAuth) {
      const token = this.getToken();
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
    }

    return headers;
  }

  // ========================================
  // HTTP METHODS
  // ========================================

  /**
   * Realiza una petición HTTP genérica
   */
  async request(method, endpoint, data = null, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const { includeAuth = true, isFormData = false, customHeaders = {} } = options;

    const config = {
      method: method.toUpperCase(),
      headers: {
        ...this.getHeaders(includeAuth, isFormData),
        ...customHeaders,
      },
    };

    // Agregar body para métodos que lo soporten
    if (data && ['POST', 'PUT', 'PATCH'].includes(config.method)) {
      config.body = isFormData ? data : JSON.stringify(data);
    }

    // Agregar query params para GET con data
    let finalURL = url;
    if (data && config.method === 'GET') {
      const params = new URLSearchParams();
      Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          params.append(key, value);
        }
      });
      const queryString = params.toString();
      if (queryString) {
        finalURL = `${url}?${queryString}`;
      }
    }

    try {
      // Crear promise con timeout
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), this.timeout);
      config.signal = controller.signal;

      const response = await fetch(finalURL, config);
      clearTimeout(timeoutId);

      return await this.handleResponse(response);
    } catch (error) {
      return this.handleError(error);
    }
  }

  /**
   * Maneja la respuesta del servidor
   */
  async handleResponse(response) {
    let data;
    
    try {
      // Intentar parsear como JSON
      const text = await response.text();
      data = text ? JSON.parse(text) : {};
    } catch {
      data = {};
    }

    // Si la respuesta es exitosa
    if (response.ok) {
      // Si la respuesta tiene estructura {success, message, data}, extraer data
      // Si no, devolver todo como data
      const responseData = data.data !== undefined ? data.data : data;
      
      return {
        success: data.success !== undefined ? data.success : true,
        status: response.status,
        message: data.message || '',
        data: responseData,
      };
    }

    // Manejar errores HTTP
    return {
      success: false,
      status: response.status,
      message: this.getErrorMessage(response.status, data),
      errors: data.errors || {},
      data: data.data || data,
    };
  }

  /**
   * Maneja errores de red/conexión
   */
  handleError(error) {
    console.error('API Error:', error);

    // Error de timeout (AbortError)
    if (error.name === 'AbortError') {
      return {
        success: false,
        status: 0,
        message: 'La solicitud tardó demasiado. Intenta de nuevo.',
        errors: {},
      };
    }

    // Error de red
    return {
      success: false,
      status: 0,
      message: CONFIG.MESSAGES.ERROR.NETWORK,
      errors: {},
    };
  }

  /**
   * Obtiene el mensaje de error apropiado según el status
   */
  getErrorMessage(status, data) {
    // Si el servidor envió un mensaje, usarlo
    if (data.message) {
      return data.message;
    }

    // DEBUG: Log para ver qué status está causando problemas
    console.log('API Error Status:', status, 'Data:', data);

    // Mensajes por defecto según status
    switch (status) {
      case 400:
        return CONFIG.MESSAGES.ERROR.VALIDATION;
      case 401:
        // NO redirigir automáticamente - dejar que la página maneje el error
        console.warn('401 Unauthorized - Token may be invalid');
        return CONFIG.MESSAGES.ERROR.UNAUTHORIZED;
      case 403:
        return CONFIG.MESSAGES.ERROR.FORBIDDEN;
      case 404:
        return CONFIG.MESSAGES.ERROR.NOT_FOUND;
      case 422:
        return CONFIG.MESSAGES.ERROR.VALIDATION;
      case 500:
        return CONFIG.MESSAGES.ERROR.SERVER;
      default:
        return CONFIG.MESSAGES.ERROR.DEFAULT;
    }
  }

  /**
   * Maneja respuestas 401 (no autorizado)
   * NOTA: Ya no se llama automáticamente, solo cuando explícitamente se necesite
   */
  handleUnauthorized() {
    this.removeToken();
    localStorage.removeItem(CONFIG.STORAGE.USER);
    
    // Solo redirigir si no estamos en la página de login
    if (!window.location.pathname.includes('login')) {
      window.location.href = CONFIG.ROUTES.LOGIN;
    }
  }

  // ========================================
  // MÉTODOS CONVENIENTES
  // ========================================

  /**
   * GET request
   */
  async get(endpoint, params = null, options = {}) {
    return this.request('GET', endpoint, params, options);
  }

  /**
   * POST request
   */
  async post(endpoint, data = null, options = {}) {
    return this.request('POST', endpoint, data, options);
  }

  /**
   * PUT request
   */
  async put(endpoint, data = null, options = {}) {
    return this.request('PUT', endpoint, data, options);
  }

  /**
   * PATCH request
   */
  async patch(endpoint, data = null, options = {}) {
    return this.request('PATCH', endpoint, data, options);
  }

  /**
   * DELETE request
   */
  async delete(endpoint, options = {}) {
    return this.request('DELETE', endpoint, null, options);
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - AUTH
  // ========================================

  async login(email, password) {
    return this.post('/auth/auth/login', { email, password }, { includeAuth: false });
  }

  async logout() {
    return this.post('/auth/auth/logout');
  }

  async getProfile() {
    return this.get('/auth/auth/profile');
  }

  async refreshToken() {
    return this.post('/auth/auth/refresh');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - PRODUCTOS
  // ========================================

  async getProducts(params = {}) {
    return this.get('/product/productos', params);
  }

  async getProduct(id) {
    return this.get(`/product/productos/${id}`);
  }

  async createProduct(data) {
    return this.post('/product/productos', data);
  }

  async updateProduct(id, data) {
    return this.put(`/product/productos/${id}`, data);
  }

  async deleteProduct(id) {
    return this.delete(`/product/productos/${id}`);
  }

  async searchProductBySku(sku) {
    return this.get('/product/productos/buscar-sku', { sku });
  }

  async searchProductByBarcode(barcode) {
    return this.get('/product/productos/buscar-codigo-barras', { codigo_barras: barcode });
  }

  async getProductsStats() {
    return this.get('/product/productos/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - CATEGORÍAS
  // ========================================

  async getCategories() {
    return this.get('/product/categorias');
  }

  async getCategory(id) {
    return this.get(`/product/categorias/${id}`);
  }

  async createCategory(data) {
    return this.post('/product/categorias', data);
  }

  async updateCategory(id, data) {
    return this.put(`/product/categorias/${id}`, data);
  }

  async deleteCategory(id) {
    return this.delete(`/product/categorias/${id}`);
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - MARCAS
  // ========================================

  async getBrands() {
    return this.get('/product/marcas');
  }

  async getBrand(id) {
    return this.get(`/product/marcas/${id}`);
  }

  async createBrand(data) {
    return this.post('/product/marcas', data);
  }

  async updateBrand(id, data) {
    return this.put(`/product/marcas/${id}`, data);
  }

  async deleteBrand(id) {
    return this.delete(`/product/marcas/${id}`);
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - INVENTARIO
  // ========================================

  async getInventory(params = {}) {
    return this.get('/inventory/inventario', params);
  }

  async getProductStock(productId) {
    return this.get(`/inventory/inventario/producto/${productId}`);
  }

  async getLowStock() {
    return this.get('/inventory/inventario/stock-bajo');
  }

  async adjustStock(data) {
    return this.post('/inventory/inventario/ajustar', data);
  }

  async transferStock(data) {
    return this.post('/inventory/inventario/transferir', data);
  }

  async setMinStock(data) {
    return this.put('/inventory/inventario/stock-minimo', data);
  }

  async getInventoryMovements() {
    return this.get('/inventory/movimientos-inventario');
  }

  async getProductKardex(productId) {
    return this.get(`/inventory/movimientos-inventario/kardex/${productId}`);
  }

  async getInventoryStats() {
    return this.get('/inventory/inventario/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - CLIENTES
  // ========================================

  async getCustomers(params = {}) {
    return this.get('/customer/clientes', params);
  }

  async getCustomer(id) {
    return this.get(`/customer/clientes/${id}`);
  }

  async createCustomer(data) {
    return this.post('/customer/clientes', data);
  }

  async updateCustomer(id, data) {
    return this.put(`/customer/clientes/${id}`, data);
  }

  async deleteCustomer(id) {
    return this.delete(`/customer/clientes/${id}`);
  }

  async activateCustomer(id) {
    return this.post(`/customer/clientes/${id}/activate`);
  }

  async getCustomerBalance(id) {
    return this.get(`/customer/clientes/${id}/estado-cuenta`);
  }

  async getCustomerPurchaseHistory(id) {
    return this.get(`/customer/clientes/${id}/historial-compras`);
  }

  async getCustomersStats() {
    return this.get('/customer/clientes/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - CRÉDITOS
  // ========================================

  async getCredits(params = {}) {
    return this.get('/customer/creditos', params);
  }

  async getCredit(id) {
    return this.get(`/customer/creditos/${id}`);
  }

  async payCredit(id, data) {
    return this.post(`/customer/creditos/${id}/pagar`, data);
  }

  async getOverdueCredits() {
    return this.get('/customer/creditos/vencidos');
  }

  async getUpcomingCredits() {
    return this.get('/customer/creditos/por-vencer');
  }

  async getCreditsStats() {
    return this.get('/customer/creditos/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - PROVEEDORES
  // ========================================

  async getSuppliers(params = {}) {
    return this.get('/purchase/proveedores', params);
  }

  async getSupplier(id) {
    return this.get(`/purchase/proveedores/${id}`);
  }

  async createSupplier(data) {
    return this.post('/purchase/proveedores', data);
  }

  async updateSupplier(id, data) {
    return this.put(`/purchase/proveedores/${id}`, data);
  }

  async deleteSupplier(id) {
    return this.delete(`/purchase/proveedores/${id}`);
  }

  async activateSupplier(id) {
    return this.post(`/purchase/proveedores/${id}/activate`);
  }

  async getSuppliersStats() {
    return this.get('/purchase/proveedores/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - COMPRAS
  // ========================================

  async getPurchases(params = {}) {
    return this.get('/purchase/compras', params);
  }

  async getPurchase(id) {
    return this.get(`/purchase/compras/${id}`);
  }

  async createPurchase(data) {
    return this.post('/purchase/compras', data);
  }

  async getPurchasesStats() {
    return this.get('/purchase/compras/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - VENTAS
  // ========================================

  async getSales(params = {}) {
    return this.get('/sales/ventas', params);
  }

  async getSale(id) {
    return this.get(`/sales/ventas/${id}`);
  }

  async createSale(data) {
    return this.post('/sales/ventas', data);
  }

  async cancelSale(id) {
    return this.post(`/sales/ventas/${id}/anular`);
  }

  async getTodaySales() {
    return this.get('/sales/ventas/hoy');
  }

  async getSalesStats() {
    return this.get('/sales/ventas/statistics');
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - REPORTES
  // ========================================

  // Reportes de Ventas
  async getSalesReport(params = {}) {
    return this.get('/reports/ventas/resumen', params);
  }

  async getSalesByProduct(params = {}) {
    return this.get('/reports/ventas/por-producto', params);
  }

  async getSalesByCategory(params = {}) {
    return this.get('/reports/ventas/por-categoria', params);
  }

  async getSalesBySeller(params = {}) {
    return this.get('/reports/ventas/por-vendedor', params);
  }

  async getSalesTrend(params = {}) {
    return this.get('/reports/ventas/tendencia-diaria', params);
  }

  async getSalesDiscounts(params = {}) {
    return this.get('/reports/ventas/descuentos', params);
  }

  // Reportes de Compras
  async getPurchasesReport(params = {}) {
    return this.get('/reports/compras/resumen', params);
  }

  async getPurchasesByProduct(params = {}) {
    return this.get('/reports/compras/por-producto', params);
  }

  async getPurchasesBySupplier(params = {}) {
    return this.get('/reports/compras/por-proveedor', params);
  }

  async getPurchasesTrend(params = {}) {
    return this.get('/reports/compras/tendencia-diaria', params);
  }

  // Reportes de Inventario
  async getInventoryReport() {
    return this.get('/reports/inventario/valorizado');
  }

  async getLowStockReport() {
    return this.get('/reports/inventario/stock-bajo');
  }

  async getNoMovementReport(params = {}) {
    return this.get('/reports/inventario/sin-movimiento', params);
  }

  async getInventoryMovementsReport(params = {}) {
    return this.get('/reports/inventario/movimientos', params);
  }

  // Reportes de Clientes
  async getTopCustomers(params = {}) {
    return this.get('/reports/clientes/top-clientes', params);
  }

  async getNewCustomers(params = {}) {
    return this.get('/reports/clientes/clientes-nuevos', params);
  }

  async getPendingCredits() {
    return this.get('/reports/clientes/creditos-pendientes');
  }

  async getDelinquencyReport() {
    return this.get('/reports/clientes/morosidad');
  }

  // Reportes Financieros
  async getCashFlow(params = {}) {
    return this.get('/reports/financiero/flujo-caja', params);
  }

  async getIncomeExpenses(params = {}) {
    return this.get('/reports/financiero/ingresos-egresos', params);
  }

  async getProfitability(params = {}) {
    return this.get('/reports/financiero/rentabilidad', params);
  }

  async getCashRegisterClose(params = {}) {
    return this.get('/reports/financiero/cierre-caja', params);
  }

  // Dashboard Stats (combinación de varios)
  async getDashboardStats() {
    try {
      // Obtener múltiples stats en paralelo, manejando errores individualmente
      const results = await Promise.allSettled([
        this.getSalesStats(),
        this.getInventoryStats(),
        this.getCustomersStats()
      ]);
      
      const salesStats = results[0].status === 'fulfilled' ? results[0].value : { data: {} };
      const inventoryStats = results[1].status === 'fulfilled' ? results[1].value : { data: {} };
      const customersStats = results[2].status === 'fulfilled' ? results[2].value : { data: {} };
      
      return {
        success: true,
        data: {
          ventas_hoy: salesStats.data?.ventas_hoy || salesStats.data?.total_ventas || 0,
          productos: inventoryStats.data?.total_productos || inventoryStats.data?.total || 0,
          clientes: customersStats.data?.total_activos || customersStats.data?.total || 0,
          stock_bajo: inventoryStats.data?.stock_bajo || inventoryStats.data?.productos_stock_bajo || 0
        }
      };
    } catch (error) {
      console.error('Error loading dashboard stats:', error);
      return {
        success: false,
        data: {
          ventas_hoy: 0,
          productos: 0,
          clientes: 0,
          stock_bajo: 0
        }
      };
    }
  }

  // ========================================
  // ENDPOINTS ESPECÍFICOS - USUARIOS
  // ========================================

  async getUsers(params = {}) {
    return this.get('/auth/usuarios', params);
  }

  async getUser(id) {
    return this.get(`/auth/usuarios/${id}`);
  }

  async createUser(data) {
    return this.post('/auth/usuarios', data);
  }

  async updateUser(id, data) {
    return this.put(`/auth/usuarios/${id}`, data);
  }

  async deleteUser(id) {
    return this.delete(`/auth/usuarios/${id}`);
  }

  async activateUser(id) {
    return this.post(`/auth/usuarios/${id}/activate`);
  }

  async changePassword(data) {
    return this.post('/auth/auth/change-password', data);
  }

  async getUsersStats() {
    return this.get('/auth/usuarios/statistics');
  }
}

// Crear instancia global
const api = new APIClient();

// Export para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { APIClient, api };
}
