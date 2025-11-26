# MÓDULO SALES - PUNTO DE VENTA

## Descripción General

Módulo que gestiona el punto de venta (POS) con soporte para efectivo y QR, facturación, descuentos y devoluciones.

---

## Tablas del Módulo

### 1. ventas

**Propósito:** Cabecera de cada venta realizada.

**Estructura:**

Ver archivo: [database/01_tables/16_ventas.sql](../../../database/01_tables/16_ventas.sql)

**Columnas Clave:**
- `numero_venta`: Auto-generado (formato: YYYYMMDD-0001)
- `tipo_venta`: contado o credito
- `tipo_documento`: factura, recibo o nota_venta
- `metodo_pago`: efectivo o qr
- `descuento_porcentaje`: Descuento general aplicado
- `iva`: IVA 13% (Bolivia) - solo si es factura

**Estados:**
- `completada`: Venta exitosa
- `anulada`: Venta cancelada
- `devuelta`: Venta con devolución total

---

### 2. detalle_ventas

**Propósito:** Items individuales de cada venta.

**Estructura:**

Ver archivo: [database/01_tables/17_detalle_ventas.sql](../../../database/01_tables/17_detalle_ventas.sql)

**Columnas Importantes:**
- `almacen_id`: De qué almacén salió el producto
- `precio_unitario`: Precio al momento de la venta (histórico)
- `descuento_monto`: Descuento por item
- `subtotal`: cantidad × precio_unitario - descuento

---

### 3. devoluciones_venta

**Propósito:** Registro de devoluciones procesadas.

**Estructura:**

Ver archivo: [database/01_tables/20_devoluciones_venta.sql](../../../database/01_tables/20_devoluciones_venta.sql)

**Tipos de Devolución:**
- `reembolso`: Se devuelve el dinero
- `cambio`: Se cambia por otro producto

**Reglas:**
- Solo mismo día de la venta
- Producto en buen estado
- Requiere justificación en campo `razon`

---

### 4. detalle_devoluciones_venta

**Propósito:** Qué productos específicos se devolvieron.

**Estructura:**

Ver archivo: [database/01_tables/21_detalle_devoluciones_venta.sql](../../../database/01_tables/21_detalle_devoluciones_venta.sql)

**Características:**
- Vincula con `detalle_venta_id` (qué item se devuelve)
- `cantidad_devuelta`: Puede ser parcial
- `almacen_retorno_id`: A qué almacén regresa

---

## Diagrama de Relaciones

```
┌─────────────┐     ┌─────────────┐
│  clientes   │     │  usuarios   │
└──────┬──────┘     └──────┬──────┘
       │                   │
       │ cliente_id        │ usuario_id
       │                   │
       ▼                   ▼
┌──────────────────────────────┐
│          ventas              │
│ (numero_venta, total, tipo)  │
└──────────────┬───────────────┘
               │
               │ venta_id
               ▼
┌──────────────────────────────┐
│      detalle_ventas          │
│ (producto_id, cantidad,      │
│  precio_unitario)            │
└──────────────┬───────────────┘
               │
               │ detalle_venta_id
               ▼
┌──────────────────────────────┐
│   detalle_devoluciones       │
└──────────────────────────────┘
               │
               ▼
┌──────────────────────────────┐
│    devoluciones_venta        │
└──────────────────────────────┘
```

---

## Reglas de Negocio

### Ventas:
1. `numero_venta` es único y auto-generado
2. Ventas a crédito solo para clientes con `es_frecuente = TRUE`
3. IVA 13% solo se aplica si `tipo_documento = 'factura'`
4. Descuentos pueden ser por item o por venta completa
5. No se pueden modificar ventas completadas (solo anular)

### Detalle de Ventas:
1. El stock se reduce automáticamente al crear el detalle
2. `precio_unitario` se guarda para histórico (no se actualiza si cambia el precio)
3. Cada item puede salir de diferente almacén
4. Trigger valida que hay stock suficiente antes de INSERT/UPDATE

### Devoluciones:
1. Solo mismo día (validar en API)
2. Producto debe estar en buen estado
3. Reembolso devuelve dinero, cambio genera nueva venta
4. El stock regresa al almacén especificado

---

## Operaciones Comunes

### Procesar Venta Completa (Función Transaccional):

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

```sql
-- Venta al contado con múltiples items
SELECT procesar_venta(
    p_cliente_id := 1,
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
            "descuento": 2.50
        },
        {
            "producto_id": 2,
            "almacen_id": 2,
            "cantidad": 10,
            "unidad_id": 11,
            "precio_unitario": 25.00,
            "descuento": 0
        }
    ]'::JSONB
);
```

