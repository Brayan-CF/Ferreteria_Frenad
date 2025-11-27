# 🔒 Protección de Ramas y Versionado

## 🎯 Objetivo

Garantizar que **`master` siempre esté desplegable** mediante:
1. Protección de rama con reglas estrictas
2. Versionado semántico (SemVer)
3. Tags para releases
4. Proceso controlado de merge

---

## 🛡️ Reglas de Protección de `master`

### **Configuración en GitHub:**

```
Settings → Branches → Branch protection rules → Add rule
```

**Configuración recomendada:**

| Regla | Activar | Propósito |
|-------|---------|-----------|
| **Require a pull request before merging** | ✅ | No push directo a master |
| **Require approvals** | ✅ (1) | Code review obligatorio |
| **Dismiss stale pull request approvals** | ✅ | Re-aprobar si hay cambios |
| **Require status checks to pass** | ✅ | Tests deben pasar |
| **Require branches to be up to date** | ✅ | Sincronizar antes de merge |
| **Require conversation resolution** | ✅ | Resolver comentarios |
| **Include administrators** | ✅ | Reglas aplican a todos |
| **Restrict who can push** | ⚠️ (opcional) | Solo roles específicos |
| **Allow force pushes** | ❌ | NUNCA forzar push |
| **Allow deletions** | ❌ | No eliminar master |

---

## 📊 Versionado Semántico (SemVer)

### **Formato:**

```
MAJOR.MINOR.PATCH

v1.2.3
│ │ │
│ │ └─ PATCH: Correcciones de bugs (compatible)
│ └─── MINOR: Nuevas funcionalidades (compatible)
└───── MAJOR: Cambios incompatibles
```

### **Ejemplos:**

| Cambio | Versión Antes | Versión Después |
|--------|---------------|-----------------|
| Bug fix | v1.2.3 | v1.2.4 |
| Nueva feature | v1.2.4 | v1.3.0 |
| Breaking change | v1.3.0 | v2.0.0 |
| Hotfix crítico | v1.3.0 | v1.3.1 |

---

## 🏷️ Sistema de Tags

### **Crear Tag:**

```bash
# Tag simple
git tag v1.0.0

# Tag anotado (recomendado)
git tag -a v1.0.0 -m "Release 1.0.0: Sistema completo de ferretería

Features implementadas:
- Autenticación con JWT
- Gestión de productos
- Control de inventario
- Ventas contado y crédito
- Gestión de clientes
- Reportes y auditoría"

# Subir tag a remoto
git push origin v1.0.0

# Subir todos los tags
git push origin --tags
```

### **Ver Tags:**

```bash
# Listar todos los tags
git tag

# Listar tags con patrón
git tag -l "v1.*"

# Ver detalles de tag
git show v1.0.0

# Ver tags ordenados por fecha
git tag -l --sort=-creatordate
```

### **Eliminar Tag:**

```bash
# Eliminar tag local
git tag -d v1.0.0

# Eliminar tag remoto
git push origin --delete v1.0.0
```

---

## 📋 Workflow de Release a Master

### **PASO 1: Verificar develop está listo**

```bash
# Cambiar a develop
git checkout develop
git pull origin develop

# Ejecutar suite completa de tests
./vendor/bin/pest

# Verificar cobertura
./vendor/bin/pest --coverage --min=80

# Verificar que no hay cambios pendientes
git status
# Should be: "nothing to commit, working tree clean"
```

---

### **PASO 2: Crear Pull Request (si usas GitHub)**

```bash
# Subir develop actualizado
git push origin develop

# En GitHub:
1. New Pull Request
2. Base: master ← Compare: develop
3. Título: "Release v1.0.0: Sistema completo"
4. Descripción:
```

**Template de PR para Release:**

```markdown
## 🚀 Release v1.0.0

### 📦 Features Implementadas

- ✅ Autenticación con JWT (módulo completo)
- ✅ Gestión de productos y categorías
- ✅ Control de inventario con transferencias
- ✅ Ventas contado y crédito
- ✅ Gestión de clientes y créditos
- ✅ Reportes y auditoría

### 🧪 Tests

- ✅ 156 tests passing
- ✅ Cobertura: 87%
- ✅ 0 errores
- ✅ 0 warnings

### 📊 Estadísticas

- **Commits:** 47
- **Archivos cambiados:** 89
- **Líneas añadidas:** +12,345
- **Líneas eliminadas:** -234

### ✅ Checklist

- [x] Todos los tests pasan
- [x] Cobertura ≥ 80%
- [x] Documentación actualizada
- [x] CHANGELOG.md actualizado
- [x] Migraciones de BD probadas
- [x] Seeders actualizados
- [x] README.md actualizado

### 🔗 Issues Relacionados

Closes #12, #15, #18, #23, #27

### 📝 Notas

Primera versión estable del sistema. Lista para despliegue en producción.
```

---

### **PASO 3: Merge a Master (Manual)**

Si NO usas GitHub, o después de aprobar PR:

```bash
# 1. Cambiar a master
git checkout master
git pull origin master

# 2. Merge desde develop (con commit de merge)
git merge develop --no-ff -m "Release v1.0.0: Sistema completo de ferretería"

# 3. Crear tag
git tag -a v1.0.0 -m "Release 1.0.0: Sistema completo"

# 4. Subir a remoto
git push origin master --tags

# 5. Volver a develop
git checkout develop
```

