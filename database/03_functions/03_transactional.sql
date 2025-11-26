-- ============================================================================
-- FUNCIONES TRANSACCIONALES
-- Operaciones complejas con múltiples pasos
-- ============================================================================

-- Función: Procesar venta completa (atomicidad)
CREATE OR REPLACE FUNCTION procesar_venta(
    p_cliente_id BIGINT,
    p_usuario_id BIGINT,
    p_tipo_venta VARCHAR(20),
    p_metodo_pago VARCHAR(20),
    p_items JSONB -- Array de items: [{producto_id, almacen_id, cantidad, precio_unitario, descuento}]
) RETURNS BIGINT AS $$
DECLARE
    v_venta_id BIGINT;
    v_subtotal NUMERIC := 0;
    v_total_descuento NUMERIC := 0;
    v_iva NUMERIC := 0;
    v_total NUMERIC := 0;
    v_item JSONB;
    v_item_subtotal NUMERIC;
BEGIN
    -- Calcular totales
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC - COALESCE((v_item->>'descuento')::NUMERIC, 0);
        v_subtotal := v_subtotal + v_item_subtotal;
        v_total_descuento := v_total_descuento + COALESCE((v_item->>'descuento')::NUMERIC, 0);
    END LOOP;
    
    v_total := v_subtotal;
    
    -- Crear venta
    INSERT INTO ventas (
        cliente_id, usuario_id, tipo_venta, metodo_pago,
        subtotal, descuento_monto, iva, total, creado_por
    ) VALUES (
        p_cliente_id, p_usuario_id, p_tipo_venta, p_metodo_pago,
        v_subtotal, v_total_descuento, v_iva, v_total, p_usuario_id
    ) RETURNING id INTO v_venta_id;
    
    -- Insertar detalles
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC - COALESCE((v_item->>'descuento')::NUMERIC, 0);
        
        INSERT INTO detalle_ventas (
            venta_id, producto_id, almacen_id, cantidad, unidad_id,
            precio_unitario, descuento_monto, subtotal, creado_por
        ) VALUES (
            v_venta_id,
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC,
            (v_item->>'unidad_id')::BIGINT,
            (v_item->>'precio_unitario')::NUMERIC,
            COALESCE((v_item->>'descuento')::NUMERIC, 0),
            v_item_subtotal,
            p_usuario_id
        );
        
        -- Actualizar inventario
        UPDATE inventario SET 
            cantidad_actual = cantidad_actual - (v_item->>'cantidad')::NUMERIC,
            ultima_actualizacion = CURRENT_TIMESTAMP
        WHERE producto_id = (v_item->>'producto_id')::BIGINT
        AND almacen_id = (v_item->>'almacen_id')::BIGINT;
        
        -- Registrar movimiento
        INSERT INTO movimientos_inventario (
            producto_id, almacen_id, tipo_movimiento, cantidad,
            usuario_id
        ) VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_id')::BIGINT,
            'SALIDA_VENTA',
            (v_item->>'cantidad')::NUMERIC,
            p_usuario_id
        );
    END LOOP;
    
    -- Si es venta a crédito, crear registro de crédito
    IF p_tipo_venta = 'credito' THEN
        INSERT INTO creditos_clientes (
            cliente_id, venta_id, monto_total, monto_pagado,
            saldo_pendiente, fecha_vencimiento
        ) VALUES (
            p_cliente_id, v_venta_id, v_total, 0,
            v_total, CURRENT_DATE + INTERVAL '30 days'
        );
    END IF;
    
    RETURN v_venta_id;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION procesar_venta IS 'Procesa una venta completa de forma atómica con actualización de inventario';

-- Función: Procesar compra completa
CREATE OR REPLACE FUNCTION procesar_compra(
    p_proveedor_id BIGINT,
    p_usuario_id BIGINT,
    p_orden_compra_id BIGINT,
    p_numero_factura VARCHAR(100),
    p_items JSONB -- Array: [{producto_id, cantidad, unidad_id, precio_unitario, almacen_destino_id}]
) RETURNS BIGINT AS $$
DECLARE
    v_compra_id BIGINT;
    v_subtotal NUMERIC := 0;
    v_total NUMERIC := 0;
    v_item JSONB;
    v_item_subtotal NUMERIC;
