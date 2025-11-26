# MÓDULO PRODUCTS - CATÁLOGO DE PRODUCTOS

## Descripción General

Módulo encargado de la gestión completa del catálogo de productos, incluyendo clasificación por categorías, marcas y unidades de medida con soporte para conversiones.

---

## Tablas del Módulo

### 1. categorias

**Propósito:** Clasificación de productos por tipo.

**Estructura:**

```sql
CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);
```

**Ejemplos de Categorías:**
- Materiales de Construcción
- Plomería
- Soldadura
- Herramientas Manuales
- Eléctrico

---

### 2. marcas

**Propósito:** Registro de fabricantes y marcas.

**Estructura:**

```sql
CREATE TABLE marcas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    pais_origen VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);
```

**Ejemplos de Marcas (Bolivia):**
- EMISA (Cemento)
- Soboce (Cemento)
- Tigre (Tubería PVC)
- Stanley (Herramientas)

---

### 3. unidades_medida

**Propósito:** Definición de unidades para medir productos.

**Estructura:**

```sql
CREATE TABLE unidades_medida (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    abreviatura VARCHAR(10) NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('longitud', 'peso', 'volumen', 'unidad', 'area')) NOT NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
```

**Tipos de Unidades:**
- **Longitud:** Metro (m)
- **Peso:** Kilogramo (kg)
- **Volumen:** Litro (l), Galón (gal)
- **Unidad:** Pieza (pza), Caja (cja), Rollo
- **Área:** Metro cuadrado (m²)

---

### 4. productos

**Propósito:** Catálogo completo de productos.

**Estructura:**

```sql
CREATE TABLE productos (
    id BIGSERIAL PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id BIGINT REFERENCES categorias(id) ON DELETE SET NULL,
    marca_id BIGINT REFERENCES marcas(id) ON DELETE SET NULL,
    unidad_base_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_compra NUMERIC(12,2) NOT NULL CHECK (precio_compra >= 0),
    precio_venta NUMERIC(12,2) NOT NULL CHECK (precio_venta >= 0),
    fecha_vencimiento DATE,
    ubicacion_fisica VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);
```

**Columnas Clave:**
- `sku`: Código interno único (Stock Keeping Unit)
- `codigo_barras`: Opcional, para escaneo
- `unidad_base_id`: Unidad mínima indivisible (ej: metro para cables)
- `fecha_vencimiento`: Solo para productos perecederos (pegamentos, selladores)
- `ubicacion_fisica`: Referencia de ubicación en bodega (ej: "Estante A-3")

**Restricciones:**
- `CHECK (precio_compra >= 0)`: No puede ser negativo
- `CHECK (precio_venta >= 0)`: No puede ser negativo
- `UNIQUE (sku)`: SKU debe ser único
- `UNIQUE (codigo_barras)`: Código de barras único (si existe)

---

### 5. producto_unidades

**Propósito:** Conversiones de unidades por producto (vender en metros, comprar en rollos).

**Estructura:**

```sql
CREATE TABLE producto_unidades (
    id BIGSERIAL PRIMARY KEY,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    factor_conversion NUMERIC(10,4) NOT NULL CHECK (factor_conversion > 0),
    es_unidad_compra BOOLEAN DEFAULT FALSE,
    es_unidad_venta BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (producto_id, unidad_id)
);
```

**Ejemplo Práctico:**

```sql
-- Cable eléctrico (producto_id = 10)
-- Unidad base: metro

-- Venta por metro
INSERT INTO producto_unidades (producto_id, unidad_id, factor_conversion, es_unidad_venta)
VALUES (10, 1, 1, TRUE); -- 1 metro = 1 metro

-- Compra por rollo de 100 metros
INSERT INTO producto_unidades (producto_id, unidad_id, factor_conversion, es_unidad_compra)
VALUES (10, 5, 100, TRUE); -- 1 rollo = 100 metros
```

**Cálculo de Conversión:**
- Si vendes 5 metros: `cantidad_base = 5 * 1 = 5 metros`
- Si compras 2 rollos: `cantidad_base = 2 * 100 = 200 metros`

---

## Diagrama de Relaciones

```
┌─────────────┐      ┌─────────────┐
│ categorias  │      │   marcas    │
└──────┬──────┘      └──────┬──────┘
       │                    │
       │ categoria_id       │ marca_id
       │                    │
       ▼                    ▼
┌──────────────────────────────────┐
│          productos               │
│  (sku, nombre, precio_compra,    │
│   precio_venta, unidad_base_id)  │
└─────────┬────────────────────────┘
          │
          │ producto_id
          ▼
┌─────────────────────┐      ┌─────────────┐
│ producto_unidades   │──────│unidades_med │
│ (factor_conversion) │unidad│  (metro,    │
└─────────────────────┘  _id │  pieza, kg) │
                             └─────────────┘
```

