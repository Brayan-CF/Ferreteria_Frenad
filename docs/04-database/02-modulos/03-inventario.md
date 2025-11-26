# MÓDULO INVENTORY - GESTIÓN DE INVENTARIO

## Descripción General

Módulo que controla el stock de productos en múltiples almacenes con trazabilidad completa mediante kardex.

---

## Tablas del Módulo

### 1. almacenes

**Propósito:** Definición de ubicaciones físicas de almacenamiento.

**Estructura:**

Ver archivo: [database/01_tables/09_almacenes.sql](../../../database/01_tables/09_almacenes.sql)

**Columnas Importantes:**
- `tipo`: bodega (materiales pesados) o mostrador (área de venta)
- `ubicacion`: Descripción de la ubicación física

**Almacenes Predefinidos:**
1. **Bodega Principal**: Materiales pesados (planchas, fierros)
2. **Mostrador Venta**: Productos de alta rotación

---

### 2. inventario

**Propósito:** Stock actual por producto y almacén.

**Estructura:**

Ver archivo: [database/01_tables/10_inventario.sql](../../../database/01_tables/10_inventario.sql)

**Características Clave:**
- Clave primaria compuesta: `(producto_id, almacen_id)`
- `cantidad_actual`: Stock disponible en unidades base
- `stock_minimo`: Nivel de reorden
- `ultima_actualizacion`: Timestamp automático

---

### 3. movimientos_inventario

**Propósito:** Kardex completo - registro de cada movimiento de stock.

**Estructura:**

Ver archivo: [database/01_tables/25_movimientos_inventario.sql](../../../database/01_tables/25_movimientos_inventario.sql)

**Tipos de Movimiento:**
- `ENTRADA_COMPRA`: Recepción de mercadería
- `SALIDA_VENTA`: Venta al cliente
- `TRANSFERENCIA`: Entre almacenes (Bodega → Mostrador)
- `AJUSTE_POSITIVO`: Corrección al alza (encontrado)
- `AJUSTE_NEGATIVO`: Corrección a la baja (faltante/daño)
- `DEVOLUCION_VENTA`: Producto devuelto por cliente
- `DEVOLUCION_COMPRA`: Devolución a proveedor

**Campos Especiales:**
- `almacen_destino_id`: Solo para TRANSFERENCIA
- `razon`: Justificación obligatoria para ajustes

---

## Diagrama de Relaciones

```
┌─────────────┐
│  productos  │
└──────┬──────┘
       │
       │ producto_id
       ├───────────────────────────┐
       │                           │
       ▼                           ▼
┌──────────────┐          ┌──────────────────┐
│  inventario  │          │  movimientos_    │
│ (stock actual│          │  inventario      │
│  por almacén)│          │  (kardex)        │
└──────┬───────┘          └──────┬───────────┘
       │                          │
       │ almacen_id               │ almacen_id
       ▼                          ▼
┌─────────────┐          ┌─────────────┐
│  almacenes  │          │  almacenes  │
└─────────────┘          └─────────────┘
```

---

## Reglas de Negocio

### Inventario:
1. `cantidad_actual` nunca puede ser negativa (CHECK constraint)
2. Cada producto puede existir en múltiples almacenes
3. Stock se actualiza automáticamente con ventas/compras
4. Alerta cuando `cantidad_actual <= stock_minimo`

### Movimientos:
1. Cada cambio en `inventario` genera un registro en `movimientos_inventario`
2. Los ajustes requieren justificación obligatoria en campo `razon`
3. Las transferencias requieren `almacen_destino_id`
4. Los movimientos son inmutables (solo INSERT, no UPDATE/DELETE)

### Kardex:
1. Proporciona trazabilidad completa del inventario
2. Permite auditorías retrospectivas
3. Responde: "¿Cuándo y por qué cambió el stock?"

---

## Operaciones Comunes

### Consultar Stock Actual:

```sql
-- Stock de un producto en un almacén específico
SELECT p.nombre, a.nombre AS almacen, i.cantidad_actual, i.stock_minimo
FROM inventario i
JOIN productos p ON i.producto_id = p.id
JOIN almacenes a ON i.almacen_id = a.id
WHERE p.id = 1 AND a.id = 2;
```

### Consultar Stock Total (todos los almacenes):

```sql
-- Usar función predefinida
SELECT obtener_stock_total_producto(1);
```

### Ver Kardex de un Producto:

```sql
-- Historial completo de movimientos
SELECT * FROM vista_kardex_producto
WHERE sku = 'PROD-001'
ORDER BY fecha_movimiento DESC
LIMIT 50;
```

### Productos con Stock Bajo:

```sql
-- Usar vista predefinida
SELECT * FROM vista_productos_stock_bajo
ORDER BY cantidad_reponer DESC;
```

---

## Funciones del Módulo

### 1. obtener_stock_total_producto()

Ver archivo: [database/03_functions/04_query_functions.sql](../../../database/03_functions/04_query_functions.sql)

**Uso:**
```sql
SELECT obtener_stock_total_producto(5);
-- Resultado: 95.00 (suma de todos los almacenes)
```

---

### 2. tiene_stock_suficiente()

**Propósito:** Validar antes de vender.

**Uso:**
```sql
SELECT tiene_stock_suficiente(5, 2, 10);
-- Resultado: TRUE si hay stock, FALSE si no
```

---

### 3. transferir_producto()

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

**Propósito:** Mover producto entre almacenes de forma atómica.

**Ejemplo:**
```sql
-- Transferir 20 unidades del producto 5 de Bodega (1) a Mostrador (2)
SELECT transferir_producto(
    p_producto_id := 5,
    p_almacen_origen_id := 1,
    p_almacen_destino_id := 2,
    p_cantidad := 20,
    p_usuario_id := 1,
    p_razon := 'Reposición semanal de mostrador'
);
```

**Qué hace:**
1. Valida que hay stock suficiente en origen
2. Reduce stock en almacén origen
3. Aumenta stock en almacén destino
4. Registra movimiento tipo TRANSFERENCIA en kardex

---

### 4. ajustar_inventario()

**Propósito:** Correcciones de inventario (conteo físico).

**Ejemplo:**
```sql
-- Ajustar stock tras conteo físico
SELECT ajustar_inventario(
    p_producto_id := 3,
    p_almacen_id := 1,
    p_nueva_cantidad := 12,
    p_razon := 'Conteo físico mensual: encontradas 12 unidades (antes 15)',
    p_usuario_id := 1
);
```

**Validaciones:**
- Razon es OBLIGATORIA
- Registra AJUSTE_POSITIVO o AJUSTE_NEGATIVO según diferencia

---

## Triggers Automáticos

### Validación de Stock antes de Venta:

Ver archivo: [database/04_triggers/02_validation_triggers.sql](../../../database/04_triggers/02_validation_triggers.sql)

```sql
-- Se ejecuta automáticamente antes de INSERT/UPDATE en detalle_ventas
-- Rechaza la operación si no hay stock suficiente
```

---

## Vistas del Módulo

### vista_stock_actual

Ver archivo: [database/05_views/01_vista_stock_actual.sql](../../../database/05_views/01_vista_stock_actual.sql)

**Uso:**
```sql
SELECT * FROM vista_stock_actual
WHERE nivel_stock = 'BAJO'
ORDER BY producto_nombre;
```

**Columnas destacadas:**
- `nivel_stock`: BAJO, MEDIO, NORMAL
- `valor_inventario`: Stock valorizado a precio de compra

---

### vista_productos_stock_bajo

Ver archivo: [database/05_views/02_vista_productos_stock_bajo.sql](../../../database/05_views/02_vista_productos_stock_bajo.sql)

