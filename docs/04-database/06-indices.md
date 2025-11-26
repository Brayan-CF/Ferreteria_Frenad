# ÍNDICES DE BASE DE DATOS

## Descripción General

Índices estratégicos para optimizar consultas frecuentes y mejorar performance del sistema.

---

## Estadísticas Generales

- **Total de índices:** 43+
- **Índices automáticos:** 25 (PRIMARY KEY, UNIQUE)
- **Índices manuales:** 18+
- **Índices especiales:** 3 (GIN full-text, índices parciales)

---

## Tipos de Índices Utilizados

### 1. B-Tree (por defecto)
- Búsquedas de igualdad y rangos
- Más comunes en el sistema

### 2. GIN (Generalized Inverted Index)
- Búsquedas full-text
- Usado en campos de texto largo

### 3. Índices Parciales (con WHERE)
- Solo indexan subconjunto de datos
- Optimizan consultas específicas

---

## Índices por Módulo

### MÓDULO PRODUCTS

**Ubicación:** `database/02_indexes/01_productos_indexes.sql`

**Índices:**

```sql
-- Búsquedas por relaciones
idx_productos_categoria (categoria_id)
idx_productos_marca (marca_id)

-- Búsquedas por códigos
idx_productos_sku (sku)
idx_productos_codigo_barras (codigo_barras) WHERE codigo_barras IS NOT NULL

-- Filtros comunes
idx_productos_activo (activo) WHERE activo = TRUE

-- Búsqueda full-text
idx_productos_nombre USING GIN(to_tsvector('spanish', nombre))
```

**Consultas optimizadas:**
- Buscar productos por categoría
- Buscar por SKU o código de barras
- Búsqueda de texto en nombres
- Listar solo productos activos

---

### MÓDULO INVENTORY

**Ubicación:** `database/02_indexes/02_inventario_indexes.sql`

**Índices:**

```sql
idx_inventario_producto (producto_id)
idx_inventario_almacen (almacen_id)
idx_inventario_stock_bajo (cantidad_actual) 
    WHERE cantidad_actual <= stock_minimo
```

**Consultas optimizadas:**
- Ver stock de un producto
- Stock por almacén
- **Productos con stock bajo** (índice parcial)

---

**Ubicación:** `database/02_indexes/03_movimientos_inventario_indexes.sql`

**Índices:**

```sql
idx_movimientos_inventario_producto (producto_id)
idx_movimientos_inventario_almacen (almacen_id)
idx_movimientos_inventario_tipo (tipo_movimiento)
idx_movimientos_inventario_fecha (creado_en)
idx_movimientos_inventario_usuario (usuario_id)
idx_movimientos_inventario_compra (compra_id) WHERE compra_id IS NOT NULL
idx_movimientos_inventario_venta (venta_id) WHERE venta_id IS NOT NULL
```

**Consultas optimizadas:**
- Kardex de producto
- Movimientos por tipo
- Movimientos por fecha
- Movimientos vinculados a ventas/compras

---

### MÓDULO SALES

**Ubicación:** `database/02_indexes/04_ventas_indexes.sql`

**Índices:**

```sql
-- UNIQUE para garantizar unicidad
idx_ventas_numero (numero_venta) UNIQUE

-- Búsquedas frecuentes
idx_ventas_fecha (fecha_venta)
idx_ventas_cliente (cliente_id)
idx_ventas_usuario (usuario_id)
idx_ventas_estado (estado)
idx_ventas_tipo (tipo_venta)

-- Detalle de ventas
idx_detalle_ventas_venta (venta_id)
idx_detalle_ventas_producto (producto_id)
idx_detalle_ventas_almacen (almacen_id)
```

**Consultas optimizadas:**
- Buscar venta por número
- Ventas por fecha (reportes diarios)
- Ventas por cliente
- Ventas por vendedor
- Detalle de una venta específica

---

### MÓDULO PURCHASES

**Ubicación:** `database/02_indexes/05_compras_indexes.sql`

**Índices:**

