-- ============================================================================
-- SEEDERS: CATEGORÍAS DE PRODUCTOS
-- ============================================================================

INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Materiales de Construcción', 'Cemento, estuco, cemento cola, fierros de construcción', TRUE),
('Plomería', 'Tubos PVC, accesorios agua potable, pegamentos', TRUE),
('Soldadura', 'Planchas metálicas, tubos, costaneras, electrodos, equipos', TRUE),
('Herramientas Manuales', 'Martillos, desarmadores, alicates, llaves', TRUE),
('Elementos de Fijación', 'Clavos, tornillos, pernos, tuercas', TRUE),
('Seguridad Industrial', 'Guantes, cascos, gafas, mascarillas', TRUE),
('Eléctrico', 'Cables, interruptores, enchufes, cinta aislante', TRUE),
('Pintura y Acabados', 'Brochas, rodillos, lijas, espátulas', TRUE);

COMMENT ON TABLE categorias IS 'Categorías principales de productos';