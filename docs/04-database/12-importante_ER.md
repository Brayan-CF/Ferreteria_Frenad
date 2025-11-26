---

# PARTE 2: DIAGRAMA ENTIDAD-RELACIÓN COMPLETO CON CARDINALIDADES

## Convenciones de Notación

**Cardinalidades utilizadas:**
- `(1)` - Uno y solo uno
- `(0,1)` - Cero o uno (opcional)
- `(1,N)` - Uno a muchos
- `(0,N)` - Cero a muchos
- `(N,N)` - Muchos a muchos

**Símbolos:**
- `──` - Relación directa
- `┌─┐` - Entidad fuerte
- `└─┘` - Entidad débil
- `◇` - Atributo derivado
- `*PK*` - Llave primaria
- `*FK*` - Llave foránea

---

## DIAGRAMA GENERAL DEL SISTEMA

```
════════════════════════════════════════════════════════════════════════════
                         SISTEMA POS FERRETERÍA FRENAD
                    DIAGRAMA ENTIDAD-RELACIÓN COMPLETO
════════════════════════════════════════════════════════════════════════════

┌──────────────────────────────────────────────────────────────────────────┐
│                           MÓDULO AUTENTICACIÓN                            │
└──────────────────────────────────────────────────────────────────────────┘

           ┌─────────────────┐                ┌─────────────────┐
           │    USUARIOS     │                │      ROLES      │
           │    (STRONG)     │                │    (STRONG)     │
           ├─────────────────┤                ├─────────────────┤
           │ *PK* id         │                │ *PK* id         │
           │      nombre     │                │      nombre     │
           │      email      │◄───────┐       │  descripcion    │
           │ password_hash   │        │       └────────┬────────┘
           │      activo     │        │                │
           └────────┬────────┘        │                │
                    │                 │                │
                    │(1)              │(N)         (N) │
                    │                 │                │
                    │        ┌────────┴────────┐       │
                    │        │ USUARIO_ROLES   │       │
                    │        │   (ASOCIATIVA)  │       │
                    │        ├─────────────────┤       │
                    │        │ *PK,FK* usuario_│       │
                    └────────┤         id      │       │
                             │ *PK,FK* rol_id  ├───────┘
                             │ asignado_en     │
                             │ asignado_por *FK│
                             └─────────────────┘

Cardinalidad: USUARIOS (1,N) ──── (N,N) ──── ROLES (1,N)
Interpretación: Un usuario puede tener varios roles, un rol puede 
                pertenecer a varios usuarios.


┌──────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO CATÁLOGO                                  │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────┐        ┌──────────────┐        ┌──────────────┐
│  CATEGORIAS  │        │    MARCAS    │        │  UNIDADES_   │
│   (STRONG)   │        │   (STRONG)   │        │   MEDIDA     │
├──────────────┤        ├──────────────┤        ├──────────────┤
│ *PK* id      │        │ *PK* id      │        │ *PK* id      │
│    nombre    │        │    nombre    │        │    nombre    │
│ descripcion  │        │ descripcion  │        │ abreviatura  │
│    activo    │        │ pais_origen  │        │    tipo      │
└──────┬───────┘        └──────┬───────┘        └──────┬───────┘
       │(1)                    │(1)                    │(1)
       │                       │                       │
       │                       │                       │
       │           ┌───────────┴───────────┐           │
       │           │                       │           │
       │(0,N)      │(0,N)                  │(1,N)      │(1,N)
       │           │                       │           │
       └───────────┼───────►┌──────────────┴───────────┼─────┐
                   │        │     PRODUCTOS            │     │
                   │        │      (STRONG)            │     │
                   │        ├──────────────────────────┤     │
                   │        │ *PK* id                  │     │
                   │        │      sku (UNIQUE)        │     │
                   │        │      codigo_barras       │     │
                   │        │      nombre              │     │
                   └────────┤ *FK* categoria_id        │     │
                            │ *FK* marca_id            ├─────┘
                            │ *FK* unidad_base_id      │
                            │      precio_compra       │
                            │      precio_venta        │
                            │ fecha_vencimiento        │
                            │ ubicacion_fisica         │
                            │      activo              │
                            └──────────┬───────────────┘
                                       │(1)
                                       │
                                       │(0,N)
                                       │
                            ┌──────────▼───────────────┐
                            │  PRODUCTO_UNIDADES       │
                            │    (ASOCIATIVA)          │
                            ├──────────────────────────┤
                            │ *PK* id                  │
                            │ *FK* producto_id         │
                            │ *FK* unidad_id           ├──┐
                            │ factor_conversion        │  │
                            │ es_unidad_compra         │  │(N)
                            │ es_unidad_venta          │  │
                            └──────────────────────────┘  │
                                                          │
                            ┌─────────────────────────────┘
                            │
                            │(1)
                            ▼
                    ┌───────────────┐
                    │ UNIDADES_MED  │
                    │   (STRONG)    │
                    └───────────────┘

Cardinalidad Productos-Categorías: (N,1) Muchos productos, una categoría
Cardinalidad Productos-Marcas: (N,1) Muchos productos, una marca
Cardinalidad Productos-Unidad Base: (N,1) Muchos productos, una unidad base
Cardinalidad Productos-Conversiones: (1,N) Un producto, múltiples conversiones


┌──────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO INVENTARIO                                │
└──────────────────────────────────────────────────────────────────────────┘

                            ┌──────────────┐
                            │  ALMACENES   │
                            │   (STRONG)   │
                            ├──────────────┤
                            │ *PK* id      │
                            │    nombre    │
                            │    tipo      │
                            │  ubicacion   │
                            │    activo    │
                            └──────┬───────┘
                                   │(1)
                    ┌──────────────┼──────────────┐
                    │(N)           │(N)           │(N)
                    │              │              │
         ┌──────────▼─────┐   ┌───▼────────────┐ │
         │   INVENTARIO   │   │  MOVIMIENTOS_  │ │
         │  (ASOCIATIVA)  │   │  INVENTARIO    │ │
         ├────────────────┤   │   (KARDEX)     │ │
         │*PK,FK*producto_│   ├────────────────┤ │
         │       id       │◄──┤*FK* producto_id│ │
         │*PK,FK*almacen_ │   │*FK* almacen_id │◄┘
         │       id       │   │*FK* almacen_   │
         │cantidad_actual │   │  destino_id    │
         │ stock_minimo   │   │ tipo_movimiento│
         │ultima_actualiz.│   │    cantidad    │
         └────────────────┘   │     razon      │
                              │ *FK* compra_id │
                              │ *FK* venta_id  │
                              │ *FK* usuario_id│
                              └────────────────┘

┌─────────────┐
│  PRODUCTOS  │
└──────┬──────┘
       │(1)
       ├──────────────────┐
       │(N)               │(N)
       ▼                  ▼
┌──────────────┐   ┌─────────────────┐
│  INVENTARIO  │   │  MOVIMIENTOS_   │
└──────────────┘   │  INVENTARIO     │
                   └─────────────────┘

Cardinalidad Productos-Inventario: (1,N) Un producto en múltiples almacenes
Cardinalidad Almacenes-Inventario: (1,N) Un almacén con múltiples productos
Cardinalidad Productos-Movimientos: (1,N) Un producto, múltiples movimientos
Cardinalidad Almacenes-Movimientos: (1,N) Un almacén, múltiples movimientos

**Clave Primaria Compuesta en INVENTARIO:**
PRIMARY KEY (producto_id, almacen_id)


┌──────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO COMPRAS                                   │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│   PROVEEDORES    │
│    (STRONG)      │
├──────────────────┤
│ *PK* id          │
│  razon_social    │
│      nit         │
│    telefono      │
│   direccion      │
│     email        │
│ nombre_contacto  │
│     activo       │
└────────┬─────────┘
         │(1)
         ├─────────────────────┐
         │(0,N)                │(0,N)
         │                     │
┌────────▼──────────┐   ┌──────▼────────────┐
│ ORDENES_COMPRA    │   │     COMPRAS       │
│    (WEAK)         │   │     (WEAK)        │
├───────────────────┤   ├───────────────────┤
│ *PK* id           │   │ *PK* id           │
│  numero_orden     │◄──┤*FK* orden_compra_ │
│*FK* proveedor_id  │   │        id         │
│  fecha_orden      │   │*FK* proveedor_id  │
│ fecha_entrega_esp.│   │ numero_factura_   │
│ total_ordenado    │   │   proveedor       │
│     estado        │   │  fecha_compra     │
│     notas         │   │ fecha_entrega_esp.│
│*FK* usuario_id    │   │ fecha_entrega_real│
└───────────────────┘   │    subtotal       │
         │(1)           │   impuestos       │
         │              │     total         │
         │(0,1)         │  metodo_pago      │
         └──────────────┤     estado        │
                        │     notas         │
                        │*FK* usuario_id    │
                        └─────────┬─────────┘
                                  │(1)
                                  │
                                  │(1,N)
                                  │
                        ┌─────────▼─────────┐
                        │ DETALLE_COMPRAS   │
                        │    (DETAIL)       │
                        ├───────────────────┤
                        │ *PK* id           │
                        │ *FK* compra_id    │
                        │ *FK* producto_id  ├──┐
                        │     cantidad      │  │
                        │ *FK* unidad_id    │  │(N)
                        │ precio_unitario   │  │
                        │    subtotal       │  │
                        │*FK* almacen_dest. │  │
                        │        id         │◄─┘
                        └───────────────────┘  │
                                              │(1)
                        ┌─────────────────────┘
                        │
                        ▼
                ┌───────────────┐
                │   PRODUCTOS   │
                └───────────────┘

Cardinalidad Proveedores-Órdenes: (1,N) Un proveedor, muchas órdenes
Cardinalidad Proveedores-Compras: (1,N) Un proveedor, muchas compras
Cardinalidad Órdenes-Compras: (1,N) Una orden, múltiples recepciones (parciales)
Cardinalidad Compras-Detalle: (1,N) Una compra, múltiples items
Cardinalidad Productos-Detalle: (1,N) Un producto en múltiples compras

**Trigger Automático:**
Al insertar en DETALLE_COMPRAS → Actualiza INVENTARIO y MOVIMIENTOS_INVENTARIO


┌──────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO VENTAS                                    │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│    CLIENTES      │
│    (STRONG)      │
├──────────────────┤
│ *PK* id          │
│ nombre_completo  │
│      nit         │
│   telefono       │
│   direccion      │
│     email        │
│ es_frecuente     │
│ limite_credito   │
│    activo        │
└────────┬─────────┘
         │(1)
         │
         │(0,N)
         │
┌────────▼──────────┐
│      VENTAS       │
│     (WEAK)        │
├───────────────────┤
│ *PK* id           │
│  numero_venta     │
│*FK* cliente_id    │◄──────────┐
│*FK* usuario_id    │           │(0,1) cliente opcional
│  fecha_venta      │           │
│  tipo_venta       │           │
│ tipo_documento    │           │
│  metodo_pago      │           │
│   subtotal        │           │
│ descuento_monto   │           │
│      iva          │           │
│     total         │           │
│    estado         │           │
└─────────┬─────────┘           │
          │(1)                  │
          │                     │
          │(1,N)                │
          │                     │
┌─────────▼─────────┐           │
│ DETALLE_VENTAS    │           │
│    (DETAIL)       │           │
├───────────────────┤           │
│ *PK* id           │           │
│ *FK* venta_id     │           │
│ *FK* producto_id  ├──┐        │
│ *FK* almacen_id   │  │        │
│    cantidad       │  │(N)     │
│ *FK* unidad_id    │  │        │
│ precio_unitario   │  │        │
│ descuento_monto   │  │        │
│   subtotal        │  │        │
└───────────────────┘  │        │
          │(1)         │(1)     │
          │            │        │
          │            ▼        │
          │      ┌───────────┐  │
          │      │ PRODUCTOS │  │
          │      └───────────┘  │
          │                     │
          │(0,N)                │
          │                     │
┌─────────▼─────────┐           │
│ DEVOLUCIONES_VENTA│           │
│     (WEAK)        │           │
├───────────────────┤           │
│ *PK* id           │           │
│ numero_devolucion │           │
│ *FK* venta_id     ├───────────┘
│ tipo_devolucion   │
│ fecha_devolucion  │
│ total_devuelto    │
│     razon         │
│    estado         │
│ *FK* usuario_id   │
└─────────┬─────────┘
          │(1)
          │
          │(1,N)
          │
┌─────────▼──────────────┐
│ DETALLE_DEVOLUCIONES   │
│      (DETAIL)          │
├────────────────────────┤
│ *PK* id                │
│*FK* devolucion_venta_id│
│*FK* detalle_venta_id   │
│  cantidad_devuelta     │
│  monto_devuelto        │
│*FK* almacen_retorno_id │
└────────────────────────┘

Cardinalidad Clientes-Ventas: (1,N) Un cliente, múltiples ventas
                               (0,N) Si cliente NULL = venta consumidor final
Cardinalidad Ventas-Detalle: (1,N) Una venta, múltiples items
Cardinalidad Productos-Detalle: (1,N) Un producto en múltiples ventas
Cardinalidad Ventas-Devoluciones: (1,N) Una venta, múltiples devoluciones

**Trigger Automático:**
Al insertar en DETALLE_VENTAS:
  1. Valida stock suficiente
  2. Actualiza INVENTARIO (resta cantidad)
  3. Registra MOVIMIENTO_INVENTARIO tipo 'SALIDA_VENTA'


┌──────────────────────────────────────────────────────────────────────────┐
│                        MÓDULO CRÉDITOS                                    │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│    CLIENTES      │
└────────┬─────────┘
         │(1)
         │
         │(0,N)
         │
┌────────▼──────────────┐       ┌──────────────────┐
│  CREDITOS_CLIENTES    │◄──────│      VENTAS      │
│      (WEAK)           │(1,1)  └──────────────────┘
├───────────────────────┤
│ *PK* id               │
│ *FK* cliente_id       │
│ *FK* venta_id         │
│   monto_total         │
│   monto_pagado        │◄─────── Calculado automáticamente
│ saldo_pendiente       │◄─────── por trigger
│ fecha_vencimiento     │
│     estado            │◄─────── Calculado automáticamente
└─────────┬─────────────┘
          │(1)
          │
          │(0,N)
          │
┌─────────▼─────────────┐
│   PAGOS_CREDITO       │
│     (DETAIL)          │
├───────────────────────┤
│ *PK* id               │
│*FK* credito_cliente_id│
│   monto_pago          │
│   metodo_pago         │
│   fecha_pago          │
│     notas             │
│ *FK* usuario_id       │
└───────────────────────┘

Cardinalidad Clientes-Créditos: (1,N) Un cliente, múltiples créditos
Cardinalidad Ventas-Créditos: (1,1) Una venta a crédito = un crédito
Cardinalidad Créditos-Pagos: (1,N) Un crédito, múltiples pagos (abonos)

**Lógica Automática:**
```sql
saldo_pendiente = monto_total - SUM(pagos_credito.monto_pago)

