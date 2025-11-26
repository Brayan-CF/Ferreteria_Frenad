-- ============================================================================
-- FOREIGN KEYS DIFERIDAS
-- Se agregan después de crear todas las tablas
-- ============================================================================

-- ⚠️ CORRECCIÓN: Agregar FK de movimientos_inventario a compras y ventas
ALTER TABLE movimientos_inventario
ADD COLUMN compra_id BIGINT REFERENCES compras(id) ON DELETE SET NULL;

ALTER TABLE movimientos_inventario
ADD COLUMN venta_id BIGINT REFERENCES ventas(id) ON DELETE SET NULL;

-- ⚠️ CORRECCIÓN: Agregar FK a detalle_compras y detalle_ventas si se necesitan
ALTER TABLE movimientos_inventario
ADD COLUMN detalle_compra_id BIGINT REFERENCES detalle_compras(id) ON DELETE SET NULL;

ALTER TABLE movimientos_inventario
ADD COLUMN detalle_venta_id BIGINT REFERENCES detalle_ventas(id) ON DELETE SET NULL;

COMMENT ON COLUMN movimientos_inventario.compra_id IS 'Referencia a la compra que generó el movimiento';
COMMENT ON COLUMN movimientos_inventario.venta_id IS 'Referencia a la venta que generó el movimiento';