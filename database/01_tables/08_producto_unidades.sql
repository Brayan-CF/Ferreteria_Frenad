-- ============================================================================
-- TABLA: PRODUCTO_UNIDADES
-- Conversiones de unidades por producto
-- ============================================================================

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

COMMENT ON TABLE producto_unidades IS 'Permite vender cable por metro pero comprar en rollos de 100m';
COMMENT ON COLUMN producto_unidades.factor_conversion IS 'Ej: 1 rollo = 100 metros (factor: 100)';