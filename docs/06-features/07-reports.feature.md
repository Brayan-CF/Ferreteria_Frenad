# Módulo de Reportes

## Descripción
Sistema completo de reportes para análisis de ventas, inventario, compras, clientes y finanzas. Solo accesible para **Administrador** y **Gerente**.

## Seguridad
- **Middleware:** `auth:sanctum` + `role:Administrador,Gerente`
- **Formato:** JSON plano (sin gráficos)

## Parámetros Comunes

### Períodos
```
hoy         → Día actual
semana      → Semana actual
mes         → Mes actual (default)
trimestre   → Trimestre actual
año         → Año actual
personalizado → Usar fecha_inicio y fecha_fin
```

### Filtros Opcionales
- `fecha_inicio` - Fecha inicio (YYYY-MM-DD)
- `fecha_fin` - Fecha fin (YYYY-MM-DD)
- `vendedor_id` - Filtrar por vendedor
- `proveedor_id` - Filtrar por proveedor
- `cliente_id` - Filtrar por cliente
- `categoria_id` - Filtrar por categoría
- `producto_id` - Filtrar por producto
- `almacen_id` - Filtrar por almacén

## Endpoints

### Reportes de Ventas (6)
- `GET /api/reports/ventas/resumen` - Resumen general
- `GET /api/reports/ventas/por-vendedor` - Ventas por vendedor
- `GET /api/reports/ventas/por-producto` - Productos más vendidos
- `GET /api/reports/ventas/por-categoria` - Ventas por categoría
- `GET /api/reports/ventas/tendencia-diaria` - Tendencia día a día
- `GET /api/reports/ventas/descuentos` - Análisis de descuentos

### Reportes de Inventario (4)
- `GET /api/reports/inventario/stock-bajo` - Productos bajo stock mínimo
- `GET /api/reports/inventario/movimientos` - Kardex de movimientos
- `GET /api/reports/inventario/valorizado` - Valor total del inventario
- `GET /api/reports/inventario/sin-movimiento` - Productos sin movimiento

### Reportes de Compras (4)
- `GET /api/reports/compras/resumen` - Resumen general
- `GET /api/reports/compras/por-proveedor` - Compras por proveedor
- `GET /api/reports/compras/por-producto` - Productos más comprados
- `GET /api/reports/compras/tendencia-diaria` - Tendencia día a día

### Reportes de Clientes (4)
- `GET /api/reports/clientes/creditos-pendientes` - Créditos activos
- `GET /api/reports/clientes/top-clientes` - Mejores clientes
- `GET /api/reports/clientes/morosidad` - Análisis de morosidad
- `GET /api/reports/clientes/clientes-nuevos` - Clientes registrados

### Reportes Financieros (4)
- `GET /api/reports/financiero/flujo-caja` - Ingresos vs egresos
- `GET /api/reports/financiero/ingresos-egresos` - Detallado por método/proveedor
- `GET /api/reports/financiero/cierre-caja` - Cierre diario
- `GET /api/reports/financiero/rentabilidad` - Margen por producto

## Ejemplos de Uso

### Resumen de Ventas del Mes
```bash
GET /api/reports/ventas/resumen?periodo=mes

# Respuesta:
{
  "success": true,
  "data": {
    "resumen": {
      "total_ventas": 45678.50,
      "cantidad_ventas": 123,
      "promedio_venta": 371.28,
      "ticket_maximo": 2500.00,
      "ticket_minimo": 45.00
    },
    "por_tipo": [...],
    "por_metodo_pago": [...],
    "periodo": {
      "fecha_inicio": "2025-12-01",
      "fecha_fin": "2025-12-31"
    }
  }
}
```

