-- ============================================================================
-- TABLA: UNIDADES DE MEDIDA
-- Metro, pieza, kilogramo, rollo, caja, etc.
-- ============================================================================

CREATE TABLE unidades_medida (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    abreviatura VARCHAR(10) NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('longitud', 'peso', 'volumen', 'unidad', 'area')) NOT NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE unidades_medida IS 'Metro, pieza, kilogramo, rollo, caja, paquete, litro';