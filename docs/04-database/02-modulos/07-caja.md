# MÓDULO CASH - CONTROL DE CAJA

## Descripción General

Módulo que gestiona la apertura y cierre diario de caja con control de diferencias y registro detallado de movimientos.

---

## Tablas del Módulo

### 1. arqueos_caja

**Propósito:** Registro de apertura y cierre diario de caja.

**Estructura:**

Ver archivo: [database/01_tables/22_arqueos_caja.sql](../../../database/01_tables/22_arqueos_caja.sql)

**Columnas Clave:**
- `fecha_apertura`: Timestamp de apertura
- `fecha_cierre`: Timestamp de cierre (NULL si aún está abierta)
- `monto_inicial`: Fondo de caja al abrir
- `monto_final`: Efectivo contado al cerrar
- `total_esperado`: Cálculo: monto_inicial + ventas_efectivo
- `diferencia`: monto_final - total_esperado (positivo = sobrante, negativo = faltante)
- `estado`: abierta o cerrada

**Reglas:**
- Solo puede haber una caja abierta por vez
- El cierre calcula automáticamente diferencias
- Las diferencias deben justificarse en `notas_cierre`

---

### 2. movimientos_caja

**Propósito:** Registro detallado de entradas y salidas de efectivo.

**Estructura:**

Ver archivo: [database/01_tables/23_movimientos_caja.sql](../../../database/01_tables/23_movimientos_caja.sql)

**Tipos de Movimiento:**
- `venta`: Ingreso por venta en efectivo
- `gasto`: Salidas (compras menores, gastos operativos)
- `retiro`: Retiro de efectivo para banco
- `ingreso_extra`: Otros ingresos

**Columnas Importantes:**
- `arqueo_caja_id`: Vincula con la caja del día
- `venta_id`: Si el movimiento es por venta (opcional)
- `concepto`: Descripción obligatoria
- `monto`: Siempre positivo (el tipo define si suma o resta)

---

## Diagrama de Relaciones

```
┌─────────────┐
│  usuarios   │
└──────┬──────┘
       │
       │ usuario_apertura_id / usuario_cierre_id
       ▼
┌─────────────────────┐
│   arqueos_caja      │
│ (fecha_apertura,    │
│  monto_inicial,     │
│  monto_final,       │
│  diferencia)        │
└──────┬──────────────┘
       │
       │ arqueo_caja_id
       ▼
┌─────────────────────┐
│  movimientos_caja   │
│ (tipo_movimiento,   │
│  monto, concepto)   │
└──────┬──────────────┘
       │
       │ venta_id (opcional)
       ▼
┌─────────────────────┐
│      ventas         │
└─────────────────────┘
```

---

## Reglas de Negocio

### Arqueos de Caja:
1. Solo puede haber una caja abierta simultáneamente
2. La apertura requiere `monto_inicial` (fondo de caja)
3. El cierre calcula automáticamente:
   - `total_ventas_efectivo`: Suma ventas en efectivo del día
   - `total_ventas_qr`: Suma ventas con QR del día
   - `total_esperado`: monto_inicial + ventas_efectivo - gastos/retiros
   - `diferencia`: monto_final - total_esperado
4. Diferencias > Bs. 10 deben justificarse

### Movimientos de Caja:
1. Solo se registran movimientos de efectivo
2. Las ventas con QR se registran pero no afectan el efectivo en caja
3. Todos los movimientos requieren `concepto` descriptivo
4. Los movimientos son inmutables (no se editan)

---

## Operaciones Comunes

### Abrir Caja:

```sql
INSERT INTO arqueos_caja (usuario_apertura_id, monto_inicial, notas_apertura)
VALUES (1, 200.00, 'Apertura caja - Fondo inicial Bs. 200')
RETURNING id;
```

---

### Registrar Movimiento Manual:

```sql
-- Ejemplo: Gasto por compra de materiales de limpieza
INSERT INTO movimientos_caja (
    arqueo_caja_id,
    tipo_movimiento,
    monto,
    concepto,
    usuario_id
) VALUES (
    5,
    'gasto',
    35.00,
    'Compra materiales limpieza - escobas y detergente',
    1
);
```

