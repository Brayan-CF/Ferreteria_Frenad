-- ============================================================================
-- TABLA: LOGS DE AUDITORIA
-- Registro completo de cambios críticos
-- ============================================================================

CREATE TABLE logs_auditoria (
    id BIGSERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    registro_id BIGINT NOT NULL,
    accion VARCHAR(20) CHECK (accion IN ('INSERT', 'UPDATE', 'DELETE')) NOT NULL,
    valores_anteriores JSONB,
    valores_nuevos JSONB,
    usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    ip_address INET,
    user_agent TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE logs_auditoria IS 'Registro completo de cambios críticos en el sistema';
COMMENT ON COLUMN logs_auditoria.valores_anteriores IS 'Estado del registro antes del cambio (UPDATE/DELETE)';
COMMENT ON COLUMN logs_auditoria.valores_nuevos IS 'Estado del registro después del cambio (INSERT/UPDATE)';