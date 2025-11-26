-- ============================================================================
-- TABLA: ARQUEOS DE CAJA
-- Apertura y cierre diario de caja
-- ============================================================================

CREATE TABLE arqueos_caja (
    id BIGSERIAL PRIMARY KEY,
    fecha_apertura TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMPTZ,
    usuario_apertura_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    usuario_cierre_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    monto_inicial NUMERIC(12,2) NOT NULL CHECK (monto_inicial >= 0),
    monto_final NUMERIC(12,2) CHECK (monto_final >= 0),
    total_ventas_efectivo NUMERIC(12,2) DEFAULT 0,
    total_ventas_qr NUMERIC(12,2) DEFAULT 0,
    total_esperado NUMERIC(12,2),
    diferencia NUMERIC(12,2),
    estado VARCHAR(20) CHECK (estado IN ('abierta', 'cerrada')) DEFAULT 'abierta',
    notas_apertura TEXT,
    notas_cierre TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE arqueos_caja IS 'Apertura y cierre diario de caja';
COMMENT ON COLUMN arqueos_caja.diferencia IS 'Faltante (negativo) o Sobrante (positivo)';
COMMENT ON COLUMN arqueos_caja.total_esperado IS 'monto_inicial + ventas_efectivo - gastos';