BEGIN
    -- Calcular totales
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC;
        v_subtotal := v_subtotal + v_item_subtotal;
    END LOOP;
    
    v_total := v_subtotal;
    
    -- Crear compra
    INSERT INTO compras (
        proveedor_id, usuario_id, orden_compra_id, numero_factura_proveedor,
        fecha_entrega_real, subtotal, total, estado, creado_por
    ) VALUES (
        p_proveedor_id, p_usuario_id, p_orden_compra_id, p_numero_factura,
        CURRENT_DATE, v_subtotal, v_total, 'recibida', p_usuario_id
    ) RETURNING id INTO v_compra_id;
    
    -- Insertar detalles
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC;
        
        INSERT INTO detalle_compras (
            compra_id, producto_id, cantidad, unidad_id,
            precio_unitario, subtotal, almacen_destino_id, creado_por
        ) VALUES (
            v_compra_id,
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC,
            (v_item->>'unidad_id')::BIGINT,
            (v_item->>'precio_unitario')::NUMERIC,
            v_item_subtotal,
            (v_item->>'almacen_destino_id')::BIGINT,
            p_usuario_id
        );
        
        -- Actualizar inventario
        INSERT INTO inventario (producto_id, almacen_id, cantidad_actual)
        VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_destino_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC
        )
        ON CONFLICT (producto_id, almacen_id) DO UPDATE
        SET cantidad_actual = inventario.cantidad_actual + (v_item->>'cantidad')::NUMERIC,
            ultima_actualizacion = CURRENT_TIMESTAMP;
        
        -- Registrar movimiento
        INSERT INTO movimientos_inventario (
            producto_id, almacen_id, tipo_movimiento, cantidad, usuario_id
        ) VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_destino_id')::BIGINT,
            'ENTRADA_COMPRA',
            (v_item->>'cantidad')::NUMERIC,
            p_usuario_id
        );
    END LOOP;
    
    -- Actualizar orden de compra si existe
    IF p_orden_compra_id IS NOT NULL THEN
        UPDATE ordenes_compra
        SET estado = 'recibida_completa'
        WHERE id = p_orden_compra_id;
    END IF;
    
    RETURN v_compra_id;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION procesar_compra IS 'Procesa una compra completa con actualización automática de inventario';

