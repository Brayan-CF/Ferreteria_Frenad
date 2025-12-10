# Frontend - Prototipo POS Ferretería Frenad

> **Estado**: Prototipo funcional (no terminado)  
> **Última actualización**: 10 de diciembre de 2025

## Descripción General

El frontend es un prototipo de interfaz web para el sistema POS de Ferretería Frenad. Implementa las vistas principales del sistema pero no está completamente integrado con todos los endpoints del backend.

## Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| HTML5 | - | Estructura de páginas |
| CSS3 | - | Estilos personalizados |
| JavaScript (ES6+) | Vanilla | Lógica de aplicación |
| Tailwind CSS | CDN | Framework de estilos utilitarios |
| Font Awesome | 6.x | Iconografía |
| Chart.js | CDN | Gráficos en reportes |
| Nginx | 1.29 | Servidor web (Docker) |

## Estructura del Proyecto

```
frontend/
├── index.html          # Redirección a login
├── login.html          # Página de autenticación
├── dashboard.html      # Panel principal con estadísticas
├── pos.html            # Punto de venta
├── productos.html      # Gestión de productos (CRUD)
├── inventario.html     # Control de inventario
├── clientes.html       # Gestión de clientes (CRUD)
├── compras.html        # Registro de compras
├── reportes.html       # Reportes y análisis
├── Dockerfile          # Configuración Docker
├── nginx.conf          # Configuración Nginx
├── js/
│   ├── config.js       # Configuración global (API URL, constantes)
│   ├── api.js          # Cliente HTTP para backend
│   ├── auth.js         # Manejo de autenticación
│   └── utils.js        # Funciones utilitarias
├── css/
│   ├── main.css        # Estilos principales
│   ├── components.css  # Estilos de componentes
│   ├── forms.css       # Estilos de formularios
│   └── tables.css      # Estilos de tablas
├── components/
│   ├── sidebar.html    # Menú lateral
│   └── header.html     # Cabecera
└── assets/
    └── img/            # Imágenes y logos
```

## Páginas Implementadas

### 1. Login (`login.html`)
- **Estado**: ✅ Funcional
- **Funcionalidades**:
  - Autenticación con email/password
  - Opción "Recordarme"
  - Almacenamiento de token en localStorage
  - Redirección a dashboard

### 2. Dashboard (`dashboard.html`)
- **Estado**: ✅ Funcional (parcial)
- **Funcionalidades**:
  - Vista de estadísticas generales
  - Accesos rápidos a módulos
  - Gráficos de ventas (requiere datos)
- **Pendiente**: Algunos endpoints de statistics no implementados en backend

### 3. Punto de Venta (`pos.html`)
- **Estado**: ✅ Funcional
- **Funcionalidades**:
  - Búsqueda de productos
  - Carrito de compras
  - Selección de cliente
  - Métodos de pago
  - Generación de venta
- **Observación**: Rendimiento puede mejorar

### 4. Productos (`productos.html`)
- **Estado**: ✅ CRUD Completo
- **Funcionalidades**:
  - Listado paginado
  - Crear producto
  - Editar producto
  - Eliminar producto
  - Filtros por categoría/estado

### 5. Inventario (`inventario.html`)
- **Estado**: ⚠️ Parcialmente funcional
- **Funcionalidades**:
  - Listado de inventario por almacén
  - Vista de stock bajo
- **Pendiente**: Ajuste de stock tiene problemas de integración

### 6. Clientes (`clientes.html`)
- **Estado**: ✅ CRUD Completo
- **Funcionalidades**:
  - Listado paginado
  - Crear cliente
  - Editar cliente
  - Eliminar/desactivar cliente

### 7. Compras (`compras.html`)
- **Estado**: ⚠️ Parcialmente funcional
- **Funcionalidades**:
  - Listado de compras
  - Selección de proveedor
- **Pendiente**: Crear compra tiene errores de validación

### 8. Reportes (`reportes.html`)
- **Estado**: ✅ Funcional
- **Funcionalidades**:
  - Reportes de ventas
  - Filtros por fecha
  - Gráficos con Chart.js

## Arquitectura JavaScript

### Cliente API (`api.js`)
```javascript
// Ejemplo de uso
const api = new APIClient();
await api.login(email, password);
const productos = await api.getProducts();
```

### Autenticación (`auth.js`)
- Manejo de tokens JWT/Sanctum
- Verificación de sesión activa
- Redirección automática si no autenticado

### Configuración (`config.js`)
- `CONFIG.API.BASE_URL`: URL del backend
- `CONFIG.STORAGE`: Keys de localStorage
- `CONFIG.ROUTES`: Rutas de navegación

## Estilos CSS

El proyecto usa una combinación de:
- **Tailwind CSS (CDN)**: Clases utilitarias
- **CSS Personalizado**: Variables CSS para tema claro/oscuro

```css
/* Variables de tema */
:root {
  --color-primary: #dc2626;
  --color-secondary: #1f2937;
  --color-background: #f9fafb;
}
```

## Despliegue

### Docker
```dockerfile
FROM nginx:alpine
COPY . /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
```

### Acceso
- **URL Local**: http://localhost:8080
- **Contenedor**: `ferreteria_frontend`

## Limitaciones Conocidas

1. **No hay gestión de usuarios**: Solo login, no CRUD de usuarios
2. **Roles no implementados en UI**: Backend tiene roles pero UI no los diferencia visualmente
3. **Sin responsive completo**: Algunas vistas no están optimizadas para móvil
4. **Favicon faltante**: Error 404 en todas las páginas (estético)
5. **Endpoints de statistics**: Algunos no implementados en backend

## Pruebas Realizadas

| Funcionalidad | Chrome | Firefox | Resultado |
|---------------|--------|---------|-----------|
| Login | ✅ | ✅ | OK |
| Dashboard | ✅ | ✅ | OK (parcial) |
| POS | ✅ | ✅ | OK |
| Productos CRUD | ✅ | ✅ | OK |
| Clientes CRUD | ✅ | ✅ | OK |
| Inventario | ⚠️ | ⚠️ | Ajuste falla |
| Compras | ⚠️ | ⚠️ | Crear falla |
| Reportes | ✅ | ✅ | OK |

## Credenciales de Prueba

```
Email: admin@frenad.com
Password: password
Rol: Administrador
```

## Mejoras Futuras (No implementadas)

- [ ] Gestión de usuarios y roles desde UI
- [ ] Tema oscuro completo
- [ ] PWA (Progressive Web App)
- [ ] Notificaciones en tiempo real
- [ ] Exportación a PDF/Excel
- [ ] Modo offline
- [ ] Tests E2E con Cypress/Playwright

---

**Nota**: Este frontend es un prototipo de demostración. Para producción se recomienda usar un framework moderno (Vue.js, React, etc.) con mejor manejo de estado y componentes.
