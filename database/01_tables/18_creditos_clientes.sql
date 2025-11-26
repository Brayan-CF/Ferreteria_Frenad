-- ============================================================================
-- TABLA: CREDITOS DE CLIENTES
-- Control de deuda por venta a crédito
-- ============================================================================

CREATE TABLE creditos_clientes (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL REFERENCES clientes(id) ON DELETE RESTRICT,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE RESTRICT,
    monto_total NUMERIC(12,2) NOT NULL CHECK (monto_total >= 0),
    monto_pagado NUMERIC(12,2) DEFAULT 0 CHECK (monto_pagado >= 0),
    saldo_pendiente NUMERIC(12,2) NOT NULL CHECK (saldo_pendiente >= 0),
    fecha_vencimiento DATE,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'pagado_parcial', 'pagado_completo', 'vencido')) DEFAULT 'pendiente',
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE creditos_clientes IS 'Control de deuda por venta a crédito';
COMMENT ON COLUMN creditos_clientes.saldo_pendiente IS 'Se actualiza con cada pago: monto_total - monto_pagado';