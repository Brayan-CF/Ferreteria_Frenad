# 🌳 Estrategia de Ramas - Ferretería Frenat

## 📊 Estructura Actual

```
Repository: Ferreteria_Frenat
├── master (producción - estable)
│   └── Última versión desplegable
│
└── develop (desarrollo - activo) ⭐ ACTUAL
    ├── Base para nuevas features
    └── Integración continua
```

---

## 🎯 Estado Actual

### **Ramas Permanentes:**

| Rama | Propósito | Estado | Commits |
|------|-----------|--------|---------|
| `master` | Producción estable | ✅ Activa | 43d989c |
| `develop` | Desarrollo/integración | ✅ Activa | 93a89a3 |

### **Ramas Temporales:**

| Rama | Estado | Descripción |
|------|--------|-------------|
| `feature/*` | ⏳ Pendiente | Se crearán por módulo |
| `bugfix/*` | ⏳ Pendiente | Solo cuando haya bugs |
| `hotfix/*` | ⏳ Pendiente | Solo emergencias |

---

## 🚀 Próximas Ramas a Crear

### **Módulos a Implementar con BDD/TDD:**

1. **`feature/bdd-auth`** - Sistema de autenticación
   - Login/Logout
   - Registro de usuarios
   - Gestión de roles

2. **`feature/bdd-products`** - Gestión de productos
   - CRUD productos
   - Categorías y marcas
   - Unidades de medida

3. **`feature/bdd-inventory`** - Control de inventario
   - Transferencias entre almacenes
   - Ajustes de stock
   - Alertas de stock bajo

4. **`feature/bdd-sales`** - Procesamiento de ventas
   - Ventas contado
   - Ventas a crédito
   - Devoluciones

5. **`feature/bdd-customers`** - Gestión de clientes
   - CRUD clientes
   - Créditos y pagos
   - Historial de compras

6. **`feature/bdd-purchases`** - Gestión de compras
   - Órdenes de compra
   - Registro de compras
   - Proveedores

7. **`feature/bdd-cash`** - Caja y arqueos
   - Apertura/cierre caja
   - Movimientos
   - Arqueos

8. **`feature/bdd-reports`** - Reportes y auditoría
   - Reportes de ventas
   - Kardex
   - Logs de auditoría

---

## 📋 Workflow Visual

### **Desarrollo de Feature:**

```
develop (base)
   │
   ├──> feature/bdd-auth (crear rama)
   │         │
   │         ├─> Spec BDD (commit)
   │         ├─> Test RED (commit)
   │         ├─> Code GREEN (commit)
   │         ├─> Refactor (commit)
   │         └─> Tests passing ✅
   │         
   └──< merge feature/bdd-auth (integrar)
   │    └─> eliminar rama
   │
   ├──> feature/bdd-products (próxima feature)
   │    ...
```

### **Release a Producción:**

```
develop (todos los módulos integrados)
   │
   ├──> Ejecutar suite completa de tests ✅
   │
   └──> master (merge)
        └──> tag v1.0.0 (versión)
             └──> deploy
```

---

## 🔒 Protección de Ramas

### **`master` - PROTEGIDA**

**Reglas a configurar en GitHub/GitLab:**
- ❌ No push directo
- ✅ Solo merge desde `develop` via Pull Request
- ✅ Requiere 1 aprobación (si hay equipo)
- ✅ Requiere tests passing
- ✅ No se puede eliminar
- ✅ No force-push

### **`develop` - PROTEGIDA (Opcional)**

**Reglas recomendadas:**
- ✅ Solo merge desde `feature/*` via Pull Request
- ✅ Requiere tests passing
- ⚠️ Permite push directo (para correcciones rápidas)

---

## 📝 Convenciones de Nomenclatura

### **Features:**
```
feature/bdd-nombre-modulo
feature/bdd-auth
feature/bdd-products
feature/bdd-sales
```

### **Bugfixes:**
```
bugfix/descripcion-corta
bugfix/fix-stock-validation
bugfix/incorrect-total-calculation
```

### **Hotfixes:**
```
hotfix/descripcion-critica
hotfix/critical-sql-injection
hotfix/sales-calculation-error
```

---

## 🎯 Comandos Rápidos

### **Ver todas las ramas:**
```bash
git branch -a
```

### **Crear nueva feature:**
```bash
git checkout develop
git pull origin develop
git checkout -b feature/bdd-nombre
```

### **Ver rama actual:**
```bash
git branch
```

### **Cambiar de rama:**
```bash
git checkout nombre-rama
```

### **Ver historial gráfico:**
```bash
git log --graph --oneline --all
```

---

## 📊 Timeline Estimado

| Semana | Rama | Módulo | Estado |
|--------|------|--------|--------|
| 1-2 | `feature/bdd-auth` | Autenticación | ⏳ Pendiente |
| 3-4 | `feature/bdd-products` | Productos | ⏳ Pendiente |
| 5-6 | `feature/bdd-inventory` | Inventario | ⏳ Pendiente |
| 7-8 | `feature/bdd-sales` | Ventas | ⏳ Pendiente |
| 9-10 | `feature/bdd-customers` | Clientes | ⏳ Pendiente |
| 11 | Release | Merge a master | ⏳ Pendiente |

---

## 🔄 Estado Actual del Repositorio

```bash
# Última actualización: 27 de noviembre de 2025

* develop (HEAD)
|   93a89a3 feat(bdd-tdd): Crear estructura de documentación BDD/TDD
|
* master
    43d989c docs(git): Agregar guías de buenas prácticas Git
```

---

## 📚 Archivos Relacionados

- **Workflow completo:** [`docs/05-testing/04-workflow.md`](docs/05-testing/04-workflow.md)
- **Guía de Git:** [`GIT_BEST_PRACTICES.md`](GIT_BEST_PRACTICES.md)
- **Script de commits:** [`commit_guide.sh`](commit_guide.sh)
- **Features BDD:** [`docs/06-features/`](docs/06-features/)

---

## ✅ Checklist de Setup Completado

- [x] Rama `master` creada
- [x] Rama `develop` creada
- [x] Estructura de directorios BDD/TDD
- [x] Documentación de workflow
- [x] Guías de buenas prácticas Git
- [ ] Configurar protección de `master` en GitHub ⏳
- [ ] Crear primera feature `feature/bdd-auth` ⏳
- [ ] Configurar remoto `origin` ⏳

---

## 🚀 Próximos Pasos

1. **Configurar remoto GitHub:**
   ```bash
   git remote add origin https://github.com/usuario/ferreteria-frenat.git
   git push -u origin master
   git push -u origin develop
   ```

2. **Proteger `master` en GitHub:**
   - Settings → Branches → Add rule
   - Branch name pattern: `master`
   - Require pull request before merging: ✅

3. **Crear primera feature:**
   ```bash
   git checkout develop
   git checkout -b feature/bdd-auth
   ```

---

**Última actualización:** 27 de noviembre de 2025
