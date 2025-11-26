-- ============================================================================
-- TABLA: ORDENES DE COMPRA
-- Pedidos enviados a proveedores
-- ============================================================================

CREATE TABLE ordenes_compra (
    id BIGSERIAL PRIMARY KEY,
    numero_orden VARCHAR(50) UNIQUE NOT NULL,
    proveedor_id BIGINT NOT NULL REFERENCES proveedores(id) ON DELETE RESTRICT,
    fecha_orden DATE DEFAULT CURRENT_DATE,
    fecha_entrega_esperada DATE,
    total_ordenado NUMERIC(12,2) NOT NULL CHECK (total_ordenado >= 0),
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'recibida_parcial', 'recibida_completa', 'cancelada')) DEFAULT 'pendiente',
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE ordenes_compra IS 'Pedidos enviados a proveedores antes de recibir mercadería';