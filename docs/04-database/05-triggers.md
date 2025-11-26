# TRIGGERS AUTOMÁTICOS

## Descripción General

Los triggers ejecutan automáticamente funciones en respuesta a eventos de base de datos (INSERT, UPDATE, DELETE).

---

## Categorías de Triggers

1. **Timestamp Triggers** - Actualización automática de fechas
2. **Validation Triggers** - Validaciones antes de modificar datos
3. **Auto-number Triggers** - Generación de números correlativos

---

## 1. TIMESTAMP TRIGGERS

**Ubicación:** `database/04_triggers/01_timestamp_triggers.sql`

**Propósito:** Actualizar `actualizado_en` automáticamente en cada UPDATE.

**Función asociada:** `actualizar_timestamp()`

### Tablas con Timestamp Triggers:

| Tabla | Trigger |
|-------|---------|
| usuarios | `trigger_actualizar_usuarios` |
| productos | `trigger_actualizar_productos` |
| categorias | `trigger_actualizar_categorias` |
| clientes | `trigger_actualizar_clientes` |
| proveedores | `trigger_actualizar_proveedores` |
| ventas | `trigger_actualizar_ventas` |
| compras | `trigger_actualizar_compras` |
| ordenes_compra | `trigger_actualizar_ordenes_compra` |
| creditos_clientes | `trigger_actualizar_creditos` |

**Sintaxis ejemplo:**
```sql
CREATE TRIGGER trigger_actualizar_productos
    BEFORE UPDATE ON productos
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();
```

**Funcionamiento:**
- Se ejecuta **BEFORE UPDATE**
- Modifica `NEW.actualizado_en = CURRENT_TIMESTAMP`
- Transparente para la aplicación

---

## 2. VALIDATION TRIGGERS

**Ubicación:** `database/04_triggers/02_validation_triggers.sql`

### trigger_validar_stock_venta_insert

**Tabla:** `detalle_ventas`

**Momento:** BEFORE INSERT

**Propósito:** Rechazar ventas si no hay stock suficiente.

**Función:** `validar_stock_venta()`

**Comportamiento:**
```sql
-- Si stock insuficiente:
RAISE EXCEPTION 'Stock insuficiente. Disponible: X, Solicitado: Y'
```

**Ejemplo de rechazo:**
```sql
INSERT INTO detalle_ventas (producto_id, almacen_id, cantidad, ...)
VALUES (5, 2, 100, ...);

-- ERROR: Stock insuficiente. Disponible: 25, Solicitado: 100
```

---

### trigger_validar_stock_venta_update

**Tabla:** `detalle_ventas`

**Momento:** BEFORE UPDATE

**Condición:** Solo si cambia `cantidad` o `almacen_id`

**Sintaxis:**
```sql
CREATE TRIGGER trigger_validar_stock_venta_update
    BEFORE UPDATE ON detalle_ventas
    FOR EACH ROW
    WHEN (OLD.cantidad IS DISTINCT FROM NEW.cantidad 
          OR OLD.almacen_id IS DISTINCT FROM NEW.almacen_id)
    EXECUTE FUNCTION validar_stock_venta();
```

**Ventaja:** No se ejecuta en actualizaciones irrelevantes (performance).

---

## 3. AUTO-NUMBER TRIGGERS

**Ubicación:** `database/04_triggers/03_auto_number_triggers.sql`

### trigger_calcular_saldo_credito

**Tabla:** `creditos_clientes`

**Momento:** BEFORE INSERT OR UPDATE

**Función:** `calcular_saldo_credito()`

**Propósito:** Calcular automáticamente:
- `saldo_pendiente = monto_total - monto_pagado`
- `estado` según saldo y fecha

**Resultado automático:**
```sql
INSERT INTO creditos_clientes (cliente_id, venta_id, monto_total, monto_pagado)
VALUES (1, 10, 1000.00, 300.00);

-- Trigger calcula automáticamente:
-- saldo_pendiente = 700.00
-- estado = 'pagado_parcial'
```

---

### trigger_generar_numero_venta

**Tabla:** `ventas`

**Momento:** BEFORE INSERT

**Función:** `generar_numero_venta()`

**Formato:** `YYYYMMDD-NNNN`

**Comportamiento:**
```sql
INSERT INTO ventas (cliente_id, usuario_id, total, ...)
VALUES (1, 1, 500.00, ...);

-- Trigger genera automáticamente:
-- numero_venta = '20241215-0001'
```

**Ventaja:** El usuario no necesita calcular el número.

---

### trigger_generar_numero_compra

**Tabla:** `compras`

**Formato:** `C-YYYYMMDD-NNNN`

**Ejemplo:** `C-20241215-0001`

---

### trigger_generar_numero_orden

**Tabla:** `ordenes_compra`

**Formato:** `OC-YYYYMMDD-NNNN`

**Ejemplo:** `OC-20241215-0001`

---

### trigger_generar_numero_devolucion

**Tabla:** `devoluciones_venta`

**Formato:** `DV-YYYYMMDD-NNNN`

**Ejemplo:** `DV-20241215-0001`

---

## Resumen de Triggers por Tabla