---

### Cerrar Caja (Función Transaccional):

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

```sql
-- Al final del día, contar el efectivo y cerrar
SELECT cerrar_caja(
    p_arqueo_id := 5,
    p_monto_final := 1523.50, -- Efectivo contado físicamente
    p_usuario_id := 1,
    p_notas := 'Cierre normal - cuadra con esperado'
);
```

**Qué hace automáticamente:**
1. Consulta todas las ventas en efectivo del día
2. Consulta todas las ventas con QR del día
3. Calcula `total_esperado` = monto_inicial + ventas_efectivo - gastos
4. Calcula `diferencia` = monto_final - total_esperado
5. Actualiza el arqueo con todos los datos
6. Cambia estado a 'cerrada'

---

### Ver Estado de Caja del Día:

```sql
-- Usar vista predefinida
SELECT * FROM vista_caja_hoy;
```

---

### Ver Movimientos de una Caja:

```sql
SELECT 
    mc.tipo_movimiento,
    mc.monto,
    mc.concepto,
    v.numero_venta,
    u.nombre AS usuario,
    mc.creado_en
FROM movimientos_caja mc
LEFT JOIN ventas v ON mc.venta_id = v.id
JOIN usuarios u ON mc.usuario_id = u.id
WHERE mc.arqueo_caja_id = 5
ORDER BY mc.creado_en;
```

---

## Funciones del Módulo

### cerrar_caja()

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

**Parámetros:**
- `p_arqueo_id`: ID del arqueo a cerrar
- `p_monto_final`: Efectivo físicamente contado
- `p_usuario_id`: Usuario que cierra
- `p_notas`: Notas sobre el cierre

**Retorna:** TRUE si exitoso

**Validaciones:**
- Verifica que el arqueo exista y esté abierto
- Calcula automáticamente ventas del día
- Detecta diferencias (faltante o sobrante)

---

## Vistas del Módulo

### vista_caja_hoy

Ver archivo: [database/05_views/06_vista_caja_hoy.sql](../../../database/05_views/06_vista_caja_hoy.sql)

**Uso:**
```sql
SELECT * FROM vista_caja_hoy;
```

**Columnas destacadas:**
- `total_ventas_efectivo`: Suma de ventas en efectivo
- `total_ventas_qr`: Suma de ventas con QR
- `diferencia`: Sobrante o faltante
- `estado_diferencia`: SOBRANTE, FALTANTE o CUADRADO

---

## Índices Definidos

Ver archivo: [database/02_indexes/08_caja_indexes.sql](../../../database/02_indexes/08_caja_indexes.sql)

```sql
CREATE INDEX idx_arqueos_caja_fecha_apertura ON arqueos_caja(fecha_apertura);
CREATE INDEX idx_arqueos_caja_estado ON arqueos_caja(estado);
CREATE INDEX idx_movimientos_caja_arqueo ON movimientos_caja(arqueo_caja_id);
CREATE INDEX idx_movimientos_caja_tipo ON movimientos_caja(tipo_movimiento);
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/ArqueoCaja.php
class ArqueoCaja extends Model
{
    protected $table = 'arqueos_caja';
    
    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'total_ventas_efectivo' => 'decimal:2',
        'total_ventas_qr' => 'decimal:2',
        'total_esperado' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];
    
    public function usuarioApertura()
    {
        return $this->belongsTo(Usuario::class, 'usuario_apertura_id');
    }
    
    public function usuarioCierre()
    {
        return $this->belongsTo(Usuario::class, 'usuario_cierre_id');
    }
    
    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }
    
    // Scope: Caja abierta
    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }
}

// app/Models/MovimientoCaja.php
class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';
    
    protected $casts = [
        'monto' => 'decimal:2',
        'creado_en' => 'datetime',
    ];
    
    public function arqueoCaja()
    {
        return $this->belongsTo(ArqueoCaja::class);
    }
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
```

---

## Casos de Uso Prácticos

### Caso 1: Flujo Completo Diario

