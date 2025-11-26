# MÓDULO PURCHASES - GESTIÓN DE COMPRAS

## Descripción General

Módulo que gestiona el flujo completo de compras desde la orden de compra hasta la recepción de mercadería y actualización automática del inventario.

---

## Tablas del Módulo

### 1. proveedores

**Propósito:** Registro de proveedores de materiales y productos.

**Estructura:**

Ver archivo: [database/01_tables/11_proveedores.sql](../../../database/01_tables/11_proveedores.sql)

**Columnas Importantes:**
- `razon_social`: Nombre legal del proveedor
- `nit`: NIT único en Bolivia
- `nombre_contacto`: Persona de contacto
- `activo`: Soft delete para proveedores históricos

**Ejemplo:**
```sql
INSERT INTO proveedores (razon_social, nit, telefono, direccion, creado_por)
VALUES ('Distribuidora La Paz SRL', '1023456789', '2-2234567', 'Av. Buenos Aires #1234', 1);
```

---

### 2. ordenes_compra

**Propósito:** Pedidos enviados a proveedores antes de recibir mercadería.

**Estructura:**

Ver archivo: [database/01_tables/12_ordenes_compra.sql](../../../database/01_tables/12_ordenes_compra.sql)

**Estados Posibles:**
- `pendiente`: Orden enviada, esperando recepción
- `recibida_parcial`: Se recibió parte del pedido
- `recibida_completa`: Toda la mercadería fue recibida
- `cancelada`: Orden cancelada

**Características:**
- `numero_orden`: Auto-generado (formato: OC-YYYYMMDD-0001)
- `fecha_entrega_esperada`: Para seguimiento de proveedores
- `total_ordenado`: Monto estimado del pedido

---

### 3. compras

**Propósito:** Registro de compras efectivamente realizadas y mercadería recibida.

**Estructura:**

Ver archivo: [database/01_tables/13_compras.sql](../../../database/01_tables/13_compras.sql)

**Columnas Clave:**
- `numero_compra`: Auto-generado (formato: C-YYYYMMDD-0001)
- `orden_compra_id`: Vincula con orden previa (nullable: compras directas sin orden)
- `numero_factura_proveedor`: Factura del proveedor
- `fecha_entrega_real`: Fecha en que se recibió la mercadería
- `estado`: pendiente, recibida, cancelada

**Flujo de Trabajo:**
1. **Opcional:** Crear orden de compra (`ordenes_compra`)
2. Recibir mercadería y crear compra (`compras`)
3. Registrar items (`detalle_compras`)
4. Inventario se actualiza automáticamente

---

### 4. detalle_compras

**Propósito:** Items específicos de cada compra.

**Estructura:**

Ver archivo: [database/01_tables/14_detalle_compras.sql](../../../database/01_tables/14_detalle_compras.sql)

**Columnas Importantes:**
- `almacen_destino_id`: A qué almacén ingresa el producto (Bodega/Mostrador)
- `cantidad`: En la unidad especificada
- `unidad_id`: Unidad de medida de la compra
- `precio_unitario`: Precio al que se compró (histórico)

---

## Diagrama de Relaciones

```
┌─────────────┐
│ proveedores │
└──────┬──────┘
       │
       ├──────────────────┐
       │                  │
       ▼                  ▼
┌──────────────┐   ┌──────────────┐
│ ordenes_     │   │   compras    │
│ compra       │◄──│(orden_compra_│
│(pendiente)   │   │   id)        │
└──────────────┘   └──────┬───────┘
                          │
                          ▼
                   ┌──────────────┐
                   │ detalle_     │
                   │ compras      │
                   └──────┬───────┘
                          │
                          ▼
                   ┌──────────────┐
                   │  productos   │
                   │  inventario  │
                   └──────────────┘
```

---

## Reglas de Negocio

### Proveedores:
1. El NIT debe ser único
2. Los proveedores inactivos no pueden recibir nuevas órdenes
3. Se mantienen históricos (no se eliminan)

### Órdenes de Compra:
1. Una orden puede tener múltiples recepciones parciales
2. Estado se actualiza automáticamente al recibir compras
3. Las órdenes canceladas no pueden recibir mercadería

### Compras:
1. Cada compra actualiza automáticamente el inventario
2. El `numero_compra` es único y auto-generado
3. Las compras registran el `precio_unitario` para histórico de costos
4. Solo las compras con estado 'recibida' afectan el inventario

### Detalle de Compras:
1. Cada item se registra en el almacén destino especificado
2. Se respetan las conversiones de unidades
3. El `subtotal` se calcula automáticamente: cantidad × precio_unitario