IF saldo_pendiente = 0 THEN estado = 'pagado_completo'
ELSIF monto_pagado > 0 AND saldo_pendiente > 0 THEN estado = 'pagado_parcial'
ELSIF fecha_vencimiento < CURRENT_DATE AND saldo_pendiente > 0 THEN estado = 'vencido'
ELSE estado = 'pendiente'
```


┌──────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO CAJA                                      │
└──────────────────────────────────────────────────────────────────────────┘

┌────────────────────────┐
│      USUARIOS          │
└───────┬────────────────┘
        │(1)
        │
        │(0,N)
        │
┌───────▼────────────────┐
│    ARQUEOS_CAJA        │
│      (STRONG)          │
├────────────────────────┤
│ *PK* id                │
│  fecha_apertura        │
│  fecha_cierre          │
│*FK* usuario_apertura_id│
│*FK* usuario_cierre_id  │
│   monto_inicial        │
│   monto_final          │
│ total_ventas_efectivo  │◄─── Calculado en cierre
│ total_ventas_qr        │◄─── Calculado en cierre
│  total_esperado        │◄─── Calculado en cierre
│    diferencia          │◄─── monto_final - total_esperado
│     estado             │
│  notas_apertura        │
│   notas_cierre         │
└─────────┬──────────────┘
          │(1)
          │
          │(0,N)
          │
┌─────────▼──────────────┐       ┌──────────────┐
│  MOVIMIENTOS_CAJA      │       │    VENTAS    │
│     (DETAIL)           │       └──────┬───────┘
├────────────────────────┤              │(0,1)
│ *PK* id                │              │
│*FK* arqueo_caja_id     │              │
│  tipo_movimiento       │              │
│      monto             │              │
│    concepto            │              │
│ *FK* venta_id          │◄─────────────┘
│ *FK* usuario_id        │
└────────────────────────┘

Cardinalidad Usuarios-Arqueos: (1,N) Un usuario abre/cierra múltiples arqueos
Cardinalidad Arqueos-Movimientos: (1,N) Un arqueo, múltiples movimientos
Cardinalidad Ventas-Movimientos: (1,1) Venta en efectivo registra movimiento

**Regla de Negocio:**
- Solo puede haber un arqueo con estado 'abierta' por día
- Todas las ventas en efectivo del día se vinculan al arqueo activo


┌──────────────────────────────────────────────────────────────────────────┐
│                        MÓDULO AUDITORÍA                                   │
└──────────────────────────────────────────────────────────────────────────┘

┌────────────────────────┐
│      USUARIOS          │
└───────┬────────────────┘
        │(1)
        │
        │(0,N)
        │
┌───────▼────────────────┐
│   LOGS_AUDITORIA       │
│     (STRONG)           │
├────────────────────────┤
│ *PK* id                │
│  tabla_afectada        │
│   registro_id          │
│     accion             │ ◄── INSERT, UPDATE, DELETE
│ valores_anteriores     │ ◄── JSONB (estado antes)
│  valores_nuevos        │ ◄── JSONB (estado después)
│ *FK* usuario_id        │
│   ip_address           │
│   user_agent           │
│   creado_en            │
└────────────────────────┘

Cardinalidad Usuarios-Logs: (1,N) Un usuario, múltiples acciones registradas

**Características:**
- Registra cambios críticos en tablas importantes
- Campos JSONB permiten almacenar estado completo antes/después
- Inmutable: Solo INSERT, no UPDATE/DELETE
- IP y User Agent para trazabilidad completa

```

