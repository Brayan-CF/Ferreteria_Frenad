-- ============================================================================
-- SEEDERS: USUARIO ADMINISTRADOR INICIAL
-- ============================================================================

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES
('Administrador Sistema', 'admin@frenad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Password: password (CAMBIAR EN PRODUCCIÓN)

-- Asignar rol administrador al usuario creado
INSERT INTO usuario_roles (usuario_id, rol_id) VALUES
(1, 1);

COMMENT ON TABLE usuarios IS 'Usuario admin inicial: admin@frenad.com / password';