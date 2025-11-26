# GUÍA DE TESTING - BASE DE DATOS

## Descripción General

Esta guía proporciona casos de prueba exhaustivos para validar el correcto funcionamiento de la base de datos.

---

## Configuración del Entorno de Testing

### Crear Base de Datos de Testing:

```bash
# Crear BD de testing
sudo -u postgres createdb ferreteria_frenad_test

# Ejecutar migraciones
cd database
DB_NAME="ferreteria_frenad_test" ./migrate.sh
```

---

## NIVEL 1: Tests de Integridad

### Test 1.1: Verificar Creación de Tablas

```sql
-- Debe retornar 25
SELECT COUNT(*) 
FROM information_schema.tables 
WHERE table_schema = 'public' AND table_type = 'BASE TABLE';
```

**Resultado Esperado:** 25

---

### Test 1.2: Verificar Foreign Keys

```sql
-- Debe retornar ~40+ constraints
SELECT COUNT(*)
FROM information_schema.table_constraints
WHERE constraint_type = 'FOREIGN KEY'
AND constraint_schema = 'public';
```

**Resultado Esperado:** > 40

---

### Test 1.3: Verificar CHECK Constraints

```sql
-- Validar que existen constraints de validación
SELECT table_name, constraint_name
FROM information_schema.table_constraints
WHERE constraint_type = 'CHECK'
AND constraint_schema = 'public'
ORDER BY table_name;
```

**Resultado Esperado:** Múltiples constraints (precio >= 0, cantidad > 0, etc.)

---

### Test 1.4: Verificar Índices

```sql
-- Debe retornar 43+
SELECT COUNT(*)
FROM pg_indexes
WHERE schemaname = 'public';
```

**Resultado Esperado:** 43+

---

## NIVEL 2: Tests de Funcionalidad Básica

### Test 2.1: Insertar Usuario

```sql
BEGIN;

INSERT INTO usuarios (nombre, email, password_hash, activo)
VALUES ('Usuario Test', 'test@example.com', 'hash_test', TRUE)
RETURNING id;

-- Verificar
SELECT COUNT(*) FROM usuarios WHERE email = 'test@example.com';
-- Esperado: 1

ROLLBACK;
```

---

### Test 2.2: Asignar Rol a Usuario

```sql
BEGIN;

-- Crear usuario
INSERT INTO usuarios (nombre, email, password_hash, activo)
VALUES ('Usuario Test', 'test@example.com', 'hash_test', TRUE)
RETURNING id; -- Supongamos: 10

-- Asignar rol Vendedor (id = 2)
INSERT INTO usuario_roles (usuario_id, rol_id, asignado_por)
VALUES (10, 2, 1);

-- Verificar
SELECT u.nombre, r.nombre AS rol
FROM usuarios u
JOIN usuario_roles ur ON u.id = ur.usuario_id
JOIN roles r ON ur.rol_id = r.id
WHERE u.id = 10;

-- Esperado: Usuario Test | Vendedor

ROLLBACK;
```

---

### Test 2.3: Crear Producto

```sql
BEGIN;

INSERT INTO productos (
    sku, nombre, categoria_id, unidad_base_id,
    precio_compra, precio_venta, creado_por
) VALUES (
    'TEST-001', 'Producto Test', 1, 2,
    10.00, 15.00, 1
) RETURNING id;

-- Verificar
SELECT * FROM productos WHERE sku = 'TEST-001';

ROLLBACK;
```

---

### Test 2.4: Actualizar Inventario

```sql
BEGIN;

-- Insertar stock
INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES (1, 1, 100, 10);

-- Actualizar
UPDATE inventario
SET cantidad_actual = 150
WHERE producto_id = 1 AND almacen_id = 1;

-- Verificar
SELECT cantidad_actual FROM inventario
WHERE producto_id = 1 AND almacen_id = 1;
-- Esperado: 150

ROLLBACK;
```

---

## NIVEL 3: Tests de Validaciones

### Test 3.1: Rechazar Precio Negativo

```sql
BEGIN;

-- Debe fallar (CHECK constraint)
INSERT INTO productos (
    sku, nombre, unidad_base_id,
    precio_compra, precio_venta, creado_por
) VALUES (
    'TEST-NEG', 'Producto Negativo', 2,
    -10.00, 15.00, 1
);

-- Esperado: ERROR - CHECK constraint

ROLLBACK;
```

---

### Test 3.2: Rechazar Stock Negativo

