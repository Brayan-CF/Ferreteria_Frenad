# Módulo de Productos

## Descripción
Gestión completa del catálogo de productos con soporte para múltiples unidades de medida, categorías y marcas.

## Modelos

### Producto
- **Ubicación:** `Modules/Product/Models/Producto.php`
- **Tabla:** `productos`
- **Campos principales:** nombre, descripción, sku, precio_venta, precio_compra, categoria_id, marca_id

### ProductoUnidad
- **Ubicación:** `Modules/Product/Models/ProductoUnidad.php`
- **Tabla:** `producto_unidades`
- **Permite:** Conversión de unidades (ej: vender por metro, comprar por rollo)

## Endpoints

### Productos
- `GET /api/products` - Listar productos (con paginación y filtros)
- `GET /api/products/{id}` - Ver detalle de producto
- `POST /api/products` - Crear producto
- `PUT /api/products/{id}` - Actualizar producto
- `DELETE /api/products/{id}` - Eliminar producto
- `POST /api/products/{id}/activate` - Activar/desactivar producto

### Categorías
- `GET /api/categories` - Listar categorías
- `POST /api/categories` - Crear categoría

### Marcas
- `GET /api/brands` - Listar marcas
- `POST /api/brands` - Crear marca

## Características

### Conversión de Unidades
Permite manejar diferentes unidades de medida por producto:
```php
// Ejemplo: Cable eléctrico
- Unidad base: metro (m)
- Unidad compra: rollo de 100m (factor: 100)
- Unidad venta: metro (factor: 1)
```

### Scopes Disponibles
- `Activos()` - Solo productos activos
- `Buscar($termino)` - Búsqueda por nombre, descripción o SKU
- `PorCategoria($categoriaId)` - Filtrar por categoría
- `PorMarca($marcaId)` - Filtrar por marca
- `ConStock()` - Productos con stock disponible

## Validaciones

### Crear Producto
- **nombre:** requerido, max 200 caracteres, único
- **sku:** requerido, max 50 caracteres, único
- **precio_venta:** requerido, numérico, mayor a 0
- **precio_compra:** requerido, numérico, mayor a 0
- **categoria_id:** requerido, debe existir
- **marca_id:** opcional, debe existir si se proporciona

## Ejemplos de Uso

### Crear Producto con Unidades
```bash
POST /api/products
{
  "nombre": "Cable THW #12",
  "sku": "CAB-THW-12",
  "descripcion": "Cable eléctrico calibre 12",
  "precio_venta": 5.50,
  "precio_compra": 3.80,
  "categoria_id": 1,
  "marca_id": 2,
  "unidades": [
    {
      "unidad_id": 1,
      "factor_conversion": 1,
      "es_unidad_venta": true
    },
    {
      "unidad_id": 5,
      "factor_conversion": 100,
      "es_unidad_compra": true
    }
  ]
}
```

### Buscar Productos
```bash
GET /api/products?buscar=cable&categoria_id=1&activo=true&per_page=20
```

## Notas Técnicas
- Los precios se almacenan en la unidad base del producto
- El SKU debe ser único en todo el sistema
- Al eliminar un producto se verifica que no tenga movimientos de inventario
- Las categorías y marcas se manejan como catálogos simples
