-- ============================================================================
-- SEEDERS: DATOS DE EJEMPLO PARA TESTING
-- ============================================================================

-- Insertar productos de ejemplo
INSERT INTO productos (sku, nombre, descripcion, categoria_id, unidad_base_id, precio_compra, precio_venta, activo, creado_por) VALUES
('PROD-001', 'Cemento EMISA 50kg', 'Cemento gris para construcción', 1, 3, 45.00, 55.00, TRUE, 1),
('PROD-002', 'Tubo PVC 1/2" x 6m', 'Tubo PVC para agua potable', 2, 11, 18.50, 25.00, TRUE, 1),
('PROD-003', 'Alambre galvanizado rollo 100m', 'Alambre calibre 18', 7, 5, 85.00, 110.00, TRUE, 1),
('PROD-004', 'Martillo carpintero', 'Martillo mango fibra de vidrio', 4, 2, 35.00, 48.00, TRUE, 1),
('PROD-005', 'Plancha metálica 1.20x2.40m', 'Plancha acero calibre 18', 3, 12, 220.00, 280.00, TRUE, 1);

-- Insertar stock inicial en almacenes
INSERT INTO inventario (producto_id, almacen_id, cantidad_actual, stock_minimo) VALUES
(1, 2, 50, 10),  -- Cemento en Mostrador
(2, 2, 30, 8),   -- Tubos en Mostrador
(3, 1, 15, 3),   -- Alambre en Bodega
(4, 2, 25, 5),   -- Martillos en Mostrador
(5, 1, 40, 8);   -- Planchas en Bodega

-- Insertar clientes de ejemplo
INSERT INTO clientes (nombre_completo, telefono, nit, es_frecuente, limite_credito, creado_por) VALUES
('Juan Pérez Construcciones', '71234567', '1234567015', TRUE, 5000.00, 1),
('María García', '72345678', NULL, FALSE, 0, 1);

-- Insertar proveedores de ejemplo
INSERT INTO proveedores (razon_social, nit, telefono, direccion, email, creado_por) VALUES
('Distribuidora La Paz SRL', '1023456789', '2-2234567', 'Av. Buenos Aires #1234', 'ventas@distlapaz.com', 1),
('Importadora Materiales SA', '9876543210', '2-2345678', 'Zona 16 de Julio', 'contacto@importmat.bo', 1);

COMMENT ON TABLE productos IS 'Datos de ejemplo para testing inicial';