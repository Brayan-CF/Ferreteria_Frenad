-- ============================================================================
-- TABLA: PAGOS DE CREDITO
-- Registro de cada abono del cliente
-- ============================================================================

CREATE TABLE pagos_credito (
    id BIGSERIAL PRIMARY KEY,
    credito_cliente_id BIGINT NOT NULL REFERENCES creditos_clientes(id) ON DELETE RESTRICT,
    monto_pago NUMERIC(12,2) NOT NULL CHECK (monto_pago > 0),
    metodo_pago VARCHAR(20) CHECK (metodo_pago IN ('efectivo', 'qr')) NOT NULL,
    fecha_pago TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE pagos_credito IS 'Registro de cada abono que hace el cliente a su deuda';