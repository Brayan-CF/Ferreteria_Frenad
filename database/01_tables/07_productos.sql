-- ============================================================================
-- TABLA: PRODUCTOS
-- Catálogo completo de productos
-- ============================================================================

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

COMMENT ON TABLE productos IS 'Catálogo completo de productos de la ferretería';
COMMENT ON COLUMN productos.sku IS 'Código interno único del producto';
COMMENT ON COLUMN productos.unidad_base_id IS 'Unidad mínima indivisible (ej: metro para cables)';
COMMENT ON COLUMN productos.fecha_vencimiento IS 'Solo para pegamentos y productos perecederos';
COMMENT ON COLUMN productos.ubicacion_fisica IS 'Pasillo/estante aproximado';