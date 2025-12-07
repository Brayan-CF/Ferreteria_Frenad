# 📐 Convenciones de la API

## Índice
1. [Introducción](#introducción)
2. [Estructura de URLs](#estructura-de-urls)
3. [Métodos HTTP](#métodos-http)
4. [Códigos de Estado](#códigos-de-estado)
5. [Formato de Respuestas](#formato-de-respuestas)
6. [Paginación](#paginación)
7. [Filtros y Ordenamiento](#filtros-y-ordenamiento)
8. [Versionado](#versionado)
9. [Nomenclatura](#nomenclatura)

---

## Introducción

Este documento define las **convenciones y estándares** que se deben seguir al desarrollar endpoints de la API REST para mantener consistencia y facilitar el uso.

---

## Estructura de URLs

### **Patrón General**

```
https://api.ferreteria-frenad.com/api/{module}/{resource}/{id?}/{action?}
```

### **Componentes**

- **`api/`** - Prefijo base para todas las rutas
- **`{module}`** - Nombre del módulo (auth, product, sales, etc.)
- **`{resource}`** - Recurso en plural (productos, ventas, clientes)
- **`{id}`** - Identificador único (opcional)
- **`{action}`** - Acción específica (opcional)

### **Ejemplos**

```
GET    /api/product/productos              # Listar productos
POST   /api/product/productos              # Crear producto
GET    /api/product/productos/123          # Ver producto
PUT    /api/product/productos/123          # Actualizar producto
DELETE /api/product/productos/123          # Eliminar producto

GET    /api/sales/ventas                   # Listar ventas
POST   /api/sales/ventas                   # Crear venta
POST   /api/sales/ventas/123/anular        # Acción: anular venta

GET    /api/inventory/inventario           # Ver inventario
POST   /api/inventory/movimientos          # Registrar movimiento
POST   /api/inventory/transferencias       # Transferir stock
```

---

### **Recursos Anidados**

Para relaciones, usar rutas anidadas cuando sea necesario:

```
GET    /api/customer/clientes/123/ventas           # Ventas de un cliente
GET    /api/product/productos/123/inventario       # Inventario de un producto
GET    /api/sales/ventas/123/detalles              # Detalles de una venta
```

---

## Métodos HTTP

### **GET - Obtener Recursos**

```http
GET /api/product/productos
GET /api/product/productos/123
```

**Características:**
- ✅ Solo lectura, no modifica datos
- ✅ Idempotente (múltiples llamadas = mismo resultado)
- ✅ Cacheable

---

### **POST - Crear Recursos**

```http
POST /api/product/productos
Content-Type: application/json

{
    "nombre": "Cemento Portland",
    "codigo": "CEM-001",
    "categoria_id": 5,
    "precio_venta": 55.00
}
```

**Características:**
- ✅ Crea un nuevo recurso
- ✅ No idempotente (múltiples llamadas = múltiples recursos)
- ✅ Retorna código 201 Created

---

### **PUT - Actualizar Recurso Completo**

```http
PUT /api/product/productos/123
Content-Type: application/json

{
    "nombre": "Cemento Portland",
    "codigo": "CEM-001",
    "categoria_id": 5,
    "precio_venta": 60.00,
    "activo": true
}
```

**Características:**
- ✅ Reemplaza completamente el recurso
- ✅ Idempotente
- ✅ Requiere todos los campos

---

### **PATCH - Actualizar Parcialmente**

```http
PATCH /api/product/productos/123
Content-Type: application/json

{
    "precio_venta": 60.00
}
```

**Características:**
- ✅ Actualiza solo los campos enviados
- ✅ Idempotente
- ✅ Más flexible que PUT

---

### **DELETE - Eliminar Recurso**

```http
DELETE /api/product/productos/123
```

**Características:**
- ✅ Elimina el recurso
- ✅ Idempotente
- ✅ Retorna código 204 No Content

---

## Códigos de Estado

### **2xx - Éxito**

| Código | Nombre | Uso |
|--------|--------|-----|
| **200** | OK | GET, PUT, PATCH exitosos |
| **201** | Created | POST exitoso (recurso creado) |
| **204** | No Content | DELETE exitoso |

**Ejemplos:**
```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "success": true,
    "data": { ... }
}
```

```http
HTTP/1.1 201 Created
Location: /api/product/productos/124
Content-Type: application/json

{
    "success": true,
    "message": "Producto creado exitosamente",
    "data": { ... }
}
```

---

### **4xx - Errores del Cliente**

| Código | Nombre | Uso |
|--------|--------|-----|
| **400** | Bad Request | Datos inválidos |
| **401** | Unauthorized | Sin autenticación |
| **403** | Forbidden | Sin permisos |
| **404** | Not Found | Recurso no existe |
| **422** | Unprocessable Entity | Validación fallida |
| **429** | Too Many Requests | Rate limit excedido |

**Ejemplos:**
```http
HTTP/1.1 401 Unauthorized

{
    "success": false,
    "message": "Token inválido o expirado"
}
```

```http
HTTP/1.1 422 Unprocessable Entity

{
    "success": false,
    "message": "Errores de validación",
    "errors": {
        "nombre": ["El campo nombre es obligatorio"],
        "precio_venta": ["El precio debe ser mayor a 0"]
    }
}
```

---

### **5xx - Errores del Servidor**

| Código | Nombre | Uso |
|--------|--------|-----|
| **500** | Internal Server Error | Error no controlado |
| **503** | Service Unavailable | Servicio temporalmente no disponible |

**Ejemplo:**
```http
HTTP/1.1 500 Internal Server Error

{
    "success": false,
    "message": "Error interno del servidor",
    "error_id": "ERR-20251204-001"
}
```

---

## Formato de Respuestas

### **Respuesta Exitosa - Recurso Único**

```json
{
    "success": true,
    "data": {
        "id": 123,
        "nombre": "Cemento Portland",
        "codigo": "CEM-001",
        "precio_venta": 55.00,
        "categoria": {
            "id": 5,
            "nombre": "Materiales de Construcción"
        },
        "created_at": "2025-12-04 10:30:00",
        "updated_at": "2025-12-04 10:30:00"
    }
}
```

---

### **Respuesta Exitosa - Colección sin Paginación**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Categoría 1"
        },
        {
            "id": 2,
            "nombre": "Categoría 2"
        }
    ]
}
```

---

### **Respuesta Exitosa - Colección con Paginación**

```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "nombre": "Producto 1"
        },
        {
            "id": 124,
            "nombre": "Producto 2"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 73
    },
    "links": {
        "first": "http://api.test/api/product/productos?page=1",
        "last": "http://api.test/api/product/productos?page=5",
        "prev": null,
        "next": "http://api.test/api/product/productos?page=2"
    }
}
```

---

### **Respuesta de Error**

```json
{
    "success": false,
    "message": "Mensaje de error legible",
    "errors": {
        "campo": ["Error específico del campo"]
    }
}
```

---

## Paginación

### **Parámetros de Query**

```
GET /api/product/productos?page=2&per_page=20
```

**Parámetros:**
- **`page`** - Número de página (default: 1)
- **`per_page`** - Elementos por página (default: 15, max: 100)

---

### **Implementación en Controller**

```php
public function index(Request $request)
{
    $perPage = min($request->get('per_page', 15), 100);
    
    $productos = Producto::with(['categoria', 'marca'])
        ->paginate($perPage);
    
    return response()->json([
        'success' => true,
        'data' => ProductResource::collection($productos->items()),
        'meta' => [
            'current_page' => $productos->currentPage(),
            'from' => $productos->firstItem(),
            'last_page' => $productos->lastPage(),
            'per_page' => $productos->perPage(),
            'to' => $productos->lastItem(),
            'total' => $productos->total(),
        ],
        'links' => [
            'first' => $productos->url(1),
            'last' => $productos->url($productos->lastPage()),
            'prev' => $productos->previousPageUrl(),
            'next' => $productos->nextPageUrl(),
        ],
    ]);
}
```

---

## Filtros y Ordenamiento

### **Filtros con Spatie Query Builder**

```
GET /api/product/productos?filter[nombre]=cemento&filter[categoria_id]=5&filter[activo]=1
```

**Implementación:**
```php
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

public function index(Request $request)
{
    $productos = QueryBuilder::for(Producto::class)
        ->allowedFilters([
            'nombre',
            'codigo',
            AllowedFilter::exact('categoria_id'),
            AllowedFilter::exact('marca_id'),
            AllowedFilter::exact('activo'),
        ])
        ->allowedSorts('nombre', 'precio_venta', 'codigo', 'created_at')
        ->allowedIncludes('categoria', 'marca', 'inventario')
        ->paginate($request->get('per_page', 15));

    return response()->json([
        'success' => true,
        'data' => ProductResource::collection($productos->items()),
        // ... meta y links
    ]);
}
```

---

### **Ordenamiento**

```
GET /api/product/productos?sort=nombre              # Ascendente
GET /api/product/productos?sort=-precio_venta       # Descendente
GET /api/product/productos?sort=categoria_id,nombre # Múltiple
```

---

### **Incluir Relaciones**

```
GET /api/product/productos?include=categoria,marca,inventario
```

**Respuesta:**
```json
{
    "id": 123,
    "nombre": "Cemento Portland",
    "categoria": {
        "id": 5,
        "nombre": "Materiales"
    },
    "marca": {
        "id": 10,
        "nombre": "Cemento Cruz"
    },
    "inventario": [
        {
            "almacen_id": 1,
            "cantidad": 500
        }
    ]
}
```

---

### **Búsqueda Global**

```
GET /api/product/productos?search=cemento
```

**Implementación:**
```php
use Spatie\QueryBuilder\AllowedFilter;

QueryBuilder::for(Producto::class)
    ->allowedFilters([
        AllowedFilter::callback('search', function ($query, $value) {
            $query->where(function ($q) use ($value) {
                $q->where('nombre', 'ILIKE', "%{$value}%")
                  ->orWhere('codigo', 'ILIKE', "%{$value}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$value}%");
            });
        }),
    ])
    ->paginate();
```

---

## Versionado

### **Versión en URL (Recomendado)**

```
https://api.ferreteria-frenad.com/api/v1/product/productos
https://api.ferreteria-frenad.com/api/v2/product/productos
```

### **Versión en Header (Alternativa)**

```http
GET /api/product/productos
Accept: application/vnd.ferreteria.v1+json
```

### **Versión Actual**

Por ahora, el sistema usa **v1 implícita** (sin prefijo en URL). Cuando se necesite v2, se agregará el prefijo.

---

## Nomenclatura

### **URLs - Kebab Case**

```
✅ /api/product/unidades-medida
✅ /api/inventory/movimientos-inventario
✅ /api/sales/tipos-comprobante

❌ /api/product/UnidadesMedida
❌ /api/inventory/movimientos_inventario
```

---

### **JSON - Snake Case**

```json
✅ {
    "precio_venta": 55.00,
    "stock_minimo": 10,
    "categoria_id": 5
}

❌ {
    "precioVenta": 55.00,
    "StockMinimo": 10,
    "CategoriaId": 5
}
```

---

### **Parámetros de Query - Snake Case**

```
✅ ?per_page=20&sort=-created_at&filter[categoria_id]=5
❌ ?perPage=20&sort=-createdAt&filter[categoriaId]=5
```

---

### **Nombres de Recursos - Plural**

```
✅ /api/product/productos
✅ /api/sales/ventas
✅ /api/customer/clientes

❌ /api/product/producto
❌ /api/sales/venta
```

---

### **Nombres de Módulos - Singular en Inglés**

```
✅ /api/product/...
✅ /api/inventory/...
✅ /api/sales/...

❌ /api/products/...
❌ /api/producto/...
```

---

## Headers Requeridos

### **Peticiones con Body**

```http
POST /api/product/productos
Content-Type: application/json
Accept: application/json
```

### **Peticiones Autenticadas**

```http
GET /api/product/productos
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Accept: application/json
```

---

## Ejemplos Completos

### **Crear Producto**

```http
POST /api/product/productos
Content-Type: application/json
Authorization: Bearer {token}

{
    "nombre": "Cemento Portland Tipo I",
    "codigo": "CEM-001",
    "descripcion": "Cemento para construcción general",
    "categoria_id": 5,
    "marca_id": 10,
    "precio_compra": 45.00,
    "precio_venta": 55.00,
    "stock_minimo": 100,
    "activo": true
}
```

**Respuesta 201 Created:**
```json
{
    "success": true,
    "message": "Producto creado exitosamente",
    "data": {
        "id": 124,
        "nombre": "Cemento Portland Tipo I",
        "codigo": "CEM-001",
        "precio_venta": 55.00,
        "categoria": {
            "id": 5,
            "nombre": "Materiales de Construcción"
        },
        "created_at": "2025-12-04 11:30:00"
    }
}
```

---

### **Listar Productos con Filtros**

```http
GET /api/product/productos?filter[categoria_id]=5&filter[activo]=1&sort=-created_at&per_page=20&include=categoria,marca
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta 200 OK:**
```json
{
    "success": true,
    "data": [
        {
            "id": 124,
            "nombre": "Cemento Portland Tipo I",
            "codigo": "CEM-001",
            "precio_venta": 55.00,
            "categoria": {
                "id": 5,
                "nombre": "Materiales de Construcción"
            },
            "marca": {
                "id": 10,
                "nombre": "Cemento Cruz"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 45
    }
}
```

---

## Checklist de Desarrollo

Al crear un nuevo endpoint, verificar:

- [ ] URL sigue el patrón `/api/{module}/{resource}`
- [ ] Recurso en plural (productos, ventas)
- [ ] Método HTTP correcto (GET, POST, PUT, DELETE)
- [ ] Código de estado apropiado (200, 201, 404, etc.)
- [ ] Respuesta en formato estándar con `success` y `data`
- [ ] Validación de entrada (FormRequest)
- [ ] Autenticación requerida (`auth:api`)
- [ ] Autorización por roles (`role:admin`)
- [ ] Paginación implementada
- [ ] Filtros y ordenamiento disponibles
- [ ] Relaciones incluibles con `?include=`
- [ ] Nomenclatura snake_case en JSON
- [ ] Tests escritos (TDD)
- [ ] Documentado en README del módulo

---

## Referencias

- [REST API Best Practices](https://restfulapi.net/)
- [JSON:API Specification](https://jsonapi.org/)
- [HTTP Status Codes](https://httpstatuses.com/)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
