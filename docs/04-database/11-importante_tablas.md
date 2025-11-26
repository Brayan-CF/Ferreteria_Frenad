# DOCUMENTACIÓN TÉCNICA DETALLADA - BASE DE DATOS

## Ferretería Frenad - Análisis Completo de Normalización y Diseño

**Autor:** Equipo de Desarrollo  
**Fecha:** Diciembre 2024  
**Versión:** 1.0.0

---

# PARTE 1: TABLAS MÁS IMPORTANTES CON ATRIBUTOS

## 1. MÓDULO AUTH - Autenticación

### Tabla: usuarios

**Propósito:** Almacenar empleados y administradores del sistema.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único autoincremental |
| nombre | VARCHAR(100) | NOT NULL | Nombre completo del usuario |
| email | VARCHAR(150) | UNIQUE NOT NULL | Correo electrónico único (usado para login) |
| password_hash | VARCHAR(255) | NOT NULL | Hash bcrypt de la contraseña (nunca texto plano) |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Indica si el usuario puede acceder al sistema |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha y hora de creación del registro |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha y hora de última actualización |

**Índices:**
- PRIMARY KEY en `id` (automático)
- UNIQUE en `email` (automático)

**Ejemplo de registro:**
```sql
INSERT INTO usuarios (nombre, email, password_hash, activo) 
VALUES ('Juan Pérez', 'juan@frenad.com', '$2y$10$abcd...', TRUE);
```

---

### Tabla: roles

**Propósito:** Definir roles del sistema (Administrador, Vendedor, Bodeguero).

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único del rol |
| nombre | VARCHAR(50) | UNIQUE NOT NULL | Nombre único del rol |
| descripcion | TEXT | NULL | Descripción detallada del rol |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

**Roles Predefinidos:**
1. Administrador
2. Vendedor
3. Bodeguero

---

### Tabla: usuario_roles (N:N)

**Propósito:** Relación muchos a muchos entre usuarios y roles.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| usuario_id | BIGINT | FK usuarios(id) ON DELETE CASCADE, NOT NULL | ID del usuario |
| rol_id | BIGINT | FK roles(id) ON DELETE CASCADE, NOT NULL | ID del rol asignado |
| asignado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de asignación del rol |
| asignado_por | BIGINT | FK usuarios(id) NULL | ID del usuario que asignó el rol |

**Clave Primaria:** `(usuario_id, rol_id)` - Compuesta

**Regla de Negocio:** Un usuario puede tener múltiples roles simultáneamente.

---

## 2. MÓDULO PRODUCTOS - Catálogo

### Tabla: categorias

**Propósito:** Clasificar productos por tipo.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| nombre | VARCHAR(100) | UNIQUE NOT NULL | Nombre único de la categoría |
| descripcion | TEXT | NULL | Descripción detallada |
| activo | BOOLEAN | DEFAULT TRUE | Estado de la categoría (soft delete) |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario creador |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario que actualizó |

**Ejemplos:** Materiales de Construcción, Plomería, Soldadura, Herramientas.

---

### Tabla: marcas

**Propósito:** Registro de fabricantes y marcas de productos.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| nombre | VARCHAR(100) | UNIQUE NOT NULL | Nombre único de la marca |
| descripcion | TEXT | NULL | Descripción de la marca |
| pais_origen | VARCHAR(50) | NULL | País de origen del fabricante |
| activo | BOOLEAN | DEFAULT TRUE | Estado activo/inactivo |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario creador |

**Ejemplos:** EMISA (Cemento), Tigre (PVC), Stanley (Herramientas).

---

### Tabla: unidades_medida

**Propósito:** Definir unidades de medida estándar.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| nombre | VARCHAR(50) | UNIQUE NOT NULL | Nombre de la unidad (Metro, Pieza, Kilogramo) |
| abreviatura | VARCHAR(10) | NOT NULL | Abreviatura (m, pza, kg) |
| tipo | VARCHAR(20) | CHECK IN ('longitud','peso','volumen','unidad','area') NOT NULL | Tipo de unidad |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

