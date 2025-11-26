-- ============================================================================
-- ÍNDICES PARA COMPRAS
-- ============================================================================

CREATE INDEX idx_compras_proveedor ON compras(proveedor_id);
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_compras_estado ON compras(estado);
CREATE INDEX idx_compras_usuario ON compras(usuario_id);
CREATE INDEX idx_compras_numero ON compras(numero_compra);
CREATE INDEX idx_ordenes_compra_proveedor ON ordenes_compra(proveedor_id);
CREATE INDEX idx_ordenes_compra_estado ON ordenes_compra(estado);