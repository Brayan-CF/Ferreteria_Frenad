-- ============================================================================
-- TABLA: MOVIMIENTOS DE INVENTARIO (KARDEX)
-- ⚠️ MOVIDA AL FINAL PARA RESOLVER REFERENCIAS CIRCULARES
-- ============================================================================

CREATE TABLE movimientos_inventario (
    id BIGSERIAL PRIMARY KEY,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE RESTRICT,
    almacen_destino_id BIGINT REFERENCES almacenes(id) ON DELETE RESTRICT,
    tipo_movimiento VARCHAR(30) CHECK (tipo_movimiento IN (
        'ENTRADA_COMPRA',
        'SALIDA_VENTA',
        'TRANSFERENCIA',
        'AJUSTE_POSITIVO',
        'AJUSTE_NEGATIVO',
        'DEVOLUCION_VENTA',
        'DEVOLUCION_COMPRA'
    )) NOT NULL,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    razon TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE movimientos_inventario IS 'Kardex completo: cada cambio de stock queda registrado';
COMMENT ON COLUMN movimientos_inventario.almacen_destino_id IS 'Solo para TRANSFERENCIA (de Bodega a Mostrador)';
COMMENT ON COLUMN movimientos_inventario.razon IS 'Justificación obligatoria en ajustes (producto dañado, robo, error conteo)';