**Tipos de Unidades:**
- **Longitud:** Metro (m)
- **Peso:** Kilogramo (kg)
- **Volumen:** Litro (l), Galón (gal)
- **Unidad:** Pieza (pza), Caja (cja), Rollo
- **Área:** Metro cuadrado (m²)

---

### Tabla: productos

**Propósito:** Catálogo completo de productos de la ferretería.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| sku | VARCHAR(50) | UNIQUE NOT NULL | Código interno único (Stock Keeping Unit) |
| codigo_barras | VARCHAR(100) | UNIQUE NULL | Código de barras (opcional) |
| nombre | VARCHAR(200) | NOT NULL | Nombre descriptivo del producto |
| descripcion | TEXT | NULL | Descripción detallada |
| categoria_id | BIGINT | FK categorias(id) ON DELETE SET NULL | Categoría del producto |
| marca_id | BIGINT | FK marcas(id) ON DELETE SET NULL | Marca del producto |
| unidad_base_id | BIGINT | FK unidades_medida(id) NOT NULL | Unidad mínima indivisible |
| precio_compra | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Precio de compra unitario |
| precio_venta | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Precio de venta sugerido |
| fecha_vencimiento | DATE | NULL | Fecha de vencimiento (solo perecederos) |
| ubicacion_fisica | VARCHAR(100) | NULL | Ubicación en bodega (ej: Estante A-3) |
| activo | BOOLEAN | DEFAULT TRUE | Producto activo/descontinuado |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario creador |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario que actualizó |

**Reglas de Negocio:**
- `sku` debe ser único e inmutable
- `precio_venta` debe ser mayor o igual a `precio_compra`
- `fecha_vencimiento` es obligatoria para pegamentos y selladores

**Índices:**
- `idx_productos_categoria` en `categoria_id`
- `idx_productos_marca` en `marca_id`
- `idx_productos_sku` en `sku`
- `idx_productos_codigo_barras` en `codigo_barras` WHERE NOT NULL
- `idx_productos_nombre` GIN full-text en `nombre`

---

### Tabla: producto_unidades

**Propósito:** Conversiones de unidades por producto (vender por metro, comprar por rollo).

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| producto_id | BIGINT | FK productos(id) ON DELETE CASCADE NOT NULL | ID del producto |
| unidad_id | BIGINT | FK unidades_medida(id) NOT NULL | Unidad de conversión |
| factor_conversion | NUMERIC(10,4) | CHECK > 0 NOT NULL | Factor multiplicador respecto a unidad base |
| es_unidad_compra | BOOLEAN | DEFAULT FALSE | Si se puede comprar en esta unidad |
| es_unidad_venta | BOOLEAN | DEFAULT FALSE | Si se puede vender en esta unidad |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

**Clave Única:** `(producto_id, unidad_id)`

**Ejemplo Práctico:**
```sql
-- Cable eléctrico: unidad base = metro
-- Se vende por metro
INSERT INTO producto_unidades (producto_id, unidad_id, factor_conversion, es_unidad_venta)
VALUES (10, 1, 1.0, TRUE); -- 1 metro = 1 metro

-- Se compra por rollo de 100 metros
INSERT INTO producto_unidades (producto_id, unidad_id, factor_conversion, es_unidad_compra)
VALUES (10, 5, 100.0, TRUE); -- 1 rollo = 100 metros
```

**Cálculo:**
- Venta de 5 metros: `stock_base -= 5 * 1 = 5 metros`
- Compra de 2 rollos: `stock_base += 2 * 100 = 200 metros`

---

## 3. MÓDULO INVENTARIO - Control de Stock

### Tabla: almacenes

**Propósito:** Definir ubicaciones físicas de almacenamiento.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| nombre | VARCHAR(100) | UNIQUE NOT NULL | Nombre del almacén |
| tipo | VARCHAR(20) | CHECK IN ('bodega','mostrador','otro') NOT NULL | Tipo de almacén |
| ubicacion | TEXT | NULL | Descripción de ubicación física |
| activo | BOOLEAN | DEFAULT TRUE | Almacén activo/inactivo |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

