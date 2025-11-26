-- ============================================================================
-- TABLA: USUARIOS
-- Empleados y administradores del sistema
-- ============================================================================

CREATE TABLE usuarios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE usuarios IS 'Empleados y administradores del sistema';
COMMENT ON COLUMN usuarios.password_hash IS 'Hash bcrypt de la contraseña';