**Uso:**
```sql
-- Lista de productos a reponer
SELECT sku, nombre, almacen, cantidad_reponer, costo_reposicion
FROM vista_productos_stock_bajo;
```

---

### vista_kardex_producto

Ver archivo: [database/05_views/07_vista_kardex_producto.sql](../../../database/05_views/07_vista_kardex_producto.sql)

**Uso:**
```sql
-- Ver últimos 20 movimientos de un producto
SELECT fecha_movimiento, tipo_movimiento, cantidad, signo, usuario
FROM vista_kardex_producto
WHERE sku = 'PROD-001'
ORDER BY fecha_movimiento DESC
LIMIT 20;
```

---

## Índices Definidos

Ver archivo: [database/02_indexes/02_inventario_indexes.sql](../../../database/02_indexes/02_inventario_indexes.sql)

```sql
-- Búsqueda rápida de stock por producto
idx_inventario_producto (producto_id)

-- Búsqueda por almacén
idx_inventario_almacen (almacen_id)

-- Productos con stock bajo (índice parcial)
idx_inventario_stock_bajo (cantidad_actual) WHERE cantidad_actual <= stock_minimo
```

Ver archivo: [database/02_indexes/03_movimientos_inventario_indexes.sql](../../../database/02_indexes/03_movimientos_inventario_indexes.sql)

---

## Seeders Iniciales

Ver archivo: [database/06_seeders/03_almacenes.sql](../../../database/06_seeders/03_almacenes.sql)

```sql
INSERT INTO almacenes (nombre, tipo, ubicacion) VALUES
('Bodega Principal', 'bodega', 'Área trasera - Materiales pesados'),
('Mostrador Venta', 'mostrador', 'Área frontal - Alta rotación');
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Almacen.php
class Almacen extends Model
{
    protected $table = 'almacenes';
    
    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
    
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}

// app/Models/Inventario.php
class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = ['producto_id', 'almacen_id'];
    public $incrementing = false;
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}

// app/Models/MovimientoInventario.php
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    
    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }
}
```

---

## Casos de Uso Prácticos

### Caso 1: Reposición de Mostrador

**Escenario:** El mostrador se está quedando sin stock de un producto que sí hay en bodega.

```sql
SELECT transferir_producto(
    p_producto_id := 10,
    p_almacen_origen_id := 1, -- Bodega
    p_almacen_destino_id := 2, -- Mostrador
    p_cantidad := 50,
    p_usuario_id := 1,
    p_razon := 'Reposición semanal - stock mostrador bajo'
);
```

---

### Caso 2: Conteo Físico con Diferencias

**Escenario:** En el conteo físico mensual se encontraron diferencias.

```sql
-- Había 100 unidades registradas, se encontraron solo 95
SELECT ajustar_inventario(
    p_producto_id := 7,
    p_almacen_id := 1,
    p_nueva_cantidad := 95,
    p_razon := 'Conteo físico: 5 unidades faltantes - posible robo o daño no reportado',
    p_usuario_id := 1
);
```

---

### Caso 3: Auditoría de Movimientos

**Escenario:** Investigar por qué el stock de un producto disminuyó.

```sql
SELECT 
    fecha_movimiento,
    tipo_movimiento,
    cantidad,
    usuario,
    razon
FROM vista_kardex_producto
WHERE producto_id = 15
AND fecha_movimiento >= CURRENT_DATE - INTERVAL '7 days'
ORDER BY fecha_movimiento DESC;
```

---

## Testing

### Casos de Prueba:

1. **Transferencia con stock insuficiente** → Debe rechazar
2. **Ajuste sin razón** → Debe fallar
3. **Cantidad negativa en inventario** → Debe rechazar (CHECK constraint)
4. **Venta sin stock** → Trigger debe bloquear
5. **Kardex consistente** → Verificar que suma de movimientos = stock actual

---

**Siguiente:** [Módulo Compras](04-compras.md)