```sql
BEGIN;

INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES (1, 1, 100, 10);

-- Debe fallar
UPDATE inventario
SET cantidad_actual = -5
WHERE producto_id = 1 AND almacen_id = 1;

-- Esperado: ERROR - CHECK constraint

ROLLBACK;
```

---

### Test 3.3: Rechazar Email Duplicado

```sql
BEGIN;

-- Ya existe admin@frenad.com
INSERT INTO usuarios (nombre, email, password_hash, activo)
VALUES ('Usuario Duplicado', 'admin@frenad.com', 'hash', TRUE);

-- Esperado: ERROR - UNIQUE constraint

ROLLBACK;
```

---

### Test 3.4: Validar Venta Sin Stock

```sql
BEGIN;

-- Asegurar que no hay stock
UPDATE inventario
SET cantidad_actual = 0
WHERE producto_id = 1 AND almacen_id = 2;

-- Intentar vender (debe fallar por trigger)
INSERT INTO detalle_ventas (
    venta_id, producto_id, almacen_id, cantidad,
    unidad_id, precio_unitario, subtotal, creado_por
) VALUES (
    1, 1, 2, 5,
    2, 55.00, 275.00, 1
);

-- Esperado: ERROR - Stock insuficiente

ROLLBACK;
```

---

## NIVEL 4: Tests de Funciones

### Test 4.1: obtener_stock_total_producto()

```sql
BEGIN;

-- Configurar datos de prueba
INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES 
    (1, 1, 50, 10),
    (1, 2, 30, 5);

-- Probar función
SELECT obtener_stock_total_producto(1);
-- Esperado: 80.00

ROLLBACK;
```

---

### Test 4.2: tiene_stock_suficiente()

```sql
BEGIN;

INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES (1, 2, 25, 5);

-- Caso 1: Stock suficiente
SELECT tiene_stock_suficiente(1, 2, 10);
-- Esperado: TRUE

-- Caso 2: Stock insuficiente
SELECT tiene_stock_suficiente(1, 2, 30);
-- Esperado: FALSE

ROLLBACK;
```

---

### Test 4.3: obtener_deuda_cliente()

```sql
BEGIN;

-- Crear crédito de prueba
INSERT INTO creditos_clientes (
    cliente_id, venta_id, monto_total, monto_pagado,
    saldo_pendiente, fecha_vencimiento
) VALUES (
    1, 1, 1000.00, 300.00, 700.00, CURRENT_DATE + INTERVAL '30 days'
);

-- Probar función
SELECT obtener_deuda_cliente(1);
-- Esperado: 700.00

ROLLBACK;
```

---

### Test 4.4: transferir_producto()

```sql
BEGIN;

-- Configurar inventario inicial
INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES 
    (1, 1, 100, 10), -- Bodega
    (1, 2, 20, 5);   -- Mostrador

-- Transferir 30 unidades de Bodega a Mostrador
SELECT transferir_producto(
    p_producto_id := 1,
    p_almacen_origen_id := 1,
    p_almacen_destino_id := 2,
    p_cantidad := 30,
    p_usuario_id := 1,
    p_razon := 'Test de transferencia'
);

-- Verificar stock en Bodega
SELECT cantidad_actual FROM inventario
WHERE producto_id = 1 AND almacen_id = 1;
-- Esperado: 70

-- Verificar stock en Mostrador
SELECT cantidad_actual FROM inventario
WHERE producto_id = 1 AND almacen_id = 2;
-- Esperado: 50

-- Verificar movimiento registrado
SELECT COUNT(*) FROM movimientos_inventario
WHERE producto_id = 1 AND tipo_movimiento = 'TRANSFERENCIA';
-- Esperado: 1

ROLLBACK;
```

---

## NIVEL 5: Tests de Triggers

### Test 5.1: Trigger de Timestamps

```sql
BEGIN;

INSERT INTO productos (
    sku, nombre, unidad_base_id,
    precio_compra, precio_venta, creado_por
) VALUES (
    'TEST-TS', 'Test Timestamps', 2,
    10.00, 15.00, 1
) RETURNING id, creado_en, actualizado_en;

-- Verificar que timestamps se crearon
-- creado_en y actualizado_en deben tener valores

-- Actualizar
UPDATE productos
SET precio_venta = 20.00
WHERE sku = 'TEST-TS'
RETURNING actualizado_en;

-- actualizado_en debe ser más reciente que creado_en

ROLLBACK;
```

---

### Test 5.2: Trigger de Auto-numeración

