# 📘 Workflow Maestro: Git + BDD + TDD - Guía Completa

## 🎯 Visión General

Este documento integra **todos los aspectos** del workflow de desarrollo del proyecto Ferretería Frenat, combinando:

1. ✅ **Metodología BDD/TDD**
2. ✅ **Features aisladas** sin afectar producción
3. ✅ **Master siempre desplegable**
4. ✅ **Integración segura** en develop
5. ✅ **Documentación clara** y actualizada

---

## 🌳 Arquitectura de Ramas

```
┌─────────────────────────────────────────────────────────────┐
│                         PRODUCCIÓN                           │
│  master (v1.0.0)                                            │
│    ├─ Solo código 100% probado                             │
│    ├─ Siempre desplegable                                  │
│    ├─ Protegida con reglas estrictas                       │
│    └─ Tags: v1.0.0, v1.1.0, v2.0.0                         │
└─────────────────────────────────────────────────────────────┘
                    ▲
                    │ Merge solo desde develop
                    │ Requiere: Tests ✅ + Review ✅
                    │
┌─────────────────────────────────────────────────────────────┐
│                        INTEGRACIÓN                           │
│  develop                                                     │
│    ├─ Base de trabajo diario                               │
│    ├─ Tests siempre pasan                                  │
│    ├─ Integración de features                              │
│    └─ Pre-release testing                                  │
└─────────────────────────────────────────────────────────────┘
           ▲         ▲         ▲         ▲
           │         │         │         │
           │         │         │         │
    ┌──────┘   ┌─────┘   ┌─────┘   ┌─────┘
    │          │         │         │
┌───┴───┐  ┌───┴───┐ ┌───┴───┐ ┌───┴───┐
│feature│  │feature│ │feature│ │bugfix │
│  auth │  │ sales │ │ inven │ │  fix  │
│       │  │       │ │ tory  │ │       │
│ BDD ✓ │  │ BDD ✓ │ │ BDD ✓ │ │Tests✓ │
│ TDD ✓ │  │ TDD ✓ │ │ TDD ✓ │ │      │
└───────┘  └───────┘ └───────┘ └───────┘
 Aislada    Aislada   Aislada   Aislada
```

---

## 📋 Workflow Completo: De Feature a Producción

### **ETAPA 1: Planificación y Spec BDD** 📝

```bash
# 1. Crear feature desde develop
git checkout develop
git pull origin develop
git checkout -b feature/bdd-nombre-modulo

# 2. Escribir especificación BDD
vim docs/06-features/XX-nombre.feature.md
```

**Contenido de Spec BDD:**
```markdown
## Feature: [Nombre descriptivo]

Como [tipo de usuario]
Quiero [realizar acción]
Para que [beneficio]

### Reglas de Negocio:
1. ...
2. ...

### Scenario 1: [Happy Path]
Given [contexto]
When [acción]
Then [resultado]

### Scenario 2: [Error Case]
...
```

```bash
# 3. Commitear spec
git add docs/06-features/XX-nombre.feature.md
git commit -m "docs(bdd): Agregar especificación BDD para módulo X

- X scenarios completos
- Reglas de negocio definidas
- Criterios de aceptación claros"
```

---

### **ETAPA 2: TDD - RED (Tests que Fallan)** 🔴

```bash
# 1. Crear estructura de tests
mkdir -p backend/tests/Feature/ModuloX
mkdir -p backend/tests/Unit/Services

# 2. Escribir test basado en Scenario 1
vim backend/tests/Feature/ModuloX/FeatureTest.php
```

**Ejemplo de Test:**
```php
it('procesa operación X correctamente', function () {
    // GIVEN (Arrange) - Del spec BDD
    $entity = Entity::factory()->create([...]);
    
    // WHEN (Act) - Del spec BDD
    $result = $this->service->process($entity);
    
    // THEN (Assert) - Del spec BDD
    expect($result->status)->toBe('success');
    $this->assertDatabaseHas('table', [...]);
});
```

```bash
# 3. Ejecutar test (DEBE fallar - RED)
./vendor/bin/pest tests/Feature/ModuloX/
# ❌ Error: Class/Method does not exist

# 4. Commitear test
git add backend/tests/Feature/ModuloX/FeatureTest.php
git commit -m "test(modulox): Agregar test para operación X (RED)

- Test basado en Scenario 1 de spec BDD
- Verifica: resultado, estado, persistencia
- Estado: FALLA (servicio no existe)"
```

---

### **ETAPA 3: TDD - GREEN (Código Mínimo)** 🟢

```bash
# 1. Crear servicio/controlador
php artisan make:service ModuloXService

# 2. Implementar código MÍNIMO para pasar test
vim backend/app/Services/ModuloXService.php
```

```php
class ModuloXService {
    public function process($entity) {
        // Código mínimo para pasar test
        return (object) ['status' => 'success'];
    }
}
```

