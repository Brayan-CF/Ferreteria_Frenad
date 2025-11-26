-- ============================================================================
-- TABLA: CATEGORIAS
-- Categorías de productos
-- ============================================================================

CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE categorias IS 'Categorías: Construcción, Plomería, Soldadura, Herramientas';