-- ============================================================================
-- TABLA: INVENTARIO
-- Stock por producto y almacén
-- ============================================================================

CREATE TABLE inventario (
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE CASCADE,
    cantidad_actual NUMERIC(12,4) NOT NULL DEFAULT 0 CHECK (cantidad_actual >= 0),
    stock_minimo NUMERIC(12,4) NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    ultima_actualizacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, almacen_id)
);

COMMENT ON TABLE inventario IS 'Stock separado por almacén (Bodega: 50 tubos, Mostrador: 20 tubos)';
COMMENT ON COLUMN inventario.cantidad_actual IS 'Stock disponible en unidades base del producto';