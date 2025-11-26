-- ============================================================================
-- TABLA: ROLES
-- Roles del sistema: Administrador, Vendedor, Bodeguero
-- ============================================================================

CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE roles IS 'Roles: Administrador, Vendedor, Bodeguero';