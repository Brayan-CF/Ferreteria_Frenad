-- ============================================================================
-- VISTA: PRODUCTOS CON STOCK BAJO
-- ============================================================================

CREATE OR REPLACE VIEW vista_productos_stock_bajo AS
SELECT 
    p.id,
    p.sku,
    p.nombre,
    c.nombre AS categoria,
    a.nombre AS almacen,
    i.cantidad_actual,
    i.stock_minimo,
    (i.stock_minimo - i.cantidad_actual) AS cantidad_reponer,
    p.precio_compra,
    ((i.stock_minimo - i.cantidad_actual) * p.precio_compra) AS costo_reposicion
FROM inventario i
INNER JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
INNER JOIN almacenes a ON i.almacen_id = a.id
WHERE i.cantidad_actual <= i.stock_minimo
AND p.activo = TRUE
ORDER BY (i.stock_minimo - i.cantidad_actual) DESC;

COMMENT ON VIEW vista_productos_stock_bajo IS 'Productos que necesitan reposición urgente';