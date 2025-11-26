# GUÍA DE VISTAS - REPORTES Y CONSULTAS

## Descripción General

Las vistas proporcionan consultas predefinidas y optimizadas para reportes comunes del sistema.

---

## Lista de Vistas

### 1. vista_stock_actual

**Propósito:** Muestra el stock actual de todos los productos por almacén con niveles de alerta.

**Ubicación:** [database/05_views/01_vista_stock_actual.sql](../../database/05_views/01_vista_stock_actual.sql)

**Columnas:**
- `producto_id`
- `sku`
- `codigo_barras`
- `producto_nombre`
- `categoria_nombre`
- `marca_nombre`
- `unidad_medida`
- `almacen_nombre`
- `cantidad_actual`
- `stock_minimo`
- `nivel_stock` (BAJO, MEDIO, NORMAL)
- `precio_compra`
- `precio_venta`
- `valor_inventario` (cantidad × precio_compra)
- `activo`

**Uso Común:**
```sql
-- Ver productos con stock bajo
SELECT * FROM vista_stock_actual
WHERE nivel_stock = 'BAJO'
ORDER BY producto_nombre;

-- Valor total del inventario
SELECT almacen_nombre, SUM(valor_inventario) AS valor_total
FROM vista_stock_actual
GROUP BY almacen_nombre;
```

---

### 2. vista_productos_stock_bajo

**Propósito:** Lista productos que necesitan reposición urgente.

**Ubicación:** [database/05_views/02_vista_productos_stock_bajo.sql](../../database/05_views/02_vista_productos_stock_bajo.sql)

**Columnas:**
- `id`
- `sku`
- `nombre`
- `categoria`
- `almacen`
- `cantidad_actual`
- `stock_minimo`
- `cantidad_reponer`
- `precio_compra`
- `costo_reposicion`

**Uso Común:**
```sql
-- Lista de compras sugerida
SELECT sku, nombre, almacen, cantidad_reponer, costo_reposicion
FROM vista_productos_stock_bajo
ORDER BY cantidad_reponer DESC;
```

---

### 3. vista_ventas_hoy

**Propósito:** Todas las ventas realizadas hoy.

**Ubicación:** [database/05_views/03_vista_ventas_hoy.sql](../../database/05_views/03_vista_ventas_hoy.sql)

**Columnas:**
- `id`
- `numero_venta`
- `fecha_venta`
- `cliente`
- `vendedor`
- `tipo_venta`
- `metodo_pago`
- `subtotal`
- `descuento_monto`
- `iva`
- `total`
- `estado`

**Uso Común:**
```sql
-- Resumen del día
SELECT 
    COUNT(*) AS total_ventas,
    SUM(total) AS ventas_dia,
    AVG(total) AS ticket_promedio
FROM vista_ventas_hoy
WHERE estado = 'completada';
```

---

### 4. vista_productos_mas_vendidos

**Propósito:** Top 50 productos más vendidos de los últimos 30 días.

**Ubicación:** [database/05_views/04_vista_productos_mas_vendidos.sql](../../database/05_views/04_vista_productos_mas_vendidos.sql)

**Columnas:**
- `id`
- `sku`
- `nombre`
- `categoria`
- `veces_vendido`
- `cantidad_total_vendida`
- `ingresos_totales`
- `precio_promedio`

**Uso Común:**
```sql
-- Top 10 productos
SELECT * FROM vista_productos_mas_vendidos
LIMIT 10;

-- Productos más rentables
SELECT nombre, categoria, ingresos_totales
FROM vista_productos_mas_vendidos
ORDER BY ingresos_totales DESC
LIMIT 5;
```

---

### 5. vista_clientes_deuda

**Propósito:** Clientes con deuda pendiente ordenados por monto.

**Ubicación:** [database/05_views/05_vista_clientes_deuda.sql](../../database/05_views/05_vista_clientes_deuda.sql)

**Columnas:**
- `id`
- `nombre_completo`
- `telefono`
- `nit`
- `ventas_credito`
- `total_credito`
- `total_pagado`
- `saldo_pendiente`
- `fecha_vencimiento_proxima`
- `estado_deuda` (VENCIDO, POR VENCER, AL DÍA)

**Uso Común:**
```sql
-- Clientes morosos
SELECT * FROM vista_clientes_deuda
WHERE estado_deuda = 'VENCIDO'
ORDER BY saldo_pendiente DESC;

-- Total por cobrar
SELECT SUM(saldo_pendiente) AS total_por_cobrar
FROM vista_clientes_deuda;
```

---

### 6. vista_caja_hoy

**Propósito:** Estado actual de la caja del día.

**Ubicación:** [database/05_views/06_vista_caja_hoy.sql](../../database/05_views/06_vista_caja_hoy.sql)

