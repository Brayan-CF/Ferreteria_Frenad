-- ============================================================================
-- SISTEMA POS FERRETERÍA FRENAD
-- Base de Datos PostgreSQL 14+
-- Ubicación: El Alto, La Paz - Bolivia
-- Zona Horaria: America/La_Paz
-- ============================================================================

-- Habilitar extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================================
-- MÓDULO: GESTIÓN DE USUARIOS Y PERMISOS
-- ============================================================================

-- Tabla de Usuarios del Sistema
CREATE TABLE usuarios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE usuarios IS 'Empleados y administradores del sistema';
COMMENT ON COLUMN usuarios.password_hash IS 'Hash bcrypt de la contraseña';

-- Tabla de Roles
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE roles IS 'Roles: Administrador, Vendedor, Bodeguero';

-- Tabla intermedia Usuarios-Roles (N:N)
CREATE TABLE usuario_roles (
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    rol_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    asignado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    asignado_por BIGINT REFERENCES usuarios(id),
    PRIMARY KEY (usuario_id, rol_id)
);

COMMENT ON TABLE usuario_roles IS 'Relación muchos a muchos: un usuario puede tener múltiples roles';

-- ============================================================================
-- MÓDULO: CATÁLOGO DE PRODUCTOS
-- ============================================================================

-- Tabla de Categorías
CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE categorias IS 'Categorías: Construcción, Plomería, Soldadura, Herramientas';

-- Tabla de Marcas/Fabricantes
CREATE TABLE marcas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    pais_origen VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE marcas IS 'Marcas de productos (cemento, herramientas, etc)';

-- Tabla de Unidades de Medida
CREATE TABLE unidades_medida (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    abreviatura VARCHAR(10) NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('longitud', 'peso', 'volumen', 'unidad', 'area')) NOT NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE unidades_medida IS 'Metro, pieza, kilogramo, rollo, caja, paquete, litro';

-- Tabla de Productos (Inventario)
CREATE TABLE productos (
    id BIGSERIAL PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id BIGINT REFERENCES categorias(id) ON DELETE SET NULL,
    marca_id BIGINT REFERENCES marcas(id) ON DELETE SET NULL,
    unidad_base_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_compra NUMERIC(12,2) NOT NULL CHECK (precio_compra >= 0),
    precio_venta NUMERIC(12,2) NOT NULL CHECK (precio_venta >= 0),
    fecha_vencimiento DATE,
    ubicacion_fisica VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE productos IS 'Catálogo completo de productos de la ferretería';
COMMENT ON COLUMN productos.sku IS 'Código interno único del producto';
COMMENT ON COLUMN productos.unidad_base_id IS 'Unidad mínima indivisible (ej: metro para cables)';
COMMENT ON COLUMN productos.fecha_vencimiento IS 'Solo para pegamentos y productos perecederos';
COMMENT ON COLUMN productos.ubicacion_fisica IS 'Pasillo/estante aproximado';

-- Tabla de Conversiones de Unidades por Producto
CREATE TABLE producto_unidades (
    id BIGSERIAL PRIMARY KEY,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    factor_conversion NUMERIC(10,4) NOT NULL CHECK (factor_conversion > 0),
    es_unidad_compra BOOLEAN DEFAULT FALSE,
    es_unidad_venta BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (producto_id, unidad_id)
);

COMMENT ON TABLE producto_unidades IS 'Permite vender cable por metro pero comprar en rollos de 100m';
COMMENT ON COLUMN producto_unidades.factor_conversion IS 'Ej: 1 rollo = 100 metros (factor: 100)';

-- ============================================================================
-- MÓDULO: GESTIÓN DE ALMACENES E INVENTARIO
-- ============================================================================

-- Tabla de Almacenes
CREATE TABLE almacenes (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('bodega', 'mostrador', 'otro')) NOT NULL,
    ubicacion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE almacenes IS 'Bodega (materiales pesados) y Mostrador (área venta)';

-- Tabla de Stock por Almacén
CREATE TABLE inventario (
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE CASCADE,
    cantidad_actual NUMERIC(12,4) NOT NULL DEFAULT 0 CHECK (cantidad_actual >= 0),
    stock_minimo NUMERIC(12,4) NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    ultima_actualizacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, almacen_id)
);

COMMENT ON TABLE inventario IS 'Stock separado por almacén (Bodega: 50 tubos, Mostrador: 20 tubos)';
COMMENT ON COLUMN inventario.cantidad_actual IS 'Stock disponible en unidades base del producto';

-- Tabla de Movimientos de Inventario (KARDEX)
CREATE TABLE movimientos_inventario (
    id BIGSERIAL PRIMARY KEY,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE RESTRICT,
    almacen_destino_id BIGINT REFERENCES almacenes(id) ON DELETE RESTRICT,
    tipo_movimiento VARCHAR(30) CHECK (tipo_movimiento IN (
        'ENTRADA_COMPRA',
        'SALIDA_VENTA',
        'TRANSFERENCIA',
        'AJUSTE_POSITIVO',
        'AJUSTE_NEGATIVO',
        'DEVOLUCION_VENTA',
        'DEVOLUCION_COMPRA'
    )) NOT NULL,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    compra_id BIGINT REFERENCES compras(id) ON DELETE SET NULL,
    venta_id BIGINT REFERENCES ventas(id) ON DELETE SET NULL,
    detalle_compra_id BIGINT,
    detalle_venta_id BIGINT,
    razon TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE movimientos_inventario IS 'Kardex completo: cada cambio de stock queda registrado';
COMMENT ON COLUMN movimientos_inventario.almacen_destino_id IS 'Solo para TRANSFERENCIA (de Bodega a Mostrador)';
COMMENT ON COLUMN movimientos_inventario.razon IS 'Justificación obligatoria en ajustes (producto dañado, robo, error conteo)';

-- ============================================================================
-- MÓDULO: PROVEEDORES Y COMPRAS
-- ============================================================================

-- Tabla de Proveedores
CREATE TABLE proveedores (
    id BIGSERIAL PRIMARY KEY,
    razon_social VARCHAR(200) NOT NULL,
    nit VARCHAR(50) UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    email VARCHAR(150),
    nombre_contacto VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro DATE DEFAULT CURRENT_DATE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE proveedores IS 'Proveedores de materiales y productos';

-- Tabla de Órdenes de Compra (Pedidos al Proveedor)
CREATE TABLE ordenes_compra (
    id BIGSERIAL PRIMARY KEY,
    numero_orden VARCHAR(50) UNIQUE NOT NULL,
    proveedor_id BIGINT NOT NULL REFERENCES proveedores(id) ON DELETE RESTRICT,
    fecha_orden DATE DEFAULT CURRENT_DATE,
    fecha_entrega_esperada DATE,
    total_ordenado NUMERIC(12,2) NOT NULL CHECK (total_ordenado >= 0),
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'recibida_parcial', 'recibida_completa', 'cancelada')) DEFAULT 'pendiente',
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE ordenes_compra IS 'Pedidos enviados a proveedores antes de recibir mercadería';

-- Tabla de Compras (Recepción de Mercadería)
CREATE TABLE compras (
    id BIGSERIAL PRIMARY KEY,
    numero_compra VARCHAR(50) UNIQUE NOT NULL,
    orden_compra_id BIGINT REFERENCES ordenes_compra(id) ON DELETE SET NULL,
    proveedor_id BIGINT NOT NULL REFERENCES proveedores(id) ON DELETE RESTRICT,
    numero_factura_proveedor VARCHAR(100),
    fecha_compra DATE DEFAULT CURRENT_DATE,
    fecha_entrega_esperada DATE,
    fecha_entrega_real DATE,
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    impuestos NUMERIC(12,2) DEFAULT 0 CHECK (impuestos >= 0),
    total NUMERIC(12,2) NOT NULL CHECK (total >= 0),
    metodo_pago VARCHAR(50) DEFAULT 'Efectivo',
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'recibida', 'cancelada')) DEFAULT 'pendiente',
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE compras IS 'Registro de compras realizadas y mercadería recibida';
COMMENT ON COLUMN compras.orden_compra_id IS 'Vincula con orden previa (nullable: compras directas sin orden)';