### Productos con Stock Bajo
```bash
GET /api/reports/inventario/stock-bajo

# Respuesta:
{
  "success": true,
  "data": [
    {
      "producto": "Cable THW #12",
      "sku": "CAB-THW-12",
      "categoria": "Cables",
      "almacen": "Almacén Principal",
      "cantidad_actual": 15,
      "stock_minimo": 50,
      "cantidad_necesaria": 35
    },
    ...
  ]
}
```

### Inventario Valorizado
```bash
GET /api/reports/inventario/valorizado?almacen_id=1

# Respuesta:
{
  "success": true,
  "data": {
    "items": [...],
    "resumen": {
      "valor_total": 156780.50,
      "cantidad_productos": 247
    }
  }
}
```

### Flujo de Caja Trimestral
```bash
GET /api/reports/financiero/flujo-caja?periodo=trimestre

# Respuesta:
{
  "success": true,
  "data": {
    "ingresos": 234567.89,
    "egresos": 178900.45,
    "flujo_neto": 55667.44,
    "periodo": {
      "fecha_inicio": "2025-10-01",
      "fecha_fin": "2025-12-31"
    }
  }
}
```

### Top Clientes del Año
```bash
GET /api/reports/clientes/top-clientes?periodo=año&limit=10

# Respuesta:
{
  "success": true,
  "data": [
    {
      "cliente": "Juan Pérez",
      "telefono": "77123456",
      "cantidad_compras": 45,
      "total_comprado": 89560.00,
      "promedio_compra": 1990.22,
      "ultima_compra": "2025-12-08"
    },
    ...
  ]
}
```

### Movimientos de Inventario con Filtros
```bash
GET /api/reports/inventario/movimientos?periodo=semana&producto_id=5&tipo_movimiento=ENTRADA_COMPRA
```

### Cierre de Caja de Hoy
```bash
GET /api/reports/financiero/cierre-caja?fecha=2025-12-09

# Respuesta:
{
  "success": true,
  "data": {
    "fecha": "2025-12-09",
    "ventas": [
      {"metodo_pago": "efectivo", "cantidad": 23, "monto": 12450.00},
      {"metodo_pago": "qr", "cantidad": 18, "monto": 8760.50}
    ],
    "pagos_creditos": {
      "cantidad": 5,
      "monto": 3200.00
    },
    "resumen": {
      "total_efectivo": 12450.00,
      "total_electronico": 8760.50,
      "total_general": 24410.50
    }
  }
}
```

## Servicios Disponibles

### VentasReportService
- `resumenGeneral()` - Totales y promedios
- `ventasPorVendedor()` - Ranking de vendedores
- `productosMasVendidos()` - Top productos
- `ventasPorCategoria()` - Distribución por categoría
- `ventasDiarias()` - Tendencia temporal
- `analisisDescuentos()` - Estadísticas de descuentos

### InventarioReportService
- `stockBajo()` - Alertas de reposición
- `movimientos()` - Kardex completo
- `inventarioValorizado()` - Valorización total
- `productosSinMovimiento()` - Productos estancados

### ComprasReportService
- `resumenGeneral()` - Totales de compras
- `comprasPorProveedor()` - Análisis por proveedor
- `productosMasComprados()` - Productos frecuentes
- `comprasDiarias()` - Tendencia de compras

### ClientesReportService
- `creditosPendientes()` - Cartera activa
- `topClientes()` - Mejores clientes
- `analisisMorosidad()` - Clientes morosos
- `clientesNuevos()` - Nuevas altas

### FinancieroReportService
- `flujoCaja()` - Ingresos vs egresos
- `ingresosEgresos()` - Detalle por método/proveedor
- `cierreCaja()` - Cierre diario
- `rentabilidad()` - Análisis de márgenes

## Notas Técnicas
- Todos los reportes usan transacciones READ ONLY
- Las consultas están optimizadas con índices
- Los resultados no están paginados (usar LIMIT en consultas)
- Los montos se retornan en formato numérico (no string)
- Las fechas se retornan en formato ISO 8601
- Los reportes NO modifican datos, solo consultan
