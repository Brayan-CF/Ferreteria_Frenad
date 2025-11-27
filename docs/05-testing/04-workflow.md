# 🔄 Workflow Git + BDD/TDD

Esta guía describe el **flujo de trabajo completo** para implementar nuevas funcionalidades usando Git branches + BDD + TDD.

---

## 🌳 Estructura de Ramas

```
master (producción)
  └── develop (integración)
      ├── feature/bdd-auth
      ├── feature/bdd-products
      ├── feature/bdd-sales
      ├── bugfix/fix-stock-validation
      └── hotfix/critical-bug
```

---

## 📋 RAMAS PRINCIPALES

### **`master` - PRODUCCIÓN**
- ✅ Solo código 100% probado y funcional
- ✅ Cada commit es desplegable
- ✅ Protegida (no push directo)
- ✅ Solo actualizada desde `develop`

### **`develop` - DESARROLLO**
- ✅ Integración de features
- ✅ Tests pasan pero puede tener bugs menores
- ✅ Base para crear nuevas ramas

---

## 🔨 RAMAS TEMPORALES

### **`feature/*` - NUEVAS FUNCIONALIDADES**
```bash
feature/bdd-auth          # Módulo de autenticación
feature/bdd-products      # Gestión de productos
feature/bdd-sales         # Procesamiento de ventas
```

### **`bugfix/*` - CORRECCIONES**
```bash
bugfix/fix-stock-validation      # Bugs no críticos
```

### **`hotfix/*` - PARCHES CRÍTICOS**
```bash
hotfix/critical-sql-injection    # Bugs en producción
```

---

## 🎯 WORKFLOW COMPLETO: Feature con BDD/TDD

### **FASE 1: Iniciar Feature**

```bash
# 1. Actualizar develop
git checkout develop
git pull origin develop

# 2. Crear rama de feature
git checkout -b feature/bdd-sales

# 3. Verificar que estamos en la rama correcta
git branch
```

---

### **FASE 2: Escribir Especificación BDD**

```bash
# 1. Crear/editar especificación
vim docs/06-features/04-sales.feature.md
```

**Contenido del archivo:**
```markdown
## Feature: Procesar Venta a Crédito

Como cajero
Quiero procesar ventas a crédito
Para que clientes frecuentes puedan comprar

### Scenario 1: Venta a crédito exitosa
Given existe un cliente frecuente con crédito disponible
When proceso una venta a crédito
Then la venta se registra correctamente
And se crea el crédito
And se descuenta del inventario
```

```bash
# 2. Commitear especificación
git add docs/06-features/04-sales.feature.md
git commit -m "docs(bdd): Agregar especificación BDD para ventas a crédito

- Feature: Procesar venta a crédito
- 4 scenarios: exitoso, límite excedido, sin stock, cliente no frecuente
- Criterios de aceptación definidos"
```

---

### **FASE 3: TDD - RED (Test que Falla)**

```bash
# 1. Crear estructura de tests
mkdir -p backend/tests/Feature/Sales

# 2. Escribir test basado en spec BDD
vim backend/tests/Feature/Sales/ProcessCreditSaleTest.php
```

**Contenido del test:**
```php
<?php

use function Pest\Laravel\{actingAs, assertDatabaseHas};

describe('Procesar Venta a Crédito', function () {
    it('procesa venta a crédito cuando cliente tiene crédito disponible', function () {
        // Given
        $cliente = Cliente::factory()->create([
            'es_frecuente' => true,
            'limite_credito' => 5000.00,
        ]);
        
        // When
        $venta = $this->saleService->procesar([
            'cliente_id' => $cliente->id,
            'tipo_venta' => 'credito',
            // ...
        ]);
        
        // Then
        expect($venta['estado'])->toBe('completada');
        assertDatabaseHas('creditos_clientes', [
            'venta_id' => $venta['id'],
        ]);
    });
});
```

