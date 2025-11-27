# 🎭 Guía Avanzada de BDD (Behaviour-Driven Development)

## 🎯 Principios Fundamentales

### **1. Lenguaje Ubicuo (Ubiquitous Language)**

Todos los involucrados (stakeholders, devs, QA) usan el **mismo lenguaje** para describir el sistema.

**❌ MAL - Lenguaje técnico:**
```
Given the user record exists in the users table with status = 1
When POST request to /api/auth/login with JSON payload
Then HTTP 200 response with JWT token in Authorization header
```

**✅ BIEN - Lenguaje de negocio:**
```
Given existe un usuario activo "Juan Pérez"
When Juan intenta iniciar sesión con sus credenciales
Then el sistema le permite acceder al dashboard
```

---

## 📐 Estructura de Features

### **Formato Estándar:**

```markdown
## Feature: [Nombre descriptivo]

Como [tipo de usuario/rol]
Quiero [realizar acción]
Para que [beneficio/objetivo]

### Reglas de Negocio:
1. [Regla 1]
2. [Regla 2]

### Scenario 1: [Caso exitoso]
Given [precondiciones]
When [acción]
Then [resultado esperado]

### Scenario 2: [Caso de error]
Given [precondiciones]
When [acción]
Then [error esperado]

### Acceptance Criteria:
- [ ] Criterio 1
- [ ] Criterio 2
```

---

## 🎪 Ejemplo Completo: Venta a Crédito

### **Feature Completa:**

