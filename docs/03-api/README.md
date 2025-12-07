# 📚 Documentación API - Ferretería Frenad

## Índice de Documentación API

Este directorio contiene toda la documentación relacionada con la API REST del sistema.

---

## 📑 Documentos Disponibles

### **Configuración y Setup**
- [`01-configuracion-inicial.md`](01-configuracion-inicial.md) - Configuración de Laravel, JWT y dependencias
- [`02-estructura-modular.md`](02-estructura-modular.md) - Arquitectura modular del backend
- [`03-middleware-compartido.md`](03-middleware-compartido.md) - Middleware de autenticación y autorización
- [`04-traits-compartidos.md`](04-traits-compartidos.md) - Traits reutilizables (Audit, Timestamps)

### **Módulos del Sistema**
- [`05-modulo-auth.md`](05-modulo-auth.md) - Módulo de autenticación (Login, Logout, JWT)
- [`06-modulo-product.md`](06-modulo-product.md) - Módulo de productos
- [`07-modulo-inventory.md`](07-modulo-inventory.md) - Módulo de inventario
- [`08-modulo-sales.md`](08-modulo-sales.md) - Módulo de ventas
- [`09-modulo-purchase.md`](09-modulo-purchase.md) - Módulo de compras
- [`10-modulo-customer.md`](10-modulo-customer.md) - Módulo de clientes
- [`11-modulo-reports.md`](11-modulo-reports.md) - Módulo de reportes
- [`12-modulo-configuration.md`](12-modulo-configuration.md) - Módulo de configuración

### **Convenciones y Estándares**
- [`13-convenciones-api.md`](13-convenciones-api.md) - Estándares de respuestas API
- [`14-manejo-errores.md`](14-manejo-errores.md) - Manejo de errores y excepciones
- [`15-testing-api.md`](15-testing-api.md) - Testing de endpoints (TDD)

---

## 🏗️ Arquitectura de la API

### **Patrón Arquitectónico**
```
Cliente (Frontend)
    ↓
Nginx (Puerto 8080)
    ↓
Laravel API (Puerto 8000)
    ↓
    ├─ Middleware (auth:api, role)
    ├─ Routes (módulos)
    ├─ Controllers (validación)
    ├─ Services (lógica de negocio)
    ├─ Models (Eloquent)
    └─ PostgreSQL (Puerto 5432)
```

### **Estructura Modular**
```
backend/app/Modules/
├── Auth/           → Autenticación JWT
├── Product/        → Gestión de productos
├── Inventory/      → Control de inventario
├── Sales/          → Ventas y facturación
├── Purchase/       → Compras y proveedores
├── Customer/       → Gestión de clientes
├── Reports/        → Reportes y estadísticas
├── Configuration/  → Configuración del sistema
└── Shared/         → Código compartido
    ├── Middleware/ → CheckRole
    ├── Traits/     → HasAudit, HasBolivianTimestamps
    └── Helpers/    → Funciones globales
```

---

## 🔑 Autenticación

### **JWT (JSON Web Token)**
- **Paquete:** `tymon/jwt-auth v2.2.1`
- **Algoritmo:** HS256 (HMAC SHA-256)
- **TTL:** 60 minutos
- **Refresh TTL:** 20160 minutos (2 semanas)

### **Headers requeridos:**
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 📡 Base URL

```
Desarrollo: http://localhost:8000/api
Producción: https://api.ferreteria-frenad.com/api
```

---

## 🎯 Convenciones de Rutas

### **Estructura de URLs**
```
/api/{módulo}/{recurso}/{id?}/{acción?}
```

### **Ejemplos:**
```http
GET    /api/auth/me                  # Usuario autenticado
POST   /api/auth/login               # Login
POST   /api/auth/logout              # Logout

GET    /api/product/productos        # Listar productos
GET    /api/product/productos/5      # Ver producto específico
POST   /api/product/productos        # Crear producto
PUT    /api/product/productos/5      # Actualizar producto
DELETE /api/product/productos/5      # Eliminar producto

GET    /api/sales/ventas             # Listar ventas
POST   /api/sales/ventas             # Crear venta
GET    /api/sales/ventas/5/detalle   # Detalle de venta

GET    /api/reports/ventas-diarias   # Reporte de ventas
```

---

## 📦 Formato de Respuestas

### **Respuesta Exitosa**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Cemento Portland"
  },
  "message": "Operación exitosa"
}
```

### **Respuesta con Paginación**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  }
}
```

### **Respuesta de Error**
```json
{
  "success": false,
  "message": "Error descriptivo",
  "errors": {
    "campo": ["Detalle del error"]
  }
}
```

---

## 🔐 Roles y Permisos

### **Roles del Sistema**
- **administrador** - Acceso total al sistema
- **gerente** - Gestión de ventas, inventario, reportes
- **vendedor** - Ventas y consultas de inventario
- **almacenero** - Gestión de inventario y compras

### **Middleware de Roles**
```php
// Proteger ruta para un rol específico
Route::middleware(['auth:api', 'role:administrador'])->group(function () {
    // Rutas solo para administrador
});

// Proteger para múltiples roles
Route::middleware(['auth:api', 'role:administrador,gerente'])->group(function () {
    // Rutas para administrador o gerente
});
```

---

## 📊 Helpers Globales Disponibles

### **Moneda**
```php
format_currency(1234.56)  // "Bs 1,234.56"
```

### **Fechas**
```php
format_bolivian_date('2024-12-04 10:30:00')  // "04/12/2024 10:30:00"
```

### **Comprobantes**
```php
generate_voucher_number('VTA')  // "VTA-20241204-0001"
```

### **RUC/NIT**
```php
clean_ruc_nit('1234567-8')   // "12345678"
format_ruc_nit('12345678')   // "1234567-8"
```

---

## 🧪 Testing

### **Ejecutar Tests**
```bash
# Todos los tests
docker exec -it ferreteria_backend php artisan test

# Tests de un módulo específico
docker exec -it ferreteria_backend php artisan test --filter=Auth

# Con cobertura
docker exec -it ferreteria_backend php artisan test --coverage
```

---

## 📞 Soporte

Para más información sobre endpoints específicos, consulta la documentación de cada módulo.

**Documentación generada:** 4 de diciembre de 2025  
**Versión del Sistema:** 1.0.0  
**Laravel Framework:** 10.50.0