```bash
# 3. Ejecutar test (DEBE pasar - GREEN)
./vendor/bin/pest tests/Feature/ModuloX/
# ✅ Tests passed

# 4. Commitear implementación
git add backend/app/Services/ModuloXService.php
git commit -m "feat(modulox): Implementar servicio X (GREEN)

- Método process() con lógica básica
- Tests: ✅ PASSING"
```

---

### **ETAPA 4: TDD - REFACTOR (Mejorar Código)** 🔵

```bash
# 1. Refactorizar manteniendo tests verdes
vim backend/app/Services/ModuloXService.php
```

```php
class ModuloXService {
    public function __construct(
        private Repository $repository,
        private Validator $validator
    ) {}
    
    public function process($entity): Result {
        $this->validator->validate($entity);
        $result = $this->repository->save($entity);
        return Result::success($result);
    }
}
```

```bash
# 2. Ejecutar tests después de cada cambio
./vendor/bin/pest
# ✅ Tests siguen pasando

# 3. Commitear refactorización
git add backend/app/Services/ModuloXService.php
git commit -m "refactor(modulox): Extraer validación y repositorio

- Inyección de dependencias
- Separación de responsabilidades
- Tests: ✅ PASSING (sin cambios en comportamiento)"
```

---

### **ETAPA 5: Completar Todos los Scenarios** 🔄

Repetir **ETAPA 2 → 3 → 4** para cada scenario de la spec BDD:

- [ ] Scenario 1: Happy path → RED → GREEN → REFACTOR ✅
- [ ] Scenario 2: Error case → RED → GREEN → REFACTOR ✅
- [ ] Scenario 3: Edge case → RED → GREEN → REFACTOR ✅
- [ ] ...

```bash
# Verificar cobertura completa
./vendor/bin/pest --coverage
# Coverage: 87% ✅
```

---

### **ETAPA 6: Documentar Feature** 📚

```bash
# 1. Actualizar README de testing
vim docs/05-testing/README.md
```

Actualizar tabla de estado:
```markdown
| Módulo X | ✅ 1 | ✅ 12 | ✅ 24 | 87% |
```

```bash
# 2. Commitear documentación
git add docs/05-testing/README.md
git commit -m "docs(modulox): Actualizar estado de tests y cobertura"
```

---

### **ETAPA 7: Preparar para Merge** 🔍

```bash
# 1. Actualizar feature con develop
git checkout develop
git pull origin develop
git checkout feature/bdd-modulox
git merge develop
# Resolver conflictos si existen

# 2. Ejecutar TODA la suite de tests
./vendor/bin/pest
# ✅ All tests passed

# 3. Verificar cobertura
./vendor/bin/pest --coverage --min=80
# ✅ Coverage: 87%

# 4. Verificar que no hay cambios pendientes
git status
# nothing to commit, working tree clean ✅
```

---

### **ETAPA 8: Integración en Develop** 🔄

#### **Opción A: Con Pull Request (Recomendado)**

```bash
# 1. Push feature
git push origin feature/bdd-modulox

# 2. Crear PR en GitHub
# Base: develop ← Compare: feature/bdd-modulox
# Usar template de PR (ver docs/05-testing/07-develop-integration.md)

# 3. Code review
# - Esperar aprobación
# - Resolver comentarios
# - Re-push cambios

# 4. Merge desde GitHub
# Click "Merge Pull Request" → "Create merge commit"

# 5. Actualizar local
git checkout develop
git pull origin develop

# 6. Eliminar feature
git branch -d feature/bdd-modulox
git push origin --delete feature/bdd-modulox
```

#### **Opción B: Merge Manual**

```bash
# 1. Cambiar a develop
git checkout develop

# 2. Merge con commit de merge (--no-ff)
git merge feature/bdd-modulox --no-ff -m "feat(modulox): Integrar módulo X completo

Merge de feature/bdd-modulox que incluye:
- Especificación BDD con X scenarios
- Tests Feature completos (12 tests)
- Tests Unit completos (24 tests)
- Implementación con TDD
- Cobertura: 87%

Closes #XX"

# 3. Push
git push origin develop

# 4. Eliminar feature
git branch -d feature/bdd-modulox
```

---

### **ETAPA 9: Verificación Post-Merge** ✅

```bash
# 1. Verificar develop
git checkout develop
git pull origin develop

# 2. Ejecutar tests
./vendor/bin/pest
# ✅ All tests passed

# 3. Verificar Docker
docker-compose up -d
docker-compose ps
# All containers running ✅

# 4. Verificar migraciones (si hay)
docker exec ferreteria_backend php artisan migrate
# Nothing to migrate ✅
```

---

### **ETAPA 10: Release a Master** 🚀

**Solo cuando develop tiene múltiples features completas:**

```bash
# 1. Verificar develop está listo
git checkout develop
./vendor/bin/pest
./vendor/bin/pest --coverage --min=80
# ✅ All checks passed

# 2. Actualizar CHANGELOG.md
vim CHANGELOG.md
```

```markdown
## [1.0.0] - 2025-11-27

### Added
- Autenticación con JWT
- Gestión de productos
- Control de inventario
- Ventas contado y crédito
```

