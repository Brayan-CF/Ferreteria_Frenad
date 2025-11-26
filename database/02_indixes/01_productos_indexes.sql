-- ============================================================================
-- ÍNDICES PARA PRODUCTOS
-- ============================================================================

CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_marca ON productos(marca_id);
CREATE INDEX idx_productos_sku ON productos(sku);
CREATE INDEX idx_productos_codigo_barras ON productos(codigo_barras) WHERE codigo_barras IS NOT NULL;
CREATE INDEX idx_productos_activo ON productos(activo) WHERE activo = TRUE;
CREATE INDEX idx_productos_nombre ON productos USING gin(to_tsvector('spanish', nombre));