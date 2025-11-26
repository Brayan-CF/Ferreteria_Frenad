-- ============================================================================
-- TABLA: DETALLE DE COMPRAS
-- Items específicos de cada compra
-- ============================================================================

CREATE TABLE detalle_compras (
    id BIGSERIAL PRIMARY KEY,
    compra_id BIGINT NOT NULL REFERENCES compras(id) ON DELETE CASCADE,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_unitario NUMERIC(12,2) NOT NULL CHECK (precio_unitario >= 0),
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    almacen_destino_id BIGINT NOT NULL REFERENCES almacenes(id),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE detalle_compras IS 'Items específicos de cada compra';
COMMENT ON COLUMN detalle_compras.almacen_destino_id IS 'A qué almacén ingresa el producto (Bodega o Mostrador)';