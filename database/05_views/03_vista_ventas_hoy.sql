-- ============================================================================
-- VISTA: VENTAS DEL DÍA
-- ============================================================================

CREATE OR REPLACE VIEW vista_ventas_hoy AS
SELECT 
    v.id,
    v.numero_venta,
    v.fecha_venta,
    c.nombre_completo AS cliente,
    u.nombre AS vendedor,
    v.tipo_venta,
    v.metodo_pago,
    v.subtotal,
    v.descuento_monto,
    v.iva,
    v.total,
    v.estado
FROM ventas v
LEFT JOIN clientes c ON v.cliente_id = c.id
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE DATE(v.fecha_venta) = CURRENT_DATE
AND v.estado != 'anulada'
ORDER BY v.fecha_venta DESC;

COMMENT ON VIEW vista_ventas_hoy IS 'Todas las ventas realizadas hoy';