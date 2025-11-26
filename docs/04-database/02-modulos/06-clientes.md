# MÓDULO CUSTOMERS - GESTIÓN DE CLIENTES Y CRÉDITOS

## Descripción General

Módulo que administra la base de datos de clientes, control de ventas a crédito y seguimiento de pagos.

---

## Tablas del Módulo

### 1. clientes

**Propósito:** Base de datos de clientes, especialmente frecuentes.

**Estructura:**

Ver archivo: [database/01_tables/15_clientes.sql](../../../database/01_tables/15_clientes.sql)

**Columnas Clave:**
- `es_frecuente`: Cliente casero con beneficios especiales
- `limite_credito`: Monto máximo que puede deber
- `nit`: Opcional, para facturación
- `activo`: Soft delete

**Características:**
- Solo clientes frecuentes pueden comprar a crédito
- NIT es opcional (solo si necesita factura)
- Se mantiene histórico (no se eliminan registros)

---

### 2. creditos_clientes

**Propósito:** Control de deuda por venta a crédito.

**Estructura:**

Ver archivo: [database/01_tables/18_creditos_clientes.sql](../../../database/01_tables/18_creditos_clientes.sql)

**Columnas Importantes:**
- `monto_total`: Total de la venta a crédito
- `monto_pagado`: Suma de todos los abonos
- `saldo_pendiente`: Calculado automáticamente (monto_total - monto_pagado)
- `fecha_vencimiento`: Típicamente 30 días después de la venta
- `estado`: pendiente, pagado_parcial, pagado_completo, vencido

**Estados:**
- `pendiente`: Sin pagos realizados
- `pagado_parcial`: Tiene abonos pero aún debe
- `pagado_completo`: Deuda saldada
- `vencido`: Fecha de vencimiento pasada y saldo > 0

---

### 3. pagos_credito

**Propósito:** Registro de cada abono que hace el cliente.

**Estructura:**

Ver archivo: [database/01_tables/19_pagos_credito.sql](../../../database/01_tables/19_pagos_credito.sql)

**Características:**
- Cada pago reduce el `saldo_pendiente` del crédito
- Trigger actualiza automáticamente el crédito
- Método de pago: efectivo o qr

---

## Diagrama de Relaciones

```
┌─────────────┐
│  clientes   │
│ (nombre,    │
│  limite_    │
│  credito)   │
└──────┬──────┘
       │
       ├──────────────────┐
       │                  │
       │ cliente_id       │ cliente_id
       ▼                  ▼
┌──────────────┐   ┌──────────────┐
│   ventas     │   │  creditos_   │
│              │───│  clientes    │
│              │   │ (monto_total,│
│              │   │  saldo_pend.)│
└──────────────┘   └──────┬───────┘
                          │
                          │ credito_cliente_id
                          ▼
                   ┌──────────────┐
                   │   pagos_     │
                   │   credito    │
                   │ (monto_pago) │
                   └──────────────┘
```

---

## Reglas de Negocio

### Clientes:
1. Solo clientes con `es_frecuente = TRUE` pueden comprar a crédito
2. El `limite_credito` es el monto máximo que pueden deber simultáneamente
3. El NIT es obligatorio solo si necesitan factura
4. Los clientes inactivos no pueden realizar nuevas compras

### Créditos:
1. Se crea automáticamente al procesar una venta a crédito
2. `saldo_pendiente` se calcula automáticamente con trigger
3. El `estado` se actualiza automáticamente según pagos y fecha
4. Un cliente puede tener múltiples créditos activos (sumando al límite)

### Pagos:
1. Cada pago reduce el saldo pendiente
2. No se pueden hacer pagos mayores al saldo
3. Los pagos son inmutables (no se editan ni eliminan)
4. Al llegar a saldo 0, el estado cambia a `pagado_completo`

---

## Operaciones Comunes

### Registrar Pago de Crédito (Función Transaccional):

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

```sql
-- Cliente abona Bs. 500 a su deuda
SELECT registrar_pago_credito(
    p_credito_id := 5,
    p_monto_pago := 500.00,
    p_metodo_pago := 'efectivo',
    p_usuario_id := 1,
    p_notas := 'Abono parcial - quedan Bs. 1,200'
);
```

**Qué hace automáticamente:**
1. Valida que el monto no exceda el saldo pendiente
2. Registra el pago en `pagos_credito`
3. Actualiza `monto_pagado` y `saldo_pendiente` en `creditos_clientes`
4. Actualiza el `estado` del crédito automáticamente (trigger)
5. Todo en transacción atómica

---

### Consultar Deuda Total de un Cliente:

```sql
-- Usar función predefinida
SELECT obtener_deuda_cliente(5);
-- Resultado: 2750.00 (suma de todos los créditos pendientes)
```

---

### Ver Clientes con Deuda:

```sql
-- Usar vista predefinida
SELECT * FROM vista_clientes_deuda
WHERE estado_deuda = 'VENCIDO'
ORDER BY saldo_pendiente DESC;
```

---

### Ver Historial de Pagos de un Cliente:

