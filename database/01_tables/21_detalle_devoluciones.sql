-- ============================================================================
-- TABLA: DETALLE DE DEVOLUCIONES
-- Qué productos específicos se devolvieron
-- ============================================================================

CREATE TABLE detalle_devoluciones_venta (
    id BIGSERIAL PRIMARY KEY,
    devolucion_venta_id BIGINT NOT NULL REFERENCES devoluciones_venta(id) ON DELETE CASCADE,
    detalle_venta_id BIGINT NOT NULL REFERENCES detalle_ventas(id) ON DELETE RESTRICT,
    cantidad_devuelta NUMERIC(12,4) NOT NULL CHECK (cantidad_devuelta > 0),
    monto_devuelto NUMERIC(12,2) NOT NULL CHECK (monto_devuelto >= 0),
    almacen_retorno_id BIGINT NOT NULL REFERENCES almacenes(id),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE detalle_devoluciones_venta IS 'Qué productos específicos se devolvieron';
COMMENT ON COLUMN detalle_devoluciones_venta.almacen_retorno_id IS 'A qué almacén regresa el producto';