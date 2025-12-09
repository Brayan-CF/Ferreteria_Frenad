# Módulo de Ventas

## Descripción
Gestión completa de ventas con soporte para contado y crédito, aplicación de descuentos y actualización automática de inventario.

## Modelos

### Venta
- **Ubicación:** `Modules/Sales/Models/Venta.php`
- **Tabla:** `ventas`
- **Campos:** numero_venta, tipo_venta, metodo_pago, cliente_id, subtotal, descuento_monto, total, estado

### DetalleVenta
- **Ubicación:** `Modules/Sales/Models/DetalleVenta.php`
- **Tabla:** `detalle_ventas`
- **Campos:** venta_id, producto_id, cantidad, precio_unitario, subtotal

## Endpoints

### Ventas
- `GET /api/sales` - Listar ventas (con filtros)
- `GET /api/sales/{id}` - Ver detalle de venta
- `POST /api/sales` - Registrar venta
- `PUT /api/sales/{id}/cancel` - Cancelar venta
- `GET /api/sales/stats` - Estadísticas de ventas

## Características

### Tipos de Venta
```
CONTADO  → Pago inmediato (efectivo o QR)
CREDITO  → Pago diferido (crea crédito automáticamente)
```

### Métodos de Pago
- **efectivo** - Pago en efectivo
- **qr** - Pago con QR (QR Boliviano)

### Numeración Automática
- Formato: `V-YYYYMMDD-####`
- Ejemplo: `V-20251209-0001`
- Secuencial por día

### Estados de Venta
```
pendiente  → Venta registrada, esperando procesamiento
completada → Venta procesada, inventario actualizado
cancelada  → Venta anulada, inventario devuelto
```

### Proceso Automático
1. Registrar venta
2. Validar stock disponible
3. Reducir inventario (movimiento SALIDA_VENTA)
4. Si es crédito: crear registro en créditos_clientes
5. Generar número de venta

## Validaciones

### Crear Venta
- **tipo_venta:** requerido (contado o credito)
- **metodo_pago:** requerido (efectivo o qr)
- **cliente_id:** requerido si es venta a crédito
- **productos:** mínimo 1 producto
- **productos.*.producto_id:** debe existir y tener stock
- **productos.*.cantidad:** mayor a 0, no exceder stock disponible
- **productos.*.precio_unitario:** mayor a 0
- **descuento_porcentaje:** opcional, entre 0 y 100
- **descuento_monto:** opcional, no mayor al subtotal

## Ejemplos de Uso

### Venta al Contado
```bash
POST /api/sales
{
  "tipo_venta": "contado",
  "metodo_pago": "efectivo",
  "productos": [
    {
      "producto_id": 1,
      "cantidad": 5,
      "precio_unitario": 150.00
    },
    {
      "producto_id": 3,
      "cantidad": 2,
      "precio_unitario": 75.50
    }
  ],
  "descuento_porcentaje": 5,
  "notas": "Cliente frecuente"
}
```

### Venta a Crédito
```bash
POST /api/sales
{
  "tipo_venta": "credito",
  "metodo_pago": "qr",
  "cliente_id": 8,
  "productos": [
    {
      "producto_id": 5,
      "cantidad": 10,
      "precio_unitario": 85.00
    }
  ],
  "dias_credito": 30
}
```

### Consultar Ventas con Filtros
```bash
GET /api/sales?fecha_inicio=2025-12-01&tipo_venta=credito&estado=completada&per_page=20
```

### Cancelar Venta
```bash
PUT /api/sales/123/cancel
{
  "razon": "Cliente devolvió productos"
}
```

## Integración con Otros Módulos

- **Inventario:** Reduce stock automáticamente al completar venta
- **Clientes:** Crea crédito automáticamente en ventas a crédito
- **Reportes:** Proporciona datos para análisis de ventas

## Scopes Disponibles
- `EntreFechas($inicio, $fin)` - Filtrar por rango de fechas
- `PorTipo($tipo)` - Filtrar por tipo (contado/credito)
- `PorEstado($estado)` - Filtrar por estado
- `PorVendedor($usuarioId)` - Ventas de un usuario específico
- `Hoy()` - Ventas del día actual

## Notas Técnicas
- El descuento puede ser por porcentaje o monto fijo, no ambos
- Al cancelar una venta, el inventario se devuelve automáticamente
- Los precios se toman del catálogo pero pueden modificarse en la venta
- Las ventas canceladas no se eliminan, solo cambian de estado
- Cada venta guarda el usuario que la registró