---

## Operaciones Comunes

### Crear Orden de Compra:

```sql
-- 1. Crear orden
INSERT INTO ordenes_compra (proveedor_id, fecha_entrega_esperada, total_ordenado, usuario_id)
VALUES (1, CURRENT_DATE + INTERVAL '7 days', 5000.00, 1)
RETURNING id;

-- 2. Registrar detalle (hacer manualmente por ahora)
-- En futuro: usar función transaccional
```

---

### Registrar Compra Completa (Función Transaccional):

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

```sql
-- Registrar recepción de mercadería de forma atómica
SELECT procesar_compra(
    p_proveedor_id := 1,
    p_usuario_id := 1,
    p_orden_compra_id := 5, -- Nullable si es compra directa
    p_numero_factura := 'FACT-001234',
    p_items := '[
        {
            "producto_id": 1,
            "cantidad": 100,
            "unidad_id": 3,
            "precio_unitario": 45.00,
            "almacen_destino_id": 1
        },
        {
            "producto_id": 2,
            "cantidad": 50,
            "unidad_id": 11,
            "precio_unitario": 18.50,
            "almacen_destino_id": 2
        }
    ]'::JSONB
);
```

**Qué hace automáticamente:**
1. Crea el registro en `compras`
2. Inserta items en `detalle_compras`
3. Actualiza el `inventario` por cada item
4. Registra movimientos en `movimientos_inventario`
5. Actualiza el estado de la orden de compra (si existe)
6. Todo en una transacción atómica (si falla algo, revierte todo)

---

### Consultar Compras Recientes:

```sql
SELECT 
    c.numero_compra,
    c.fecha_compra,
    p.razon_social AS proveedor,
    c.total,
    c.estado,
    u.nombre AS usuario
FROM compras c
JOIN proveedores p ON c.proveedor_id = p.id
JOIN usuarios u ON c.usuario_id = u.id
WHERE c.fecha_compra >= CURRENT_DATE - INTERVAL '30 days'
ORDER BY c.fecha_compra DESC;
```

---

### Ver Detalle de una Compra:

```sql
SELECT 
    pr.sku,
    pr.nombre AS producto,
    dc.cantidad,
    um.abreviatura AS unidad,
    dc.precio_unitario,
    dc.subtotal,
    a.nombre AS almacen_destino
FROM detalle_compras dc
JOIN productos pr ON dc.producto_id = pr.id
JOIN unidades_medida um ON dc.unidad_id = um.id
JOIN almacenes a ON dc.almacen_destino_id = a.id
WHERE dc.compra_id = 10
ORDER BY pr.nombre;
```

---

### Órdenes Pendientes:

```sql
SELECT 
    oc.numero_orden,
    p.razon_social AS proveedor,
    oc.fecha_orden,
    oc.fecha_entrega_esperada,
    oc.total_ordenado,
    oc.estado
FROM ordenes_compra oc
JOIN proveedores p ON oc.proveedor_id = p.id
WHERE oc.estado IN ('pendiente', 'recibida_parcial')
ORDER BY oc.fecha_entrega_esperada ASC;
```

---

## Funciones del Módulo

### procesar_compra()

Ver archivo: [database/03_functions/03_transactional_functions.sql](../../../database/03_functions/03_transactional_functions.sql)

**Parámetros:**
- `p_proveedor_id`: ID del proveedor
- `p_usuario_id`: Usuario que registra la compra
- `p_orden_compra_id`: ID de orden previa (opcional)
- `p_numero_factura`: Factura del proveedor
- `p_items`: Array JSON con productos y cantidades

**Retorna:** ID de la compra creada

**Validaciones:**
- Todos los productos deben existir
- Los almacenes destino deben ser válidos
- Las cantidades deben ser positivas

---

### generar_numero_compra()

Ver archivo: [database/03_functions/02_validation_functions.sql](../../../database/03_functions/02_validation_functions.sql)

**Propósito:** Genera número correlativo único de compra.

**Formato:** C-YYYYMMDD-NNNN

**Ejemplo:** C-20241215-0001

---

### generar_numero_orden()

**Propósito:** Genera número correlativo único de orden.

**Formato:** OC-YYYYMMDD-NNNN

**Ejemplo:** OC-20241215-0001

---

## Triggers Automáticos

### Auto-numeración de Compras:

Ver archivo: [database/04_triggers/03_auto_number_triggers.sql](../../../database/04_triggers/03_auto_number_triggers.sql)

```sql
-- Se ejecuta automáticamente antes de INSERT en compras
-- Genera numero_compra si no se proporciona
CREATE TRIGGER trigger_generar_numero_compra
    BEFORE INSERT ON compras
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_compra();
```

