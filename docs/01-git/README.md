# 📚 Documentación Git - Ferretería Frenad

Esta carpeta contiene toda la documentación relacionada con el control de versiones y workflow de Git del proyecto.

---

## 📋 Contenido

### 1. **GIT_BEST_PRACTICES.md**
Guía completa de buenas prácticas de Git que incluye:
- ✅ Conventional Commits (feat, fix, docs, test, refactor, chore)
- ✅ Estrategias de branches (GitHub Flow adaptado)
- ✅ Mensajes de commit profesionales
- ✅ Comandos útiles de Git
- ✅ Antipatrones a evitar
- ✅ Resolución de conflictos
- ✅ Reescritura de historia (rebase, squash)

**Cuándo consultar:** Antes de hacer cualquier commit o merge.

---

### 2. **BRANCHES.md**
Documentación de la estrategia de ramas del proyecto:
- 🌳 Estructura de branches (master, develop, feature/*, bugfix/*, hotfix/*)
- 📊 Diagrama visual del flujo de trabajo
- ✅ Checklist de creación de features
- 🔄 Proceso de integración
- 📖 Referencia rápida de comandos

**Cuándo consultar:** Al crear una nueva feature o al hacer merge.

---

## 🎯 Flujo de Trabajo Recomendado

### 1. **Crear Feature**
```bash
# Desde develop
git checkout develop
git pull origin develop

# Crear feature branch
git checkout -b feature/nombre-descriptivo
```

### 2. **Trabajo en Feature** (BDD/TDD)
```bash
# 1. Escribir especificación BDD
# docs/06-features/XX-modulo.feature.md

# 2. Commits atómicos con Conventional Commits
git add docs/06-features/XX-modulo.feature.md
git commit -m "docs(bdd): Agregar especificación BDD para módulo X"

# 3. Escribir tests (TDD RED)
git add tests/
git commit -m "test(modulo): Agregar tests para funcionalidad Y (RED)"

# 4. Implementar código (TDD GREEN)
git add src/
git commit -m "feat(modulo): Implementar funcionalidad Y"

# 5. Refactorizar (TDD REFACTOR)
git add src/
git commit -m "refactor(modulo): Optimizar implementación de Y"
```

### 3. **Integrar a Develop**
```bash
# Ver guía completa en:
# - docs/05-testing/07-develop-integration.md
# - docs/05-testing/00-workflow-maestro.md

# Resumen:
git checkout develop
git pull origin develop
git merge --no-ff feature/nombre-descriptivo
git push origin develop
```

### 4. **Release a Master**
```bash
# Ver guía completa en:
# - docs/05-testing/06-master-protection.md

# Resumen:
git checkout master
git merge --no-ff develop
git tag -a v1.0.0 -m "Release v1.0.0: Descripción"
git push origin master --tags
```

---

## 🔗 Documentación Relacionada

### Workflow Completo
- **`docs/05-testing/00-workflow-maestro.md`** - Workflow maestro Git+BDD+TDD
- **`docs/05-testing/06-master-protection.md`** - Protección de master y versionado
- **`docs/05-testing/07-develop-integration.md`** - Integración segura en develop

### Metodología BDD/TDD
- **`docs/05-testing/01-introduccion.md`** - Introducción a BDD/TDD
- **`docs/05-testing/02-bdd-guide.md`** - Guía completa BDD
- **`docs/05-testing/03-tdd-guide.md`** - Guía completa TDD
- **`docs/05-testing/04-workflow.md`** - Workflow Git+BDD+TDD

### Features
- **`docs/06-features/`** - Especificaciones BDD por módulo

---

## 🛠️ Herramientas

### Script de Commits Interactivo
El proyecto incluye un script que guía en la creación de commits profesionales:

```bash
# Desde la raíz del proyecto
./commit_guide.sh
```

**Características:**
- ✅ Guía paso a paso
- ✅ Conventional Commits automático
- ✅ 4 commits tipo: Feature, Bugfix, Documentation, Refactor
- ✅ Validación de formato
- ✅ Colores y emojis

---

## 📖 Convenciones del Proyecto

### Tipos de Commit

| Tipo | Uso | Ejemplo |
|------|-----|---------|
| `feat` | Nueva funcionalidad | `feat(auth): Agregar login con JWT` |
| `fix` | Corrección de bug | `fix(ventas): Corregir cálculo de total` |
| `docs` | Documentación | `docs(bdd): Agregar spec Auth` |
| `test` | Tests | `test(productos): Agregar tests unitarios` |
| `refactor` | Refactorización | `refactor(inventario): Optimizar query` |
| `chore` | Tareas de mantenimiento | `chore: Actualizar dependencias` |
| `style` | Formato de código | `style: Aplicar PSR-12` |
| `perf` | Mejora de performance | `perf(ventas): Cache de productos` |

### Scopes Comunes
- `auth` - Autenticación/Autorización
- `productos` - Catálogo de productos
- `inventario` - Gestión de stock
- `ventas` - Punto de venta
- `compras` - Órdenes de compra
- `clientes` - Gestión de clientes
- `caja` - Arqueo de caja
- `auditoria` - Logs y trazabilidad
- `bdd` - Especificaciones BDD
- `tdd` - Tests TDD
- `git` - Documentación Git
- `workflow` - Documentación de proceso

---

## 🚨 Reglas Importantes

### ❌ NO hacer
1. Commits directos a `master` (protegido)
2. Commits con mensaje genérico ("fix", "update", "cambios")
3. `git add .` sin revisar qué archivos se incluyen
4. Merge de feature a master (siempre pasar por develop)
5. Force push a ramas compartidas (`develop`, `master`)

### ✅ SÍ hacer
1. Commits atómicos (una responsabilidad por commit)
2. Mensajes descriptivos con Conventional Commits
3. Revisar diff antes de commitear (`git diff --staged`)
4. Crear feature branch por cada funcionalidad
5. Pull antes de push para evitar conflictos
6. Seguir el workflow BDD → TDD → Implementación

---

## 🎓 Recursos de Aprendizaje

### Git Básico
- [Pro Git Book (Español)](https://git-scm.com/book/es/v2)
- [Learn Git Branching](https://learngitbranching.js.org/?locale=es_ES)

### Conventional Commits
- [Conventional Commits Spec](https://www.conventionalcommits.org/es/v1.0.0/)
- [Commitizen](https://github.com/commitizen/cz-cli) - CLI helper

### GitHub Flow
- [GitHub Flow Guide](https://docs.github.com/en/get-started/quickstart/github-flow)

---

## 📞 Ayuda

Si tienes dudas sobre:
- **Commits:** Consulta `GIT_BEST_PRACTICES.md`
- **Branches:** Consulta `BRANCHES.md`
- **Workflow completo:** Consulta `docs/05-testing/00-workflow-maestro.md`
- **Integraciones:** Consulta `docs/05-testing/07-develop-integration.md`

---

**Última actualización:** 27 de noviembre de 2025  
**Versión:** 2.0.0
