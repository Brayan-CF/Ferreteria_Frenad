-- ============================================================================
-- FUNCIÓN: ACTUALIZAR TIMESTAMP AUTOMÁTICAMENTE
-- ============================================================================

CREATE OR REPLACE FUNCTION actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION actualizar_timestamp IS 'Actualiza automáticamente el campo actualizado_en';