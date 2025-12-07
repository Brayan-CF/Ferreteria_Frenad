-- ============================================================================
-- TABLA: personal_access_tokens (Laravel Sanctum)
-- Descripción: Almacena tokens de autenticación API
-- Autor: Sistema
-- Fecha: 2025-12-06
-- ============================================================================

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMPTZ,
    expires_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- ÍNDICES
-- ============================================================================

CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index 
ON personal_access_tokens (tokenable_type, tokenable_id);

-- ============================================================================
-- COMENTARIOS
-- ============================================================================

COMMENT ON TABLE personal_access_tokens IS 'Tokens de autenticación de Laravel Sanctum';
COMMENT ON COLUMN personal_access_tokens.id IS 'ID único del token';
COMMENT ON COLUMN personal_access_tokens.tokenable_type IS 'Tipo de modelo (Modules\Auth\Models\Usuario)';
COMMENT ON COLUMN personal_access_tokens.tokenable_id IS 'ID del usuario propietario del token';
COMMENT ON COLUMN personal_access_tokens.name IS 'Nombre descriptivo del token (ej: auth_token)';
COMMENT ON COLUMN personal_access_tokens.token IS 'Hash del token (64 caracteres)';
COMMENT ON COLUMN personal_access_tokens.abilities IS 'Permisos del token en formato JSON';
COMMENT ON COLUMN personal_access_tokens.last_used_at IS 'Última vez que se usó el token';
COMMENT ON COLUMN personal_access_tokens.expires_at IS 'Fecha de expiración del token';
COMMENT ON COLUMN personal_access_tokens.created_at IS 'Fecha de creación';
COMMENT ON COLUMN personal_access_tokens.updated_at IS 'Fecha de última actualización';