-- Tabla de Detalle de Compras
CREATE TABLE detalle_compras (
    id BIGSERIAL PRIMARY KEY,
    compra_id BIGINT NOT NULL REFERENCES compras(id) ON DELETE CASCADE,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_unitario NUMERIC(12,2) NOT NULL CHECK (precio_unitario >= 0),
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    almacen_destino_id BIGINT NOT NULL REFERENCES almacenes(id),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE detalle_compras IS 'Items específicos de cada compra';
COMMENT ON COLUMN detalle_compras.almacen_destino_id IS 'A qué almacén ingresa el producto (Bodega o Mostrador)';

-- ============================================================================
-- MÓDULO: CLIENTES Y CRÉDITOS
-- ============================================================================

-- Tabla de Clientes
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

-- ============================================================================
-- MÓDULO: VENTAS Y PUNTO DE VENTA
-- ============================================================================

-- Tabla de Ventas (Cabecera)
CREATE TABLE ventas (
    id BIGSERIAL PRIMARY KEY,
    numero_venta VARCHAR(50) UNIQUE NOT NULL,
    cliente_id BIGINT REFERENCES clientes(id) ON DELETE SET NULL,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    fecha_venta TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    tipo_venta VARCHAR(20) CHECK (tipo_venta IN ('contado', 'credito')) DEFAULT 'contado',
    tipo_documento VARCHAR(20) CHECK (tipo_documento IN ('factura', 'recibo', 'nota_venta')) DEFAULT 'nota_venta',
    metodo_pago VARCHAR(20) CHECK (metodo_pago IN ('efectivo', 'qr')) NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    descuento_porcentaje NUMERIC(5,2) DEFAULT 0 CHECK (descuento_porcentaje >= 0 AND descuento_porcentaje <= 100),
    descuento_monto NUMERIC(12,2) DEFAULT 0 CHECK (descuento_monto >= 0),
    iva NUMERIC(12,2) DEFAULT 0 CHECK (iva >= 0),
    total NUMERIC(12,2) NOT NULL CHECK (total >= 0),
    estado VARCHAR(20) CHECK (estado IN ('completada', 'anulada', 'devuelta')) DEFAULT 'completada',
    notas TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE ventas IS 'Registro de todas las ventas realizadas';
COMMENT ON COLUMN ventas.numero_venta IS 'Número correlativo único de venta (auto-generado)';
COMMENT ON COLUMN ventas.iva IS 'IVA 13% en Bolivia (solo si factura)';
COMMENT ON COLUMN ventas.tipo_documento IS 'Factura (con NIT), Recibo o Nota de venta simple';

-- Tabla de Detalle de Ventas
CREATE TABLE detalle_ventas (
    id BIGSERIAL PRIMARY KEY,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    almacen_id BIGINT NOT NULL REFERENCES almacenes(id) ON DELETE RESTRICT,
    cantidad NUMERIC(12,4) NOT NULL CHECK (cantidad > 0),
    unidad_id BIGINT NOT NULL REFERENCES unidades_medida(id),
    precio_unitario NUMERIC(12,2) NOT NULL CHECK (precio_unitario >= 0),
    descuento_monto NUMERIC(12,2) DEFAULT 0 CHECK (descuento_monto >= 0),
    subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE detalle_ventas IS 'Items individuales de cada venta';
COMMENT ON COLUMN detalle_ventas.precio_unitario IS 'Precio al momento de la venta (historial)';
COMMENT ON COLUMN detalle_ventas.almacen_id IS 'De qué almacén salió el producto';

-- Tabla de Créditos de Clientes (Por Venta)
CREATE TABLE creditos_clientes (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL REFERENCES clientes(id) ON DELETE RESTRICT,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE RESTRICT,
    monto_total NUMERIC(12,2) NOT NULL CHECK (monto_total >= 0),
    monto_pagado NUMERIC(12,2) DEFAULT 0 CHECK (monto_pagado >= 0),
    saldo_pendiente NUMERIC(12,2) NOT NULL CHECK (saldo_pendiente >= 0),
    fecha_vencimiento DATE,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'pagado_parcial', 'pagado_completo', 'vencido')) DEFAULT 'pendiente',
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE creditos_clientes IS 'Control de deuda por venta a crédito';
COMMENT ON COLUMN creditos_clientes.saldo_pendiente IS 'Se actualiza con cada pago: monto_total - monto_pagado';

-- Tabla de Pagos de Crédito
CREATE TABLE pagos_credito (
    id BIGSERIAL PRIMARY KEY,
    credito_cliente_id BIGINT NOT NULL REFERENCES creditos_clientes(id) ON DELETE RESTRICT,
    monto_pago NUMERIC(12,2) NOT NULL CHECK (monto_pago > 0),
    metodo_pago VARCHAR(20) CHECK (metodo_pago IN ('efectivo', 'qr')) NOT NULL,
    fecha_pago TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE pagos_credito IS 'Registro de cada abono que hace el cliente a su deuda';

-- Tabla de Devoluciones de Venta
CREATE TABLE devoluciones_venta (
    id BIGSERIAL PRIMARY KEY,
    numero_devolucion VARCHAR(50) UNIQUE NOT NULL,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE RESTRICT,
    tipo_devolucion VARCHAR(20) CHECK (tipo_devolucion IN ('reembolso', 'cambio')) NOT NULL,
    fecha_devolucion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    total_devuelto NUMERIC(12,2) NOT NULL CHECK (total_devuelto >= 0),
    razon TEXT NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('procesada', 'anulada')) DEFAULT 'procesada',
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    creado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);

COMMENT ON TABLE devoluciones_venta IS 'Devoluciones: mismo día, producto en buen estado';

-- Tabla de Detalle de Devoluciones
CREATE TABLE detalle_devoluciones_venta (
    id BIGSERIAL PRIMARY KEY,
    devolucion_venta_id BIGINT NOT NULL REFERENCES devoluciones_venta(id) ON DELETE CASCADE,
    detalle_venta_id BIGINT NOT NULL REFERENCES detalle_ventas(id) ON DELETE RESTRICT,
    cantidad_devuelta NUMERIC(12,4) NOT NULL CHECK (cantidad_devuelta > 0),
    monto_devuelto NUMERIC(12,2) NOT NULL CHECK (monto_devuelto >= 0),
    almacen_retorno_id BIGINT NOT NULL REFERENCES almacenes(id),
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE detalle_devoluciones_venta IS 'Qué productos específicos se devolvieron';
COMMENT ON COLUMN detalle_devoluciones_venta.almacen_retorno_id IS 'A qué almacén regresa el producto';

-- ============================================================================
-- MÓDULO: CONTROL DE CAJA
-- ============================================================================

-- Tabla de Arqueos de Caja
CREATE TABLE arqueos_caja (
    id BIGSERIAL PRIMARY KEY,
    fecha_apertura TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMPTZ,
    usuario_apertura_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    usuario_cierre_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    monto_inicial NUMERIC(12,2) NOT NULL CHECK (monto_inicial >= 0),
    monto_final NUMERIC(12,2) CHECK (monto_final >= 0),
    total_ventas_efectivo NUMERIC(12,2) DEFAULT 0,
    total_ventas_qr NUMERIC(12,2) DEFAULT 0,
    total_esperado NUMERIC(12,2),
    diferencia NUMERIC(12,2),
    estado VARCHAR(20) CHECK (estado IN ('abierta', 'cerrada')) DEFAULT 'abierta',
    notas_apertura TEXT,
    notas_cierre TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE arqueos_caja IS 'Apertura y cierre diario de caja';
COMMENT ON COLUMN arqueos_caja.diferencia IS 'Faltante (negativo) o Sobrante (positivo)';
COMMENT ON COLUMN arqueos_caja.total_esperado IS 'monto_inicial + ventas_efectivo - gastos';

-- Tabla de Movimientos de Caja
CREATE TABLE movimientos_caja (
    id BIGSERIAL PRIMARY KEY,
    arqueo_caja_id BIGINT NOT NULL REFERENCES arqueos_caja(id) ON DELETE CASCADE,
    tipo_movimiento VARCHAR(20) CHECK (tipo_movimiento IN ('venta', 'gasto', 'retiro', 'ingreso_extra')) NOT NULL,
    monto NUMERIC(12,2) NOT NULL CHECK (monto > 0),
    concepto TEXT NOT NULL,
    venta_id BIGINT REFERENCES ventas(id) ON DELETE SET NULL,
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE movimientos_caja IS 'Registro detallado de entradas/salidas de efectivo';

-- ============================================================================
-- MÓDULO: AUDITORÍA Y LOGS
-- ============================================================================

-- Tabla de Logs de Auditoría
CREATE TABLE logs_auditoria (
    id BIGSERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(100) NOT NULL,
    registro_id BIGINT NOT NULL,
    accion VARCHAR(20) CHECK (accion IN ('INSERT', 'UPDATE', 'DELETE')) NOT NULL,
    valores_anteriores JSONB,
    valores_nuevos JSONB,
    usuario_id BIGINT REFERENCES usuarios(id) ON DELETE SET NULL,
    ip_address INET,
    user_agent TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE logs_auditoria IS 'Registro completo de cambios críticos en el sistema';
COMMENT ON COLUMN logs_auditoria.valores_anteriores IS 'Estado del registro antes del cambio (UPDATE/DELETE)';
COMMENT ON COLUMN logs_auditoria.valores_nuevos IS 'Estado del registro después del cambio (INSERT/UPDATE)';

-- ============================================================================
-- ÍNDICES PARA OPTIMIZACIÓN DE PERFORMANCE
-- ============================================================================

-- Índices en Productos
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_marca ON productos(marca_id);
CREATE INDEX idx_productos_sku ON productos(sku);
CREATE INDEX idx_productos_codigo_barras ON productos(codigo_barras) WHERE codigo_barras IS NOT NULL;
CREATE INDEX idx_productos_activo ON productos(activo) WHERE activo = TRUE;
CREATE INDEX idx_productos_nombre ON productos USING gin(to_tsvector('spanish', nombre));

-- Índices en Inventario
CREATE INDEX idx_inventario_producto ON inventario(producto_id);
CREATE INDEX idx_inventario_almacen ON inventario(almacen_id);
CREATE INDEX idx_inventario_stock_bajo ON inventario(cantidad_actual) WHERE cantidad_actual <= stock_minimo;

-- Índices en Movimientos de Inventario
CREATE INDEX idx_movimientos_inventario_producto ON movimientos_inventario(producto_id);
CREATE INDEX idx_movimientos_inventario_almacen ON movimientos_inventario(almacen_id);
CREATE INDEX idx_movimientos_inventario_tipo ON movimientos_inventario(tipo_movimiento);
CREATE INDEX idx_movimientos_inventario_fecha ON movimientos_inventario(creado_en);
CREATE INDEX idx_movimientos_inventario_usuario ON movimientos_inventario(usuario_id);

-- Índices en Ventas
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);
CREATE INDEX idx_ventas_tipo ON ventas(tipo_venta);
CREATE INDEX idx_ventas_numero ON ventas(numero_venta);

-- Índices en Detalle de Ventas
CREATE INDEX idx_detalle_ventas_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_ventas_producto ON detalle_ventas(producto_id);
CREATE INDEX idx_detalle_ventas_almacen ON detalle_ventas(almacen_id);

-- Índices en Compras
CREATE INDEX idx_compras_proveedor ON compras(proveedor_id);
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_compras_estado ON compras(estado);
CREATE INDEX idx_compras_usuario ON compras(usuario_id);

-- Índices en Clientes
CREATE INDEX idx_clientes_nit ON clientes(nit) WHERE nit IS NOT NULL;
CREATE INDEX idx_clientes_frecuente ON clientes(es_frecuente) WHERE es_frecuente = TRUE;
CREATE INDEX idx_clientes_nombre ON clientes USING gin(to_tsvector('spanish', nombre_completo));

-- Índices en Créditos
CREATE INDEX idx_creditos_cliente ON creditos_clientes(cliente_id);
CREATE INDEX idx_creditos_venta ON creditos_clientes(venta_id);
CREATE INDEX idx_creditos_estado ON creditos_clientes(estado);
CREATE INDEX idx_creditos_vencimiento ON creditos_clientes(fecha_vencimiento);

-- Índices en Caja
CREATE INDEX idx_arqueos_caja_fecha_apertura ON arqueos_caja(fecha_apertura);
CREATE INDEX idx_arqueos_caja_estado ON arqueos_caja(estado);
CREATE INDEX idx_movimientos_caja_arqueo ON movimientos_caja(arqueo_caja_id);

-- Índices en Auditoría
CREATE INDEX idx_logs_tabla ON logs_auditoria(tabla_afectada);
CREATE INDEX idx_logs_registro ON logs_auditoria(registro_id);
CREATE INDEX idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX idx_logs_fecha ON logs_auditoria(creado_en);
CREATE INDEX idx_logs_accion ON logs_auditoria(accion);

-- ============================================================================
-- DATOS INICIALES (SEEDERS)
-- ============================================================================

-- Insertar Roles predefinidos
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema, gestión de usuarios, cierre de caja'),
('Vendedor', 'Realiza ventas, consulta inventario, genera reportes básicos'),
('Bodeguero', 'Gestiona compras, recibe mercadería, hace transferencias de inventario');

-- Insertar Unidades de Medida básicas
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

-- Insertar Almacenes predefinidos
INSERT INTO almacenes (nombre, tipo, ubicacion) VALUES
('Bodega Principal', 'bodega', 'Área trasera - Materiales pesados (planchas, fierros, tubos soldadura)'),
('Mostrador Venta', 'mostrador', 'Área frontal - Estantes con productos de alta rotación');

-- Insertar Categorías principales
INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Materiales de Construcción', 'Cemento, estuco, cemento cola, fierros de construcción', TRUE),
('Plomería', 'Tubos PVC, accesorios agua potable, pegamentos', TRUE),
('Soldadura', 'Planchas metálicas, tubos, costaneras, electrodos, equipos', TRUE),
('Herramientas Manuales', 'Martillos, desarmadores, alicates, llaves', TRUE),
('Elementos de Fijación', 'Clavos, tornillos, pernos, tuercas', TRUE),
('Seguridad Industrial', 'Guantes, cascos, gafas, mascarillas', TRUE),
('Eléctrico', 'Cables, interruptores, enchufes, cinta aislante', TRUE),
('Pintura y Acabados', 'Brochas, rodillos, lijas, espátulas', TRUE);

-- Insertar Marcas comunes en Bolivia
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

-- ============================================================================
-- FUNCIONES Y TRIGGERS ÚTILES
-- ============================================================================

-- Función para actualizar timestamp automáticamente
CREATE OR REPLACE FUNCTION actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar trigger a todas las tablas con actualizado_en
CREATE TRIGGER trigger_actualizar_usuarios
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_productos
    BEFORE UPDATE ON productos
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_categorias
    BEFORE UPDATE ON categorias
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_clientes
    BEFORE UPDATE ON clientes
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_proveedores
    BEFORE UPDATE ON proveedores
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_ventas
    BEFORE UPDATE ON ventas
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_compras
    BEFORE UPDATE ON compras
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_ordenes_compra
    BEFORE UPDATE ON ordenes_compra
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

CREATE TRIGGER trigger_actualizar_creditos
    BEFORE UPDATE ON creditos_clientes
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_timestamp();

-- Función para calcular saldo pendiente en créditos
CREATE OR REPLACE FUNCTION calcular_saldo_credito()
RETURNS TRIGGER AS $$
BEGIN
    NEW.saldo_pendiente = NEW.monto_total - NEW.monto_pagado;
    
    -- Actualizar estado según saldo
    IF NEW.saldo_pendiente = 0 THEN
        NEW.estado = 'pagado_completo';
    ELSIF NEW.monto_pagado > 0 AND NEW.saldo_pendiente > 0 THEN
        NEW.estado = 'pagado_parcial';
    ELSIF NEW.fecha_vencimiento < CURRENT_DATE AND NEW.saldo_pendiente > 0 THEN
        NEW.estado = 'vencido';
    ELSE
        NEW.estado = 'pendiente';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_calcular_saldo_credito
    BEFORE INSERT OR UPDATE ON creditos_clientes
    FOR EACH ROW
    EXECUTE FUNCTION calcular_saldo_credito();

-- Función para validar stock antes de venta
CREATE OR REPLACE FUNCTION validar_stock_venta()
RETURNS TRIGGER AS $$
DECLARE
    stock_disponible NUMERIC;
BEGIN
    -- Obtener stock actual del producto en el almacén
    SELECT cantidad_actual INTO stock_disponible
    FROM inventario
    WHERE producto_id = NEW.producto_id 
    AND almacen_id = NEW.almacen_id;
    
    -- Validar que hay suficiente stock
    IF stock_disponible IS NULL OR stock_disponible < NEW.cantidad THEN
        RAISE EXCEPTION 'Stock insuficiente. Disponible: %, Solicitado: %', 
            COALESCE(stock_disponible, 0), NEW.cantidad;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_validar_stock_venta
    BEFORE INSERT ON detalle_ventas
    FOR EACH ROW
    EXECUTE FUNCTION validar_stock_venta();

-- Función para auto-generar número de venta
CREATE OR REPLACE FUNCTION generar_numero_venta()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    -- Si ya tiene número asignado, no hacer nada
    IF NEW.numero_venta IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    -- Obtener el último número del día actual
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_venta FROM 10) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM ventas
    WHERE DATE(fecha_venta) = CURRENT_DATE;
    
    -- Generar nuevo número: YYYYMMDD-XXXX
    nuevo_numero = TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_venta = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_numero_venta
    BEFORE INSERT ON ventas
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_venta();

-- Función para auto-generar número de compra
CREATE OR REPLACE FUNCTION generar_numero_compra()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_compra IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_compra FROM 11) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM compras
    WHERE DATE(fecha_compra) = CURRENT_DATE;
    
    nuevo_numero = 'C-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_compra = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_numero_compra
    BEFORE INSERT ON compras
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_compra();

-- Función para auto-generar número de orden de compra
CREATE OR REPLACE FUNCTION generar_numero_orden()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_orden IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_orden FROM 12) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM ordenes_compra
    WHERE DATE(fecha_orden) = CURRENT_DATE;
    
    nuevo_numero = 'OC-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_orden = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_numero_orden
    BEFORE INSERT ON ordenes_compra
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_orden();

-- Función para auto-generar número de devolución
CREATE OR REPLACE FUNCTION generar_numero_devolucion()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_numero INTEGER;
    nuevo_numero VARCHAR(50);
BEGIN
    IF NEW.numero_devolucion IS NOT NULL THEN
        RETURN NEW;
    END IF;
    
    SELECT COALESCE(
        MAX(CAST(SUBSTRING(numero_devolucion FROM 12) AS INTEGER)), 
        0
    ) INTO ultimo_numero
    FROM devoluciones_venta
    WHERE DATE(fecha_devolucion) = CURRENT_DATE;
    
    nuevo_numero = 'DV-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || 
                   LPAD((ultimo_numero + 1)::TEXT, 4, '0');
    
    NEW.numero_devolucion = nuevo_numero;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_generar_numero_devolucion
    BEFORE INSERT ON devoluciones_venta
    FOR EACH ROW
    EXECUTE FUNCTION generar_numero_devolucion();

-- ============================================================================
-- VISTAS ÚTILES PARA REPORTES
-- ============================================================================

-- Vista: Stock actual consolidado por producto
CREATE OR REPLACE VIEW vista_stock_actual AS
SELECT 
    p.id AS producto_id,
    p.sku,
    p.codigo_barras,
    p.nombre AS producto_nombre,
    c.nombre AS categoria_nombre,
    m.nombre AS marca_nombre,
    um.nombre AS unidad_medida,
    a.nombre AS almacen_nombre,
    i.cantidad_actual,
    i.stock_minimo,
    CASE 
        WHEN i.cantidad_actual <= i.stock_minimo THEN 'BAJO'
        WHEN i.cantidad_actual <= (i.stock_minimo * 1.5) THEN 'MEDIO'
        ELSE 'NORMAL'
    END AS nivel_stock,
    p.precio_compra,
    p.precio_venta,
    (i.cantidad_actual * p.precio_compra) AS valor_inventario,
    p.activo
FROM inventario i
INNER JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
INNER JOIN unidades_medida um ON p.unidad_base_id = um.id
INNER JOIN almacenes a ON i.almacen_id = a.id
WHERE p.activo = TRUE
ORDER BY p.nombre, a.nombre;

COMMENT ON VIEW vista_stock_actual IS 'Muestra stock actual de todos los productos por almacén con niveles de alerta';

-- Vista: Productos con stock bajo
CREATE OR REPLACE VIEW vista_productos_stock_bajo AS
SELECT 
    p.id,
    p.sku,
    p.nombre,
    c.nombre AS categoria,
    a.nombre AS almacen,
    i.cantidad_actual,
    i.stock_minimo,
    (i.stock_minimo - i.cantidad_actual) AS cantidad_reponer,
    p.precio_compra,
    ((i.stock_minimo - i.cantidad_actual) * p.precio_compra) AS costo_reposicion
FROM inventario i
INNER JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
INNER JOIN almacenes a ON i.almacen_id = a.id
WHERE i.cantidad_actual <= i.stock_minimo
AND p.activo = TRUE
ORDER BY (i.stock_minimo - i.cantidad_actual) DESC;

COMMENT ON VIEW vista_productos_stock_bajo IS 'Productos que necesitan reposición urgente';

-- Vista: Ventas del día
CREATE OR REPLACE VIEW vista_ventas_hoy AS
SELECT 
    v.id,
    v.numero_venta,
    v.fecha_venta,
    c.nombre_completo AS cliente,
    u.nombre AS vendedor,
    v.tipo_venta,
    v.metodo_pago,
    v.subtotal,
    v.descuento_monto,
    v.iva,
    v.total,
    v.estado
FROM ventas v
LEFT JOIN clientes c ON v.cliente_id = c.id
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE DATE(v.fecha_venta) = CURRENT_DATE
AND v.estado != 'anulada'
ORDER BY v.fecha_venta DESC;

COMMENT ON VIEW vista_ventas_hoy IS 'Todas las ventas realizadas hoy';

-- Vista: Productos más vendidos
CREATE OR REPLACE VIEW vista_productos_mas_vendidos AS
SELECT 
    p.id,
    p.sku,
    p.nombre,
    c.nombre AS categoria,
    COUNT(dv.id) AS veces_vendido,
    SUM(dv.cantidad) AS cantidad_total_vendida,
    SUM(dv.subtotal) AS ingresos_totales,
    AVG(dv.precio_unitario) AS precio_promedio
FROM detalle_ventas dv
INNER JOIN productos p ON dv.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
INNER JOIN ventas v ON dv.venta_id = v.id
WHERE v.estado = 'completada'
AND v.fecha_venta >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY p.id, p.sku, p.nombre, c.nombre
ORDER BY cantidad_total_vendida DESC
LIMIT 50;

COMMENT ON VIEW vista_productos_mas_vendidos IS 'Top 50 productos más vendidos últimos 30 días';

-- Vista: Clientes con deuda
CREATE OR REPLACE VIEW vista_clientes_deuda AS
SELECT 
    c.id,
    c.nombre_completo,
    c.telefono,
    c.nit,
    COUNT(cc.id) AS ventas_credito,
    SUM(cc.monto_total) AS total_credito,
    SUM(cc.monto_pagado) AS total_pagado,
    SUM(cc.saldo_pendiente) AS saldo_pendiente,
    MAX(cc.fecha_vencimiento) AS fecha_vencimiento_proxima,
    CASE 
        WHEN MAX(cc.fecha_vencimiento) < CURRENT_DATE THEN 'VENCIDO'
        WHEN MAX(cc.fecha_vencimiento) <= CURRENT_DATE + INTERVAL '7 days' THEN 'POR VENCER'
        ELSE 'AL DÍA'
    END AS estado_deuda
FROM clientes c
INNER JOIN creditos_clientes cc ON c.id = cc.cliente_id
WHERE cc.estado IN ('pendiente', 'pagado_parcial', 'vencido')
GROUP BY c.id, c.nombre_completo, c.telefono, c.nit
HAVING SUM(cc.saldo_pendiente) > 0
ORDER BY saldo_pendiente DESC;

COMMENT ON VIEW vista_clientes_deuda IS 'Clientes con deuda pendiente ordenados por monto';

-- Vista: Reporte de caja del día
CREATE OR REPLACE VIEW vista_caja_hoy AS
SELECT 
    ac.id AS arqueo_id,
    ac.fecha_apertura,
    ac.fecha_cierre,
    u_apertura.nombre AS usuario_apertura,
    u_cierre.nombre AS usuario_cierre,
    ac.monto_inicial,
    ac.total_ventas_efectivo,
    ac.total_ventas_qr,
    ac.monto_final,
    ac.total_esperado,
    ac.diferencia,
    ac.estado,
    CASE 
        WHEN ac.diferencia > 0 THEN 'SOBRANTE'
        WHEN ac.diferencia < 0 THEN 'FALTANTE'
        ELSE 'CUADRADO'
    END AS estado_diferencia
FROM arqueos_caja ac
INNER JOIN usuarios u_apertura ON ac.usuario_apertura_id = u_apertura.id
LEFT JOIN usuarios u_cierre ON ac.usuario_cierre_id = u_cierre.id
WHERE DATE(ac.fecha_apertura) = CURRENT_DATE
ORDER BY ac.fecha_apertura DESC;

COMMENT ON VIEW vista_caja_hoy IS 'Estado actual de caja del día';

-- Vista: Kardex simplificado por producto
CREATE OR REPLACE VIEW vista_kardex_producto AS
SELECT 
    mi.id,
    mi.creado_en AS fecha_movimiento,
    p.sku,
    p.nombre AS producto,
    a.nombre AS almacen,
    a_destino.nombre AS almacen_destino,
    mi.tipo_movimiento,
    mi.cantidad,
    u.nombre AS usuario,
    mi.razon,
    CASE 
        WHEN mi.tipo_movimiento IN ('ENTRADA_COMPRA', 'AJUSTE_POSITIVO', 'DEVOLUCION_VENTA') THEN '+'
        WHEN mi.tipo_movimiento IN ('SALIDA_VENTA', 'AJUSTE_NEGATIVO', 'DEVOLUCION_COMPRA') THEN '-'
        ELSE '~'
    END AS signo
FROM movimientos_inventario mi
INNER JOIN productos p ON mi.producto_id = p.id
INNER JOIN almacenes a ON mi.almacen_id = a.id
LEFT JOIN almacenes a_destino ON mi.almacen_destino_id = a_destino.id
INNER JOIN usuarios u ON mi.usuario_id = u.id
ORDER BY mi.creado_en DESC;

COMMENT ON VIEW vista_kardex_producto IS 'Historial completo de movimientos de inventario';

-- Vista: Utilidad por venta
CREATE OR REPLACE VIEW vista_utilidad_ventas AS
SELECT 
    v.id AS venta_id,
    v.numero_venta,
    v.fecha_venta,
    u.nombre AS vendedor,
    v.total AS total_venta,
    SUM(dv.cantidad * p.precio_compra) AS costo_total,
    v.total - SUM(dv.cantidad * p.precio_compra) AS utilidad_bruta,
    CASE 
        WHEN SUM(dv.cantidad * p.precio_compra) > 0 
        THEN ROUND(((v.total - SUM(dv.cantidad * p.precio_compra)) / SUM(dv.cantidad * p.precio_compra) * 100), 2)
        ELSE 0 
    END AS margen_porcentaje
FROM ventas v
INNER JOIN detalle_ventas dv ON v.id = dv.venta_id
INNER JOIN productos p ON dv.producto_id = p.id
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE v.estado = 'completada'
GROUP BY v.id, v.numero_venta, v.fecha_venta, u.nombre, v.total
ORDER BY v.fecha_venta DESC;

COMMENT ON VIEW vista_utilidad_ventas IS 'Cálculo de utilidad y margen por venta';

-- ============================================================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- ============================================================================

-- Procedimiento: Procesar venta completa (atomicidad)
CREATE OR REPLACE FUNCTION procesar_venta(
    p_cliente_id BIGINT,
    p_usuario_id BIGINT,
    p_tipo_venta VARCHAR(20),
    p_metodo_pago VARCHAR(20),
    p_items JSONB -- Array de items: [{producto_id, almacen_id, cantidad, precio_unitario, descuento}]
) RETURNS BIGINT AS $$
DECLARE
    v_venta_id BIGINT;
    v_subtotal NUMERIC := 0;
    v_total_descuento NUMERIC := 0;
    v_iva NUMERIC := 0;
    v_total NUMERIC := 0;
    v_item JSONB;
    v_item_subtotal NUMERIC;
BEGIN
    -- Calcular totales
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC - COALESCE((v_item->>'descuento')::NUMERIC, 0);
        v_subtotal := v_subtotal + v_item_subtotal;
        v_total_descuento := v_total_descuento + COALESCE((v_item->>'descuento')::NUMERIC, 0);
    END LOOP;
    
    v_total := v_subtotal;
    
    -- Crear venta
    INSERT INTO ventas (
        cliente_id, usuario_id, tipo_venta, metodo_pago,
        subtotal, descuento_monto, iva, total, creado_por
    ) VALUES (
        p_cliente_id, p_usuario_id, p_tipo_venta, p_metodo_pago,
        v_subtotal, v_total_descuento, v_iva, v_total, p_usuario_id
    ) RETURNING id INTO v_venta_id;
    
    -- Insertar detalles
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC - COALESCE((v_item->>'descuento')::NUMERIC, 0);
        
        INSERT INTO detalle_ventas (
            venta_id, producto_id, almacen_id, cantidad, unidad_id,
            precio_unitario, descuento_monto, subtotal, creado_por
        ) VALUES (
            v_venta_id,
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC,
            (v_item->>'unidad_id')::BIGINT,
            (v_item->>'precio_unitario')::NUMERIC,
            COALESCE((v_item->>'descuento')::NUMERIC, 0),
            v_item_subtotal,
            p_usuario_id
        );
        
        -- Actualizar inventario
        UPDATE inventario SET 
            cantidad_actual = cantidad_actual - (v_item->>'cantidad')::NUMERIC,
            ultima_actualizacion = CURRENT_TIMESTAMP
        WHERE producto_id = (v_item->>'producto_id')::BIGINT
        AND almacen_id = (v_item->>'almacen_id')::BIGINT;
        
        -- Registrar movimiento
        INSERT INTO movimientos_inventario (
            producto_id, almacen_id, tipo_movimiento, cantidad,
            venta_id, usuario_id
        ) VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_id')::BIGINT,
            'SALIDA_VENTA',
            (v_item->>'cantidad')::NUMERIC,
            v_venta_id,
            p_usuario_id
        );
    END LOOP;
    
    -- Si es venta a crédito, crear registro de crédito
    IF p_tipo_venta = 'credito' THEN
        INSERT INTO creditos_clientes (
            cliente_id, venta_id, monto_total, monto_pagado,
            saldo_pendiente, fecha_vencimiento
        ) VALUES (
            p_cliente_id, v_venta_id, v_total, 0,
            v_total, CURRENT_DATE + INTERVAL '30 days'
        );
    END IF;
    
    RETURN v_venta_id;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION procesar_venta IS 'Procesa una venta completa de forma atómica con actualización de inventario';

-- Procedimiento: Procesar compra completa
CREATE OR REPLACE FUNCTION procesar_compra(
    p_proveedor_id BIGINT,
    p_usuario_id BIGINT,
    p_orden_compra_id BIGINT,
    p_numero_factura VARCHAR(100),
    p_items JSONB -- Array: [{producto_id, cantidad, unidad_id, precio_unitario, almacen_destino_id}]
) RETURNS BIGINT AS $$
DECLARE
    v_compra_id BIGINT;
    v_subtotal NUMERIC := 0;
    v_total NUMERIC := 0;
    v_item JSONB;
    v_item_subtotal NUMERIC;
BEGIN
    -- Calcular totales
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC;
        v_subtotal := v_subtotal + v_item_subtotal;
    END LOOP;
    
    v_total := v_subtotal;
    
    -- Crear compra
    INSERT INTO compras (
        proveedor_id, usuario_id, orden_compra_id, numero_factura_proveedor,
        fecha_entrega_real, subtotal, total, estado, creado_por
    ) VALUES (
        p_proveedor_id, p_usuario_id, p_orden_compra_id, p_numero_factura,
        CURRENT_DATE, v_subtotal, v_total, 'recibida', p_usuario_id
    ) RETURNING id INTO v_compra_id;
    
    -- Insertar detalles
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_item_subtotal := (v_item->>'cantidad')::NUMERIC * (v_item->>'precio_unitario')::NUMERIC;
        
        INSERT INTO detalle_compras (
            compra_id, producto_id, cantidad, unidad_id,
            precio_unitario, subtotal, almacen_destino_id, creado_por
        ) VALUES (
            v_compra_id,
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC,
            (v_item->>'unidad_id')::BIGINT,
            (v_item->>'precio_unitario')::NUMERIC,
            v_item_subtotal,
            (v_item->>'almacen_destino_id')::BIGINT,
            p_usuario_id
        );
        
        -- Actualizar inventario
        INSERT INTO inventario (producto_id, almacen_id, cantidad_actual)
        VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_destino_id')::BIGINT,
            (v_item->>'cantidad')::NUMERIC
        )
        ON CONFLICT (producto_id, almacen_id) DO UPDATE
        SET cantidad_actual = inventario.cantidad_actual + (v_item->>'cantidad')::NUMERIC,
            ultima_actualizacion = CURRENT_TIMESTAMP;
        
        -- Registrar movimiento
        INSERT INTO movimientos_inventario (
            producto_id, almacen_id, tipo_movimiento, cantidad,
            compra_id, usuario_id
        ) VALUES (
            (v_item->>'producto_id')::BIGINT,
            (v_item->>'almacen_destino_id')::BIGINT,
            'ENTRADA_COMPRA',
            (v_item->>'cantidad')::NUMERIC,
            v_compra_id,
            p_usuario_id
        );
    END LOOP;
    
    -- Actualizar orden de compra si existe
    IF p_orden_compra_id IS NOT NULL THEN
        UPDATE ordenes_compra
        SET estado = 'recibida_completa'
        WHERE id = p_orden_compra_id;
    END IF;
    
    RETURN v_compra_id;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION procesar_compra IS 'Procesa una compra completa con actualización automática de inventario';