---

## RESUMEN DE CARDINALIDADES POR MÓDULO

### MÓDULO AUTH:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| usuarios → usuario_roles | 1:N | Un usuario puede tener múltiples roles |
| roles → usuario_roles | 1:N | Un rol puede estar en múltiples usuarios |
| usuarios ↔ roles | N:N | Relación muchos a muchos a través de usuario_roles |

---

### MÓDULO PRODUCTOS:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| productos → categorias | N:1 | Muchos productos pertenecen a una categoría |
| productos → marcas | N:1 | Muchos productos de una marca |
| productos → unidad_base | N:1 | Muchos productos usan una unidad base |
| productos → producto_unidades | 1:N | Un producto tiene múltiples conversiones |
| producto_unidades → unidades_medida | N:1 | Muchas conversiones usan una unidad |

---

### MÓDULO INVENTARIO:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| productos ↔ almacenes | N:N | Un producto puede estar en múltiples almacenes, un almacén tiene múltiples productos (a través de inventario) |
| productos → movimientos_inventario | 1:N | Un producto tiene múltiples movimientos |
| almacenes → movimientos_inventario | 1:N | Un almacén tiene múltiples movimientos |

---

### MÓDULO COMPRAS:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| proveedores → ordenes_compra | 1:N | Un proveedor recibe múltiples órdenes |
| proveedores → compras | 1:N | Un proveedor tiene múltiples compras |
| ordenes_compra → compras | 1:N | Una orden puede tener múltiples recepciones (parciales) |
| compras → detalle_compras | 1:N | Una compra tiene múltiples items |
| productos → detalle_compras | 1:N | Un producto aparece en múltiples compras |

