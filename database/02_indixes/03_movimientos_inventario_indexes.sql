-- ============================================================================
-- ÍNDICES PARA MOVIMIENTOS DE INVENTARIO
-- ============================================================================

CREATE INDEX idx_movimientos_inventario_producto ON movimientos_inventario(producto_id);
CREATE INDEX idx_movimientos_inventario_almacen ON movimientos_inventario(almacen_id);
CREATE INDEX idx_movimientos_inventario_tipo ON movimientos_inventario(tipo_movimiento);
CREATE INDEX idx_movimientos_inventario_fecha ON movimientos_inventario(creado_en);
CREATE INDEX idx_movimientos_inventario_usuario ON movimientos_inventario(usuario_id);
CREATE INDEX idx_movimientos_inventario_compra ON movimientos_inventario(compra_id) WHERE compra_id IS NOT NULL;
CREATE INDEX idx_movimientos_inventario_venta ON movimientos_inventario(venta_id) WHERE venta_id IS NOT NULL;