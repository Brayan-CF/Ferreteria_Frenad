# 🎭 Features - Especificaciones BDD

Esta carpeta contiene las **especificaciones de comportamiento** del sistema en formato BDD (Behaviour-Driven Development).

---

## 📂 Estructura

```
06-features/
├── README.md                # Este archivo
├── 01-auth.feature.md       # Autenticación y autorización ✅
├── 02-products.feature.md   # Gestión de productos ✅
├── 03-inventory.feature.md  # Control de inventario ✅
├── 04-sales.feature.md      # Procesamiento de ventas ✅
├── 05-customers.feature.md  # Gestión de clientes ✅
├── 06-purchases.feature.md  # Gestión de compras ✅
└── 07-reports.feature.md    # Reportes y análisis ✅
```

---

## 🎯 ¿Qué es una Feature?

Una **feature** (característica) describe el **comportamiento esperado** de una funcionalidad desde la perspectiva del usuario.

### **Formato:**

```markdown
## Feature: [Nombre de la funcionalidad]

Como [tipo de usuario]
Quiero [realizar acción]
Para que [beneficio/objetivo]

### Scenario: [Descripción del escenario]

**Given** [contexto inicial]
**And** [más contexto]
**When** [acción que se ejecuta]
**Then** [resultado esperado]
**And** [más resultados]
```

---

## 📋 Ejemplo: Venta a Crédito

```markdown
## Feature: Procesar Venta a Crédito

Como **cajero**
Quiero **procesar una venta a crédito para un cliente frecuente**
Para que **el cliente pueda llevarse el producto y pagar después**

---

### Scenario: Venta a crédito exitosa

**Given** existe un cliente frecuente "Juan Pérez" con ID 5
**And** tiene un límite de crédito de Bs. 5,000
**And** tiene una deuda actual de Bs. 1,200
**And** el producto "Cemento EMISA 50kg" existe con precio Bs. 55.00
**And** hay 100 unidades en stock en el almacén

**When** el cajero procesa una venta a crédito de:
- Cliente: Juan Pérez (ID: 5)
- Productos: 10 unidades de Cemento
- Total: Bs. 550.00

**Then** la venta se registra exitosamente
**And** se crea un crédito por Bs. 550.00
**And** el inventario se reduce de 100 a 90 unidades
**And** la deuda total del cliente es Bs. 1,750
```

---

## 🔗 Relación con Tests

Cada **Scenario** en una feature tiene un **test correspondiente** en:

```
docs/06-features/04-sales.feature.md
  Scenario: Venta a crédito exitosa
           ↓
backend/tests/Feature/Sales/ProcessCreditSaleTest.php
  it('procesa venta a crédito cuando cliente tiene crédito disponible')
```

---

## 📊 Estado de Features

| Feature | Estado | Scenarios | Tests Implementados | Cobertura |
|---------|--------|-----------|---------------------|-----------|
| Auth | ⏳ Pendiente | 0 | 0 | 0% |
| Products | ⏳ Pendiente | 0 | 0 | 0% |
| Inventory | ⏳ Pendiente | 0 | 0 | 0% |
| Sales | ⏳ Pendiente | 0 | 0 | 0% |
| Customers | ⏳ Pendiente | 0 | 0 | 0% |
| Purchases | ⏳ Pendiente | 0 | 0 | 0% |
| Cash | ⏳ Pendiente | 0 | 0 | 0% |
| Reports | ⏳ Pendiente | 0 | 0 | 0% |

---

## 🎓 Beneficios de BDD

1. **Lenguaje Natural:** Cualquiera puede entender las specs
2. **Documentación Viva:** Siempre actualizada (son tests ejecutables)
3. **Colaboración:** Stakeholders, devs y QA en la misma página
4. **Trazabilidad:** Desde requisito hasta implementación
5. **Confianza:** Tests automatizan verificación de comportamiento

---

## 🚀 Workflow

### **1. Escribir Feature (BDD)**
```bash
# Crear/editar especificación
vim docs/06-features/04-sales.feature.md
```

### **2. Escribir Test que Falla (TDD - RED)**
```bash
# Crear test basado en scenario
vim backend/tests/Feature/Sales/ProcessCreditSaleTest.php

# Ejecutar (debe fallar)
./vendor/bin/pest tests/Feature/Sales/ProcessCreditSaleTest.php
```

### **3. Implementar Código Mínimo (TDD - GREEN)**
```bash
# Implementar funcionalidad
vim backend/app/Services/SaleService.php

# Ejecutar (debe pasar)
./vendor/bin/pest tests/Feature/Sales/ProcessCreditSaleTest.php
```

### **4. Refactorizar (TDD - REFACTOR)**
```bash
# Mejorar código manteniendo tests verdes
vim backend/app/Services/SaleService.php

# Ejecutar (debe seguir pasando)
./vendor/bin/pest
```

---

## 📝 Convenciones

### **Nomenclatura de Archivos:**
- `01-auth.feature.md` - Autenticación
- `02-products.feature.md` - Productos
- `XX-nombre.feature.md` - Patrón general

### **Palabras Clave BDD:**
- **Given:** Contexto inicial (precondiciones)
- **When:** Acción que se ejecuta
- **Then:** Resultado esperado (postcondiciones)
- **And:** Continuación de cualquiera de las anteriores
- **But:** Negación o excepción

### **Estructura de Scenario:**
```
### Scenario: [Caso exitoso | Caso de error | Edge case]

**Given** [contexto]
**When** [acción]
**Then** [resultado]
```

---

## 🔄 Actualización

Cada vez que se implementa una nueva funcionalidad:
1. ✅ Escribir feature con scenarios
2. ✅ Implementar tests
3. ✅ Actualizar tabla de estado arriba
4. ✅ Commitear con mensaje: `docs(bdd): Agregar feature X`

---

## 📚 Referencias

- **Gherkin Language:** Sintaxis Given-When-Then
- **BDD con Pest:** https://pestphp.com/
- **Cucumber Best Practices:** https://cucumber.io/docs/bdd/
- **Feature Mapping:** Técnica de diseño de features

---

**Última actualización:** 27 de noviembre de 2025