---

### MÓDULO VENTAS:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| clientes → ventas | 1:N | Un cliente tiene múltiples ventas |
| ventas → detalle_ventas | 1:N | Una venta tiene múltiples items |
| productos → detalle_ventas | 1:N | Un producto se vende en múltiples ventas |
| ventas → devoluciones_venta | 1:N | Una venta puede tener múltiples devoluciones |
| devoluciones_venta → detalle_devoluciones | 1:N | Una devolución tiene múltiples items |

---

### MÓDULO CRÉDITOS:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| clientes → creditos_clientes | 1:N | Un cliente puede tener múltiples créditos |
| ventas → creditos_clientes | 1:1 | Una venta a crédito genera un crédito |
| creditos_clientes → pagos_credito | 1:N | Un crédito tiene múltiples pagos (abonos) |

---

### MÓDULO CAJA:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| usuarios → arqueos_caja | 1:N | Un usuario realiza múltiples aperturas/cierres |
| arqueos_caja → movimientos_caja | 1:N | Un arqueo tiene múltiples movimientos |
| ventas → movimientos_caja | 1:1 | Venta en efectivo registra un movimiento |

---

### MÓDULO AUDITORÍA:
| Relación | Cardinalidad | Descripción |
|----------|--------------|-------------|
| usuarios → logs_auditoria | 1:N | Un usuario genera múltiples logs de acciones |

