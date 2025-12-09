# Módulo de Inventario

## Descripción
Control de existencias en múltiples almacenes con seguimiento completo de movimientos (entradas, salidas, traspasos).

## Modelos

### Inventario
- **Ubicación:** `Modules/Inventory/Models/Inventario.php`
- **Tabla:** `inventario`
- **Campos:** producto_id, almacen_id, cantidad_actual, stock_minimo

### MovimientoInventario
- **Ubicación:** `Modules/Inventory/Models/MovimientoInventario.php`
- **Tabla:** `movimientos_inventario`
- **Tipos:** ENTRADA_COMPRA, SALIDA_VENTA, AJUSTE_ENTRADA, AJUSTE_SALIDA, TRASPASO

### Almacen
- **Ubicación:** `Modules/Inventory/Models/Almacen.php`
- **Tabla:** `almacenes`
- **Campos:** nombre, direccion, activo

## Endpoints

### Inventario
- `GET /api/inventory` - Listar inventario (con filtros)
- `GET /api/inventory/{id}` - Ver detalle de inventario
- `POST /api/inventory/adjust` - Ajustar inventario manualmente
- `GET /api/inventory/kardex/{productoId}` - Ver kardex de producto

### Almacenes
- `GET /api/warehouses` - Listar almacenes
- `POST /api/warehouses` - Crear almacén
- `PUT /api/warehouses/{id}` - Actualizar almacén
- `POST /api/warehouses/{id}/activate` - Activar/desactivar almacén

### Movimientos
- `GET /api/inventory/movements` - Listar movimientos
- `POST /api/inventory/transfer` - Traspasar entre almacenes

## Características

### Tipos de Movimiento
```
ENTRADA_COMPRA    → Incrementa stock (automático desde compras)
SALIDA_VENTA      → Reduce stock (automático desde ventas)
AJUSTE_ENTRADA    → Incrementa stock (manual)
AJUSTE_SALIDA     → Reduce stock (manual)
TRASPASO          → Mueve entre almacenes
```

### Kardex
Historial completo de movimientos por producto:
- Fecha y hora exacta
- Tipo de movimiento
- Cantidad entrada/salida
- Saldo resultante
- Usuario responsable
- Razón del movimiento

### Stock Mínimo
- Alertas automáticas cuando `cantidad_actual < stock_minimo`
- Configurable por producto y almacén

## Validaciones

### Ajuste de Inventario
- **producto_id:** requerido, debe existir
- **almacen_id:** requerido, debe existir
- **tipo_movimiento:** requerido (AJUSTE_ENTRADA o AJUSTE_SALIDA)
- **cantidad:** requerido, mayor a 0
- **razon:** requerido para ajustes

### Traspaso
- **producto_id:** requerido
- **almacen_origen_id:** requerido, debe tener stock suficiente
- **almacen_destino_id:** requerido, diferente al origen
- **cantidad:** requerido, mayor a 0, no exceder stock disponible

## Ejemplos de Uso

### Ajustar Inventario
```bash
POST /api/inventory/adjust
{
  "producto_id": 5,
  "almacen_id": 1,
  "tipo_movimiento": "AJUSTE_ENTRADA",
  "cantidad": 10,
  "razon": "Corrección por inventario físico"
}
```

### Traspasar entre Almacenes
```bash
POST /api/inventory/transfer
{
  "producto_id": 8,
  "almacen_origen_id": 1,
  "almacen_destino_id": 2,
  "cantidad": 50,
  "razon": "Reposición sucursal"
}
```

### Consultar Kardex
```bash
GET /api/inventory/kardex/5?fecha_inicio=2025-01-01&fecha_fin=2025-12-31
```

## Integración con Otros Módulos

- **Compras:** Genera automáticamente movimientos ENTRADA_COMPRA
- **Ventas:** Genera automáticamente movimientos SALIDA_VENTA
- **Reportes:** Proporciona datos para reportes de stock y valorización

## Notas Técnicas
- Los movimientos son inmutables (no se pueden editar, solo crear nuevos ajustes)
- Cada movimiento registra el usuario responsable
- El sistema previene stock negativo en salidas y ventas
- Los traspasos crean dos movimientos: salida en origen y entrada en destino