```sql
SELECT 
    c.nombre_completo,
    v.numero_venta,
    cc.monto_total,
    cc.monto_pagado,
    cc.saldo_pendiente,
    pc.fecha_pago,
    pc.monto_pago,
    pc.metodo_pago,
    u.nombre AS recibido_por
FROM pagos_credito pc
JOIN creditos_clientes cc ON pc.credito_cliente_id = cc.id
JOIN clientes c ON cc.cliente_id = c.id
JOIN ventas v ON cc.venta_id = v.id
JOIN usuarios u ON pc.usuario_id = u.id
WHERE c.id = 5
ORDER BY pc.fecha_pago DESC;
```

---

### Verificar si Cliente Puede Comprar a Crédito:

```sql
SELECT 
    c.nombre_completo,
    c.es_frecuente,
    c.limite_credito,
    obtener_deuda_cliente(c.id) AS deuda_actual,
    (c.limite_credito - obtener_deuda_cliente(c.id)) AS credito_disponible,
    CASE 
        WHEN NOT c.es_frecuente THEN 'NO - No es cliente frecuente'
        WHEN NOT c.activo THEN 'NO - Cliente inactivo'
        WHEN (c.limite_credito - obtener_deuda_cliente(c.id)) <= 0 THEN 'NO - Límite excedido'
        ELSE 'SÍ - Puede comprar a crédito'
    END AS puede_comprar_credito
FROM clientes c
WHERE c.id = 5;
```

---

## Funciones del Módulo

### registrar_pago_credito()

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

**Parámetros:**
- `p_credito_id`: ID del crédito
- `p_monto_pago`: Monto del abono
- `p_metodo_pago`: efectivo o qr
- `p_usuario_id`: Usuario que recibe el pago
- `p_notas`: Notas adicionales (opcional)

**Validaciones:**
- Verifica que el crédito exista
- Valida que el monto no exceda el saldo pendiente
- Actualiza automáticamente el estado

---

### obtener_deuda_cliente()

Ver archivo: [database/03_functions/04_query_functions.sql](../../../database/03_functions/04_query_functions.sql)

**Propósito:** Retorna la deuda total actual de un cliente.

**Uso:**
```sql
SELECT obtener_deuda_cliente(5);
-- Resultado: 2750.00
```

---

### calcular_saldo_credito()

Ver archivo: [database/03_functions/02_validation_functions.sql](../../../database/03_functions/02_validation_functions.sql)

**Propósito:** Trigger function que calcula automáticamente el saldo y estado.

**Se ejecuta en:**
- INSERT en `creditos_clientes`
- UPDATE en `creditos_clientes`

**Lógica:**
```sql
saldo_pendiente = monto_total - monto_pagado

IF saldo_pendiente = 0 THEN
    estado = 'pagado_completo'
ELSIF monto_pagado > 0 AND saldo_pendiente > 0 THEN
    estado = 'pagado_parcial'
ELSIF fecha_vencimiento < CURRENT_DATE AND saldo_pendiente > 0 THEN
    estado = 'vencido'
ELSE
    estado = 'pendiente'
```

---

## Triggers Automáticos

### Calcular Saldo de Crédito:

Ver archivo: [database/04_triggers/03_auto_number_triggers.sql](../../../database/04_triggers/03_auto_number_triggers.sql)

```sql
CREATE TRIGGER trigger_calcular_saldo_credito
    BEFORE INSERT OR UPDATE ON creditos_clientes
    FOR EACH ROW
    EXECUTE FUNCTION calcular_saldo_credito();
```

**Qué hace:**
- Calcula `saldo_pendiente` automáticamente
- Actualiza `estado` según saldo y fecha de vencimiento

---

## Vistas del Módulo

### vista_clientes_deuda

Ver archivo: [database/05_views/05_vista_clientes_deuda.sql](../../../database/05_views/05_vista_clientes_deuda.sql)

**Uso:**
```sql
-- Clientes con deuda vencida
SELECT nombre_completo, telefono, saldo_pendiente, fecha_vencimiento_proxima
FROM vista_clientes_deuda
WHERE estado_deuda = 'VENCIDO'
ORDER BY saldo_pendiente DESC;
```

**Columnas destacadas:**
- `total_credito`: Suma de todos los créditos
- `total_pagado`: Suma de todos los pagos
- `saldo_pendiente`: Deuda actual
- `estado_deuda`: VENCIDO, POR VENCER, AL DÍA

---

## Índices Definidos

Ver archivo: [database/02_indexes/06_clientes_indexes.sql](../../../database/02_indexes/06_clientes_indexes.sql)

```sql
-- Búsqueda rápida por NIT
CREATE INDEX idx_clientes_nit ON clientes(nit) WHERE nit IS NOT NULL;

-- Clientes frecuentes
CREATE INDEX idx_clientes_frecuente ON clientes(es_frecuente) WHERE es_frecuente = TRUE;

-- Búsqueda full-text por nombre
CREATE INDEX idx_clientes_nombre ON clientes USING gin(to_tsvector('spanish', nombre_completo));
```

Ver archivo: [database/02_indexes/07_creditos_indexes.sql](../../../database/02_indexes/07_creditos_indexes.sql)

