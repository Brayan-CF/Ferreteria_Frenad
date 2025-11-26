-- ============================================================================
-- ÍNDICES PARA CAJA
-- ============================================================================

CREATE INDEX idx_arqueos_caja_fecha_apertura ON arqueos_caja(fecha_apertura);
CREATE INDEX idx_arqueos_caja_estado ON arqueos_caja(estado);
CREATE INDEX idx_movimientos_caja_arqueo ON movimientos_caja(arqueo_caja_id);
CREATE INDEX idx_movimientos_caja_tipo ON movimientos_caja(tipo_movimiento);