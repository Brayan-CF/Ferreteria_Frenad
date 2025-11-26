-- ============================================================================
-- TABLA: DEVOLUCIONES DE VENTA
-- Devoluciones procesadas
-- ============================================================================

CREATE TABLE devoluciones_venta (
    id BIGSERIAL PRIMARY KEY,
    numero_devolucion VARCHAR(50) UNIQUE NOT NULL,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE RESTRICT,
    tipo_devolucion VARCHAR(20) CHECK (tipo_devolucion IN ('reembolso', 'cambio')) NOT NULL,
    fecha_devolucion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    total_devuelto NUMERIC(12,2) NOT NULL CHECK (total_devuelto >= 0),
    razon TEXT NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('procesada', 'anulada')) DEFAULT 'procesada',
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE devoluciones_venta IS 'Devoluciones: mismo día, producto en buen estado';