---

## CLAVES FORÁNEAS CRÍTICAS

### Relaciones con ON DELETE CASCADE (Eliminación en Cascada):

```sql
-- Si se elimina un usuario, se eliminan sus roles asignados
usuario_roles.usuario_id → usuarios.id (ON DELETE CASCADE)

-- Si se elimina un producto, se eliminan sus conversiones
producto_unidades.producto_id → productos.id (ON DELETE CASCADE)

-- Si se elimina una venta, se eliminan sus detalles
detalle_ventas.venta_id → ventas.id (ON DELETE CASCADE)

-- Si se elimina una compra, se eliminan sus detalles
detalle_compras.compra_id → compras.id (ON DELETE CASCADE)

-- Si se elimina un arqueo, se eliminan sus movimientos
movimientos_caja.arqueo_caja_id → arqueos_caja.id (ON DELETE CASCADE)
```

---

### Relaciones con ON DELETE RESTRICT (Impedir Eliminación):

```sql
-- No se puede eliminar un producto que tiene ventas
detalle_ventas.producto_id → productos.id (ON DELETE RESTRICT)

-- No se puede eliminar un proveedor con compras registradas
compras.proveedor_id → proveedores.id (ON DELETE RESTRICT)

-- No se puede eliminar un cliente con créditos pendientes
creditos_clientes.cliente_id → clientes.id (ON DELETE RESTRICT)
```

