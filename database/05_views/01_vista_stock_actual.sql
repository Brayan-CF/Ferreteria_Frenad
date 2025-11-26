-- ============================================================================
-- VISTA: STOCK ACTUAL CONSOLIDADO
-- ============================================================================

CREATE OR REPLACE VIEW vista_stock_actual AS
SELECT 
    p.id AS producto_id,
    p.sku,
    p.codigo_barras,
    p.nombre AS producto_nombre,
    c.nombre AS categoria_nombre,
    m.nombre AS marca_nombre,
    um.nombre AS unidad_medida,
    a.nombre AS almacen_nombre,
    i.cantidad_actual,
    i.stock_minimo,
    CASE 
        WHEN i.cantidad_actual <= i.stock_minimo THEN 'BAJO'
        WHEN i.cantidad_actual <= (i.stock_minimo * 1.5) THEN 'MEDIO'
        ELSE 'NORMAL'
    END AS nivel_stock,
    p.precio_compra,
    p.precio_venta,
    (i.cantidad_actual * p.precio_compra) AS valor_inventario,
    p.activo
FROM inventario i
INNER JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
INNER JOIN unidades_medida um ON p.unidad_base_id = um.id
INNER JOIN almacenes a ON i.almacen_id = a.id
WHERE p.activo = TRUE
ORDER BY p.nombre, a.nombre;

COMMENT ON VIEW vista_stock_actual IS 'Muestra stock actual de todos los productos por almacén con niveles de alerta';