# 🏛️ ARQUITECTURA DE BASE DE DATOS

## 📐 Diseño General

La base de datos está diseñada siguiendo principios de **normalización 3FN** (Tercera Forma Normal) con enfoque en:

1. **Modularidad:** Cada módulo es independiente
2. **Escalabilidad:** Preparada para crecimiento
3. **Mantenibilidad:** Código limpio y documentado
4. **Performance:** Índices estratégicos

---

## 🗂️ Módulos del Sistema

### **1. AUTH - Autenticación y Autorización**

**Tablas:**
- `usuarios` - Empleados del sistema
- `roles` - Roles predefinidos
- `usuario_roles` - Relación N:N

**Características:**
- Sistema de roles múltiples por usuario
- Passwords hasheados con bcrypt
- Auditoría de asignación de roles

```sql
usuarios (1) ──┐
               ├── (N) usuario_roles (N) ── roles (1)
usuarios (1) ──┘
```

---

### **2. PRODUCTS - Catálogo de Productos**

**Tablas:**
- `productos` - Catálogo completo
- `categorias` - Clasificación de productos
- `marcas` - Fabricantes
- `unidades_medida` - Metro, pieza, kg, etc.
- `producto_unidades` - Conversiones de unidades

**Características:**
- SKU único por producto
- Código de barras opcional
- Conversiones de unidades (vender por metro, comprar por rollo)
- Fecha de vencimiento para productos perecederos

```sql
productos (1) ── (N) producto_unidades (N) ── unidades_medida (1)
productos (N) ── (1) categorias
productos (N) ── (1) marcas
```

---

### **3. INVENTORY - Gestión de Inventario**

**Tablas:**
- `almacenes` - Bodega y Mostrador
- `inventario` - Stock por producto y almacén
- `movimientos_inventario` - Kardex completo

**Características:**
- Stock separado por almacén
- Kardex con trazabilidad completa
- Alertas de stock bajo
- Transferencias entre almacenes

```sql
productos (1) ──┐
                ├── (N) inventario (N) ── almacenes (1)
productos (1) ──┘

productos (1) ── (N) movimientos_inventario (N) ── almacenes (1)
```

---

### **4. PURCHASES - Gestión de Compras**

**Tablas:**
- `proveedores` - Base de datos de proveedores
- `ordenes_compra` - Pedidos enviados
- `compras` - Recepción de mercadería
- `detalle_compras` - Items específicos

**Características:**
- Flujo: Orden → Recepción
- Actualización automática de inventario
- Múltiples almacenes de destino

```sql
proveedores (1) ── (N) ordenes_compra
proveedores (1) ── (N) compras
compras (1) ── (N) detalle_compras (N) ── productos
```

---

### **5. SALES - Punto de Venta**

**Tablas:**
- `ventas` - Cabecera de ventas
- `detalle_ventas` - Items vendidos
- `devoluciones_venta` - Devoluciones procesadas
- `detalle_devoluciones_venta` - Items devueltos

**Características:**
- POS con efectivo y QR
- Factura, recibo o nota de venta
- Descuentos por item o venta completa
- IVA 13% (Bolivia) opcional
- Devoluciones el mismo día

```sql
clientes (1) ── (N) ventas (1) ── (N) detalle_ventas (N) ── productos
ventas (1) ── (N) devoluciones_venta (1) ── (N) detalle_devoluciones_venta
```

---

### **6. CUSTOMERS - Gestión de Clientes**

**Tablas:**
- `clientes` - Base de datos de clientes
- `creditos_clientes` - Control de deuda
- `pagos_credito` - Abonos realizados

**Características:**
- Clientes frecuentes con límite de crédito
- Control de pagos parciales
- Alertas de vencimiento
- Cálculo automático de saldo

```sql
clientes (1) ── (N) creditos_clientes (N) ── ventas (1)
creditos_clientes (1) ── (N) pagos_credito
```

---

### **7. CASH - Control de Caja**

**Tablas:**
- `arqueos_caja` - Apertura y cierre diario
- `movimientos_caja` - Entradas y salidas

**Características:**
- Apertura diaria con monto inicial
- Registro de todas las transacciones
- Cierre con cálculo automático
- Detección de diferencias (sobrante/faltante)

