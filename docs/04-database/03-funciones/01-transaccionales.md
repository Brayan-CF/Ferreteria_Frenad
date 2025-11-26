# FUNCIONES DE BASE DE DATOS

## Descripción General

Funciones almacenadas que encapsulan lógica de negocio compleja en transacciones atómicas.

---

## Categorías de Funciones

### 1. Funciones de Timestamp
### 2. Funciones de Validación
### 3. Funciones Transaccionales
### 4. Funciones de Consulta

---

## 1. FUNCIONES DE TIMESTAMP

**Ubicación:** `database/03_functions/01_timestamp_functions.sql`

### actualizar_timestamp()

**Propósito:** Actualiza automáticamente el campo `actualizado_en` en cada UPDATE.

**Uso:** Se aplica mediante triggers en tablas con auditoría.

**Retorno:** TRIGGER

**Aplicado en:**
- usuarios
- productos
- categorias
- clientes
- ventas
- compras

---

## 2. FUNCIONES DE VALIDACIÓN

**Ubicación:** `database/03_functions/02_validation_functions.sql`

### calcular_saldo_credito()

**Propósito:** Calcula automáticamente saldo y estado de créditos.

**Lógica:**
```sql
saldo_pendiente = monto_total - monto_pagado

-- Estado automático:
IF saldo = 0 → 'pagado_completo'
IF monto_pagado > 0 AND saldo > 0 → 'pagado_parcial'
IF fecha_vencimiento < HOY AND saldo > 0 → 'vencido'
ELSE → 'pendiente'
```

**Usado en:** Trigger en `creditos_clientes`

---

### generar_numero_venta()

**Propósito:** Genera número correlativo único de venta.

**Formato:** `YYYYMMDD-NNNN`

**Ejemplo:** `20241215-0001`

**Lógica:** Reinicia contador cada día.

---

### generar_numero_compra()

**Formato:** `C-YYYYMMDD-NNNN`

**Ejemplo:** `C-20241215-0001`

---

### generar_numero_orden()

**Formato:** `OC-YYYYMMDD-NNNN`

**Ejemplo:** `OC-20241215-0001`

---

### generar_numero_devolucion()

**Formato:** `DV-YYYYMMDD-NNNN`

**Ejemplo:** `DV-20241215-0001`

---

### validar_stock_venta()

**Propósito:** Valida stock suficiente antes de vender.

**Parámetros:** Recibe NEW (registro de detalle_ventas)

**Validación:**
```sql
IF stock_disponible < cantidad_solicitada THEN
    RAISE EXCEPTION 'Stock insuficiente'
END IF
```

**Usado en:** Trigger BEFORE INSERT/UPDATE en `detalle_ventas`

---

## 3. FUNCIONES TRANSACCIONALES

**Ubicación:** `database/03_functions/03_transactional_functions.sql`

### procesar_venta()

**Propósito:** Procesa venta completa de forma atómica.

**Parámetros:**
- `p_cliente_id` (BIGINT)
- `p_usuario_id` (BIGINT)
- `p_tipo_venta` (VARCHAR)
- `p_metodo_pago` (VARCHAR)
- `p_items` (JSONB array)

**Retorna:** BIGINT (ID de venta creada)

**Operaciones:**
1. Crea registro en `ventas`
2. Inserta items en `detalle_ventas`
3. Reduce stock en `inventario`
4. Registra movimientos en `movimientos_inventario`
5. Si es crédito, crea registro en `creditos_clientes`

**Ejemplo de uso:** Ver `docs/04-database/02-modulos/05-ventas.md`

**Código completo:** `database/03_functions/03_transactional_functions.sql` (líneas 1-80)

---

### procesar_compra()

**Propósito:** Procesa compra completa con actualización de inventario.

**Parámetros:**
- `p_proveedor_id` (BIGINT)
- `p_usuario_id` (BIGINT)
- `p_orden_compra_id` (BIGINT, nullable)
- `p_numero_factura` (VARCHAR)
- `p_items` (JSONB array)

**Retorna:** BIGINT (ID de compra)

**Operaciones:**
1. Crea registro en `compras`
2. Inserta items en `detalle_compras`
3. Aumenta stock en `inventario`
4. Registra movimientos tipo `ENTRADA_COMPRA`
5. Actualiza estado de orden de compra (si existe)

**Ejemplo:** Ver `docs/04-database/02-modulos/04-compras.md`

---

### transferir_producto()

**Propósito:** Transfiere producto entre almacenes.

**Parámetros:**
- `p_producto_id` (BIGINT)
- `p_almacen_origen_id` (BIGINT)
- `p_almacen_destino_id` (BIGINT)
- `p_cantidad` (NUMERIC)
- `p_usuario_id` (BIGINT)
- `p_razon` (TEXT, opcional)

**Retorna:** BOOLEAN

**Validaciones:**
- Verifica stock suficiente en origen
- No permite transferir a mismo almacén

**Ejemplo:**
```sql
SELECT transferir_producto(5, 1, 2, 20, 1, 'Reposición mostrador');
```

---

### registrar_pago_credito()

**Propósito:** Registra pago parcial o total de crédito.

**Parámetros:**
- `p_credito_id` (BIGINT)
- `p_monto_pago` (NUMERIC)
- `p_metodo_pago` (VARCHAR)
- `p_usuario_id` (BIGINT)
- `p_notas` (TEXT, opcional)

