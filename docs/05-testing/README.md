# 📚 Documentación de Testing

Esta carpeta contiene toda la documentación relacionada con **BDD (Behaviour-Driven Development)** y **TDD (Test-Driven Development)**.

---

## 📂 Estructura

```
05-testing/
├── README.md                    # Este archivo
├── 01-introduccion.md          # Introducción a BDD/TDD
├── 02-bdd-guide.md             # Guía completa de BDD
├── 03-tdd-guide.md             # Guía completa de TDD
├── 04-workflow.md              # Workflow BDD/TDD para el proyecto
└── 05-coverage-reports.md      # Reportes de cobertura de tests
```

---

## 🎯 Propósito

### **BDD (Behaviour-Driven Development)**
- Especificar comportamiento del sistema en lenguaje natural
- Formato Given-When-Then
- Colaboración entre stakeholders y desarrolladores
- Documentación viva y ejecutable

### **TDD (Test-Driven Development)**
- Escribir tests ANTES de código
- Ciclo: Red → Green → Refactor
- Garantizar cobertura de código
- Diseño emergente y código limpio

---

## 🚀 Guías Disponibles

### **Para empezar:**
1. Lee [`01-introduccion.md`](01-introduccion.md) - Conceptos básicos
2. Sigue [`04-workflow.md`](04-workflow.md) - Proceso paso a paso

### **Para profundizar:**
- [`02-bdd-guide.md`](02-bdd-guide.md) - Técnicas avanzadas de BDD
- [`03-tdd-guide.md`](03-tdd-guide.md) - Patrones de TDD

---

## 🔗 Relación con Features

Cada especificación BDD en [`docs/06-features/`](../06-features/) tiene tests correspondientes en:
- `backend/tests/Feature/` - Tests de integración (BDD)
- `backend/tests/Unit/` - Tests unitarios (TDD)

---

## 📊 Estado Actual

| Módulo | Specs BDD | Tests Feature | Tests Unit | Cobertura |
|--------|-----------|---------------|------------|-----------|
| Auth | ⏳ Pendiente | ⏳ Pendiente | ⏳ Pendiente | 0% |
| Products | ⏳ Pendiente | ⏳ Pendiente | ⏳ Pendiente | 0% |
| Inventory | ⏳ Pendiente | ⏳ Pendiente | ⏳ Pendiente | 0% |
| Sales | ⏳ Pendiente | ⏳ Pendiente | ⏳ Pendiente | 0% |
| Customers | ⏳ Pendiente | ⏳ Pendiente | ⏳ Pendiente | 0% |

---

## 🎓 Referencias

- **Conventional Commits:** Para mensajes de commit de tests
- **Pest PHP:** Framework de testing moderno para Laravel
- **PHPUnit:** Testing framework base de Laravel
- **Behat:** BDD framework para PHP (opcional)

---

## 📝 Convenciones

### **Nomenclatura de Tests:**

```php
// Feature Tests (BDD)
tests/Feature/Auth/LoginTest.php
tests/Feature/Sales/ProcessCreditSaleTest.php

// Unit Tests (TDD)
tests/Unit/Models/ProductTest.php
tests/Unit/Services/InventoryServiceTest.php
```

### **Estructura de Test:**

```php
// Usando Pest (recomendado)
it('procesa venta a crédito cuando cliente tiene crédito disponible', function () {
    // Given - Preparación
    $cliente = Cliente::factory()->create(['limite_credito' => 5000]);
    
    // When - Ejecución
    $venta = $this->saleService->procesar(...);
    
    // Then - Verificación
    expect($venta['estado'])->toBe('completada');
});
```

---

## 🔄 Actualización

Esta documentación se actualiza con cada nueva feature implementada.

**Última actualización:** 27 de noviembre de 2025
