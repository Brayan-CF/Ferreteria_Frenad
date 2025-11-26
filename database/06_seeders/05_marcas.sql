-- ============================================================================
-- SEEDERS: MARCAS DE PRODUCTOS
-- ============================================================================

INSERT INTO marcas (nombre, descripcion, pais_origen) VALUES
('EMISA', 'Cemento y materiales construcción', 'Bolivia'),
('Soboce', 'Cemento y productos construcción', 'Bolivia'),
('Viacha', 'Cemento', 'Bolivia'),
('Tigre', 'Tubería y accesorios PVC', 'Brasil'),
('Plasmar', 'Tubería PVC', 'Bolivia'),
('Indura', 'Equipos y materiales soldadura', 'Chile'),
('Lincoln Electric', 'Equipos soldadura profesional', 'USA'),
('Stanley', 'Herramientas manuales', 'USA'),
('Truper', 'Herramientas', 'México'),
('Pretul', 'Herramientas', 'México');

COMMENT ON TABLE marcas IS 'Marcas comunes en Bolivia';