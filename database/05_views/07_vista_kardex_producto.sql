-- ============================================================================
-- VISTA: KARDEX SIMPLIFICADO POR PRODUCTO
-- ============================================================================

CREATE OR REPLACE VIEW vista_kardex_producto AS
SELECT 
    mi.id,
    mi.creado_en AS fecha_movimiento,
    p.sku,
    p.nombre AS producto,
    a.nombre AS almacen,
    a_destino.nombre AS almacen_destino,
    mi.tipo_movimiento,
    mi.cantidad,
    u.nombre AS usuario,
    mi.razon,
    CASE 
        WHEN mi.tipo_movimiento IN ('ENTRADA_COMPRA', 'AJUSTE_POSITIVO', 'DEVOLUCION_VENTA') THEN '+'
        WHEN mi.tipo_movimiento IN ('SALIDA_VENTA', 'AJUSTE_NEGATIVO', 'DEVOLUCION_COMPRA') THEN '-'
        ELSE '~'
    END AS signo
FROM movimientos_inventario mi
INNER JOIN productos p ON mi.producto_id = p.id
INNER JOIN almacenes a ON mi.almacen_id = a.id
LEFT JOIN almacenes a_destino ON mi.almacen_destino_id = a_destino.id
INNER JOIN usuarios u ON mi.usuario_id = u.id
ORDER BY mi.creado_en DESC;

COMMENT ON VIEW vista_kardex_producto IS 'Historial completo de movimientos de inventario';