**Almacenes Predefinidos:**
1. **Bodega Principal:** Materiales pesados (planchas, fierros, tubos)
2. **Mostrador Venta:** Productos de alta rotación

---

### Tabla: inventario

**Propósito:** Stock actual por producto y almacén.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| producto_id | BIGINT | FK productos(id) ON DELETE CASCADE NOT NULL | ID del producto |
| almacen_id | BIGINT | FK almacenes(id) ON DELETE CASCADE NOT NULL | ID del almacén |
| cantidad_actual | NUMERIC(12,4) | CHECK >= 0 DEFAULT 0 NOT NULL | Stock disponible actual |
| stock_minimo | NUMERIC(12,4) | CHECK >= 0 DEFAULT 0 NOT NULL | Nivel de reorden |
| ultima_actualizacion | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última modificación |

**Clave Primaria:** `(producto_id, almacen_id)` - Compuesta

**Regla de Negocio:**
- `cantidad_actual` nunca puede ser negativa
- Alerta cuando `cantidad_actual <= stock_minimo`

**Índices:**
- `idx_inventario_producto` en `producto_id`
- `idx_inventario_almacen` en `almacen_id`
- `idx_inventario_stock_bajo` en `cantidad_actual` WHERE `cantidad_actual <= stock_minimo`

---

### Tabla: movimientos_inventario (KARDEX)

**Propósito:** Trazabilidad completa de movimientos de stock.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| producto_id | BIGINT | FK productos(id) ON DELETE RESTRICT NOT NULL | Producto afectado |
| almacen_id | BIGINT | FK almacenes(id) ON DELETE RESTRICT NOT NULL | Almacén origen |
| almacen_destino_id | BIGINT | FK almacenes(id) ON DELETE RESTRICT NULL | Almacén destino (solo transferencias) |
| tipo_movimiento | VARCHAR(30) | CHECK IN (...) NOT NULL | Tipo de movimiento (ver abajo) |
| cantidad | NUMERIC(12,4) | CHECK > 0 NOT NULL | Cantidad movida |
| razon | TEXT | NULL | Justificación (obligatoria en ajustes) |
| compra_id | BIGINT | FK compras(id) ON DELETE SET NULL | Referencia a compra |
| venta_id | BIGINT | FK ventas(id) ON DELETE SET NULL | Referencia a venta |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario que realizó |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha del movimiento |

**Tipos de Movimiento:**
- `ENTRADA_COMPRA`: Recepción de mercadería
- `SALIDA_VENTA`: Venta al cliente
- `TRANSFERENCIA`: Entre almacenes (Bodega → Mostrador)
- `AJUSTE_POSITIVO`: Corrección al alza (encontrado en conteo)
- `AJUSTE_NEGATIVO`: Corrección a la baja (faltante, daño, robo)
- `DEVOLUCION_VENTA`: Cliente devuelve producto
- `DEVOLUCION_COMPRA`: Devolución a proveedor

**Índices:**
- `idx_movimientos_inventario_producto` en `producto_id`
- `idx_movimientos_inventario_almacen` en `almacen_id`
- `idx_movimientos_inventario_tipo` en `tipo_movimiento`
- `idx_movimientos_inventario_fecha` en `creado_en`
- `idx_movimientos_inventario_usuario` en `usuario_id`

---

## 4. MÓDULO COMPRAS - Gestión de Proveedores

### Tabla: proveedores

**Propósito:** Base de datos de proveedores de materiales.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| razon_social | VARCHAR(200) | NOT NULL | Nombre legal del proveedor |
| nit | VARCHAR(50) | UNIQUE NULL | NIT único en Bolivia |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| direccion | TEXT | NULL | Dirección física |
| email | VARCHAR(150) | NULL | Email de contacto |
| nombre_contacto | VARCHAR(100) | NULL | Persona de contacto |
| activo | BOOLEAN | DEFAULT TRUE | Proveedor activo/inactivo |
| fecha_registro | DATE | DEFAULT CURRENT_DATE | Fecha de alta |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario creador |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario actualizador |