```sql
-- 1. Apertura (8:00 AM)
INSERT INTO arqueos_caja (usuario_apertura_id, monto_inicial, notas_apertura)
VALUES (1, 200.00, 'Apertura normal')
RETURNING id; -- Resultado: id = 10

-- 2. Durante el día, las ventas se registran automáticamente
-- (función procesar_venta() registra movimientos automáticamente)

-- 3. Gasto operativo (compra papel higiénico)
INSERT INTO movimientos_caja (
    arqueo_caja_id, tipo_movimiento, monto, concepto, usuario_id
) VALUES (10, 'gasto', 25.00, 'Papel higiénico y jabón', 1);

-- 4. Retiro para banco
INSERT INTO movimientos_caja (
    arqueo_caja_id, tipo_movimiento, monto, concepto, usuario_id
) VALUES (10, 'retiro', 1000.00, 'Depósito banco BCP', 1);

-- 5. Cierre (7:00 PM)
SELECT cerrar_caja(
    p_arqueo_id := 10,
    p_monto_final := 523.50,
    p_usuario_id := 1,
    p_notas := 'Cierre OK - diferencia mínima'
);
```

---

### Caso 2: Caja con Faltante

```sql
SELECT cerrar_caja(
    p_arqueo_id := 10,
    p_monto_final := 480.00, -- Deberían ser Bs. 500
    p_usuario_id := 1,
    p_notas := 'FALTANTE Bs. 20 - revisado dos veces, no encontrado. Posible error en cambio.'
);

-- diferencia = -20.00 (negativo = faltante)
```

---

### Caso 3: Reporte de Cajas del Mes

```sql
SELECT 
    DATE(fecha_apertura) AS fecha,
    u_apertura.nombre AS abrió,
    u_cierre.nombre AS cerró,
    monto_inicial,
    total_ventas_efectivo,
    total_ventas_qr,
    monto_final,
    diferencia,
    CASE 
        WHEN diferencia > 0 THEN 'SOBRANTE'
        WHEN diferencia < 0 THEN 'FALTANTE'
        ELSE 'CUADRADO'
    END AS estado
FROM arqueos_caja ac
JOIN usuarios u_apertura ON ac.usuario_apertura_id = u_apertura.id
LEFT JOIN usuarios u_cierre ON ac.usuario_cierre_id = u_cierre.id
WHERE DATE(fecha_apertura) >= DATE_TRUNC('month', CURRENT_DATE)
AND estado = 'cerrada'
ORDER BY fecha_apertura DESC;
```

---

### Caso 4: Arqueo Pendiente (no cerrado ayer)

```sql
-- Detectar cajas no cerradas
SELECT 
    id,
    fecha_apertura,
    usuario_apertura_id,
    monto_inicial,
    estado
FROM arqueos_caja
WHERE estado = 'abierta'
AND DATE(fecha_apertura) < CURRENT_DATE;

-- Cerrar manualmente con justificación
```

---

## Reportes Útiles

### Reporte Diario de Caja:

```sql
SELECT 
    'APERTURA' AS concepto,
    NULL AS tipo,
    monto_inicial AS monto
FROM arqueos_caja
WHERE id = 10

UNION ALL

SELECT 
    'MOVIMIENTO' AS concepto,
    tipo_movimiento,
    CASE 
        WHEN tipo_movimiento IN ('venta', 'ingreso_extra') THEN monto
        ELSE -monto
    END AS monto
FROM movimientos_caja
WHERE arqueo_caja_id = 10

UNION ALL

SELECT 
    'CIERRE' AS concepto,
    'EFECTIVO CONTADO' AS tipo,
    monto_final AS monto
FROM arqueos_caja
WHERE id = 10

ORDER BY concepto;
```

---

## Testing

### Casos de Prueba:

1. **Abrir dos cajas simultáneamente** → Debe rechazar (lógica API)
2. **Cerrar caja ya cerrada** → Debe rechazar
3. **Movimiento en caja cerrada** → Debe rechazar (validación API)
4. **Cálculo de diferencia correcto** → Verificar fórmula
5. **Ventas QR no afectan efectivo** → Verificar separación
6. **Histórico de cajas completo** → No se pueden eliminar arqueos

---

**Siguiente:** [Módulo Auditoría](08-auditoria.md)