---

### **PASO 4: Actualizar develop desde master**

```bash
# Asegurar que develop tiene los cambios de master
git checkout develop
git merge master --ff-only
git push origin develop
```

---

## 🚨 Manejo de Hotfixes

### **Cuando usar Hotfix:**

- 🔴 Bug crítico en producción (master)
- 🔴 Vulnerabilidad de seguridad
- 🔴 Pérdida de datos
- 🔴 Sistema caído

### **Workflow:**

```bash
# 1. Crear hotfix desde MASTER (no develop)
git checkout master
git pull origin master
git checkout -b hotfix/critical-bug-fix

# 2. Arreglar SOLO el problema crítico
# ... hacer cambios mínimos ...

# 3. Commitear
git add .
git commit -m "hotfix: Corregir error crítico de cálculo en ventas

- Bug: Total de venta redondeaba incorrectamente
- Fix: Usar round() con 2 decimales
- Impacto: Afectaba ventas con múltiples items"

# 4. Ejecutar tests
./vendor/bin/pest

# 5. Merge a MASTER
git checkout master
git merge hotfix/critical-bug-fix --no-ff

# 6. Tag de parche
git tag -a v1.0.1 -m "Hotfix 1.0.1: Corrección de redondeo en ventas"

# 7. Push
git push origin master --tags

# 8. También merge a DEVELOP (no perder el fix)
git checkout develop
git merge hotfix/critical-bug-fix --no-ff
git push origin develop

# 9. Eliminar rama hotfix
git branch -d hotfix/critical-bug-fix
```

---

## 📊 Historial de Versiones

### **Mantener CHANGELOG.md:**

```markdown
# Changelog

Todos los cambios notables en este proyecto se documentan aquí.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Versionado Semántico](https://semver.org/lang/es/).

## [Unreleased]

### Added
- En desarrollo

## [1.0.1] - 2025-11-28

### Fixed
- Corrección de redondeo en cálculo de total de ventas

## [1.0.0] - 2025-11-27

### Added
- Sistema de autenticación con JWT
- Gestión de productos y categorías
- Control de inventario
- Ventas contado y crédito
- Gestión de clientes
- Reportes y auditoría

### Security
- Protección contra SQL injection
- Validación de inputs
- Rate limiting en login

## [0.1.0] - 2025-11-01

### Added
- Estructura inicial del proyecto
- Configuración Docker
- Base de datos PostgreSQL
- Documentación BDD/TDD

[Unreleased]: https://github.com/tu-usuario/ferreteria/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/tu-usuario/ferreteria/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/tu-usuario/ferreteria/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/tu-usuario/ferreteria/releases/tag/v0.1.0
```

---

## 🎯 Garantías de Master Desplegable

### **Checklist ANTES de Merge a Master:**

- [ ] ✅ Todos los tests pasan (`./vendor/bin/pest`)
- [ ] ✅ Cobertura ≥ 80% (`./vendor/bin/pest --coverage`)
- [ ] ✅ Sin errores de linting
- [ ] ✅ Migraciones de BD probadas
- [ ] ✅ Seeders funcionan correctamente
- [ ] ✅ Docker Compose levanta sin errores
- [ ] ✅ Documentación actualizada
- [ ] ✅ CHANGELOG.md actualizado
- [ ] ✅ README.md refleja estado actual
- [ ] ✅ No hay TODOs o FIXMEs críticos
- [ ] ✅ Code review aprobado (si hay equipo)

### **Checklist DESPUÉS de Merge a Master:**

- [ ] ✅ Tag creado con versión correcta
- [ ] ✅ Tag pusheado a remoto
- [ ] ✅ CHANGELOG.md tiene entrada de versión
- [ ] ✅ Develop sincronizado con master
- [ ] ✅ Deploy ejecutado (si es automático)
- [ ] ✅ Verificación en producción
- [ ] ✅ Monitoreo de errores activo

---

## 🔧 Scripts de Automatización

### **Script de Pre-Release Check:**

```bash
#!/bin/bash
# pre-release-check.sh

echo "🔍 Ejecutando checks pre-release..."

# Tests
echo "1️⃣ Ejecutando tests..."
./vendor/bin/pest || { echo "❌ Tests fallaron"; exit 1; }

# Cobertura
echo "2️⃣ Verificando cobertura..."
./vendor/bin/pest --coverage --min=80 || { echo "❌ Cobertura < 80%"; exit 1; }

# Linting
echo "3️⃣ Verificando código..."
./vendor/bin/phpcs || { echo "⚠️ Problemas de estilo"; }

# Git status
echo "4️⃣ Verificando git..."
if [[ -n $(git status -s) ]]; then
    echo "⚠️ Hay cambios sin commitear"
    exit 1
fi

echo "✅ Todos los checks pasaron. Listo para release!"
```

---

## 📚 Referencias

- **SemVer:** https://semver.org/lang/es/
- **Keep a Changelog:** https://keepachangelog.com/es/1.0.0/
- **GitHub Protected Branches:** https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches

---

**Última actualización:** 27 de noviembre de 2025