---

### Auto-numeración de Órdenes:

```sql
CREATE TRIGGER trigger_generar_numero_orden
    BEFORE INSERT ON ordenes_compra
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_orden();
```

---

## Índices Definidos

Ver archivo: [database/02_indexes/05_compras_indexes.sql](../../../database/02_indexes/05_compras_indexes.sql)

```sql
CREATE INDEX idx_compras_proveedor ON compras(proveedor_id);
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_compras_estado ON compras(estado);
CREATE INDEX idx_compras_usuario ON compras(usuario_id);
CREATE INDEX idx_compras_numero ON compras(numero_compra);
CREATE INDEX idx_ordenes_compra_proveedor ON ordenes_compra(proveedor_id);
CREATE INDEX idx_ordenes_compra_estado ON ordenes_compra(estado);
```

---

## Seeders Iniciales

Ver archivo: [database/06_seeders/07_datos_ejemplo.sql](../../../database/06_seeders/07_datos_ejemplo.sql)

```sql
-- Proveedores de ejemplo
INSERT INTO proveedores (razon_social, nit, telefono, direccion, email, creado_por) VALUES
('Distribuidora La Paz SRL', '1023456789', '2-2234567', 'Av. Buenos Aires #1234', 'ventas@distlapaz.com', 1),
('Importadora Materiales SA', '9876543210', '2-2345678', 'Zona 16 de Julio', 'contacto@importmat.bo', 1);
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Proveedor.php
class Proveedor extends Model
{
    protected $table = 'proveedores';
    
    public function ordenes()
    {
        return $this->hasMany(OrdenCompra::class);
    }
    
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}

// app/Models/OrdenCompra.php
class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';
    
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}

// app/Models/Compra.php
class Compra extends Model
{
    protected $table = 'compras';
    
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    
    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class);
    }
    
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}

// app/Models/DetalleCompra.php
class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';
    
    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }
}
```

---

## Casos de Uso Prácticos

### Caso 1: Compra con Orden Previa

**Escenario:** Se envió una orden de compra hace 5 días y hoy llega la mercadería.

```sql
-- 1. Buscar la orden
SELECT id, numero_orden, total_ordenado
FROM ordenes_compra
WHERE proveedor_id = 1 AND estado = 'pendiente';

-- 2. Registrar la compra vinculada
SELECT procesar_compra(
    p_proveedor_id := 1,
    p_usuario_id := 1,
    p_orden_compra_id := 5,
    p_numero_factura := 'PROV-12345',
    p_items := '[{"producto_id":1, "cantidad":100, "unidad_id":3, "precio_unitario":45.00, "almacen_destino_id":1}]'::JSONB
);

-- 3. La orden se marca automáticamente como 'recibida_completa'
```

---

### Caso 2: Compra Directa (sin Orden)

**Escenario:** El proveedor llega sin previo aviso con mercadería.

```sql
SELECT procesar_compra(
    p_proveedor_id := 2,
    p_usuario_id := 1,
    p_orden_compra_id := NULL, -- Sin orden previa
    p_numero_factura := 'URGENTE-001',
    p_items := '[
        {"producto_id":3, "cantidad":25, "unidad_id":2, "precio_unitario":35.00, "almacen_destino_id":2}
    ]'::JSONB
);
```

---

### Caso 3: Análisis de Compras por Proveedor

**Escenario:** Evaluar qué proveedor es más confiable.

```sql
SELECT 
    p.razon_social,
    COUNT(c.id) AS total_compras,
    SUM(c.total) AS monto_total,
    AVG(c.fecha_entrega_real - oc.fecha_entrega_esperada) AS promedio_retraso_dias
FROM proveedores p
LEFT JOIN compras c ON p.id = c.proveedor_id
LEFT JOIN ordenes_compra oc ON c.orden_compra_id = oc.id
WHERE c.fecha_compra >= CURRENT_DATE - INTERVAL '1 year'
GROUP BY p.id, p.razon_social
ORDER BY total_compras DESC;
```

---

## Testing

### Casos de Prueba:

1. **Compra con proveedor inexistente** → Debe fallar (FK constraint)
2. **Procesar compra sin items** → Debe rechazar
3. **Cantidad negativa** → Debe fallar (CHECK constraint)
4. **Almacén destino inválido** → Debe fallar
5. **Inventario actualizado correctamente** → Verificar suma de stock
6. **Número de compra único** → No puede haber duplicados
7. **Orden se marca como recibida** → Verificar actualización automática

---

**Siguiente:** [Módulo Ventas](05-ventas.md)
