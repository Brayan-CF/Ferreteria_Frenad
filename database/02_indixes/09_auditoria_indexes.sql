-- ============================================================================
-- ÍNDICES PARA AUDITORÍA
-- ============================================================================

CREATE INDEX idx_logs_tabla ON logs_auditoria(tabla_afectada);
CREATE INDEX idx_logs_registro ON logs_auditoria(registro_id);
CREATE INDEX idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX idx_logs_fecha ON logs_auditoria(creado_en);
CREATE INDEX idx_logs_accion ON logs_auditoria(accion);