```sql
BEGIN;

-- Crear venta sin especificar numero_venta
INSERT INTO ventas (
    cliente_id, usuario_id, tipo_venta, metodo_pago,
    subtotal, total, creado_por
) VALUES (
    NULL, 1, 'contado', 'efectivo',
    100.00, 100.00, 1
) RETURNING numero_venta;

-- Verificar que se generó automáticamente
-- Formato esperado: YYYYMMDD-0001

ROLLBACK;
```

---

### Test 5.3: Trigger de Cálculo de Saldo

```sql
BEGIN;

-- Crear crédito
INSERT INTO creditos_clientes (
    cliente_id, venta_id, monto_total, monto_pagado
) VALUES (
    1, 1, 1000.00, 300.00
) RETURNING saldo_pendiente, estado;

-- Verificar que trigger calculó correctamente
-- saldo_pendiente: 700.00
-- estado: 'pagado_parcial'

-- Actualizar pago
UPDATE creditos_clientes
SET monto_pagado = 1000.00
WHERE cliente_id = 1 AND venta_id = 1
RETURNING saldo_pendiente, estado;

-- saldo_pendiente: 0.00
-- estado: 'pagado_completo'

ROLLBACK;
```

---

## NIVEL 6: Tests de Transacciones Complejas

### Test 6.1: procesar_venta()

```sql
BEGIN;

-- Configurar inventario
INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo)
VALUES (1, 2, 50, 10);

-- Procesar venta
SELECT procesar_venta(
    p_cliente_id := NULL,
    p_usuario_id := 1,
    p_tipo_venta := 'contado',
    p_metodo_pago := 'efectivo',
    p_items := '[
        {
            "producto_id": 1,
            "almacen_id": 2,
            "cantidad": 5,
            "unidad_id": 2,
            "precio_unitario": 55.00,
            "descuento": 0
        }
    ]'::JSONB
) AS venta_id;

-- Verificar venta creada
SELECT COUNT(*) FROM ventas WHERE creado_por = 1;
-- Esperado: 1

-- Verificar detalle
SELECT COUNT(*) FROM detalle_ventas WHERE producto_id = 1;
-- Esperado: 1

-- Verificar stock reducido
SELECT cantidad_actual FROM inventario
WHERE producto_id = 1 AND almacen_id = 2;
-- Esperado: 45

-- Verificar movimiento registrado
SELECT COUNT(*) FROM movimientos_inventario
WHERE producto_id = 1 AND tipo_movimiento = 'SALIDA_VENTA';
-- Esperado: 1

ROLLBACK;
```

---

### Test 6.2: procesar_compra()

```sql
BEGIN;

-- Procesar compra
SELECT procesar_compra(
    p_proveedor_id := 1,
    p_usuario_id := 1,
    p_orden_compra_id := NULL,
    p_numero_factura := 'TEST-001',
    p_items := '[
        {
            "producto_id": 1,
            "cantidad": 100,
            "unidad_id": 3,
            "precio_unitario": 45.00,
            "almacen_destino_id": 1
        }
    ]'::JSONB
) AS compra_id;

-- Verificar compra creada
SELECT COUNT(*) FROM compras WHERE numero_factura_proveedor = 'TEST-001';
-- Esperado: 1

-- Verificar stock aumentado
SELECT cantidad_actual FROM inventario
WHERE producto_id = 1 AND almacen_id = 1;
-- Esperado: valor anterior + 100

-- Verificar movimiento
SELECT COUNT(*) FROM movimientos_inventario
WHERE producto_id = 1 AND tipo_movimiento = 'ENTRADA_COMPRA';
-- Esperado: 1

ROLLBACK;
```

---

### Test 6.3: registrar_pago_credito()

```sql
BEGIN;

-- Crear crédito
INSERT INTO creditos_clientes (
    cliente_id, venta_id, monto_total, monto_pagado,
    saldo_pendiente, fecha_vencimiento
) VALUES (
    1, 1, 1000.00, 0, 1000.00, CURRENT_DATE + INTERVAL '30 days'
) RETURNING id; -- Supongamos: 10

-- Registrar pago
SELECT registrar_pago_credito(
    p_credito_id := 10,
    p_monto_pago := 400.00,
    p_metodo_pago := 'efectivo',
    p_usuario_id := 1,
    p_notas := 'Pago parcial test'
);

-- Verificar crédito actualizado
SELECT monto_pagado, saldo_pendiente, estado
FROM creditos_clientes
WHERE id = 10;
-- Esperado: monto_pagado = 400.00, saldo_pendiente = 600.00, estado = 'pagado_parcial'

-- Verificar pago registrado
SELECT COUNT(*) FROM pagos_credito WHERE credito_cliente_id = 10;
-- Esperado: 1

ROLLBACK;
```