```sql
CREATE INDEX idx_creditos_cliente ON creditos_clientes(cliente_id);
CREATE INDEX idx_creditos_venta ON creditos_clientes(venta_id);
CREATE INDEX idx_creditos_estado ON creditos_clientes(estado);
CREATE INDEX idx_creditos_vencimiento ON creditos_clientes(fecha_vencimiento);
CREATE INDEX idx_pagos_credito_credito ON pagos_credito(credito_cliente_id);
```

---

## Seeders Iniciales

Ver archivo: [database/06_seeders/07_datos_ejemplo.sql](../../../database/06_seeders/07_datos_ejemplo.sql)

```sql
-- Clientes de ejemplo
INSERT INTO clientes (nombre_completo, telefono, nit, es_frecuente, limite_credito, creado_por) VALUES
('Juan Pérez Construcciones', '71234567', '1234567015', TRUE, 5000.00, 1),
('María García', '72345678', NULL, FALSE, 0, 1);
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Cliente.php
class Cliente extends Model
{
    protected $table = 'clientes';
    
    protected $casts = [
        'es_frecuente' => 'boolean',
        'activo' => 'boolean',
        'limite_credito' => 'decimal:2',
        'fecha_registro' => 'date',
    ];
    
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
    
    public function creditos()
    {
        return $this->hasMany(CreditoCliente::class);
    }
    
    // Accessor: Deuda total actual
    public function getDeudaTotalAttribute()
    {
        return $this->creditos()
            ->whereIn('estado', ['pendiente', 'pagado_parcial', 'vencido'])
            ->sum('saldo_pendiente');
    }
    
    // Accessor: Crédito disponible
    public function getCreditoDisponibleAttribute()
    {
        return $this->limite_credito - $this->deuda_total;
    }
}

// app/Models/CreditoCliente.php
class CreditoCliente extends Model
{
    protected $table = 'creditos_clientes';
    
    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function pagos()
    {
        return $this->hasMany(PagoCredito::class);
    }
}

// app/Models/PagoCredito.php
class PagoCredito extends Model
{
    protected $table = 'pagos_credito';
    
    protected $casts = [
        'monto_pago' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];
    
    public function credito()
    {
        return $this->belongsTo(CreditoCliente::class, 'credito_cliente_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
```

---

## Casos de Uso Prácticos

### Caso 1: Cliente Hace Abono a su Deuda

**Escenario:** Cliente Juan debe Bs. 1,700 y abona Bs. 500.

```sql
-- 1. Ver deuda actual
SELECT * FROM vista_clientes_deuda WHERE id = 5;

-- 2. Registrar pago
SELECT registrar_pago_credito(
    p_credito_id := 12,
    p_monto_pago := 500.00,
    p_metodo_pago := 'efectivo',
    p_usuario_id := 1,
    p_notas := 'Abono parcial'
);

-- 3. Verificar nuevo saldo
SELECT saldo_pendiente, estado FROM creditos_clientes WHERE id = 12;
-- Resultado: saldo_pendiente = 1200.00, estado = 'pagado_parcial'
```

---

### Caso 2: Cliente Salda su Deuda Completa

```sql
SELECT registrar_pago_credito(
    p_credito_id := 12,
    p_monto_pago := 1200.00, -- Paga el saldo restante
    p_metodo_pago := 'qr',
    p_usuario_id := 1,
    p_notas := 'Pago completo - deuda saldada'
);

-- Estado cambia automáticamente a 'pagado_completo'
```

---

### Caso 3: Reporte de Cobranza Diaria

```sql
-- Ver cobros del día
SELECT 
    c.nombre_completo AS cliente,
    pc.monto_pago,
    pc.metodo_pago,
    cc.saldo_pendiente AS saldo_restante,
    u.nombre AS recibido_por
FROM pagos_credito pc
JOIN creditos_clientes cc ON pc.credito_cliente_id = cc.id
JOIN clientes c ON cc.cliente_id = c.id
JOIN usuarios u ON pc.usuario_id = u.id
WHERE DATE(pc.fecha_pago) = CURRENT_DATE
ORDER BY pc.fecha_pago DESC;
```

---

### Caso 4: Clientes Morosos (Seguimiento)

```sql
-- Lista de clientes con deuda vencida para llamar
SELECT 
    nombre_completo,
    telefono,
    saldo_pendiente,
    fecha_vencimiento_proxima,
    (CURRENT_DATE - fecha_vencimiento_proxima) AS dias_vencido
FROM vista_clientes_deuda
WHERE estado_deuda = 'VENCIDO'
ORDER BY dias_vencido DESC;
```

---

## Testing

### Casos de Prueba:

1. **Pago mayor al saldo** → Debe rechazar
2. **Cliente no frecuente compra a crédito** → Debe rechazar (API)
3. **Exceder límite de crédito** → Debe rechazar (API)
4. **Estado se actualiza automáticamente** → Verificar trigger
5. **Deuda total correcta** → Función debe sumar todos los créditos
6. **Crédito vencido cambia estado** → Verificar actualización automática

---

**Siguiente:** [Módulo Caja](07-caja.md)