| Tabla | Trigger | Tipo | Propósito |
|-------|---------|------|-----------|
| detalle_ventas | validar_stock (INSERT) | BEFORE | Validar stock |
| detalle_ventas | validar_stock (UPDATE) | BEFORE | Validar stock |
| creditos_clientes | calcular_saldo | BEFORE | Calcular saldo |
| ventas | generar_numero | BEFORE | Auto-número |
| compras | generar_numero | BEFORE | Auto-número |
| ordenes_compra | generar_numero | BEFORE | Auto-número |
| devoluciones_venta | generar_numero | BEFORE | Auto-número |
| productos | actualizar_timestamp | BEFORE | Timestamp |
| clientes | actualizar_timestamp | BEFORE | Timestamp |
| ventas | actualizar_timestamp | BEFORE | Timestamp |

---

## Momentos de Ejecución

### BEFORE Triggers:
- Se ejecutan **antes** de modificar datos
- Pueden **modificar** NEW
- Pueden **rechazar** la operación (RAISE EXCEPTION)
- Usados para **validaciones** y **cálculos**

### AFTER Triggers:
- Se ejecutan **después** de modificar datos
- No pueden modificar NEW
- Usados para **auditoría** y **propagación**

**Nota:** En este proyecto usamos principalmente BEFORE triggers.

---

## Triggers por Módulo

### Módulo AUTH:
- Timestamp en `usuarios`

### Módulo PRODUCTS:
- Timestamp en `productos`, `categorias`

### Módulo INVENTORY:
- Ninguno (actualización manual controlada)

### Módulo PURCHASES:
- Auto-número en `compras`, `ordenes_compra`
- Timestamp

### Módulo SALES:
- **Validación de stock** en `detalle_ventas` ⚠️ CRÍTICO
- Auto-número en `ventas`, `devoluciones_venta`
- Timestamp

### Módulo CUSTOMERS:
- **Cálculo de saldo** en `creditos_clientes` ⚠️ CRÍTICO
- Timestamp en `clientes`

### Módulo CASH:
- Ninguno (cálculos en función `cerrar_caja()`)

---

## Desactivar/Activar Triggers

### Desactivar temporalmente:

```sql
-- Desactivar un trigger específico
ALTER TABLE detalle_ventas DISABLE TRIGGER trigger_validar_stock_venta_insert;

-- Desactivar todos los triggers de una tabla
ALTER TABLE detalle_ventas DISABLE TRIGGER ALL;
```

### Reactivar:

```sql
ALTER TABLE detalle_ventas ENABLE TRIGGER trigger_validar_stock_venta_insert;
ALTER TABLE detalle_ventas ENABLE TRIGGER ALL;
```

**Uso:** Solo en migraciones masivas de datos. **NO en producción.**

---

## Depuración de Triggers

### Ver triggers de una tabla:

```sql
SELECT tgname AS trigger_name,
       tgtype,
       proname AS function_name
FROM pg_trigger t
JOIN pg_proc p ON t.tgfoid = p.oid
WHERE tgrelid = 'detalle_ventas'::regclass
AND NOT tgisinternal;
```

### Ver función asociada:

```sql
\df+ validar_stock_venta
```

---

## Performance de Triggers

### Consideraciones:

1. **BEFORE triggers** se ejecutan en cada fila → afectan INSERT masivos
2. **Validaciones complejas** pueden ralentizar operaciones
3. **Triggers anidados** (trigger llama función que modifica otra tabla con trigger)

### Optimizaciones aplicadas:

- ✅ Triggers solo se ejecutan cuando es necesario (WHEN clause)
- ✅ Índices en columnas usadas por funciones de triggers
- ✅ Validaciones simples (no subconsultas pesadas)

---

## Testing de Triggers

Ver: `docs/04-database/10-testing.md` (Sección NIVEL 5)

**Tests críticos:**

1. **Validación de stock** debe rechazar ventas sin stock
2. **Auto-numeración** debe generar números únicos
3. **Cálculo de saldo** debe actualizar estado correctamente
4. **Timestamps** deben actualizarse en cada UPDATE

---

## Buenas Prácticas

### Al crear triggers:

1. **Nomenclatura clara:** `trigger_[accion]_[tabla]`
2. **Documentación:** Comentar propósito y comportamiento
3. **Testing exhaustivo:** Validar casos edge
4. **WHEN clause:** Evitar ejecuciones innecesarias

### Al modificar triggers:

1. **Desactivar temporalmente** para migraciones
2. **No eliminar triggers activos** en producción
3. **Coordinar con equipo** antes de cambios

---

## Triggers Futuros (Propuestos)

### Auditoría automática:

```sql
-- Registrar todos los cambios en logs_auditoria
CREATE TRIGGER trigger_auditoria_productos
    AFTER INSERT OR UPDATE OR DELETE ON productos
    FOR EACH ROW
    EXECUTE FUNCTION registrar_auditoria();
```

**Status:** Pendiente de implementación

**Razón:** Actualmente se registra desde la API para tener control de IP y user_agent.

---

## Referencias

- **Código fuente:** `database/04_triggers/`
- **Funciones asociadas:** `database/03_functions/`
- **Testing:** `docs/04-database/10-testing.md`
- **Documentación PostgreSQL:** https://www.postgresql.org/docs/current/triggers.html