```bash
# 3. Commit de CHANGELOG
git add CHANGELOG.md
git commit -m "docs: Actualizar CHANGELOG para v1.0.0"
git push origin develop

# 4. Merge a master
git checkout master
git merge develop --no-ff -m "Release v1.0.0: Sistema completo"

# 5. Tag de versión
git tag -a v1.0.0 -m "Release 1.0.0: Sistema completo

Features:
- Autenticación ✅
- Productos ✅
- Inventario ✅
- Ventas ✅"

# 6. Push master y tags
git push origin master --tags

# 7. Sincronizar develop con master
git checkout develop
git merge master --ff-only
git push origin develop
```

---

## 🚨 Casos Especiales

### **HOTFIX Crítico en Producción**

```bash
# 1. Crear hotfix desde MASTER
git checkout master
git pull origin master
git checkout -b hotfix/critical-bug

# 2. Arreglar SOLO el problema crítico
# ... fix ...

# 3. Commit
git commit -m "hotfix: Corregir bug crítico X"

# 4. Tests
./vendor/bin/pest

# 5. Merge a master
git checkout master
git merge hotfix/critical-bug --no-ff
git tag -a v1.0.1 -m "Hotfix 1.0.1: Bug crítico X"
git push origin master --tags

# 6. También merge a develop
git checkout develop
git merge hotfix/critical-bug --no-ff
git push origin develop

# 7. Eliminar hotfix
git branch -d hotfix/critical-bug
```

---

### **BUGFIX No Crítico**

```bash
# 1. Crear bugfix desde DEVELOP
git checkout develop
git checkout -b bugfix/fix-validation

# 2. Arreglar bug
# ... fix ...

# 3. Seguir workflow normal (RED→GREEN→REFACTOR)

# 4. Merge a develop (no va a master hasta próximo release)
git checkout develop
git merge bugfix/fix-validation --no-ff
git push origin develop
```

---

## 📊 Métricas de Calidad

### **Antes de Merge a Develop:**

- ✅ Tests: 100% passing
- ✅ Cobertura: ≥ 80%
- ✅ Linting: 0 errores
- ✅ Commits: Conventional Commits
- ✅ Documentación: Actualizada

### **Antes de Release a Master:**

- ✅ Todos los checks de develop +
- ✅ CHANGELOG.md actualizado
- ✅ README.md refleja estado actual
- ✅ Tag de versión creado
- ✅ Tests de integración E2E (si existen)

---

## 🛠️ Comandos Rápidos de Referencia

### **Crear Feature:**
```bash
git checkout develop && git pull && git checkout -b feature/bdd-nombre
```

### **Ciclo TDD:**
```bash
# RED
git add tests/ && git commit -m "test: RED"

# GREEN  
git add src/ && git commit -m "feat: GREEN"

# REFACTOR
git add src/ && git commit -m "refactor: REFACTOR"
```

### **Merge a Develop:**
```bash
git checkout develop && git merge feature/bdd-nombre --no-ff && git push
```

### **Release a Master:**
```bash
git checkout master && git merge develop --no-ff && git tag -a vX.Y.Z -m "Release" && git push --tags
```

---

## 📚 Documentos de Referencia

| Documento | Propósito |
|-----------|-----------|
| [`01-introduccion.md`](../05-testing/01-introduccion.md) | Conceptos BDD/TDD |
| [`02-bdd-guide.md`](../05-testing/02-bdd-guide.md) | Guía avanzada BDD |
| [`03-tdd-guide.md`](../05-testing/03-tdd-guide.md) | Guía avanzada TDD |
| [`04-workflow.md`](../05-testing/04-workflow.md) | Workflow Git+BDD+TDD |
| [`06-master-protection.md`](../05-testing/06-master-protection.md) | Protección de master |
| [`07-develop-integration.md`](../05-testing/07-develop-integration.md) | Integración segura |
| [`GIT_BEST_PRACTICES.md`](../../GIT_BEST_PRACTICES.md) | Buenas prácticas Git |
| [`BRANCHES.md`](../../BRANCHES.md) | Estrategia de ramas |

---

## ✅ Checklist por Feature

- [ ] Spec BDD creada con scenarios completos
- [ ] Tests Feature implementados (BDD)
- [ ] Tests Unit implementados (TDD)
- [ ] Código con ciclo RED→GREEN→REFACTOR
- [ ] Cobertura ≥ 80%
- [ ] Documentación actualizada
- [ ] Commits siguen Conventional Commits
- [ ] Sin conflictos con develop
- [ ] Tests pasan en develop
- [ ] Feature merged y rama eliminada

---

## 🎯 Objetivos Logrados

1. ✅ **Metodología BDD/TDD** implementada y documentada
2. ✅ **Features aisladas** sin afectar producción
3. ✅ **Master siempre desplegable** con protección
4. ✅ **Integración segura** con proceso de PR
5. ✅ **Documentación clara** del workflow completo

---

**Última actualización:** 27 de noviembre de 2025  
**Versión del documento:** 1.0.0  
**Mantenido por:** Equipo Ferretería Frenat