**Qué hace automáticamente:**
1. Crea el registro en `ventas`
2. Calcula subtotal, descuentos y total
3. Inserta items en `detalle_ventas`
4. Reduce el stock en `inventario`
5. Registra movimientos en `movimientos_inventario`
6. Si es venta a crédito, crea registro en `creditos_clientes`
7. Todo en transacción atómica

---

### Consultar Ventas del Día:

```sql
-- Usar vista predefinida
SELECT * FROM vista_ventas_hoy
ORDER BY fecha_venta DESC;
```

---

### Ver Detalle de una Venta:

```sql
SELECT 
    v.numero_venta,
    v.fecha_venta,
    c.nombre_completo AS cliente,
    pr.nombre AS producto,
    dv.cantidad,
    um.abreviatura AS unidad,
    dv.precio_unitario,
    dv.descuento_monto,
    dv.subtotal
FROM ventas v
LEFT JOIN clientes c ON v.cliente_id = c.id
JOIN detalle_ventas dv ON v.id = dv.venta_id
JOIN productos pr ON dv.producto_id = pr.id
JOIN unidades_medida um ON dv.unidad_id = um.id
WHERE v.numero_venta = '20241215-0001';
```

---

### Procesar Devolución:

```sql
-- 1. Crear devolución
INSERT INTO devoluciones_venta (venta_id, tipo_devolucion, total_devuelto, razon, usuario_id)
VALUES (10, 'reembolso', 55.00, 'Producto defectuoso', 1)
RETURNING id;

-- 2. Registrar items devueltos
INSERT INTO detalle_devoluciones_venta (
    devolucion_venta_id, detalle_venta_id, cantidad_devuelta, monto_devuelto, almacen_retorno_id
) VALUES (1, 25, 1, 55.00, 2);

-- 3. Actualizar inventario (stock regresa)
UPDATE inventario
SET cantidad_actual = cantidad_actual + 1
WHERE producto_id = 1 AND almacen_id = 2;

-- 4. Registrar movimiento
INSERT INTO movimientos_inventario (producto_id, almacen_id, tipo_movimiento, cantidad, usuario_id)
VALUES (1, 2, 'DEVOLUCION_VENTA', 1, 1);
```

**Nota:** En producción, crear función transaccional para devoluciones.

---

## Funciones del Módulo

### procesar_venta()

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

**Parámetros:**
- `p_cliente_id`: ID del cliente (nullable para venta al público)
- `p_usuario_id`: Vendedor que procesa la venta
- `p_tipo_venta`: contado o credito
- `p_metodo_pago`: efectivo o qr
- `p_items`: Array JSON con productos y cantidades

**Retorna:** ID de la venta creada

**Validaciones:**
- Verifica stock suficiente antes de procesar
- Calcula automáticamente totales
- Si es crédito, verifica límite del cliente

---

### generar_numero_venta()

Ver archivo: [database/03_functions/02_validation_functions.sql](../../../database/03_functions/02_validation_functions.sql)

**Propósito:** Genera número correlativo único de venta.

**Formato:** YYYYMMDD-NNNN

**Ejemplo:** 20241215-0001

**Lógica:** Reinicia el contador cada día.

---

### generar_numero_devolucion()

**Propósito:** Genera número correlativo único de devolución.

**Formato:** DV-YYYYMMDD-NNNN

**Ejemplo:** DV-20241215-0001

---

## Triggers Automáticos

### Validación de Stock antes de Venta:

Ver archivo: [database/04_triggers/02_validation_triggers.sql](../../../database/04_triggers/02_validation_triggers.sql)

```sql
-- Se ejecuta antes de INSERT/UPDATE en detalle_ventas
-- Rechaza la venta si no hay stock suficiente
CREATE TRIGGER trigger_validar_stock_venta_insert
    BEFORE INSERT ON detalle_ventas
    FOR EACH ROW
    EXECUTE FUNCTION validar_stock_venta();

CREATE TRIGGER trigger_validar_stock_venta_update
    BEFORE UPDATE ON detalle_ventas
    FOR EACH ROW
    WHEN (OLD.cantidad IS DISTINCT FROM NEW.cantidad OR OLD.almacen_id IS DISTINCT FROM NEW.almacen_id)
    EXECUTE FUNCTION validar_stock_venta();
```

---

### Auto-numeración de Ventas:

```sql
CREATE TRIGGER trigger_generar_numero_venta
    BEFORE INSERT ON ventas
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_venta();
```

---

## Vistas del Módulo

### vista_ventas_hoy

Ver archivo: [database/05_views/03_vista_ventas_hoy.sql](../../../database/05_views/03_vista_ventas_hoy.sql)

**Uso:**
```sql
SELECT * FROM vista_ventas_hoy
WHERE estado = 'completada';
```

---

### vista_productos_mas_vendidos

Ver archivo: [database/05_views/04_vista_productos_mas_vendidos.sql](../../../database/05_views/04_vista_productos_mas_vendidos.sql)

