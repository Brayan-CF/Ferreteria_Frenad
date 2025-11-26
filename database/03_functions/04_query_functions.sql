-- ============================================================================
-- FUNCIONES DE CONSULTA ÚTILES
-- ============================================================================

-- Función: Obtener stock total de un producto (todos los almacenes)
CREATE OR REPLACE FUNCTION obtener_stock_total_producto(p_producto_id BIGINT)
RETURNS NUMERIC AS $$
DECLARE
    v_stock_total NUMERIC;
BEGIN
    SELECT COALESCE(SUM(cantidad_actual), 0) INTO v_stock_total
    FROM inventario
    WHERE producto_id = p_producto_id;
    
    RETURN v_stock_total;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION obtener_stock_total_producto IS 'Retorna stock total de un producto sumando todos los almacenes';

-- Función: Verificar si producto tiene stock suficiente
CREATE OR REPLACE FUNCTION tiene_stock_suficiente(
    p_producto_id BIGINT,
    p_almacen_id BIGINT,
    p_cantidad_requerida NUMERIC
) RETURNS BOOLEAN AS $$
DECLARE
    v_stock_disponible NUMERIC;
BEGIN
    SELECT cantidad_actual INTO v_stock_disponible
    FROM inventario
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_id;
    
    RETURN COALESCE(v_stock_disponible, 0) >= p_cantidad_requerida;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION tiene_stock_suficiente IS 'Verifica si hay stock suficiente antes de vender';

-- Función: Obtener deuda total de un cliente
CREATE OR REPLACE FUNCTION obtener_deuda_cliente(p_cliente_id BIGINT)
RETURNS NUMERIC AS $$
DECLARE
    v_deuda_total NUMERIC;
BEGIN
    SELECT COALESCE(SUM(saldo_pendiente), 0) INTO v_deuda_total
    FROM creditos_clientes
    WHERE cliente_id = p_cliente_id
    AND estado IN ('pendiente', 'pagado_parcial', 'vencido');
    
    RETURN v_deuda_total;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION obtener_deuda_cliente IS 'Retorna deuda total actual de un cliente';

-- Función: Calcular utilidad de un período
CREATE OR REPLACE FUNCTION calcular_utilidad_periodo(
    p_fecha_inicio DATE,
    p_fecha_fin DATE
) RETURNS TABLE(
    total_ventas NUMERIC,
    costo_total NUMERIC,
    utilidad_bruta NUMERIC,
    margen_porcentaje NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COALESCE(SUM(v.total), 0) AS total_ventas,
        COALESCE(SUM(dv.cantidad * p.precio_compra), 0) AS costo_total,
        COALESCE(SUM(v.total) - SUM(dv.cantidad * p.precio_compra), 0) AS utilidad_bruta,
        CASE 
            WHEN SUM(dv.cantidad * p.precio_compra) > 0 
            THEN ROUND(((SUM(v.total) - SUM(dv.cantidad * p.precio_compra)) / SUM(dv.cantidad * p.precio_compra) * 100), 2)
            ELSE 0 
        END AS margen_porcentaje
    FROM ventas v
    INNER JOIN detalle_ventas dv ON v.id = dv.venta_id
    INNER JOIN productos p ON dv.producto_id = p.id
    WHERE DATE(v.fecha_venta) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND v.estado = 'completada';
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION calcular_utilidad_periodo IS 'Calcula utilidad bruta y margen de un período';

-- Función: Obtener productos próximos a vencer
CREATE OR REPLACE FUNCTION productos_proximos_vencer(p_dias INT DEFAULT 30)
RETURNS TABLE(
    producto_id BIGINT,
    sku VARCHAR,
    nombre VARCHAR,
    fecha_vencimiento DATE,
    dias_restantes INT,
    stock_total NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id,
        p.sku,
        p.nombre,
        p.fecha_vencimiento,
        (p.fecha_vencimiento - CURRENT_DATE)::INT AS dias_restantes,
        obtener_stock_total_producto(p.id) AS stock_total
    FROM productos p
    WHERE p.fecha_vencimiento IS NOT NULL
    AND p.fecha_vencimiento <= CURRENT_DATE + (p_dias || ' days')::INTERVAL
    AND p.fecha_vencimiento >= CURRENT_DATE
    AND p.activo = TRUE
    AND obtener_stock_total_producto(p.id) > 0
    ORDER BY p.fecha_vencimiento ASC;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION productos_proximos_vencer IS 'Lista productos con fecha de vencimiento próxima';