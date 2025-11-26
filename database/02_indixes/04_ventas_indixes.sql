-- ============================================================================
-- ÍNDICES PARA VENTAS Y DETALLE VENTAS
-- ============================================================================

-- ⚠️ CORRECCIÓN: Hacer UNIQUE el índice de numero_venta
CREATE UNIQUE INDEX idx_ventas_numero ON ventas(numero_venta);

CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);
CREATE INDEX idx_ventas_tipo ON ventas(tipo_venta);

-- Detalle Ventas
CREATE INDEX idx_detalle_ventas_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_ventas_producto ON detalle_ventas(producto_id);
CREATE INDEX idx_detalle_ventas_almacen ON detalle_ventas(almacen_id);