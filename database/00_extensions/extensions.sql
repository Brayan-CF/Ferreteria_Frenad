-- ============================================================================
-- EXTENSIONES NECESARIAS PARA POSTGRESQL
-- ============================================================================

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

COMMENT ON EXTENSION "uuid-ossp" IS 'Generación de UUIDs únicos';