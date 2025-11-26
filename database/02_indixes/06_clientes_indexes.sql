-- ============================================================================
-- ÍNDICES PARA CLIENTES
-- ============================================================================

CREATE INDEX idx_clientes_nit ON clientes(nit) WHERE nit IS NOT NULL;
CREATE INDEX idx_clientes_frecuente ON clientes(es_frecuente) WHERE es_frecuente = TRUE;
CREATE INDEX idx_clientes_nombre ON clientes USING gin(to_tsvector('spanish', nombre_completo));