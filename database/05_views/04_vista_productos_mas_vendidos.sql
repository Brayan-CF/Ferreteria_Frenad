-- ============================================================================
-- VISTA: PRODUCTOS MÁS VENDIDOS
-- ============================================================================

CREATE OR REPLACE VIEW vista_productos_mas_vendidos AS
SELECT 
    p.id,
    p.sku,
    p.nombre,
    c.nombre AS categoria,
    COUNT(dv.id) AS veces_vendido,
    SUM(dv.cantidad) AS cantidad_total_vendida,
    SUM(dv.subtotal) AS ingresos_totales,
    AVG(dv.precio_unitario) AS precio_promedio
FROM detalle_ventas dv
INNER JOIN productos p ON dv.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
INNER JOIN ventas v ON dv.venta_id = v.id
WHERE v.estado = 'completada'
AND v.fecha_venta >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY p.id, p.sku, p.nombre, c.nombre
ORDER BY cantidad_total_vendida DESC
LIMIT 50;

COMMENT ON VIEW vista_productos_mas_vendidos IS 'Top 50 productos más vendidos últimos 30 días';