---

### Tabla: ordenes_compra

**Propósito:** Pedidos enviados a proveedores (antes de recibir mercadería).

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| numero_orden | VARCHAR(50) | UNIQUE NOT NULL | Número correlativo (OC-YYYYMMDD-0001) |
| proveedor_id | BIGINT | FK proveedores(id) ON DELETE RESTRICT NOT NULL | Proveedor destino |
| fecha_orden | DATE | DEFAULT CURRENT_DATE | Fecha de emisión |
| fecha_entrega_esperada | DATE | NULL | Fecha prometida de entrega |
| total_ordenado | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Monto total de la orden |
| estado | VARCHAR(20) | CHECK IN ('pendiente','recibida_parcial','recibida_completa','cancelada') DEFAULT 'pendiente' | Estado actual |
| notas | TEXT | NULL | Observaciones |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario creador |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Generación Automática:** Trigger `generar_numero_orden()` genera `numero_orden`

---

### Tabla: compras

**Propósito:** Registro de compras realizadas y mercadería recibida.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| numero_compra | VARCHAR(50) | UNIQUE NOT NULL | Número correlativo (C-YYYYMMDD-0001) |
| orden_compra_id | BIGINT | FK ordenes_compra(id) ON DELETE SET NULL | Orden de compra asociada (nullable) |
| proveedor_id | BIGINT | FK proveedores(id) ON DELETE RESTRICT NOT NULL | Proveedor |
| numero_factura_proveedor | VARCHAR(100) | NULL | Número de factura del proveedor |
| fecha_compra | DATE | DEFAULT CURRENT_DATE | Fecha de registro |
| fecha_entrega_esperada | DATE | NULL | Fecha prometida |
| fecha_entrega_real | DATE | NULL | Fecha real de entrega |
| subtotal | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Subtotal sin impuestos |
| impuestos | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | Impuestos aplicados |
| total | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Total de la compra |
| metodo_pago | VARCHAR(50) | DEFAULT 'Efectivo' | Método de pago usado |
| estado | VARCHAR(20) | CHECK IN ('pendiente','recibida','cancelada') DEFAULT 'pendiente' | Estado actual |
| notas | TEXT | NULL | Observaciones |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario registrador |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Generación Automática:** Trigger `generar_numero_compra()`

---

### Tabla: detalle_compras

**Propósito:** Items específicos de cada compra.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| compra_id | BIGINT | FK compras(id) ON DELETE CASCADE NOT NULL | Compra a la que pertenece |
| producto_id | BIGINT | FK productos(id) ON DELETE RESTRICT NOT NULL | Producto comprado |
| cantidad | NUMERIC(12,4) | CHECK > 0 NOT NULL | Cantidad recibida |
| unidad_id | BIGINT | FK unidades_medida(id) NOT NULL | Unidad de compra |
| precio_unitario | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Precio unitario |
| subtotal | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Cantidad × Precio |
| almacen_destino_id | BIGINT | FK almacenes(id) NOT NULL | Almacén donde ingresa |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha registro |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Regla de Negocio:** Al insertar, actualiza automáticamente `inventario` y `movimientos_inventario`.

---

## 5. MÓDULO VENTAS - Punto de Venta (POS)

### Tabla: clientes

**Propósito:** Base de datos de clientes.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| nombre_completo | VARCHAR(150) | NOT NULL | Nombre del cliente |
| nit | VARCHAR(50) | NULL | NIT (opcional) |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| direccion | TEXT | NULL | Dirección |
| email | VARCHAR(150) | NULL | Email |
| es_frecuente | BOOLEAN | DEFAULT FALSE | Cliente casero (merece crédito) |
| limite_credito | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | Monto máximo de deuda |
| activo | BOOLEAN | DEFAULT TRUE | Cliente activo |
| fecha_registro | DATE | DEFAULT CURRENT_DATE | Fecha alta |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario creador |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario actualizador |

