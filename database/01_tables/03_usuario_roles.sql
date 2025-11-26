-- ============================================================================
-- TABLA: USUARIO_ROLES
-- Relación N:N entre usuarios y roles
-- ============================================================================

CREATE TABLE usuario_roles (
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    rol_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    asignado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    asignado_por BIGINT REFERENCES usuarios(id),
    PRIMARY KEY (usuario_id, rol_id)
);

COMMENT ON TABLE usuario_roles IS 'Relación muchos a muchos: un usuario puede tener múltiples roles';