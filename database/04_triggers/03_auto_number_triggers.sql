-- ============================================================================
-- TRIGGERS DE AUTO-NUMERACIÓN
-- ============================================================================

-- Trigger: Calcular saldo de crédito
CREATE TRIGGER trigger_calcular_saldo_credito
    BEFORE INSERT OR UPDATE ON creditos_clientes
    FOR EACH ROW
    EXECUTE FUNCTION calcular_saldo_credito();

-- Trigger: Generar número de venta
CREATE TRIGGER trigger_generar_numero_venta
    BEFORE INSERT ON ventas
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_venta();

-- Trigger: Generar número de compra
CREATE TRIGGER trigger_generar_numero_compra
    BEFORE INSERT ON compras
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_compra();

-- Trigger: Generar número de orden de compra
CREATE TRIGGER trigger_generar_numero_orden
    BEFORE INSERT ON ordenes_compra
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_orden();

-- Trigger: Generar número de devolución
CREATE TRIGGER trigger_generar_numero_devolucion
    BEFORE INSERT ON devoluciones_venta
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_devolucion();