**Regla de Negocio:** Solo clientes con `es_frecuente = TRUE` pueden comprar a crédito.

---

### Tabla: ventas

**Propósito:** Cabecera de cada venta realizada.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| numero_venta | VARCHAR(50) | UNIQUE NOT NULL | Número correlativo (YYYYMMDD-0001) |
| cliente_id | BIGINT | FK clientes(id) ON DELETE SET NULL | Cliente (NULL = venta a consumidor final) |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Vendedor |
| fecha_venta | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha y hora de venta |
| tipo_venta | VARCHAR(20) | CHECK IN ('contado','credito') DEFAULT 'contado' | Modalidad de pago |
| metodo_pago | VARCHAR(20) | CHECK IN ('efectivo','qr') DEFAULT 'efectivo' | Método de pago (solo contado) |
| tipo_comprobante | VARCHAR(20) | CHECK IN ('factura','recibo','nota_venta') DEFAULT 'nota_venta' | Tipo de documento |
| subtotal | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Suma de items |
| descuento_monto | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | Descuento aplicado |
| iva | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | IVA 13% Bolivia (opcional) |
| total | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Total a pagar |
| estado | VARCHAR(20) | CHECK IN ('pendiente','completada','anulada') DEFAULT 'completada' | Estado de venta |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp creación |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |
| actualizado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Generación Automática:** Trigger `generar_numero_venta()`

**Regla de Negocio:**
- Si `tipo_venta = 'credito'`, se crea automáticamente registro en `creditos_clientes`
- Si `metodo_pago = 'efectivo'`, se registra en `movimientos_caja`

---

### Tabla: detalle_ventas

**Propósito:** Items individuales vendidos en cada venta.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| venta_id | BIGINT | FK ventas(id) ON DELETE CASCADE NOT NULL | Venta a la que pertenece |
| producto_id | BIGINT | FK productos(id) ON DELETE RESTRICT NOT NULL | Producto vendido |
| almacen_id | BIGINT | FK almacenes(id) ON DELETE RESTRICT NOT NULL | Almacén de donde salió |
| cantidad | NUMERIC(12,4) | CHECK > 0 NOT NULL | Cantidad vendida |
| unidad_id | BIGINT | FK unidades_medida(id) NOT NULL | Unidad de venta |
| precio_unitario | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Precio al momento de venta |
| descuento_monto | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | Descuento por item |
| subtotal | NUMERIC(12,2) | CHECK >= 0 NOT NULL | (Cantidad × Precio) - Descuento |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Trigger Automático:**
- `validar_stock_venta`: Valida que haya stock suficiente antes de insertar
- Actualiza `inventario` restando cantidad
- Registra movimiento tipo `SALIDA_VENTA` en `movimientos_inventario`

---

### Tabla: creditos_clientes

**Propósito:** Control de deuda por ventas a crédito.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| cliente_id | BIGINT | FK clientes(id) ON DELETE RESTRICT NOT NULL | Cliente deudor |
| venta_id | BIGINT | FK ventas(id) ON DELETE RESTRICT NOT NULL | Venta a crédito |
| monto_total | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Monto total de la deuda |
| monto_pagado | NUMERIC(12,2) | CHECK >= 0 DEFAULT 0 | Suma de abonos |
| saldo_pendiente | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Deuda actual (calculado automático) |
| fecha_vencimiento | DATE | NULL | Fecha límite de pago |
| estado | VARCHAR(20) | CHECK IN ('pendiente','pagado_parcial','pagado_completo','vencido') DEFAULT 'pendiente' | Estado |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha creación |
| actualizado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Última actualización |

**Trigger Automático:**
- `calcular_saldo_credito`: Calcula automáticamente `saldo_pendiente = monto_total - monto_pagado`
- Actualiza `estado` según saldo y vencimiento