---

## NIVEL 7: Tests de Performance

### Test 7.1: Consulta de Vistas

```sql
-- Tiempo de ejecución < 100ms
EXPLAIN ANALYZE SELECT * FROM vista_stock_actual;
```

---

### Test 7.2: Búsqueda Full-Text

```sql
-- Tiempo de ejecución < 50ms
EXPLAIN ANALYZE 
SELECT * FROM productos
WHERE to_tsvector('spanish', nombre) @@ to_tsquery('spanish', 'cemento')
LIMIT 10;
```

---

## NIVEL 8: Tests de Consistencia de Datos

### Test 8.1: Kardex vs Inventario

```sql
-- El stock actual debe coincidir con la suma de movimientos
SELECT 
    i.producto_id,
    i.almacen_id,
    i.cantidad_actual AS stock_actual,
    COALESCE(SUM(
        CASE 
            WHEN mi.tipo_movimiento IN ('ENTRADA_COMPRA', 'AJUSTE_POSITIVO', 'DEVOLUCION_VENTA') THEN mi.cantidad
            WHEN mi.tipo_movimiento IN ('SALIDA_VENTA', 'AJUSTE_NEGATIVO') THEN -mi.cantidad
            WHEN mi.tipo_movimiento = 'TRANSFERENCIA' AND mi.almacen_id = i.almacen_id THEN -mi.cantidad
            WHEN mi.tipo_movimiento = 'TRANSFERENCIA' AND mi.almacen_destino_id = i.almacen_id THEN mi.cantidad
            ELSE 0
        END
    ), 0) AS stock_calculado
FROM inventario i
LEFT JOIN movimientos_inventario mi ON i.producto_id = mi.producto_id 
    AND (mi.almacen_id = i.almacen_id OR mi.almacen_destino_id = i.almacen_id)
GROUP BY i.producto_id, i.almacen_id, i.cantidad_actual
HAVING i.cantidad_actual != COALESCE(SUM(
    CASE 
        WHEN mi.tipo_movimiento IN ('ENTRADA_COMPRA', 'AJUSTE_POSITIVO', 'DEVOLUCION_VENTA') THEN mi.cantidad
        WHEN mi.tipo_movimiento IN ('SALIDA_VENTA', 'AJUSTE_NEGATIVO') THEN -mi.cantidad
        WHEN mi.tipo_movimiento = 'TRANSFERENCIA' AND mi.almacen_id = i.almacen_id THEN -mi.cantidad
        WHEN mi.tipo_movimiento = 'TRANSFERENCIA' AND mi.almacen_destino_id = i.almacen_id THEN mi.cantidad
        ELSE 0
    END
), 0);

-- Resultado esperado: 0 filas (sin inconsistencias)
```

---

## Script de Testing Automatizado

### Archivo: `database/test.sh`

```bash
#!/bin/bash

DB_NAME="ferreteria_frenad_test"
DB_USER="postgres"

echo "Ejecutando tests de base de datos..."

# Test 1: Conteo de tablas
TABLES=$(psql -U $DB_USER -d $DB_NAME -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';")
if [ "$TABLES" -eq 25 ]; then
    echo "✅ Test 1 PASSED: 25 tablas creadas"
else
    echo "❌ Test 1 FAILED: Esperadas 25 tablas, encontradas $TABLES"
fi

# Test 2: Conteo de vistas
VIEWS=$(psql -U $DB_USER -d $DB_NAME -t -c "SELECT COUNT(*) FROM information_schema.views WHERE table_schema = 'public';")
if [ "$VIEWS" -eq 8 ]; then
    echo "✅ Test 2 PASSED: 8 vistas creadas"
else
    echo "❌ Test 2 FAILED: Esperadas 8 vistas, encontradas $VIEWS"
fi

# Agregar más tests...

echo "Tests completados"
```

---

## Checklist de Testing Completo

- [ ] Todas las tablas se crean correctamente
- [ ] Foreign keys funcionan (ON DELETE, ON UPDATE)
- [ ] CHECK constraints validan datos
- [ ] Índices mejoran performance
- [ ] Funciones retornan valores correctos
- [ ] Triggers se ejecutan automáticamente
- [ ] Vistas muestran datos correctos
- [ ] Transacciones complejas son atómicas
- [ ] No hay inconsistencias entre kardex e inventario
- [ ] Performance es aceptable (< 100ms queries comunes)

---

**Testing Completado**

**Documentación de Base de Datos Finalizada**