-- Función: Transferir producto entre almacenes
CREATE OR REPLACE FUNCTION transferir_producto(
    p_producto_id BIGINT,
    p_almacen_origen_id BIGINT,
    p_almacen_destino_id BIGINT,
    p_cantidad NUMERIC,
    p_usuario_id BIGINT,
    p_razon TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    v_stock_origen NUMERIC;
BEGIN
    -- Verificar stock en origen
    SELECT cantidad_actual INTO v_stock_origen
    FROM inventario
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_origen_id;
    
    IF v_stock_origen IS NULL OR v_stock_origen < p_cantidad THEN
        RAISE EXCEPTION 'Stock insuficiente en almacén origen. Disponible: %, Solicitado: %', 
            COALESCE(v_stock_origen, 0), p_cantidad;
    END IF;
    
    -- Reducir stock en origen
    UPDATE inventario
    SET cantidad_actual = cantidad_actual - p_cantidad,
        ultima_actualizacion = CURRENT_TIMESTAMP
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_origen_id;
    
    -- Aumentar stock en destino
    INSERT INTO inventario (producto_id, almacen_id, cantidad_actual)
    VALUES (p_producto_id, p_almacen_destino_id, p_cantidad)
    ON CONFLICT (producto_id, almacen_id) DO UPDATE
    SET cantidad_actual = inventario.cantidad_actual + p_cantidad,
        ultima_actualizacion = CURRENT_TIMESTAMP;
    
    -- Registrar movimiento
    INSERT INTO movimientos_inventario (
        producto_id, almacen_id, almacen_destino_id,
        tipo_movimiento, cantidad, usuario_id, razon
    ) VALUES (
        p_producto_id, p_almacen_origen_id, p_almacen_destino_id,
        'TRANSFERENCIA', p_cantidad, p_usuario_id, p_razon
    );
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION transferir_producto IS 'Transfiere producto de un almacén a otro (ej: Bodega → Mostrador)';

-- Función: Registrar pago de crédito
CREATE OR REPLACE FUNCTION registrar_pago_credito(
    p_credito_id BIGINT,
    p_monto_pago NUMERIC,
    p_metodo_pago VARCHAR(20),
    p_usuario_id BIGINT,
    p_notas TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    v_saldo_actual NUMERIC;
BEGIN
    -- Obtener saldo actual
    SELECT saldo_pendiente INTO v_saldo_actual
    FROM creditos_clientes
    WHERE id = p_credito_id;
    
    IF v_saldo_actual IS NULL THEN
        RAISE EXCEPTION 'Crédito no encontrado';
    END IF;
    
    IF p_monto_pago > v_saldo_actual THEN
        RAISE EXCEPTION 'Monto de pago (%) excede saldo pendiente (%)', p_monto_pago, v_saldo_actual;
    END IF;
    
    -- Registrar pago
    INSERT INTO pagos_credito (
        credito_cliente_id, monto_pago, metodo_pago,
        notas, usuario_id, creado_por
    ) VALUES (
        p_credito_id, p_monto_pago, p_metodo_pago,
        p_notas, p_usuario_id, p_usuario_id
    );
    
    -- Actualizar crédito
    UPDATE creditos_clientes
    SET monto_pagado = monto_pagado + p_monto_pago,
        saldo_pendiente = saldo_pendiente - p_monto_pago
    WHERE id = p_credito_id;
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION registrar_pago_credito IS 'Registra un pago parcial o total de un crédito';

-- Función: Ajustar inventario
CREATE OR REPLACE FUNCTION ajustar_inventario(
    p_producto_id BIGINT,
    p_almacen_id BIGINT,
    p_nueva_cantidad NUMERIC,
    p_razon TEXT,
    p_usuario_id BIGINT
) RETURNS BOOLEAN AS $$
DECLARE
    v_cantidad_actual NUMERIC;
    v_diferencia NUMERIC;
    v_tipo_movimiento VARCHAR(30);
BEGIN
    -- Obtener cantidad actual
    SELECT cantidad_actual INTO v_cantidad_actual
    FROM inventario
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_id;
    
    IF v_cantidad_actual IS NULL THEN
        RAISE EXCEPTION 'Producto no existe en el inventario del almacén especificado';
    END IF;
    
    -- Calcular diferencia
    v_diferencia := p_nueva_cantidad - v_cantidad_actual;
    
    IF v_diferencia = 0 THEN
        RAISE EXCEPTION 'La nueva cantidad es igual a la actual. No hay nada que ajustar.';
    END IF;
    
    -- Determinar tipo de movimiento
    IF v_diferencia > 0 THEN
        v_tipo_movimiento := 'AJUSTE_POSITIVO';
    ELSE
        v_tipo_movimiento := 'AJUSTE_NEGATIVO';
        v_diferencia := ABS(v_diferencia);
    END IF;
    
    -- Actualizar inventario
    UPDATE inventario
    SET cantidad_actual = p_nueva_cantidad,
        ultima_actualizacion = CURRENT_TIMESTAMP
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_id;
    
    -- Registrar movimiento (la razón es OBLIGATORIA)
    INSERT INTO movimientos_inventario (
        producto_id, almacen_id, tipo_movimiento,
        cantidad, razon, usuario_id
    ) VALUES (
        p_producto_id, p_almacen_id, v_tipo_movimiento,
        v_diferencia, p_razon, p_usuario_id
    );
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION ajustar_inventario IS 'Ajusta el inventario con justificación obligatoria (error conteo, robo, daño)';

-- Función: Cerrar caja
CREATE OR REPLACE FUNCTION cerrar_caja(
    p_arqueo_id BIGINT,
    p_monto_final NUMERIC,
    p_usuario_id BIGINT,
    p_notas TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    v_monto_inicial NUMERIC;
    v_total_ventas_efectivo NUMERIC;
    v_total_ventas_qr NUMERIC;
    v_total_esperado NUMERIC;
    v_diferencia NUMERIC;
BEGIN
    -- Obtener datos del arqueo
    SELECT monto_inicial INTO v_monto_inicial
    FROM arqueos_caja
    WHERE id = p_arqueo_id
    AND estado = 'abierta';
    
    IF v_monto_inicial IS NULL THEN
        RAISE EXCEPTION 'Arqueo no encontrado o ya está cerrado';
    END IF;
    
    -- Calcular ventas del día por método de pago
    SELECT 
        COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END), 0),
        COALESCE(SUM(CASE WHEN metodo_pago = 'qr' THEN total ELSE 0 END), 0)
    INTO v_total_ventas_efectivo, v_total_ventas_qr
    FROM ventas
    WHERE DATE(fecha_venta) = (SELECT DATE(fecha_apertura) FROM arqueos_caja WHERE id = p_arqueo_id)
    AND estado = 'completada';
    
    -- Calcular total esperado
    v_total_esperado := v_monto_inicial + v_total_ventas_efectivo;
    
    -- Calcular diferencia
    v_diferencia := p_monto_final - v_total_esperado;
    
    -- Actualizar arqueo
    UPDATE arqueos_caja
    SET fecha_cierre = CURRENT_TIMESTAMP,
        usuario_cierre_id = p_usuario_id,
        monto_final = p_monto_final,
        total_ventas_efectivo = v_total_ventas_efectivo,
        total_ventas_qr = v_total_ventas_qr,
        total_esperado = v_total_esperado,
        diferencia = v_diferencia,
        estado = 'cerrada',
        notas_cierre = p_notas
    WHERE id = p_arqueo_id;
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION cerrar_caja IS 'Cierra el arqueo de caja del día calculando diferencias';