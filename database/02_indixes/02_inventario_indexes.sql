-- ============================================================================
-- ÍNDICES PARA INVENTARIO
-- ============================================================================

CREATE INDEX idx_inventario_producto ON inventario(producto_id);
CREATE INDEX idx_inventario_almacen ON inventario(almacen_id);
CREATE INDEX idx_inventario_stock_bajo ON inventario(cantidad_actual) WHERE cantidad_actual <= stock_minimo;