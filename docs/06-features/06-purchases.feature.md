# Módulo de Compras

## Descripción
Gestión de compras a proveedores con órdenes de compra, recepción de productos y actualización automática de inventario.

## Modelos

### Proveedor
- **Ubicación:** `Modules/Purchase/Models/Proveedor.php`
- **Tabla:** `proveedores`
- **Campos:** razon_social, nit, telefono, direccion, email, nombre_contacto

### OrdenCompra
- **Ubicación:** `Modules/Purchase/Models/OrdenCompra.php`
- **Tabla:** `ordenes_compra`
- **Campos:** numero_orden, proveedor_id, fecha_orden, fecha_entrega, total, estado

### Compra
- **Ubicación:** `Modules/Purchase/Models/Compra.php`
- **Tabla:** `compras`
- **Campos:** numero_compra, proveedor_id, orden_compra_id, fecha_compra, subtotal, total, estado

### DetalleCompra
- **Ubicación:** `Modules/Purchase/Models/DetalleCompra.php`
- **Tabla:** `detalle_compras`
- **Campos:** compra_id, producto_id, cantidad, precio_unitario, subtotal, almacen_destino_id

## Endpoints

### Proveedores
- `GET /api/suppliers` - Listar proveedores
- `GET /api/suppliers/{id}` - Ver proveedor con estadísticas
- `POST /api/suppliers` - Registrar proveedor
- `PUT /api/suppliers/{id}` - Actualizar proveedor
- `DELETE /api/suppliers/{id}` - Eliminar proveedor
- `POST /api/suppliers/{id}/activate` - Activar/desactivar proveedor
- `GET /api/suppliers/{id}/purchase-history` - Historial de compras
- `GET /api/suppliers/stats` - Estadísticas de proveedores

### Compras
- `GET /api/purchases` - Listar compras
- `GET /api/purchases/{id}` - Ver detalle de compra
- `POST /api/purchases` - Registrar compra
- `PUT /api/purchases/{id}/cancel` - Cancelar compra
- `GET /api/purchases/stats` - Estadísticas de compras

## Características

### Estados de Orden de Compra
```
pendiente           → Orden creada, esperando aprobación
aprobada            → Orden aprobada, lista para compra
recibida_parcial    → Productos recibidos parcialmente
recibida_completa   → Todos los productos recibidos
cancelada           → Orden cancelada
```

### Estados de Compra
```
pendiente  → Compra registrada, esperando recepción
recibida   → Productos recibidos, inventario actualizado
cancelada  → Compra anulada
```

### Numeración Automática
- Formato: `C-YYYYMMDD-####`
- Ejemplo: `C-20251209-0001`
- Secuencial por día

### Proceso Automático
1. Registrar compra
2. Validar proveedor activo
3. Incrementar inventario por almacén destino
4. Crear movimiento ENTRADA_COMPRA
5. Actualizar estado de orden de compra (si existe)
6. Generar número de compra

## Validaciones

### Crear Proveedor
- **razon_social:** requerido, max 200 caracteres
- **nit:** opcional, único si se proporciona
- **telefono:** requerido
- **email:** opcional, formato válido
- **nombre_contacto:** opcional, max 100 caracteres

### Crear Compra
- **proveedor_id:** requerido, debe existir y estar activo
- **orden_compra_id:** opcional, debe existir
- **productos:** mínimo 1 producto
- **productos.*.producto_id:** debe existir
- **productos.*.cantidad:** mayor a 0
- **productos.*.precio_unitario:** mayor a 0
- **productos.*.almacen_destino_id:** requerido, debe existir

## Ejemplos de Uso

### Registrar Proveedor
```bash
POST /api/suppliers
{
  "razon_social": "FERRETERÍA IMPORTADORA BOLIVIA S.R.L.",
  "nit": "1029384756012",
  "telefono": "2-2345678",
  "direccion": "Zona Industrial, Calle 5 #234",
  "email": "ventas@importadorabolivia.com",
  "nombre_contacto": "Roberto Gutiérrez"
}
```

### Registrar Compra
```bash
POST /api/purchases
{
  "proveedor_id": 3,
  "orden_compra_id": 15,
  "fecha_compra": "2025-12-09",
  "productos": [
    {
      "producto_id": 8,
      "cantidad": 100,
      "precio_unitario": 45.50,
      "almacen_destino_id": 1
    },
    {
      "producto_id": 12,
      "cantidad": 50,
      "precio_unitario": 120.00,
      "almacen_destino_id": 2
    }
  ],
  "notas": "Compra de fin de mes"
}
```

### Consultar Historial de Proveedor
```bash
GET /api/suppliers/3/purchase-history?fecha_inicio=2025-01-01
```

### Cancelar Compra
```bash
PUT /api/purchases/45/cancel
{
  "razon": "Productos defectuosos, se devuelven"
}
```

## Integración con Otros Módulos

- **Inventario:** Incrementa stock automáticamente al registrar compra
- **Productos:** Valida existencia de productos
- **Reportes:** Proporciona datos para análisis de compras

## Scopes Disponibles

### Proveedor
- `Activos()` - Solo proveedores activos
- `Buscar($termino)` - Por razón social, NIT o contacto

### Compra
- `EntreFechas($inicio, $fin)` - Compras en rango
- `PorProveedor($proveedorId)` - De un proveedor específico
- `PorEstado($estado)` - Filtrar por estado

## Accessors Útiles

### Proveedor
```php
$proveedor->total_compras       // Suma de todas las compras
$proveedor->cantidad_compras    // Número de compras realizadas
```

## Notas Técnicas
- Cada producto en la compra puede ir a un almacén diferente
- Al cancelar una compra, el inventario se reduce automáticamente
- Las compras pueden crearse sin orden de compra previa
- Si hay orden de compra, el sistema actualiza su estado automáticamente:
  - `recibida_completa` si todos los productos fueron recibidos
- Los precios de compra NO actualizan el precio del catálogo
- Cada compra registra el usuario que la realizó
