-- ============================================================================
-- TABLA: CLIENTES
-- Base de datos de clientes
-- ============================================================================

CREATE TABLE clientes (
    id BIGSERIAL PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    nit VARCHAR(50),
    telefono VARCHAR(20),
    direccion TEXT,
    email VARCHAR(150),
    es_frecuente BOOLEAN DEFAULT FALSE,
    limite_credito NUMERIC(12,2) DEFAULT 0 CHECK (limite_credito >= 0),
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro DATE DEFAULT CURRENT_DATE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE clientes IS 'Base de datos de clientes, especialmente frecuentes/caseros';
COMMENT ON COLUMN clientes.es_frecuente IS 'Cliente casero que merece descuentos y puede comprar a crédito';
COMMENT ON COLUMN clientes.limite_credito IS 'Monto máximo que puede deber';