```markdown
## Feature: Procesar Venta a Crédito

Como cajero de la ferretería
Quiero procesar ventas a crédito para clientes frecuentes
Para que puedan llevarse productos y pagar después

---

### Reglas de Negocio:

1. Solo clientes con `es_frecuente = TRUE` pueden comprar a crédito
2. Deuda actual + nueva venta ≤ `limite_credito`
3. Stock debe ser suficiente antes de procesar venta
4. La venta crea automáticamente un registro en `creditos_clientes`
5. El inventario se descuenta inmediatamente
6. Todo debe ser transaccional (todo o nada)

---

### Scenario 1: Venta a crédito exitosa

**Given** existe un cliente frecuente "María López" con ID 3
**And** María tiene un límite de crédito de Bs. 10,000
**And** María tiene una deuda actual de Bs. 2,500
**And** el producto "Cemento Portland 50kg" (ID: 15) existe
**And** el precio del cemento es Bs. 55.00
**And** hay 200 unidades en el almacén "Bodega Central"

**When** el cajero procesa una venta a crédito:
- Cliente: María López (ID: 3)
- Productos:
  * 20 unidades de Cemento Portland (Bs. 55.00 c/u)
- Total: Bs. 1,100.00
- Tipo: crédito
- Almacén: Bodega Central

**Then** la venta se registra con estado "completada"
**And** el número de venta se genera automáticamente (formato: 20251127-0001)
**And** se crea un crédito en `creditos_clientes`:
  * `venta_id`: ID de la venta creada
  * `cliente_id`: 3
  * `monto_total`: Bs. 1,100.00
  * `saldo_pendiente`: Bs. 1,100.00
  * `estado`: "pendiente"
  * `fecha_vencimiento`: 30 días desde hoy
**And** el inventario del cemento se reduce de 200 a 180 unidades
**And** se registra un movimiento en `movimientos_inventario`:
  * `tipo_movimiento`: "SALIDA_VENTA"
  * `cantidad`: 20
  * `venta_id`: ID de la venta
**And** la deuda total de María ahora es Bs. 3,600 (2,500 + 1,100)
**And** se registra en `logs_auditoria`:
  * `usuario_id`: ID del cajero
  * `tabla_afectada`: "ventas"
  * `accion`: "INSERT"

---

### Scenario 2: Venta rechazada - cliente excede límite de crédito

**Given** existe un cliente frecuente "Carlos Rojas" con ID 8
**And** Carlos tiene un límite de crédito de Bs. 5,000
**And** Carlos tiene una deuda actual de Bs. 4,800
**And** el producto "Fierro corrugado 12mm" existe con precio Bs. 120.00

**When** el cajero intenta procesar una venta a crédito:
- Cliente: Carlos Rojas (ID: 8)
- Productos: 5 unidades de Fierro corrugado
- Total: Bs. 600.00
- Tipo: crédito

**Then** la venta es rechazada con el error:
  "Cliente excede límite de crédito disponible. Disponible: Bs. 200.00, Solicitado: Bs. 600.00"
**And** NO se crea ningún registro en `ventas`
**And** NO se crea ningún registro en `creditos_clientes`
**And** el inventario NO se modifica
**And** NO se registra movimiento en `movimientos_inventario`
**And** se registra en `logs_auditoria`:
  * `accion`: "INTENTO_VENTA_RECHAZADA"
  * `descripcion`: "Crédito insuficiente"

---

### Scenario 3: Venta rechazada - cliente no frecuente

**Given** existe un cliente ocasional "Ana Torres" con ID 12
**And** Ana tiene `es_frecuente = FALSE`
**And** existe un producto disponible

**When** el cajero intenta procesar una venta a crédito para Ana

**Then** la venta es rechazada inmediatamente con el error:
  "Solo clientes frecuentes pueden realizar compras a crédito"
**And** NO se procesan los demás pasos de validación
**And** NO se crea ningún registro

---

### Scenario 4: Venta rechazada - stock insuficiente

**Given** existe un cliente frecuente "Pedro Gómez" con crédito disponible
**And** el producto "Tubo PVC 1/2 pulgada" (ID: 25) existe
**And** hay solo 3 unidades en stock

**When** el cajero intenta procesar una venta a crédito de 10 unidades

**Then** la venta es rechazada con el error:
  "Stock insuficiente para producto 'Tubo PVC 1/2 pulgada'. Disponible: 3, Solicitado: 10"
**And** NO se crea ningún registro
**And** el inventario NO se modifica

---

### Scenario 5: Venta rechazada - producto inactivo

**Given** existe un cliente frecuente con crédito disponible
**And** el producto "Martillo antiguo" (ID: 99) tiene `estado = 'inactivo'`

**When** el cajero intenta agregar el producto a una venta

**Then** el sistema rechaza la venta con el error:
  "El producto 'Martillo antiguo' no está disponible para venta"
**And** NO se procede con la venta

---

### Scenario 6: Venta con múltiples productos

**Given** existe un cliente frecuente "Luis Mendoza" con crédito disponible
**And** existen estos productos con stock suficiente:
  * Cemento Portland: Bs. 55.00 (stock: 100)
  * Fierro corrugado: Bs. 120.00 (stock: 50)
  * Clavos 3": Bs. 25.00 (stock: 200)

**When** el cajero procesa una venta a crédito con:
- 10 unidades de Cemento (10 × 55 = 550)
- 5 unidades de Fierro (5 × 120 = 600)
- 20 unidades de Clavos (20 × 25 = 500)
- Total: Bs. 1,650.00

**Then** la venta se registra correctamente
**And** se crean 3 registros en `detalle_ventas`
**And** cada producto tiene su línea de detalle con cantidad y subtotal
**And** el inventario de los 3 productos se reduce correctamente
**And** se crean 3 movimientos en `movimientos_inventario`
**And** el total de la venta es Bs. 1,650.00

---

## Acceptance Criteria (Criterios de Aceptación):

### Funcionales:
- [x] La función `procesar_venta()` valida crédito ANTES de insertar
- [x] La transacción es ATÓMICA (si falla un paso, se revierte todo)
- [x] Los mensajes de error son claros y en español
- [x] El `numero_venta` es único por día (formato YYYYMMDD-XXXX)
- [x] El trigger de inventario se ejecuta automáticamente
- [x] El crédito se crea solo si la venta se completa
- [x] El log de auditoría registra la operación
- [x] Soporte para múltiples productos en una venta

### No Funcionales:
- [x] Tiempo de respuesta < 500ms para venta simple
- [x] Tiempo de respuesta < 1s para venta con 10+ productos
- [x] Manejo de concurrencia (2 ventas simultáneas del mismo producto)
- [x] Rollback automático en caso de error

---

## Tests Asociados:

### Feature Tests (BDD):
- `tests/Feature/Sales/ProcessCreditSaleTest.php`
- `tests/Feature/Sales/CreditLimitValidationTest.php`
- `tests/Feature/Sales/StockValidationTest.php`

### Unit Tests (TDD):
- `tests/Unit/Services/SaleServiceTest.php`
- `tests/Unit/Services/CreditServiceTest.php`
- `tests/Unit/Models/VentaTest.php`

### Database Tests:
- `docs/04-database/10-testing.md` (Sección 6.1)

---

## Trazabilidad:

- **Tabla Principal:** [`ventas`](../../04-database/02-modulos/05-ventas.md)
- **Tablas Relacionadas:** `creditos_clientes`, `detalle_ventas`, `movimientos_inventario`
- **Función:** `procesar_venta()` (database/03_functions/03_transactional.sql)
- **Trigger:** `validar_stock_venta` (database/04_triggers/02_validation_triggers.sql)
- **Vista:** `vista_clientes_deuda` (database/05_views/05_vista_clientes_deuda.sql)

---
```