```bash
# 3. Ejecutar test (debe fallar - RED)
./vendor/bin/pest tests/Feature/Sales/ProcessCreditSaleTest.php
# ❌ Error: Class SaleService not found

# 4. Commitear test
git add backend/tests/Feature/Sales/ProcessCreditSaleTest.php
git commit -m "test(sales): Agregar test para venta a crédito (RED)

- Test basado en spec BDD
- Verifica: venta completada, crédito creado, inventario descontado
- Estado: FALLA (SaleService no existe aún)"
```

---

### **FASE 4: TDD - GREEN (Código Mínimo)**

```bash
# 1. Crear servicio mínimo
mkdir -p backend/app/Services
vim backend/app/Services/SaleService.php
```

**Contenido mínimo:**
```php
<?php

namespace App\Services;

class SaleService
{
    public function procesar(array $data)
    {
        // Implementación mínima para pasar test
        $venta = Venta::create([
            'cliente_id' => $data['cliente_id'],
            'tipo_venta' => $data['tipo_venta'],
            'estado' => 'completada',
        ]);
        
        CreditoCliente::create([
            'venta_id' => $venta->id,
            // ...
        ]);
        
        return $venta;
    }
}
```

```bash
# 2. Ejecutar test (debe pasar - GREEN)
./vendor/bin/pest tests/Feature/Sales/ProcessCreditSaleTest.php
# ✅ Tests passed

# 3. Commitear implementación
git add backend/app/Services/SaleService.php
git commit -m "feat(sales): Implementar procesamiento de venta a crédito (GREEN)

- SaleService con método procesar()
- Creación de venta y crédito
- Descuento de inventario
- Tests: ✅ PASSING"
```

---

### **FASE 5: TDD - REFACTOR (Mejorar Código)**

```bash
# 1. Refactorizar manteniendo tests verdes
vim backend/app/Services/SaleService.php
```

**Mejoras:**
- Extraer validaciones a métodos privados
- Agregar transacciones DB
- Mejorar nombres de variables
- Agregar documentación

```bash
# 2. Ejecutar tests después de cada cambio
./vendor/bin/pest tests/Feature/Sales/
# ✅ Tests passed

# 3. Commitear refactorización
git add backend/app/Services/SaleService.php
git commit -m "refactor(sales): Extraer validaciones y agregar transacciones

- Método privado validarCreditoDisponible()
- Método privado validarStock()
- Transacción DB::transaction()
- Tests: ✅ PASSING"
```

---

### **FASE 6: Documentar Cobertura**

```bash
# 1. Generar reporte de cobertura
./vendor/bin/pest --coverage

# 2. Actualizar documentación
vim docs/05-testing/README.md
```

**Actualizar tabla:**
```markdown
| Módulo | Specs BDD | Tests Feature | Tests Unit | Cobertura |
|--------|-----------|---------------|------------|-----------|
| Sales  | ✅ 1      | ✅ 4          | ✅ 8       | 95% |
```

```bash
# 3. Commitear documentación
git add docs/05-testing/README.md
git commit -m "docs(sales): Actualizar cobertura de tests

- 4 tests feature implementados
- 8 tests unitarios
- Cobertura: 95%"
```

---

### **FASE 7: Merge a Develop**

```bash
# 1. Asegurarse de que todos los tests pasan
./vendor/bin/pest
# ✅ All tests passed

# 2. Ver commits de la feature
git log --oneline develop..feature/bdd-sales

# 3. Cambiar a develop
git checkout develop

# 4. Actualizar develop
git pull origin develop

# 5. Merge de feature
git merge feature/bdd-sales --no-ff

# 6. Push a remoto
git push origin develop

# 7. Eliminar rama feature local
git branch -d feature/bdd-sales

# 8. Eliminar rama remota (si existe)
git push origin --delete feature/bdd-sales
```

---

### **FASE 8: Release a Master (Cuando esté listo)**

