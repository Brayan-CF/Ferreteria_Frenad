# 🔄 Integración Segura de Features en Develop

## 🎯 Objetivo

Garantizar que **`develop` siempre esté funcional** mediante:
1. Proceso controlado de merge de features
2. Pull Requests con code review
3. Tests automáticos antes de integrar
4. Resolución de conflictos de forma segura

---

## 📋 Workflow Completo: Feature → Develop

### **FASE 1: Preparar Feature para Merge**

```bash
# 1. Asegurar que estás en la feature
git checkout feature/bdd-auth

# 2. Ejecutar todos los tests
./vendor/bin/pest
# ✅ All tests passed

# 3. Verificar cobertura
./vendor/bin/pest --coverage --min=80
# ✅ Coverage: 87%

# 4. Verificar que no hay cambios pendientes
git status
# Should be: "nothing to commit, working tree clean"

# 5. Ver commits de la feature
git log --oneline develop..feature/bdd-auth
```

---

### **FASE 2: Actualizar Feature con Develop**

Antes de mergear, **actualizar la feature con los últimos cambios de develop**:

```bash
# 1. Actualizar develop local
git checkout develop
git pull origin develop

# 2. Volver a feature
git checkout feature/bdd-auth

# 3. Rebase sobre develop (opción A - recomendada)
git rebase develop

# O merge desde develop (opción B - más segura)
git merge develop

# 4. Resolver conflictos si existen
# ... editar archivos conflictivos ...
git add .
git rebase --continue  # si usaste rebase
# o
git commit  # si usaste merge

# 5. Ejecutar tests de nuevo
./vendor/bin/pest
# ✅ Tests pasan después de actualizar
```

---

### **FASE 3: Crear Pull Request (GitHub/GitLab)**

#### **Opción A: Con GitHub**

```bash
# 1. Push de feature actualizada
git push origin feature/bdd-auth

# 2. En GitHub: New Pull Request
# Base: develop ← Compare: feature/bdd-auth
```

**Template de PR:**

```markdown
## 🎯 Feature: Autenticación con JWT

### 📝 Descripción

Implementa el sistema completo de autenticación basado en la especificación BDD.

### ✨ Cambios Incluidos

#### Documentación:
- ✅ Guías completas de BDD/TDD (01-03)
- ✅ Especificación BDD con 13 scenarios
- ✅ Guía práctica de implementación

#### Tests (cuando se implementen):
- ⏳ Tests Feature: Login, Logout, Registro, Cambio Password
- ⏳ Tests Unit: AuthService, TokenService, PasswordService
- ⏳ Cobertura esperada: ≥ 85%

#### Código (cuando se implemente):
- ⏳ AuthController con endpoints API
- ⏳ AuthService con lógica de negocio
- ⏳ Middleware auth:jwt
- ⏳ Log de auditoría automático

### 🧪 Tests

```bash
./vendor/bin/pest
# ✅ Tests passed: 0 (sin implementación aún)
# 📊 Coverage: N/A
```

### 📊 Estadísticas

- **Commits:** 3
- **Archivos nuevos:** 5
- **Líneas añadidas:** +13,273
- **Líneas eliminadas:** 0

### ✅ Checklist

- [x] Especificación BDD completa
- [x] Guías de metodología documentadas
- [x] Commits siguen Conventional Commits
- [x] Sin conflictos con develop
- [x] Documentación clara y completa
- [ ] Tests implementados (próxima fase)
- [ ] Código implementado (próxima fase)

### 🔗 Issues Relacionados

Closes #1 (Setup BDD/TDD)
Related to #2 (Implementar Auth)

### 👥 Reviewers

@reviewer1 @reviewer2

### 📝 Notas para Reviewers

Esta PR contiene SOLO documentación y especificaciones BDD. La implementación de código y tests vendrá en commits posteriores siguiendo el ciclo TDD (RED→GREEN→REFACTOR).

Por favor revisar:
1. Claridad de especificaciones BDD
2. Completitud de scenarios (13 casos)
3. Coherencia con arquitectura del proyecto
4. Calidad de documentación
```

---

#### **Opción B: Merge Manual (Sin GitHub)**