---

### Relaciones con ON DELETE SET NULL (Mantener Registro):

```sql
-- Si se elimina un usuario, las ventas quedan sin vendedor asignado
ventas.usuario_id → usuarios.id (ON DELETE SET NULL)

-- Si se elimina una categoría, los productos quedan sin categoría
productos.categoria_id → categorias.id (ON DELETE SET NULL)

-- Si se elimina una orden, las compras quedan sin referencia
compras.orden_compra_id → ordenes_compra.id (ON DELETE SET NULL)
```

---

## INTEGRIDAD REFERENCIAL

### Restricciones de Unicidad:

```sql
-- Emails únicos
usuarios.email (UNIQUE)

-- SKU único por producto
productos.sku (UNIQUE)

-- Números correlativos únicos
ventas.numero_venta (UNIQUE)
compras.numero_compra (UNIQUE)
ordenes_compra.numero_orden (UNIQUE)

-- Nombres únicos
categorias.nombre (UNIQUE)
marcas.nombre (UNIQUE)
roles.nombre (UNIQUE)
unidades_medida.nombre (UNIQUE)
```

---

### Claves Primarias Compuestas:

```sql
-- Un producto solo puede estar una vez en un almacén
inventario: PRIMARY KEY (producto_id, almacen_id)

-- Un usuario solo puede tener un rol específico una vez
usuario_roles: PRIMARY KEY (usuario_id, rol_id)

-- Un producto solo puede tener una conversión a una unidad
producto_unidades: UNIQUE (producto_id, unidad_id)
```

---

**FIN PARTE 2 - DIAGRAMA ENTIDAD-RELACIÓN CON CARDINALIDADES**

---