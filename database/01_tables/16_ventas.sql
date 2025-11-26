-- ============================================================================
-- TABLA: VENTAS
-- Registro de todas las ventas realizadas
-- ============================================================================

CREATE TABLE ventas (
    id BIGSERIAL PRIMARY KEY,
    numero_venta VARCHAR(50) UNIQUE NOT NULL,
    cliente_id BIGINT REFERENCES clientes(id) ON DELETE SET NULL,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    fecha_venta TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    tipo_venta VARCHAR(20) CHECK (tipo_venta IN ('contado', 'credito')) DEFAULT 'contado',
    tipo_documento VARCHAR(20) CHECK (tipo_documento IN ('factura', 'recibo', 'nota_venta')) DEFAULT 'nota_venta',
    metodo_pago VARCHAR(20) CHECK (metodo_pago IN ('efectivo', 'qr')) NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    descuento_porcentaje NUMERIC(5,2) DEFAULT 0 CHECK (descuento_porcentaje >= 0 AND descuento_porcentaje <= 100),
    descuento_monto NUMERIC(12,2) DEFAULT 0 CHECK (descuento_monto >= 0),
    iva NUMERIC(12,2) DEFAULT 0 CHECK (iva >= 0),
    total NUMERIC(12,2) NOT NULL CHECK (total >= 0),
    estado VARCHAR(20) CHECK (estado IN ('completada', 'anulada', 'devuelta')) DEFAULT 'completada',
    notas TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE ventas IS 'Registro de todas las ventas realizadas';
COMMENT ON COLUMN ventas.numero_venta IS 'Número correlativo único de venta (auto-generado)';
COMMENT ON COLUMN ventas.iva IS 'IVA 13% en Bolivia (solo si factura)';
COMMENT ON COLUMN ventas.tipo_documento IS 'Factura (con NIT), Recibo o Nota de venta simple';