---

## 🎨 Patrones de Scenarios

### **1. Happy Path (Camino Feliz)**
```
Scenario: [Nombre del caso exitoso]
Given [todo está perfecto]
When [acción normal]
Then [resultado esperado exitoso]
```

### **2. Error Handling (Manejo de Errores)**
```
Scenario: [Nombre del caso de error]
Given [condición que causa error]
When [acción]
Then [error descriptivo]
And [ningún efecto secundario]
```

### **3. Edge Cases (Casos Límite)**
```
Scenario: [Nombre del caso límite]
Given [valor en el borde - ej: límite exacto]
When [acción]
Then [comportamiento esperado]
```

### **4. Boundary Testing (Pruebas de Frontera)**
```
Scenario: [Justo en el límite]
Scenario: [Justo debajo del límite]
Scenario: [Justo sobre el límite]
```

---

## 📊 Matriz de Scenarios Recomendada

Para cada feature, cubre:

| Tipo | Descripción | Ejemplo |
|------|-------------|---------|
| ✅ **Happy Path** | Todo funciona perfecto | Login con credenciales válidas |
| ❌ **Validation Error** | Input inválido | Email mal formado |
| 🚫 **Business Rule** | Regla de negocio | Cliente no frecuente |
| 🔒 **Authorization** | Permisos | Usuario sin rol admin |
| ⚡ **Edge Case** | Caso límite | Exactamente en el límite de crédito |
| 🔄 **State Change** | Cambio de estado | De "pendiente" a "completada" |

---

## 🎯 Tips para Escribir Buenos Scenarios

### **DO ✅**

1. **Sé específico con los datos:**
```
✅ BIEN:
Given un cliente "María López" con límite de crédito Bs. 10,000

❌ MAL:
Given un cliente con crédito
```

2. **Usa nombres reales:**
```
✅ BIEN:
Given un usuario "Juan Pérez" con email "juan@example.com"

❌ MAL:
Given un usuario "user123"
```

3. **Enfócate en el comportamiento, no la implementación:**
```
✅ BIEN:
When el cajero procesa una venta a crédito

❌ MAL:
When se ejecuta INSERT INTO ventas con tipo='credito'
```

4. **Un scenario = un flujo completo:**
```
✅ BIEN:
Given cliente frecuente con crédito
When procesa venta
Then venta creada AND crédito creado AND inventario descontado

❌ MAL:
Scenario 1: crear venta
Scenario 2: crear crédito
Scenario 3: descontar inventario
```

### **DON'T ❌**

1. **No mezcles escenarios:**
```
❌ MAL:
Scenario: Venta exitosa y error de crédito
```

2. **No uses detalles técnicos:**
```
❌ MAL:
Given la tabla users tiene un registro con id=5
```

3. **No repitas contexto innecesario:**
```
❌ MAL:
Given sistema está arriba
And base de datos conectada
And usuario autenticado
```

---

## 🔧 Mantener Features Actualizadas

### **Cuando cambiar una feature:**

1. **Nueva funcionalidad** → Agregar nuevo scenario
2. **Cambio de regla de negocio** → Actualizar scenarios existentes
3. **Bug encontrado** → Agregar scenario que lo reproduzca

### **Workflow:**

```bash
# 1. Actualizar feature
vim docs/06-features/04-sales.feature.md

# 2. Actualizar tests para reflejar cambios
vim tests/Feature/Sales/ProcessCreditSaleTest.php

# 3. Commitear juntos
git add docs/06-features/04-sales.feature.md
git add tests/Feature/Sales/ProcessCreditSaleTest.php
git commit -m "docs(bdd): Actualizar feature de ventas - nueva regla de crédito"
```

---

## 📚 Referencias

- **Gherkin Reference:** https://cucumber.io/docs/gherkin/reference/
- **BDD Best Practices:** https://cucumber.io/docs/bdd/
- **Example Mapping:** https://cucumber.io/blog/bdd/example-mapping-introduction/
- **Feature Injection:** https://cucumber.io/blog/bdd/feature-injection/

---

**Última actualización:** 27 de noviembre de 2025