**Uso:**
```sql
-- Top 10 productos más vendidos del mes
SELECT * FROM vista_productos_mas_vendidos
LIMIT 10;
```

---

### vista_utilidad_ventas

Ver archivo: [database/05_views/08_vista_utilidad_ventas.sql](../../../database/05_views/08_vista_utilidad_ventas.sql)

**Uso:**
```sql
-- Ventas más rentables del día
SELECT numero_venta, total_venta, utilidad_bruta, margen_porcentaje
FROM vista_utilidad_ventas
WHERE DATE(fecha_venta) = CURRENT_DATE
ORDER BY margen_porcentaje DESC;
```

---

## Índices Definidos

Ver archivo: [database/02_indexes/04_ventas_indexes.sql](../../../database/02_indexes/04_ventas_indexes.sql)

```sql
-- Número de venta único
CREATE UNIQUE INDEX idx_ventas_numero ON ventas(numero_venta);

-- Búsquedas comunes
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);
CREATE INDEX idx_ventas_tipo ON ventas(tipo_venta);

-- Detalle ventas
CREATE INDEX idx_detalle_ventas_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_ventas_producto ON detalle_ventas(producto_id);
CREATE INDEX idx_detalle_ventas_almacen ON detalle_ventas(almacen_id);
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Venta.php
class Venta extends Model
{
    protected $table = 'ventas';
    
    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }
    
    public function devoluciones()
    {
        return $this->hasMany(DevolucionVenta::class);
    }
    
    public function credito()
    {
        return $this->hasOne(CreditoCliente::class);
    }
}

// app/Models/DetalleVenta.php
class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
```

---

## Casos de Uso Prácticos

### Caso 1: Venta Rápida al Contado

**Escenario:** Cliente llega, compra 3 productos y paga en efectivo.

```sql
SELECT procesar_venta(
    p_cliente_id := NULL, -- Venta al público (sin cliente registrado)
    p_usuario_id := 2,
    p_tipo_venta := 'contado',
    p_metodo_pago := 'efectivo',
    p_items := '[
        {"producto_id":1, "almacen_id":2, "cantidad":2, "unidad_id":2, "precio_unitario":55.00, "descuento":0},
        {"producto_id":4, "almacen_id":2, "cantidad":1, "unidad_id":2, "precio_unitario":48.00, "descuento":0},
        {"producto_id":2, "almacen_id":2, "cantidad":5, "unidad_id":1, "precio_unitario":25.00, "descuento":10.00}
    ]'::JSONB
);
```

---

### Caso 2: Venta a Crédito (Cliente Frecuente)

**Escenario:** Cliente casero compra materiales para obra y pagará en 30 días.

```sql
-- 1. Verificar límite de crédito
SELECT 
    limite_credito,
    obtener_deuda_cliente(5) AS deuda_actual,
    (limite_credito - obtener_deuda_cliente(5)) AS credito_disponible
FROM clientes
WHERE id = 5;

-- 2. Procesar venta a crédito
SELECT procesar_venta(
    p_cliente_id := 5,
    p_usuario_id := 2,
    p_tipo_venta := 'credito',
    p_metodo_pago := 'efectivo', -- Se usará cuando pague
    p_items := '[
        {"producto_id":1, "almacen_id":1, "cantidad":50, "unidad_id":3, "precio_unitario":55.00, "descuento":137.50}
    ]'::JSONB
);

-- 3. Se crea automáticamente el registro en creditos_clientes
```

---

### Caso 3: Devolución por Producto Defectuoso

**Escenario:** Cliente devuelve producto comprado hace 2 horas.

```sql
-- Verificar que sea el mismo día
SELECT numero_venta, fecha_venta
FROM ventas
WHERE numero_venta = '20241215-0005';

-- Procesar devolución (implementar función transaccional en producción)
```

---

### Caso 4: Reporte de Ventas del Día

```sql
SELECT 
    COUNT(*) AS total_ventas,
    SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END) AS total_efectivo,
    SUM(CASE WHEN metodo_pago = 'qr' THEN total ELSE 0 END) AS total_qr,
    SUM(total) AS total_dia
FROM ventas
WHERE DATE(fecha_venta) = CURRENT_DATE
AND estado = 'completada';
```

---

## Testing

### Casos de Prueba:

1. **Venta sin stock** → Trigger debe rechazar
2. **Venta a crédito con cliente no frecuente** → Debe rechazar (lógica API)
3. **Venta con cliente excediendo límite** → Debe rechazar
4. **Número de venta único** → No puede haber duplicados por día
5. **Cálculo de totales correcto** → Verificar subtotal + IVA - descuento
6. **Stock actualizado correctamente** → Verificar reducción en inventario
7. **Devolución aumenta stock** → Verificar incremento correcto

---

**Siguiente:** [Módulo Clientes](06-clientes.md)
