-- ============================================================================
-- TABLA: DETALLE DE VENTAS
-- Items individuales de cada venta
-- ============================================================================

CREATE TABLE detalle_ventas (
    id BIGSERIAL PRIMARY KEY,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE RESTRICT,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_unitario NUMERIC(12,2) NOT NULL CHECK (precio_unitario >= 0),
    descuento_monto NUMERIC(12,2) DEFAULT 0 CHECK (descuento_monto >= 0),
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE detalle_ventas IS 'Items individuales de cada venta';
COMMENT ON COLUMN detalle_ventas.precio_unitario IS 'Precio al momento de la venta (historial)';
COMMENT ON COLUMN detalle_ventas.almacen_id IS 'De qué almacén salió el producto';