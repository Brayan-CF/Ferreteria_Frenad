-- ============================================================================
-- TRIGGERS DE VALIDACIÓN
-- ============================================================================

-- Función para validar stock antes de venta
CREATE OR REPLACE FUNCTION validar_stock_venta()
RETURNS TRIGGER AS $$
DECLARE
    stock_disponible NUMERIC;
BEGIN
    SELECT cantidad_actual INTO stock_disponible
    FROM inventario
    WHERE producto_id = NEW.producto_id 
    AND almacen_id = NEW.almacen_id;
    
    IF stock_disponible IS NULL OR stock_disponible < NEW.cantidad THEN
        RAISE EXCEPTION 'Stock insuficiente. Disponible: %, Solicitado: %', 
            COALESCE(stock_disponible, 0), NEW.cantidad;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ⚠️ CORRECCIÓN: Agregar validación en UPDATE también
CREATE TRIGGER trigger_validar_stock_venta_insert
    BEFORE INSERT ON detalle_ventas
    FOR EACH ROW
    EXECUTE FUNCTION validar_stock_venta();

CREATE TRIGGER trigger_validar_stock_venta_update
    BEFORE UPDATE ON detalle_ventas
    FOR EACH ROW
    WHEN (OLD.cantidad IS DISTINCT FROM NEW.cantidad OR OLD.almacen_id IS DISTINCT FROM NEW.almacen_id)
    EXECUTE FUNCTION validar_stock_venta();