**Retorna:** BOOLEAN

**Validaciones:**
- Monto no puede exceder saldo pendiente
- Actualiza automáticamente estado del crédito

---

### ajustar_inventario()

**Propósito:** Ajustes manuales de inventario con justificación obligatoria.

**Parámetros:**
- `p_producto_id` (BIGINT)
- `p_almacen_id` (BIGINT)
- `p_nueva_cantidad` (NUMERIC)
- `p_razon` (TEXT, **obligatorio**)
- `p_usuario_id` (BIGINT)

**Retorna:** BOOLEAN

**Tipo de movimiento:**
- `AJUSTE_POSITIVO` si aumenta
- `AJUSTE_NEGATIVO` si disminuye

**Ejemplo:**
```sql
SELECT ajustar_inventario(
    3, 1, 12,
    'Conteo físico: encontradas 12 unidades',
    1
);
```

---

### cerrar_caja()

**Propósito:** Cierra arqueo de caja calculando diferencias.

**Parámetros:**
- `p_arqueo_id` (BIGINT)
- `p_monto_final` (NUMERIC)
- `p_usuario_id` (BIGINT)
- `p_notas` (TEXT, opcional)

**Retorna:** BOOLEAN

**Cálculos automáticos:**
```sql
total_esperado = monto_inicial + ventas_efectivo - gastos
diferencia = monto_final - total_esperado
```

**Ejemplo:** Ver `docs/04-database/02-modulos/07-caja.md`

---

## 4. FUNCIONES DE CONSULTA

**Ubicación:** `database/03_functions/04_query_functions.sql`

### obtener_stock_total_producto()

**Propósito:** Suma stock de todos los almacenes.

**Parámetros:** `p_producto_id` (BIGINT)

**Retorna:** NUMERIC

**Uso:**
```sql
SELECT obtener_stock_total_producto(5);
-- Resultado: 125.00
```

---

### tiene_stock_suficiente()

**Propósito:** Valida disponibilidad antes de vender.

**Parámetros:**
- `p_producto_id` (BIGINT)
- `p_almacen_id` (BIGINT)
- `p_cantidad_requerida` (NUMERIC)

**Retorna:** BOOLEAN

**Uso:**
```sql
SELECT tiene_stock_suficiente(5, 2, 10);
-- Resultado: TRUE o FALSE
```

---

### obtener_deuda_cliente()

**Propósito:** Calcula deuda total actual de un cliente.

**Parámetros:** `p_cliente_id` (BIGINT)

**Retorna:** NUMERIC

**Lógica:** Suma todos los `saldo_pendiente` de créditos activos.

---

### calcular_utilidad_periodo()

**Propósito:** Calcula utilidad bruta y margen de un período.

**Parámetros:**
- `p_fecha_inicio` (DATE)
- `p_fecha_fin` (DATE)

**Retorna:** TABLE con:
- `total_ventas` (NUMERIC)
- `costo_total` (NUMERIC)
- `utilidad_bruta` (NUMERIC)
- `margen_porcentaje` (NUMERIC)

**Uso:**
```sql
SELECT * FROM calcular_utilidad_periodo('2024-12-01', '2024-12-31');
```

---

### productos_proximos_vencer()

**Propósito:** Lista productos con vencimiento próximo.

**Parámetros:** `p_dias` (INT, default 30)

**Retorna:** TABLE con productos que vencen en X días.

**Uso:**
```sql
-- Productos que vencen en 15 días
SELECT * FROM productos_proximos_vencer(15);
```

---

## Diagrama de Dependencias

```
TIMESTAMP FUNCTIONS
    ↓
[Triggers automáticos]
    
VALIDATION FUNCTIONS
    ↓
[Triggers BEFORE INSERT/UPDATE]

TRANSACTIONAL FUNCTIONS
    ↓
[Llamadas desde API]

QUERY FUNCTIONS
    ↓
[Consultas directas / Reportes]
```

---

## Buenas Prácticas

### Al usar funciones transaccionales:

1. **Siempre en transacciones:** BEGIN ... COMMIT/ROLLBACK
2. **Validar parámetros:** Verificar que existan antes de llamar
3. **Manejo de errores:** Usar bloques TRY-CATCH en la API
4. **Logging:** Registrar llamadas críticas en auditoría

### Al crear nuevas funciones:

1. **Nomenclatura:** Verbos en infinitivo (`procesar_`, `calcular_`, `obtener_`)
2. **Documentación:** Agregar COMMENT ON FUNCTION
3. **Atomicidad:** Una función = una responsabilidad
4. **Testing:** Crear casos de prueba antes de producción

---

## Testing de Funciones

Ver guía completa en: `docs/04-database/10-testing.md` (Sección NIVEL 4)

**Tests mínimos requeridos:**
- Caso exitoso
- Caso con parámetros inválidos
- Caso con datos faltantes
- Caso de rollback por error

---

## Referencias

- **Código fuente:** `database/03_functions/`
- **Uso en módulos:** `docs/04-database/02-modulos/`
- **Testing:** `docs/04-database/10-testing.md`
- **Documentación PostgreSQL:** https://www.postgresql.org/docs/current/xfunc.html
