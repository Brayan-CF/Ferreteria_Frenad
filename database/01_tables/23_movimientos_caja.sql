-- ============================================================================
-- TABLA: MOVIMIENTOS DE CAJA
-- Registro detallado de entradas/salidas
-- ============================================================================

CREATE TABLE movimientos_caja (
    id BIGSERIAL PRIMARY KEY,
    arqueo_caja_id BIGINT NOT NULL REFERENCES arqueos_caja(id) ON DELETE CASCADE,
    tipo_movimiento VARCHAR(20) CHECK (tipo_movimiento IN ('venta', 'gasto', 'retiro', 'ingreso_extra')) NOT NULL,
    monto NUMERIC(12,2) NOT NULL CHECK (monto > 0),
    concepto TEXT NOT NULL,
    venta_id BIGINT REFERENCES ventas(id) ON DELETE SET NULL,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE movimientos_caja IS 'Registro detallado de entradas/salidas de efectivo';