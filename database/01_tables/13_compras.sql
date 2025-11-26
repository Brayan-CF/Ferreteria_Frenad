-- ============================================================================
-- TABLA: COMPRAS
-- Recepción de mercadería
-- ============================================================================

CREATE TABLE compras (
    id BIGSERIAL PRIMARY KEY,
    numero_compra VARCHAR(50) UNIQUE NOT NULL,
    orden_compra_id BIGINT REFERENCES ordenes_compra(id) ON DELETE SET NULL,
    proveedor_id BIGINT NOT NULL REFERENCES proveedores(id) ON DELETE RESTRICT,
    numero_factura_proveedor VARCHAR(100),
    fecha_compra DATE DEFAULT CURRENT_DATE,
    fecha_entrega_esperada DATE,
    fecha_entrega_real DATE,
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    impuestos NUMERIC(12,2) DEFAULT 0 CHECK (impuestos >= 0),
    total NUMERIC(12,2) NOT NULL CHECK (total >= 0),
    metodo_pago VARCHAR(50) DEFAULT 'Efectivo',
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'recibida', 'cancelada')) DEFAULT 'pendiente',
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE compras IS 'Registro de compras realizadas y mercadería recibida';
COMMENT ON COLUMN compras.orden_compra_id IS 'Vincula con orden previa (nullable: compras directas sin orden)';