-- ============================================================================
-- VISTA: UTILIDAD POR VENTA
-- ============================================================================

CREATE OR REPLACE VIEW vista_utilidad_ventas AS
SELECT 
    v.id AS venta_id,
    v.numero_venta,
    v.fecha_venta,
    u.nombre AS vendedor,
    v.total AS total_venta,
    SUM(dv.cantidad * p.precio_compra) AS costo_total,
    v.total - SUM(dv.cantidad * p.precio_compra) AS utilidad_bruta,
    CASE 
        WHEN SUM(dv.cantidad * p.precio_compra) > 0 
        THEN ROUND(((v.total - SUM(dv.cantidad * p.precio_compra)) / SUM(dv.cantidad * p.precio_compra) * 100), 2)
        ELSE 0 
    END AS margen_porcentaje
FROM ventas v
INNER JOIN detalle_ventas dv ON v.id = dv.venta_id
INNER JOIN productos p ON dv.producto_id = p.id
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE v.estado = 'completada'
GROUP BY v.id, v.numero_venta, v.fecha_venta, u.nombre, v.total
ORDER BY v.fecha_venta DESC;

COMMENT ON VIEW vista_utilidad_ventas IS 'Cálculo de utilidad y margen por venta';