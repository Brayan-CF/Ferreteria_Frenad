-- ============================================================================
-- SEEDERS: ALMACENES
-- ============================================================================

INSERT INTO almacenes (nombre, tipo, ubicacion) VALUES
('Bodega Principal', 'bodega', 'Área trasera - Materiales pesados (planchas, fierros, tubos soldadura)'),
('Mostrador Venta', 'mostrador', 'Área frontal - Estantes con productos de alta rotación');

COMMENT ON TABLE almacenes IS 'Almacenes: Bodega y Mostrador';