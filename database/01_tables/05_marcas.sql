-- ============================================================================
-- TABLA: MARCAS
-- Marcas/Fabricantes de productos
-- ============================================================================

CREATE TABLE marcas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    pais_origen VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE marcas IS 'Marcas de productos (cemento, herramientas, etc)';