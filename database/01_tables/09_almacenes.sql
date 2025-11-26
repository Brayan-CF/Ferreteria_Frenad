-- ============================================================================
-- TABLA: ALMACENES
-- Bodega y Mostrador
-- ============================================================================

CREATE TABLE almacenes (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('bodega', 'mostrador', 'otro')) NOT NULL,
    ubicacion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE almacenes IS 'Bodega (materiales pesados) y Mostrador (área venta)';