---

### Tabla: pagos_credito

**Propósito:** Registro de cada abono que hace el cliente.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| credito_cliente_id | BIGINT | FK creditos_clientes(id) ON DELETE RESTRICT NOT NULL | Crédito al que abona |
| monto_pago | NUMERIC(12,2) | CHECK > 0 NOT NULL | Monto del abono |
| metodo_pago | VARCHAR(20) | CHECK IN ('efectivo','qr') NOT NULL | Método de pago |
| fecha_pago | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha del abono |
| notas | TEXT | NULL | Observaciones |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario que registró |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Trigger Automático:** Al insertar, actualiza `creditos_clientes.monto_pagado` y recalcula `saldo_pendiente`.

---

### Tabla: devoluciones_venta

**Propósito:** Registro de devoluciones procesadas.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| numero_devolucion | VARCHAR(50) | UNIQUE NOT NULL | Número correlativo (DV-YYYYMMDD-0001) |
| venta_id | BIGINT | FK ventas(id) ON DELETE RESTRICT NOT NULL | Venta original |
| tipo_devolucion | VARCHAR(20) | CHECK IN ('reembolso','cambio') NOT NULL | Tipo de devolución |
| fecha_devolucion | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha devolución |
| total_devuelto | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Monto devuelto |
| razon | TEXT | NOT NULL | Razón de devolución (obligatorio) |
| estado | VARCHAR(20) | CHECK IN ('procesada','anulada') DEFAULT 'procesada' | Estado |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario que procesó |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |
| creado_por | BIGINT | FK usuarios(id) ON DELETE SET NULL | Auditoría |

**Generación Automática:** Trigger `generar_numero_devolucion()`

**Regla de Negocio:** Solo se permiten devoluciones el mismo día de la venta y con producto en buen estado.

---

### Tabla: detalle_devoluciones_venta

**Propósito:** Qué productos específicos se devolvieron.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| devolucion_venta_id | BIGINT | FK devoluciones_venta(id) ON DELETE CASCADE NOT NULL | Devolución |
| detalle_venta_id | BIGINT | FK detalle_ventas(id) ON DELETE RESTRICT NOT NULL | Item original vendido |
| cantidad_devuelta | NUMERIC(12,4) | CHECK > 0 NOT NULL | Cantidad devuelta |
| monto_devuelto | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Monto a reembolsar |
| almacen_retorno_id | BIGINT | FK almacenes(id) NOT NULL | Almacén donde regresa |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |

**Trigger Automático:**
- Aumenta `inventario` en `almacen_retorno_id`
- Registra movimiento tipo `DEVOLUCION_VENTA` en `movimientos_inventario`

---

## 6. MÓDULO CAJA - Control Diario

### Tabla: arqueos_caja

**Propósito:** Apertura y cierre diario de caja.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| fecha_apertura | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Fecha/hora apertura |
| fecha_cierre | TIMESTAMPTZ | NULL | Fecha/hora cierre (null si abierta) |
| usuario_apertura_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario que abrió |
| usuario_cierre_id | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario que cerró |
| monto_inicial | NUMERIC(12,2) | CHECK >= 0 NOT NULL | Efectivo inicial |
| monto_final | NUMERIC(12,2) | CHECK >= 0 | Efectivo final (al cierre) |
| total_ventas_efectivo | NUMERIC(12,2) | DEFAULT 0 | Total ventas en efectivo |
| total_ventas_qr | NUMERIC(12,2) | DEFAULT 0 | Total ventas por QR |
| total_esperado | NUMERIC(12,2) | NULL | Monto esperado (calculado) |
| diferencia | NUMERIC(12,2) | NULL | Sobrante (+) o Faltante (-) |
| estado | VARCHAR(20) | CHECK IN ('abierta','cerrada') DEFAULT 'abierta' | Estado |
| notas_apertura | TEXT | NULL | Observaciones apertura |
| notas_cierre | TEXT | NULL | Observaciones cierre |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |

**Regla de Negocio:**
- Solo puede haber un arqueo `abierta` por día
- `total_esperado = monto_inicial + total_ventas_efectivo - gastos`
- `diferencia = monto_final - total_esperado`

---

### Tabla: movimientos_caja

**Propósito:** Registro detallado de entradas/salidas de efectivo.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| arqueo_caja_id | BIGINT | FK arqueos_caja(id) ON DELETE CASCADE NOT NULL | Arqueo del día |
| tipo_movimiento | VARCHAR(20) | CHECK IN ('venta','gasto','retiro','ingreso_extra') NOT NULL | Tipo |
| monto | NUMERIC(12,2) | CHECK > 0 NOT NULL | Monto del movimiento |
| concepto | TEXT | NOT NULL | Descripción del movimiento |
| venta_id | BIGINT | FK ventas(id) ON DELETE SET NULL | Referencia a venta (si aplica) |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL NOT NULL | Usuario |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp |

---

## 7. MÓDULO AUDITORÍA - Trazabilidad

### Tabla: logs_auditoria

**Propósito:** Registro completo de cambios críticos en el sistema.

**Atributos:**

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Identificador único |
| tabla_afectada | VARCHAR(100) | NOT NULL | Nombre de la tabla modificada |
| registro_id | BIGINT | NOT NULL | ID del registro afectado |
| accion | VARCHAR(20) | CHECK IN ('INSERT','UPDATE','DELETE') NOT NULL | Tipo de acción |
| valores_anteriores | JSONB | NULL | Estado del registro antes del cambio |
| valores_nuevos | JSONB | NULL | Estado del registro después del cambio |
| usuario_id | BIGINT | FK usuarios(id) ON DELETE SET NULL | Usuario responsable |
| ip_address | INET | NULL | Dirección IP del cliente |
| user_agent | TEXT | NULL | Navegador/app del cliente |
| creado_en | TIMESTAMPTZ | DEFAULT CURRENT_TIMESTAMP | Timestamp del cambio |

**Uso:**
```sql
-- Ejemplo de registro automático
-- Al actualizar precio de producto:
{
  "tabla_afectada": "productos",
  "registro_id": 15,
  "accion": "UPDATE",
  "valores_anteriores": {"precio_venta": 50.00},
  "valores_nuevos": {"precio_venta": 55.00},
  "usuario_id": 1
}
```

**Índices:**
- `idx_logs_tabla` en `tabla_afectada`
- `idx_logs_registro` en `registro_id`
- `idx_logs_usuario` en `usuario_id`
- `idx_logs_fecha` en `creado_en`
- `idx_logs_accion` en `accion`

---

# RESUMEN DE TABLAS CRÍTICAS

## Ranking por Importancia:

### Nivel 1 - CORE (Críticas):
1. **productos** - Sin productos no hay negocio
2. **inventario** - Control de stock esencial
3. **ventas** - Registro de ingresos
4. **usuarios** - Control de acceso

### Nivel 2 - OPERACIONALES (Muy Importantes):
5. **detalle_ventas** - Desglose de ventas
6. **compras** - Reposición de stock
7. **movimientos_inventario** - Trazabilidad
8. **clientes** - Base de clientes

### Nivel 3 - SOPORTE (Importantes):
9. **creditos_clientes** - Gestión de crédito
10. **arqueos_caja** - Control financiero
11. **proveedores** - Gestión compras
12. **categorias** - Organización catálogo

### Nivel 4 - CONFIGURACIÓN (Necesarias):
13. **roles** - Control acceso
14. **unidades_medida** - Conversiones
15. **almacenes** - Ubicaciones

### Nivel 5 - AUDITORÍA Y DETALLES:
16. **logs_auditoria** - Trazabilidad cambios
17. **pagos_credito** - Abonos
18. **devoluciones_venta** - Devoluciones
19. Demás tablas...

---

**FIN PARTE 1 - TABLAS MÁS IMPORTANTES CON ATRIBUTOS**

---