```bash
# 1. Verificar que develop está estable
git checkout develop
./vendor/bin/pest
# ✅ All tests passed

# 2. Merge a master
git checkout master
git merge develop --no-ff

# 3. Etiquetar versión
git tag -a v1.0.0 -m "Release 1.0.0: Sistema de ventas completo"

# 4. Push con tags
git push origin master --tags

# 5. Volver a develop
git checkout develop
```

---

## 🐛 WORKFLOW: Bugfix

```bash
# 1. Crear rama desde develop
git checkout develop
git checkout -b bugfix/fix-stock-validation

# 2. Escribir test que reproduce el bug (RED)
git add tests/Feature/Inventory/StockValidationTest.php
git commit -m "test(inventory): Agregar test que reproduce bug de stock negativo"

# 3. Arreglar bug (GREEN)
git add backend/app/Services/InventoryService.php
git commit -m "fix(inventory): Validar stock no puede ser negativo"

# 4. Ejecutar todos los tests
./vendor/bin/pest
# ✅ All tests passed

# 5. Merge a develop
git checkout develop
git merge bugfix/fix-stock-validation
git push origin develop
git branch -d bugfix/fix-stock-validation
```

---

## 🚨 WORKFLOW: Hotfix (Emergencia en Producción)

```bash
# 1. Crear desde master (NO desde develop)
git checkout master
git checkout -b hotfix/critical-sql-injection

# 2. Arreglar SOLO el problema crítico
git add backend/app/Http/Controllers/AuthController.php
git commit -m "hotfix(auth): Sanitizar input en login para prevenir SQL injection"

# 3. Merge a master
git checkout master
git merge hotfix/critical-sql-injection
git tag v1.0.1
git push origin master --tags

# 4. También merge a develop (no perder el fix)
git checkout develop
git merge hotfix/critical-sql-injection
git push origin develop

# 5. Eliminar rama
git branch -d hotfix/critical-sql-injection
```

---

## 📊 Resumen de Comandos

### **Iniciar Feature**
```bash
git checkout develop
git pull origin develop
git checkout -b feature/bdd-nombre
```

### **Ciclo BDD/TDD**
```bash
# 1. Spec BDD
git add docs/06-features/XX-nombre.feature.md
git commit -m "docs(bdd): Spec para feature X"

# 2. Test RED
git add tests/Feature/X/Test.php
git commit -m "test(x): Test que falla (RED)"

# 3. Código GREEN
git add backend/app/Services/XService.php
git commit -m "feat(x): Implementación mínima (GREEN)"

# 4. Refactor
git add backend/app/Services/XService.php
git commit -m "refactor(x): Mejorar código (REFACTOR)"
```

### **Finalizar Feature**
```bash
./vendor/bin/pest  # Verificar tests
git checkout develop
git merge feature/bdd-nombre --no-ff
git push origin develop
git branch -d feature/bdd-nombre
```

---

## ✅ Checklist por Feature

- [ ] Spec BDD creada en `docs/06-features/`
- [ ] Tests feature implementados en `backend/tests/Feature/`
- [ ] Tests unitarios en `backend/tests/Unit/`
- [ ] Código implementado con TDD (RED→GREEN→REFACTOR)
- [ ] Todos los tests pasan (`./vendor/bin/pest`)
- [ ] Cobertura ≥ 80%
- [ ] Documentación actualizada
- [ ] Commits siguen Conventional Commits
- [ ] Merge a `develop` exitoso
- [ ] Rama feature eliminada

---

## 🎓 Convenciones de Commits

```bash
# Features
git commit -m "feat(modulo): Descripción"

# Fixes
git commit -m "fix(modulo): Descripción"

# Tests
git commit -m "test(modulo): Descripción"

# Docs BDD
git commit -m "docs(bdd): Descripción"

# Refactoring
git commit -m "refactor(modulo): Descripción"
```

---

**Última actualización:** 27 de noviembre de 2025
