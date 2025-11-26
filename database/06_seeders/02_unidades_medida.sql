-- ============================================================================
-- SEEDERS: UNIDADES DE MEDIDA
-- ============================================================================

INSERT INTO unidades_medida (nombre, abreviatura, tipo) VALUES
('Metro', 'm', 'longitud'),
('Pieza', 'pza', 'unidad'),
('Kilogramo', 'kg', 'peso'),
('Caja', 'cja', 'unidad'),
('Rollo', 'rollo', 'unidad'),
('Paquete', 'pqt', 'unidad'),
('Litro', 'l', 'volumen'),
('Metro cuadrado', 'm²', 'area'),
('Bolsa', 'bolsa', 'unidad'),
('Galón', 'gal', 'volumen'),
('Tubo', 'tubo', 'unidad'),
('Plancha', 'plancha', 'unidad'),
('Par', 'par', 'unidad'),
('Docena', 'doc', 'unidad');

COMMENT ON TABLE unidades_medida IS 'Unidades de medida estándar para productos';