---

## Reglas de Negocio

### Productos:
1. El SKU debe ser único y no modificable después de creación
2. `precio_venta` debe ser mayor o igual a `precio_compra` (validar en API)
3. Los productos inactivos no pueden venderse
4. La `fecha_vencimiento` es obligatoria para pegamentos y selladores

### Unidades de Medida:
1. La `unidad_base_id` define la unidad mínima de inventario
2. Todas las conversiones se calculan respecto a la unidad base
3. Un producto puede tener múltiples unidades de venta/compra

### Categorías y Marcas:
1. Pueden desactivarse (`activo = FALSE`) sin eliminarlas
2. Si se elimina una categoría (`ON DELETE SET NULL`), el producto queda sin categoría

---

## Operaciones Comunes

### Crear Producto:

```sql
INSERT INTO productos (
    sku, nombre, categoria_id, marca_id, unidad_base_id,
    precio_compra, precio_venta, creado_por
) VALUES (
    'PROD-001', 'Cemento EMISA 50kg', 1, 1, 3,
    45.00, 55.00, 1
);
```

### Buscar Productos por Nombre:

```sql
-- Búsqueda full-text (aprovecha índice GIN)
SELECT id, sku, nombre, precio_venta
FROM productos
WHERE to_tsvector('spanish', nombre) @@ to_tsquery('spanish', 'cemento')
AND activo = TRUE;
```

### Obtener Productos con Stock Bajo:

```sql
-- Usar vista predefinida
SELECT * FROM vista_productos_stock_bajo
ORDER BY cantidad_reponer DESC;
```

### Consultar Unidades de Conversión:

```sql
SELECT p.nombre,
       um_base.nombre AS unidad_base,
       um_conv.nombre AS unidad_conversion,
       pu.factor_conversion,
       pu.es_unidad_compra,
       pu.es_unidad_venta
FROM productos p
INNER JOIN unidades_medida um_base ON p.unidad_base_id = um_base.id
INNER JOIN producto_unidades pu ON p.id = pu.producto_id
INNER JOIN unidades_medida um_conv ON pu.unidad_id = um_conv.id
WHERE p.id = 10;
```

---

## Índices Definidos

Ver archivo: [database/02_indexes/01_productos_indexes.sql](database/02_indexes/01_productos_indexes.sql)

```sql
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_marca ON productos(marca_id);
CREATE INDEX idx_productos_sku ON productos(sku);
CREATE INDEX idx_productos_codigo_barras ON productos(codigo_barras) 
    WHERE codigo_barras IS NOT NULL;
CREATE INDEX idx_productos_activo ON productos(activo) 
    WHERE activo = TRUE;
-- Índice full-text para búsqueda por nombre
CREATE INDEX idx_productos_nombre ON productos 
    USING gin(to_tsvector('spanish', nombre));
```

---

## Seeders Iniciales

Ver archivo: [database/06_seeders/04_categorias.sql](database/06_seeders/04_categorias.sql)

```sql
INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Materiales de Construcción', 'Cemento, estuco, cemento cola, fierros de construcción', TRUE),
('Plomería', 'Tubos PVC, accesorios agua potable, pegamentos', TRUE),
('Soldadura', 'Planchas metálicas, tubos, costaneras, electrodos, equipos', TRUE),
('Herramientas Manuales', 'Martillos, desarmadores, alicates, llaves', TRUE);
```

Ver archivo: [database/06_seeders/02_unidades_medida.sql](database/06_seeders/02_unidades_medida.sql)

```sql
INSERT INTO unidades_medida (nombre, abreviatura, tipo) VALUES
('Metro', 'm', 'longitud'),
('Pieza', 'pza', 'unidad'),
('Kilogramo', 'kg', 'peso'),
('Rollo', 'rollo', 'unidad');
```

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Producto.php
class Producto extends Model
{
    protected $table = 'productos';
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
    
    public function unidadBase()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_base_id');
    }
    
    public function unidadesConversion()
    {
        return $this->hasMany(ProductoUnidad::class);
    }
}
```

---

## Testing

### Casos de Prueba:

1. **Crear producto sin SKU** → Debe fallar
2. **SKU duplicado** → Debe rechazar
3. **Precio venta < precio compra** → Debe advertir (lógica en API)
4. **Código de barras duplicado** → Debe rechazar
5. **Conversión de unidades** → Verificar cálculos correctos
6. **Búsqueda por nombre** → Debe funcionar con tildes y mayúsculas

---

**Siguiente:** [Módulo Inventario](03-inventario.md)