```bash
# 1. Cambiar a develop
git checkout develop

# 2. Merge feature con commit de merge
git merge feature/bdd-auth --no-ff -m "feat(auth): Integrar especificaciones y guías BDD/TDD

Merge de feature/bdd-auth que incluye:
- Guías completas de metodología BDD/TDD
- Especificación BDD de autenticación (13 scenarios)
- Guía práctica de implementación

Commits incluidos:
- docs(bdd): Agregar especificación BDD para módulo de Autenticación
- docs(bdd-tdd): Agregar guías completas de metodología BDD/TDD
- docs(auth): Agregar guía práctica de trabajo para feature Auth"

# 3. Ejecutar tests
./vendor/bin/pest

# 4. Si todo pasa, push a remoto
git push origin develop

# 5. Eliminar rama feature
git branch -d feature/bdd-auth
git push origin --delete feature/bdd-auth
```

---

### **FASE 4: Code Review (Si hay equipo)**

#### **Checklist para Reviewer:**

**Aspectos Generales:**
- [ ] ¿El código/docs siguen las convenciones del proyecto?
- [ ] ¿Los commits son descriptivos y siguen Conventional Commits?
- [ ] ¿Hay conflictos con develop?
- [ ] ¿El historial de commits es limpio?

**Aspectos de BDD:**
- [ ] ¿Los scenarios cubren casos de éxito y error?
- [ ] ¿El lenguaje es claro y entendible por no-técnicos?
- [ ] ¿Las reglas de negocio están bien definidas?
- [ ] ¿Los criterios de aceptación son verificables?

**Aspectos de TDD:**
- [ ] ¿Los tests están bien estructurados (AAA)?
- [ ] ¿Los tests son independientes?
- [ ] ¿Los nombres de tests son descriptivos?
- [ ] ¿La cobertura es adecuada (≥ 80%)?

**Aspectos de Código:**
- [ ] ¿El código es legible y mantenible?
- [ ] ¿Hay duplicación innecesaria?
- [ ] ¿Las abstracciones son apropiadas?
- [ ] ¿Hay comentarios donde es necesario?

**Aspectos de Documentación:**
- [ ] ¿La documentación está actualizada?
- [ ] ¿Los ejemplos son claros?
- [ ] ¿Hay enlaces a recursos relacionados?

---

### **FASE 5: Resolución de Comentarios**

```bash
# 1. Hacer cambios solicitados en code review
git checkout feature/bdd-auth

# 2. Hacer los cambios
# ... editar archivos ...

# 3. Commitear
git add .
git commit -m "docs(auth): Mejorar claridad de scenarios según review"

# 4. Push
git push origin feature/bdd-auth

# 5. Re-solicitar review
```

---

### **FASE 6: Merge Final**

Una vez aprobada la PR:

```bash
# Opción A: Merge desde GitHub (recomendado)
# Click en "Merge Pull Request" → "Squash and merge" o "Merge commit"

# Opción B: Merge manual
git checkout develop
git merge feature/bdd-auth --no-ff
git push origin develop

# Eliminar rama
git branch -d feature/bdd-auth
git push origin --delete feature/bdd-auth
```

---

## 🚨 Manejo de Conflictos

### **Detectar Conflictos:**

```bash
git checkout feature/bdd-auth
git merge develop

# Si hay conflictos:
# Auto-merging docs/README.md
# CONFLICT (content): Merge conflict in docs/README.md
# Automatic merge failed; fix conflicts and then commit the result.
```

### **Resolver Conflictos:**

```bash
# 1. Ver archivos con conflictos
git status
# both modified:   docs/README.md

# 2. Abrir archivo y buscar marcadores
# <<<<<<< HEAD (tu cambio en feature)
# ...código de feature...
# =======
# ...código de develop...
# >>>>>>> develop
```

**Estrategias de Resolución:**

1. **Mantener cambio de feature:**
```markdown
<<<<<<< HEAD
# Nueva sección que agregaste
=======
# Sección que estaba en develop
>>>>>>> develop

↓ RESULTADO ↓

# Nueva sección que agregaste
```

2. **Mantener cambio de develop:**
```markdown
# Sección que estaba en develop
```

3. **Combinar ambos:**
```markdown
# Sección que estaba en develop
# Nueva sección que agregaste
```

```bash
# 3. Marcar como resuelto
git add docs/README.md

# 4. Continuar merge
git commit -m "merge: Resolver conflictos con develop

- Combinar cambios de README.md
- Mantener ambas secciones"

# 5. Ejecutar tests
./vendor/bin/pest

# 6. Push
git push origin feature/bdd-auth
```

---

## 🎯 Políticas de Integración