**Columnas:**
- `arqueo_id`
- `fecha_apertura`
- `fecha_cierre`
- `usuario_apertura`
- `usuario_cierre`
- `monto_inicial`
- `total_ventas_efectivo`
- `total_ventas_qr`
- `monto_final`
- `total_esperado`
- `diferencia`
- `estado`
- `estado_diferencia` (SOBRANTE, FALTANTE, CUADRADO)

**Uso Común:**
```sql
-- Ver estado de caja actual
SELECT * FROM vista_caja_hoy;

-- Verificar si hay diferencias
SELECT * FROM vista_caja_hoy
WHERE ABS(diferencia) > 10; -- Diferencias mayores a Bs. 10
```

---

### 7. vista_kardex_producto

**Propósito:** Historial completo de movimientos de inventario.

**Ubicación:** [database/05_views/07_vista_kardex_producto.sql](../../database/05_views/07_vista_kardex_producto.sql)

**Columnas:**
- `id`
- `fecha_movimiento`
- `sku`
- `producto`
- `almacen`
- `almacen_destino`
- `tipo_movimiento`
- `cantidad`
- `usuario`
- `razon`
- `signo` (+, -, ~)

**Uso Común:**
```sql
-- Últimos movimientos de un producto
SELECT * FROM vista_kardex_producto
WHERE sku = 'PROD-001'
ORDER BY fecha_movimiento DESC
LIMIT 20;

-- Movimientos del día
SELECT * FROM vista_kardex_producto
WHERE DATE(fecha_movimiento) = CURRENT_DATE
ORDER BY fecha_movimiento DESC;
```

---

### 8. vista_utilidad_ventas

**Propósito:** Cálculo de utilidad y margen por venta.

**Ubicación:** [database/05_views/08_vista_utilidad_ventas.sql](../../database/05_views/08_vista_utilidad_ventas.sql)

**Columnas:**
- `venta_id`
- `numero_venta`
- `fecha_venta`
- `vendedor`
- `total_venta`
- `costo_total`
- `utilidad_bruta`
- `margen_porcentaje`

**Uso Común:**
```sql
-- Ventas más rentables del mes
SELECT * FROM vista_utilidad_ventas
WHERE DATE_TRUNC('month', fecha_venta) = DATE_TRUNC('month', CURRENT_DATE)
ORDER BY margen_porcentaje DESC
LIMIT 10;

-- Utilidad total del día
SELECT 
    COUNT(*) AS ventas,
    SUM(utilidad_bruta) AS utilidad_total,
    AVG(margen_porcentaje) AS margen_promedio
FROM vista_utilidad_ventas
WHERE DATE(fecha_venta) = CURRENT_DATE;
```

---

## Uso Combinado de Vistas

### Reporte Completo del Día:

```sql
-- 1. Ventas
SELECT COUNT(*), SUM(total) FROM vista_ventas_hoy WHERE estado = 'completada';

-- 2. Caja
SELECT estado, diferencia FROM vista_caja_hoy;

-- 3. Productos más vendidos hoy
SELECT p.nombre, SUM(dv.cantidad) AS vendido
FROM detalle_ventas dv
JOIN productos p ON dv.producto_id = p.id
JOIN ventas v ON dv.venta_id = v.id
WHERE DATE(v.fecha_venta) = CURRENT_DATE
GROUP BY p.id, p.nombre
ORDER BY vendido DESC
LIMIT 5;

-- 4. Utilidad
SELECT SUM(utilidad_bruta) FROM vista_utilidad_ventas
WHERE DATE(fecha_venta) = CURRENT_DATE;
```

---

## Vistas Materializadas (Futuro)

Para reportes pesados, considerar vistas materializadas:

```sql
-- Ejemplo: Vista materializada de ventas mensuales
CREATE MATERIALIZED VIEW vista_ventas_mes AS
SELECT 
    DATE_TRUNC('month', fecha_venta) AS mes,
    COUNT(*) AS total_ventas,
    SUM(total) AS ingresos
FROM ventas
WHERE estado = 'completada'
GROUP BY DATE_TRUNC('month', fecha_venta);

-- Índice para búsqueda rápida
CREATE INDEX idx_ventas_mes ON vista_ventas_mes(mes);

-- Refrescar datos (ejecutar diariamente)
REFRESH MATERIALIZED VIEW vista_ventas_mes;
```

---

## Performance de Vistas

### Análisis de Performance:

```sql
-- Ver plan de ejecución de una vista
EXPLAIN ANALYZE SELECT * FROM vista_stock_actual;
```

### Optimizaciones Aplicadas:

1. **Índices estratégicos** en columnas usadas en JOINs
2. **WHERE en definición** para filtrar datos innecesarios
3. **Evitar subconsultas complejas** donde sea posible
4. **Uso de COALESCE** para manejar NULL eficientemente

---

**Siguiente:** [Guía de Testing](10-testing.md)