```sql
arqueos_caja (1) ── (N) movimientos_caja
movimientos_caja (N) ── ventas (1)
```

---

### **8. AUDIT - Auditoría**

**Tablas:**
- `logs_auditoria` - Registro de cambios críticos

**Características:**
- Log de INSERT, UPDATE, DELETE
- Valores anteriores y nuevos en JSONB
- IP y user agent
- Búsqueda por tabla, usuario o fecha

```sql
logs_auditoria (N) ── usuarios (1)
```

---

## 🔗 Diagrama Entidad-Relación Completo

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  USUARIOS   │────▶│USUARIO_ROLES│◀────│    ROLES    │
└──────┬──────┘     └─────────────┘     └─────────────┘
       │
       │ creado_por
       ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  PRODUCTOS  │────▶│ INVENTARIO  │◀────│  ALMACENES  │
└──────┬──────┘     └──────┬──────┘     └─────────────┘
       │                   │
       │                   │ movimientos
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│ CATEGORIAS  │     │MOV_INVENT   │
└─────────────┘     └─────────────┘

┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ PROVEEDORES │────▶│   COMPRAS   │────▶│DETALLE_COMP │
└─────────────┘     └─────────────┘     └─────────────┘

┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  CLIENTES   │────▶│   VENTAS    │────▶│DETALLE_VENTA│
└──────┬──────┘     └──────┬──────┘     └─────────────┘
       │                   │
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐
│  CREDITOS   │     │ DEVOLUCIONES│
└──────┬──────┘     └─────────────┘
       │
       ▼
┌─────────────┐
│PAGOS_CREDITO│
└─────────────┘

┌─────────────┐     ┌─────────────┐
│ARQUEOS_CAJA │────▶│MOV_CAJA     │
└─────────────┘     └─────────────┘

┌─────────────┐
│LOGS_AUDITOR │
└─────────────┘
```

---

## 📊 Tipos de Relaciones

| Relación | Tipo | Descripción |
|----------|------|-------------|
| usuarios → ventas | 1:N | Un usuario registra múltiples ventas |
| productos → inventario | 1:N | Un producto en múltiples almacenes |
| ventas → detalle_ventas | 1:N | Una venta con múltiples items |
| clientes → creditos | 1:N | Un cliente con múltiples créditos |
| usuarios → roles | N:N | Usuarios con roles múltiples |

---

## 🔐 Restricciones de Integridad

### **Foreign Keys:**
```sql
-- Ejemplo: Detalle de ventas
CONSTRAINT fk_venta 
    FOREIGN KEY (venta_id) 
    REFERENCES ventas(id) 
    ON DELETE CASCADE

CONSTRAINT fk_producto 
    FOREIGN KEY (producto_id) 
    REFERENCES productos(id) 
    ON DELETE RESTRICT
```

### **Check Constraints:**
```sql
CHECK (cantidad > 0)
CHECK (precio_venta >= precio_compra)
CHECK (estado IN ('pendiente', 'completada', 'anulada'))
```

---

## 🎯 Decisiones de Diseño

### **1. Timestamps con Zona Horaria**
```sql
creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
```
**Razón:** Bolivia (UTC-4) requiere zona horaria explícita.

### **2. Soft Deletes vs Hard Deletes**
- **Soft Delete:** `activo BOOLEAN` en tablas maestras
- **Hard Delete:** `ON DELETE CASCADE` en detalles

### **3. Auditoría Completa**
```sql
creado_por BIGINT REFERENCES usuarios(id)
actualizado_por BIGINT REFERENCES usuarios(id)
```

### **4. Números Correlativos Automáticos**
```sql
numero_venta VARCHAR(50) -- Formato: YYYYMMDD-0001
```
Generado por trigger para garantizar unicidad.

---

## 📈 Estrategia de Crecimiento

### **Fase 1: Actual**
- ✅ 1 sucursal
- ✅ ~50,000 ventas/año
- ✅ ~2,000 productos

### **Fase 2: Expansión**
- 🔄 Multi-sucursal
- 🔄 ~200,000 ventas/año
- 🔄 ~10,000 productos

### **Fase 3: Escalamiento**
- 📋 Facturación electrónica
- 📋 API para e-commerce
- 📋 Business Intelligence

---

**Siguiente:** [Módulo AUTH](02-modulos/01-auth.md)