-- ============================================================================
-- TABLA: PROVEEDORES
-- Proveedores de materiales
-- ============================================================================

CREATE TABLE proveedores (
    id BIGSERIAL PRIMARY KEY,
    razon_social VARCHAR(200) NOT NULL,
    nit VARCHAR(50) UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    email VARCHAR(150),
    nombre_contacto VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro DATE DEFAULT CURRENT_DATE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE proveedores IS 'Proveedores de materiales y productos';