-- ============================================================================
-- SEEDERS: ROLES DEL SISTEMA
-- ============================================================================

INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema, gestión de usuarios, cierre de caja'),
('Vendedor', 'Realiza ventas, consulta inventario, genera reportes básicos'),
('Bodeguero', 'Gestiona compras, recibe mercadería, hace transferencias de inventario');

COMMENT ON TABLE roles IS 'Roles predefinidos del sistema';