```sql
-- Compras
idx_compras_proveedor (proveedor_id)
idx_compras_fecha (fecha_compra)
idx_compras_estado (estado)
idx_compras_usuario (usuario_id)
idx_compras_numero (numero_compra)

-- Órdenes de compra
idx_ordenes_compra_proveedor (proveedor_id)
idx_ordenes_compra_estado (estado)
```

**Consultas optimizadas:**
- Compras por proveedor
- Compras por fecha
- Órdenes pendientes

---

### MÓDULO CUSTOMERS

**Ubicación:** `database/02_indexes/06_clientes_indexes.sql`

**Índices:**

```sql
idx_clientes_nit (nit) WHERE nit IS NOT NULL
idx_clientes_frecuente (es_frecuente) WHERE es_frecuente = TRUE
idx_clientes_nombre USING GIN(to_tsvector('spanish', nombre_completo))
```

**Consultas optimizadas:**
- Buscar cliente por NIT
- Listar clientes frecuentes
- Búsqueda de texto por nombre

---

**Ubicación:** `database/02_indexes/07_creditos_indexes.sql`

**Índices:**

```sql
idx_creditos_cliente (cliente_id)
idx_creditos_venta (venta_id)
idx_creditos_estado (estado)
idx_creditos_vencimiento (fecha_vencimiento)
idx_pagos_credito_credito (credito_cliente_id)
```

**Consultas optimizadas:**
- Créditos de un cliente
- Créditos por estado
- Créditos vencidos
- Pagos de un crédito

---

### MÓDULO CASH

**Ubicación:** `database/02_indexes/08_caja_indexes.sql`

**Índices:**

```sql
idx_arqueos_caja_fecha_apertura (fecha_apertura)
idx_arqueos_caja_estado (estado)
idx_movimientos_caja_arqueo (arqueo_caja_id)
idx_movimientos_caja_tipo (tipo_movimiento)
```

**Consultas optimizadas:**
- Arqueos por fecha
- Caja actual (estado = 'abierta')
- Movimientos de un arqueo

---

### MÓDULO AUDIT

**Ubicación:** `database/02_indexes/09_auditoria_indexes.sql`

**Índices:**

```sql
idx_logs_tabla (tabla_afectada)
idx_logs_registro (registro_id)
idx_logs_usuario (usuario_id)
idx_logs_fecha (creado_en)
idx_logs_accion (accion)
```

**Consultas optimizadas:**
- Logs de una tabla específica
- Historial de un registro
- Cambios por usuario
- Logs por fecha

---

## Índices Especiales

### 1. Índices Full-Text (GIN)

**Propósito:** Búsqueda de texto en español.

**Implementados en:**
- `productos.nombre`
- `clientes.nombre_completo`

**Uso:**
```sql
SELECT * FROM productos
WHERE to_tsvector('spanish', nombre) @@ to_tsquery('spanish', 'cemento');
```

**Ventaja:** Búsqueda rápida incluso con tildes y variaciones.

---

### 2. Índices Parciales (con WHERE)

**Propósito:** Indexar solo datos relevantes.

**Ejemplos:**

```sql
-- Solo productos activos
CREATE INDEX idx_productos_activo ON productos(activo) 
WHERE activo = TRUE;

-- Solo stock bajo
CREATE INDEX idx_inventario_stock_bajo ON inventario(cantidad_actual) 
WHERE cantidad_actual <= stock_minimo;

-- Solo clientes frecuentes
CREATE INDEX idx_clientes_frecuente ON clientes(es_frecuente) 
WHERE es_frecuente = TRUE;
```

**Ventaja:** Menor tamaño, mayor velocidad.

---

### 3. Índices UNIQUE

**Propósito:** Garantizar unicidad además de optimizar.

**Implementados:**
- `usuarios.email`
- `productos.sku`
- `productos.codigo_barras`
- `ventas.numero_venta`
- `compras.numero_compra`

---

## Análisis de Performance

### Ver uso de índices:

```sql
SELECT schemaname, tablename, indexname, idx_scan, idx_tup_read, idx_tup_fetch
FROM pg_stat_user_indexes
WHERE schemaname = 'public'
ORDER BY idx_scan DESC;
```

**Columnas:**
- `idx_scan`: Veces que se usó el índice
- `idx_tup_read`: Filas leídas
- `idx_tup_fetch`: Filas devueltas

---

### Ver tamaño de índices:

```sql
SELECT 
    indexname,
    pg_size_pretty(pg_relation_size(indexrelid)) AS size
FROM pg_stat_user_indexes
WHERE schemaname = 'public'
ORDER BY pg_relation_size(indexrelid) DESC;
```

---

### Detectar índices no utilizados:

```sql
SELECT schemaname, tablename, indexname, idx_scan
FROM pg_stat_user_indexes
WHERE schemaname = 'public'
AND idx_scan = 0
AND indexname NOT LIKE '%_pkey'; -- Excluir PRIMARY KEYs
```

**Acción:** Considerar eliminar si no se usan después de 3 meses.

---

## Mantenimiento de Índices

### REINDEX

**Cuándo:** Después de actualizaciones masivas.

```sql
-- Reindexar una tabla
REINDEX TABLE productos;

-- Reindexar un índice específico
REINDEX INDEX idx_productos_nombre;

-- Reindexar toda la base de datos
REINDEX DATABASE ferreteria_frenad;
```

---

### VACUUM y ANALYZE

**Cuándo:** Semanalmente o después de cambios grandes.

```sql
-- Actualizar estadísticas (ayuda al planner)
ANALYZE productos;

-- Limpiar y actualizar
VACUUM ANALYZE productos;
```

---

## Estrategia de Indexación

### Columnas que DEBEN tener índice:

✅ Foreign Keys (todas)
✅ Columnas en WHERE frecuentes
✅ Columnas en JOIN
✅ Columnas en ORDER BY de reportes
✅ Columnas UNIQUE

### Columnas que NO necesitan índice:

❌ Tablas muy pequeñas (< 1000 filas)
❌ Columnas con pocos valores distintos (booleanos simples)
❌ Columnas que nunca se consultan

---

## Índices Propuestos para el Futuro

### Si el volumen de datos crece:

1. **Índice compuesto en detalle_ventas:**
```sql
CREATE INDEX idx_detalle_ventas_venta_producto 
ON detalle_ventas(venta_id, producto_id);
```

2. **Índice en fecha + estado de ventas:**
```sql
CREATE INDEX idx_ventas_fecha_estado 
ON ventas(fecha_venta, estado);
```

3. **Particionamiento de logs_auditoria:**
```sql
-- Particionar por mes cuando > 1 millón de registros
CREATE TABLE logs_auditoria_2025_01 
PARTITION OF logs_auditoria
FOR VALUES FROM ('2025-01-01') TO ('2025-02-01');
```

---

## Impacto en Performance

### Benchmarks (estimados):

| Operación | Sin índice | Con índice |
|-----------|-----------|------------|
| Buscar venta por número | 500ms | 5ms |
| Buscar producto por nombre | 1200ms | 15ms |
| Kardex de producto | 800ms | 25ms |
| Clientes con deuda | 2000ms | 50ms |

**Mejora promedio:** 20-100x más rápido

---

## Buenas Prácticas

### Al crear índices:

1. **Analizar queries lentos** primero (EXPLAIN ANALYZE)
2. **No sobre-indexar** (cada índice cuesta espacio y writes)
3. **Índices compuestos** si se buscan múltiples columnas juntas
4. **Índices parciales** para filtros comunes

### Al mantener índices:

1. **Monitorear uso** mensualmente
2. **REINDEX** después de migraciones
3. **VACUUM ANALYZE** semanalmente
4. **Revisar tamaño** (no deben crecer descontroladamente)

---

## Referencias

- **Código fuente:** `database/02_indexes/`
- **Performance testing:** `docs/04-database/10-testing.md` (Sección NIVEL 7)
- **Documentación PostgreSQL:** https://www.postgresql.org/docs/current/indexes.html