-- Procedimiento: Transferir producto entre almacenes
CREATE OR REPLACE FUNCTION transferir_producto(
    p_producto_id BIGINT,
    p_almacen_origen_id BIGINT,
    p_almacen_destino_id BIGINT,
    p_cantidad NUMERIC,
    p_usuario_id BIGINT,
    p_razon TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    v_stock_origen NUMERIC;
BEGIN
    -- Verificar stock en origen
    SELECT cantidad_actual INTO v_stock_origen
    FROM inventario
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_origen_id;
    
    IF v_stock_origen IS NULL OR v_stock_origen < p_cantidad THEN
        RAISE EXCEPTION 'Stock insuficiente en almacén origen. Disponible: %, Solicitado: %', 
            COALESCE(v_stock_origen, 0), p_cantidad;
    END IF;
    
    -- Reducir stock en origen
    UPDATE inventario
    SET cantidad_actual = cantidad_actual - p_cantidad,
        ultima_actualizacion = CURRENT_TIMESTAMP
    WHERE producto_id = p_producto_id
    AND almacen_id = p_almacen_origen_id;
    
    -- Aumentar stock en destino
    INSERT INTO inventario (producto_id, almacen_id, cantidad_actual)
    VALUES (p_producto_id, p_almacen_destino_id, p_cantidad)
ON CONFLICT (producto_id, almacen_id) DO UPDATE
SET cantidad_actual = inventario.cantidad_actual + p_cantidad,
ultima_actualizacion = CURRENT_TIMESTAMP;

-- Registrar movimiento
INSERT INTO movimientos_inventario (
    producto_id, almacen_id, almacen_destino_id,
    tipo_movimiento, cantidad, usuario_id, razon
) VALUES (
    p_producto_id, p_almacen_origen_id, p_almacen_destino_id,
    'TRANSFERENCIA', p_cantidad, p_usuario_id, p_razon
);

RETURN TRUE;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION transferir_producto IS 'Transfiere producto de un almacén a otro (ej: Bodega → Mostrador)';
-- Procedimiento: Registrar pago de crédito
CREATE OR REPLACE FUNCTION registrar_pago_credito(
p_credito_id BIGINT,
p_monto_pago NUMERIC,
p_metodo_pago VARCHAR(20),
p_usuario_id BIGINT,
p_notas TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
v_saldo_actual NUMERIC;
BEGIN
-- Obtener saldo actual
SELECT saldo_pendiente INTO v_saldo_actual
FROM creditos_clientes
WHERE id = p_credito_id;

IF v_saldo_actual IS NULL THEN
    RAISE EXCEPTION 'Crédito no encontrado';
END IF;

IF p_monto_pago > v_saldo_actual THEN
    RAISE EXCEPTION 'Monto de pago (%) excede saldo pendiente (%)', p_monto_pago, v_saldo_actual;
END IF;

-- Registrar pago
INSERT INTO pagos_credito (
    credito_cliente_id, monto_pago, metodo_pago,
    notas, usuario_id, creado_por
) VALUES (
    p_credito_id, p_monto_pago, p_metodo_pago,
    p_notas, p_usuario_id, p_usuario_id
);

-- Actualizar crédito
UPDATE creditos_clientes
SET monto_pagado = monto_pagado + p_monto_pago,
    saldo_pendiente = saldo_pendiente - p_monto_pago
WHERE id = p_credito_id;

RETURN TRUE;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION registrar_pago_credito IS 'Registra un pago parcial o total de un crédito';
-- Procedimiento: Ajustar inventario
CREATE OR REPLACE FUNCTION ajustar_inventario(
p_producto_id BIGINT,
p_almacen_id BIGINT,
p_nueva_cantidad NUMERIC,
p_razon TEXT,
p_usuario_id BIGINT
) RETURNS BOOLEAN AS $$
DECLARE
v_cantidad_actual NUMERIC;
v_diferencia NUMERIC;
v_tipo_movimiento VARCHAR(30);
BEGIN
-- Obtener cantidad actual
SELECT cantidad_actual INTO v_cantidad_actual
FROM inventario
WHERE producto_id = p_producto_id
AND almacen_id = p_almacen_id;

IF v_cantidad_actual IS NULL THEN
    RAISE EXCEPTION 'Producto no existe en el inventario del almacén especificado';
END IF;

-- Calcular diferencia
v_diferencia := p_nueva_cantidad - v_cantidad_actual;

IF v_diferencia = 0 THEN
    RAISE EXCEPTION 'La nueva cantidad es igual a la actual. No hay nada que ajustar.';
END IF;

-- Determinar tipo de movimiento
IF v_diferencia > 0 THEN
    v_tipo_movimiento := 'AJUSTE_POSITIVO';
ELSE
    v_tipo_movimiento := 'AJUSTE_NEGATIVO';
    v_diferencia := ABS(v_diferencia);
END IF;

-- Actualizar inventario
UPDATE inventario
SET cantidad_actual = p_nueva_cantidad,
    ultima_actualizacion = CURRENT_TIMESTAMP
WHERE producto_id = p_producto_id
AND almacen_id = p_almacen_id;

-- Registrar movimiento (la razón es OBLIGATORIA)
INSERT INTO movimientos_inventario (
    producto_id, almacen_id, tipo_movimiento,
    cantidad, razon, usuario_id
) VALUES (
    p_producto_id, p_almacen_id, v_tipo_movimiento,
    v_diferencia, p_razon, p_usuario_id
);

RETURN TRUE;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION ajustar_inventario IS 'Ajusta el inventario con justificación obligatoria (error conteo, robo, daño)';
-- Procedimiento: Cerrar caja
CREATE OR REPLACE FUNCTION cerrar_caja(
p_arqueo_id BIGINT,
p_monto_final NUMERIC,
p_usuario_id BIGINT,
p_notas TEXT DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
v_monto_inicial NUMERIC;
v_total_ventas_efectivo NUMERIC;
v_total_ventas_qr NUMERIC;
v_total_esperado NUMERIC;
v_diferencia NUMERIC;
BEGIN
-- Obtener datos del arqueo
SELECT monto_inicial INTO v_monto_inicial
FROM arqueos_caja
WHERE id = p_arqueo_id
AND estado = 'abierta';

IF v_monto_inicial IS NULL THEN
    RAISE EXCEPTION 'Arqueo no encontrado o ya está cerrado';
END IF;

-- Calcular ventas del día por método de pago
SELECT 
    COALESCE(SUM(CASE WHEN metodo_pago = 'efectivo' THEN total ELSE 0 END), 0),
    COALESCE(SUM(CASE WHEN metodo_pago = 'qr' THEN total ELSE 0 END), 0)
INTO v_total_ventas_efectivo, v_total_ventas_qr
FROM ventas
WHERE DATE(fecha_venta) = (SELECT DATE(fecha_apertura) FROM arqueos_caja WHERE id = p_arqueo_id)
AND estado = 'completada';

-- Calcular total esperado
v_total_esperado := v_monto_inicial + v_total_ventas_efectivo;

-- Calcular diferencia
v_diferencia := p_monto_final - v_total_esperado;

-- Actualizar arqueo
UPDATE arqueos_caja
SET fecha_cierre = CURRENT_TIMESTAMP,
    usuario_cierre_id = p_usuario_id,
    monto_final = p_monto_final,
    total_ventas_efectivo = v_total_ventas_efectivo,
    total_ventas_qr = v_total_ventas_qr,
    total_esperado = v_total_esperado,
    diferencia = v_diferencia,
    estado = 'cerrada',
    notas_cierre = p_notas
WHERE id = p_arqueo_id;

RETURN TRUE;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION cerrar_caja IS 'Cierra el arqueo de caja del día calculando diferencias';
-- ============================================================================
-- FUNCIONES DE CONSULTA ÚTILES
-- ============================================================================
-- Función: Obtener stock total de un producto (todos los almacenes)
CREATE OR REPLACE FUNCTION obtener_stock_total_producto(p_producto_id BIGINT)
RETURNS NUMERIC AS $$
DECLARE
v_stock_total NUMERIC;
BEGIN
SELECT COALESCE(SUM(cantidad_actual), 0) INTO v_stock_total
FROM inventario
WHERE producto_id = p_producto_id;
RETURN v_stock_total;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION obtener_stock_total_producto IS 'Retorna stock total de un producto sumando todos los almacenes';
-- Función: Verificar si producto tiene stock suficiente
CREATE OR REPLACE FUNCTION tiene_stock_suficiente(
p_producto_id BIGINT,
p_almacen_id BIGINT,
p_cantidad_requerida NUMERIC
) RETURNS BOOLEAN AS $$
DECLARE
v_stock_disponible NUMERIC;
BEGIN
SELECT cantidad_actual INTO v_stock_disponible
FROM inventario
WHERE producto_id = p_producto_id
AND almacen_id = p_almacen_id;

RETURN COALESCE(v_stock_disponible, 0) >= p_cantidad_requerida;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION tiene_stock_suficiente IS 'Verifica si hay stock suficiente antes de vender';
-- Función: Obtener deuda total de un cliente
CREATE OR REPLACE FUNCTION obtener_deuda_cliente(p_cliente_id BIGINT)
RETURNS NUMERIC AS $$
DECLARE
v_deuda_total NUMERIC;
BEGIN
SELECT COALESCE(SUM(saldo_pendiente), 0) INTO v_deuda_total
FROM creditos_clientes
WHERE cliente_id = p_cliente_id
AND estado IN ('pendiente', 'pagado_parcial', 'vencido');
RETURN v_deuda_total;

END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION obtener_deuda_cliente IS 'Retorna deuda total actual de un cliente';
-- Función: Calcular utilidad de un período
CREATE OR REPLACE FUNCTION calcular_utilidad_periodo(
p_fecha_inicio DATE,
p_fecha_fin DATE
) RETURNS TABLE(
total_ventas NUMERIC,
costo_total NUMERIC,
utilidad_bruta NUMERIC,
margen_porcentaje NUMERIC
) AS $$
BEGIN
RETURN QUERY
SELECT
COALESCE(SUM(v.total), 0) AS total_ventas,
COALESCE(SUM(dv.cantidad * p.precio_compra), 0) AS costo_total,
COALESCE(SUM(v.total) - SUM(dv.cantidad * p.precio_compra), 0) AS utilidad_bruta,
CASE
WHEN SUM(dv.cantidad * p.precio_compra) > 0
THEN ROUND(((SUM(v.total) - SUM(dv.cantidad * p.precio_compra)) / SUM(dv.cantidad * p.precio_compra) * 100), 2)
ELSE 0
END AS margen_porcentaje
FROM ventas v
INNER JOIN detalle_ventas dv ON v.id = dv.venta_id
INNER JOIN productos p ON dv.producto_id = p.id
WHERE DATE(v.fecha_venta) BETWEEN p_fecha_inicio AND p_fecha_fin
AND v.estado = 'completada';
END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION calcular_utilidad_periodo IS 'Calcula utilidad bruta y margen de un período';
-- Función: Obtener productos próximos a vencer
CREATE OR REPLACE FUNCTION productos_proximos_vencer(p_dias INT DEFAULT 30)
RETURNS TABLE(
producto_id BIGINT,
sku VARCHAR,
nombre VARCHAR,
fecha_vencimiento DATE,
dias_restantes INT,
stock_total NUMERIC
) AS $$
BEGIN
RETURN QUERY
SELECT
p.id,
p.sku,
p.nombre,
p.fecha_vencimiento,
(p.fecha_vencimiento - CURRENT_DATE)::INT AS dias_restantes,
obtener_stock_total_producto(p.id) AS stock_total
FROM productos p
WHERE p.fecha_vencimiento IS NOT NULL
AND p.fecha_vencimiento <= CURRENT_DATE + (p_dias || ' days')::INTERVAL
AND p.fecha_vencimiento >= CURRENT_DATE
AND p.activo = TRUE
AND obtener_stock_total_producto(p.id) > 0
ORDER BY p.fecha_vencimiento ASC;
END;
$$ LANGUAGE plpgsql;
COMMENT ON FUNCTION productos_proximos_vencer IS 'Lista productos con fecha de vencimiento próxima';
-- ============================================================================
-- POLÍTICAS DE SEGURIDAD (ROW LEVEL SECURITY) - OPCIONAL PERO RECOMENDADO
-- ============================================================================
-- Ejemplo: Solo permitir ver ventas del usuario o si es admin
-- Descomentar si deseas activar RLS
/*
ALTER TABLE ventas ENABLE ROW LEVEL SECURITY;
CREATE POLICY ventas_usuario_policy ON ventas
FOR SELECT
USING (
usuario_id = current_setting('app.current_user_id')::BIGINT
OR EXISTS (
SELECT 1 FROM usuario_roles ur
INNER JOIN roles r ON ur.rol_id = r.id
WHERE ur.usuario_id = current_setting('app.current_user_id')::BIGINT
AND r.nombre = 'Administrador'
)
);
*/
-- ============================================================================
-- DATOS DE EJEMPLO PARA TESTING
-- ============================================================================
-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES
('Administrador Sistema', 'admin@frenad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
-- Password: password (cambiar en producción)
-- Asignar rol administrador al usuario creado
INSERT INTO usuario_roles (usuario_id, rol_id) VALUES
(1, 1);
-- Insertar productos de ejemplo
INSERT INTO categorias (nombre, descripcion, creado_por) VALUES
('Productos Ejemplo', 'Categoría de prueba', 1);
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
-- Insertar cliente de ejemplo
INSERT INTO clientes (nombre_completo, telefono, nit, es_frecuente, limite_credito, creado_por) VALUES
('Juan Pérez Construcciones', '71234567', '1234567015', TRUE, 5000.00, 1),
('María García', '72345678', NULL, FALSE, 0, 1);
-- Insertar proveedor de ejemplo
INSERT INTO proveedores (razon_social, nit, telefono, direccion, email, creado_por) VALUES
('Distribuidora La Paz SRL', '1023456789', '2-2234567', 'Av. Buenos Aires #1234', 'ventas@distlapaz.com', 1),
('Importadora Materiales SA', '9876543210', '2-2345678', 'Zona 16 de Julio', 'contacto@importmat.bo', 1);
-- ============================================================================
-- COMENTARIOS FINALES Y DOCUMENTACIÓN
-- ============================================================================
COMMENT ON DATABASE current_database() IS 'Sistema POS Ferretería Frenad - El Alto, Bolivia';
-- ============================================================================
-- FIN DEL SCRIPT DE BASE DE DATOS
-- ============================================================================
-- Para verificar la instalación completa, ejecutar:
-- SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;
-- Deberías ver 32 tablas creadas
-- Para verificar las vistas:
-- SELECT table_name FROM information_schema.views WHERE table_schema = 'public';
-- Deberías ver 8 vistas
-- Para verificar las funciones:
-- SELECT routine_name FROM information_schema.routines WHERE routine_schema = 'public' AND routine_type = 'FUNCTION';
-- Deberías ver múltiples funciones y procedimientos almacenados