### **Regla 1: Tests Siempre Pasan**

```bash
# ANTES de merge
./vendor/bin/pest
# ✅ DEBE pasar

# SI falla → NO mergear
```

### **Regla 2: Cobertura Mínima**

```bash
./vendor/bin/pest --coverage --min=80
# ✅ Coverage debe ser ≥ 80%
```

### **Regla 3: Sin Warnings Críticos**

```bash
# Linting
./vendor/bin/phpcs

# Static analysis
./vendor/bin/phpstan analyse
```

### **Regla 4: Commits Limpios**

```bash
# Ver historial
git log --oneline develop..feature/bdd-auth

# ✅ BIEN:
# abc1234 docs(bdd): Agregar spec Auth
# def5678 test(auth): Implementar tests
# ghi9012 feat(auth): Implementar servicio

# ❌ MAL:
# aaa1111 fix
# bbb2222 wip
# ccc3333 test
```

**Si hay commits sucios, hacer squash:**

```bash
git rebase -i develop
# Cambiar "pick" por "squash" en commits a combinar
```

---

## 🔧 Automatización con GitHub Actions

### **Workflow de CI/CD:**

```yaml
# .github/workflows/ci.yml
name: CI

on:
  pull_request:
    branches: [develop, master]
  push:
    branches: [develop, master]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: ./vendor/bin/pest
        
      - name: Check coverage
        run: ./vendor/bin/pest --coverage --min=80
        
      - name: Run linter
        run: ./vendor/bin/phpcs
```

---

## 📊 Estrategias de Merge

### **1. Merge Commit (Recomendado para features)**

```bash
git merge feature/bdd-auth --no-ff
```

**Resultado:**
```
* Merge branch 'feature/bdd-auth' into develop
|\
| * docs(auth): Guía práctica
| * docs(bdd-tdd): Guías completas
| * docs(bdd): Spec BDD Auth
|/
* Previous develop commit
```

**Ventajas:**
- ✅ Historial completo preservado
- ✅ Fácil de revertir feature completa
- ✅ Clara separación de features

---

### **2. Squash Merge (Para features pequeñas)**

```bash
git merge feature/bdd-auth --squash
git commit -m "feat(auth): Agregar especificaciones y guías BDD/TDD completas"
```

**Resultado:**
```
* feat(auth): Agregar especificaciones y guías BDD/TDD completas
|
* Previous develop commit
```

**Ventajas:**
- ✅ Historial limpio (1 commit por feature)
- ✅ Fácil de seguir
- ❌ Se pierden commits individuales

---

### **3. Rebase (Para mantener historial lineal)**

```bash
git checkout feature/bdd-auth
git rebase develop
git checkout develop
git merge feature/bdd-auth --ff-only
```

**Resultado:**
```
* docs(auth): Guía práctica
* docs(bdd-tdd): Guías completas
* docs(bdd): Spec BDD Auth
* Previous develop commit
```

**Ventajas:**
- ✅ Historial completamente lineal
- ✅ Sin commits de merge
- ⚠️ Reescribe historial (no usar en ramas públicas)

---

## ✅ Checklist de Integración Segura

### **ANTES de crear PR/merge:**

- [ ] ✅ Todos los tests pasan localmente
- [ ] ✅ Feature actualizada con último develop
- [ ] ✅ Sin conflictos
- [ ] ✅ Cobertura ≥ 80%
- [ ] ✅ Commits siguen Conventional Commits
- [ ] ✅ Documentación actualizada
- [ ] ✅ Sin cambios pendientes (git status clean)

### **DURANTE code review:**

- [ ] ✅ PR tiene descripción clara
- [ ] ✅ Checklist completo
- [ ] ✅ Tests automáticos pasan (CI)
- [ ] ✅ Comentarios de review resueltos
- [ ] ✅ Al menos 1 aprobación (si hay equipo)

### **DESPUÉS del merge:**

- [ ] ✅ Develop actualizado en remoto
- [ ] ✅ Tests pasan en develop
- [ ] ✅ Rama feature eliminada (local y remota)
- [ ] ✅ Próxima feature puede basarse en develop

---

## 📚 Referencias

- **GitHub Pull Requests:** https://docs.github.com/en/pull-requests
- **Git Merge Strategies:** https://git-scm.com/docs/git-merge
- **Conventional Commits:** https://www.conventionalcommits.org/

---

**Última actualización:** 27 de noviembre de 2025
