-- ============================================================================
-- FUNCIONES DE VALIDACIÓN
-- ============================================================================

-- Calcular saldo de crédito
CREATE OR REPLACE FUNCTION calcular_saldo_credito()
RETURNS TRIGGER AS $$
BEGIN
    NEW.saldo_pendiente = NEW.monto_total - NEW.monto_pagado;
    
    IF NEW.saldo_pendiente = 0 THEN
        NEW.estado = 'pagado_completo';
    ELSIF NEW.monto_pagado > 0 AND NEW.saldo_pendiente > 0 THEN
        NEW.estado = 'pagado_parcial';
    ELSIF NEW.fecha_vencimiento < CURRENT_DATE AND NEW.saldo_pendiente > 0 THEN
        NEW.estado = 'vencido';
    ELSE
        NEW.estado = 'pendiente';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION calcular_saldo_credito IS 'Calcula automáticamente el saldo pendiente y estado del crédito';

-- Generar número de venta
CREATE OR REPLACE FUNCTION generar_numero_venta()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_venta IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_venta FROM 10) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM ventas
    WHERE DATE(fecha_venta) = CURRENT_DATE;
    
    nuevo_numero = TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_venta = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Generar número de compra
CREATE OR REPLACE FUNCTION generar_numero_compra()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_compra IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_compra FROM 11) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM compras
    WHERE DATE(fecha_compra) = CURRENT_DATE;
    
    nuevo_numero = 'C-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_compra = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Generar número de orden
CREATE OR REPLACE FUNCTION generar_numero_orden()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_orden IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_orden FROM 12) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM ordenes_compra
    WHERE DATE(fecha_orden) = CURRENT_DATE;
    
    nuevo_numero = 'OC-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_orden = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Generar número de devolución
CREATE OR REPLACE FUNCTION generar_numero_devolucion()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_devolucion IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_devolucion FROM 12) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM devoluciones_venta
    WHERE DATE(fecha_devolucion) = CURRENT_DATE;
    
    nuevo_numero = 'DV-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_devolucion = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;