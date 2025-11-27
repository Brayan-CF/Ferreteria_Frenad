# 📚 Guía de Buenas Prácticas Git

## 🎯 Filosofía de Commits Profesionales

### ❌ MALO (Evitar)
```bash
git add .
git commit -m "cambios"
git commit -m "fix"
git commit -m "actualizaciones"
```

### ✅ BUENO (Usar)
```bash
git add docker-compose.yml
git commit -m "feat(docker): Configurar PostgreSQL con volumen persistente"

git add README.md
git commit -m "docs: Agregar instrucciones de instalación"
```

---

## 📝 Convención de Mensajes (Conventional Commits)

### Formato
```
<tipo>(<scope>): <descripción corta>

<descripción larga opcional>

<footer opcional>
```

### Tipos Estándar

| Tipo | Uso | Ejemplo |
|------|-----|---------|
| `feat` | Nueva funcionalidad | `feat(auth): Agregar login con JWT` |
| `fix` | Corrección de bug | `fix(api): Corregir error 500 en /users` |
| `docs` | Solo documentación | `docs(readme): Actualizar instalación` |
| `style` | Formato, punto y coma | `style: Aplicar prettier a todo el código` |
| `refactor` | Refactorización | `refactor(database): Optimizar queries` |
| `test` | Agregar tests | `test(user): Agregar tests unitarios` |
| `chore` | Mantenimiento | `chore: Actualizar dependencias` |
| `perf` | Mejora rendimiento | `perf(api): Agregar caché Redis` |
| `ci` | Integración continua | `ci: Agregar GitHub Actions` |
| `build` | Sistema de build | `build: Configurar Webpack` |

---

## 🔥 Comandos Esenciales

### Antes de Commitear
```bash
# Ver estado resumido
git status -s

# Ver cambios en detalle
git diff

# Ver cambios de archivo específico
git diff archivo.txt

# Ver qué está en staging
git diff --staged
```

### Agregar Archivos
```bash
# Agregar archivo específico
git add archivo.txt

# Agregar varios archivos
git add archivo1.txt archivo2.txt

# Agregar directorio completo
git add carpeta/

# Agregar todo (⚠️ usar con cuidado)
git add .

# Agregar interactivamente (recomendado)
git add -p
```

### Commits
```bash
# Commit simple
git commit -m "tipo: mensaje corto"

# Commit con descripción larga
git commit -m "tipo: mensaje corto" -m "Descripción detallada"

# Abrir editor para commit
git commit

# Modificar último commit (solo local, no pusheado)
git commit --amend

# Modificar mensaje del último commit
git commit --amend -m "nuevo mensaje"
```

### Deshacer Cambios
```bash
# Deshacer git add (archivo sigue modificado)
git restore --staged archivo.txt

# Descartar cambios en archivo (⚠️ pérdida permanente)
git restore archivo.txt

# Volver todo a como estaba
git restore --staged .
git restore .

# Deshacer último commit (conservar cambios)
git reset --soft HEAD~1

# Deshacer último commit (perder cambios ⚠️)
git reset --hard HEAD~1
```

---

## 📊 Ver Historial

```bash
# Historial completo
git log

# Historial resumido (una línea por commit)
git log --oneline

# Historial con gráfico
git log --graph --oneline --all

# Últimos 5 commits
git log -5

# Commits de autor específico
git log --author="Juan"

# Commits con "fix" en mensaje
git log --grep="fix"

# Ver cambios de commit específico
git show abc123

# Ver archivos cambiados en commit
git show --name-only abc123

# Ver último commit
git show
```

---

## 🌿 Ramas (Branches)

```bash
# Ver ramas
git branch

# Crear rama
git branch feature/nueva-funcionalidad

# Cambiar de rama
git checkout feature/nueva-funcionalidad

# Crear y cambiar (shortcut)
git checkout -b feature/nueva-funcionalidad

# Eliminar rama
git branch -d feature/antigua

# Forzar eliminar (⚠️)
git branch -D feature/antigua

# Ver ramas remotas
git branch -r

# Ver todas las ramas
git branch -a
```

---

## 🔄 Sincronizar con Remoto

```bash
# Ver remotos configurados
git remote -v

# Agregar remoto
git remote add origin https://github.com/usuario/repo.git

# Traer cambios (no fusionar)
git fetch origin

# Traer y fusionar
git pull origin master

# Subir cambios
git push origin master

# Subir rama nueva
git push -u origin feature/nueva-rama

# Forzar push (⚠️ peligroso)
git push --force

# Forzar push seguro
git push --force-with-lease
```

---

## 🛠️ Workflow Recomendado

### Para Proyectos Personales/Académicos

```bash
# 1. Ver qué cambió
git status

# 2. Ver cambios en detalle
git diff

# 3. Agregar archivos relacionados
git add archivo1.txt archivo2.txt

# 4. Ver qué está en staging
git diff --staged

# 5. Hacer commit descriptivo
git commit -m "feat: Agregar funcionalidad X"

# 6. Repetir 3-5 para otros grupos de cambios

# 7. Subir al remoto
git push origin master
```

### Para Proyectos Profesionales/Equipo

```bash
# 1. Crear rama para funcionalidad
git checkout -b feature/nueva-funcionalidad

# 2. Hacer commits atómicos
git add archivo_relacionado.txt
git commit -m "feat: Implementar parte A"

git add otro_archivo.txt
git commit -m "test: Agregar tests para parte A"

# 3. Subir rama
git push -u origin feature/nueva-funcionalidad

# 4. Crear Pull Request en GitHub

# 5. Después de merge, actualizar master
git checkout master
git pull origin master

# 6. Eliminar rama
git branch -d feature/nueva-funcionalidad
```

---

## 🎓 Ejemplo Real: Tu Proyecto Ferretería

### Situación Inicial
```bash
➜ git status
 M .gitignore
 M README.md
 M docker-compose.yml
?? .env.example
?? CHECKLIST.md
?? backend/
?? frontend/
?? start.sh
```

### Commits Profesionales (4 commits lógicos)

```bash
# COMMIT 1: Infraestructura
git add docker-compose.yml backend/Dockerfile backend/php.ini frontend/Dockerfile frontend/nginx.conf
git commit -m "feat(docker): Configurar infraestructura completa con Docker Compose

- PostgreSQL 15 Alpine con persistencia
- Backend PHP 8.2 FPM con extensiones PostgreSQL
- Frontend Nginx Alpine para archivos estáticos
- Red personalizada ferreteria_network"

# COMMIT 2: Automatización
git add start.sh comandos.md
git commit -m "feat(automation): Agregar script de despliegue automatizado

- start.sh con verificación de requisitos
- Construcción y migración automática
- comandos.md con documentación útil"

# COMMIT 3: Documentación
git add README.md RESUMEN.md SOLUCIONES.md CHECKLIST.md
git commit -m "docs: Actualizar documentación completa del proyecto

- README.md con guía de instalación
- RESUMEN.md para presentación académica
- SOLUCIONES.md con problemas resueltos
- CHECKLIST.md de verificación"

# COMMIT 4: Configuración
git add .gitignore .env.example database/backups/
git commit -m "chore: Configurar entorno y estructura de directorios

- .gitignore para archivos sensibles
- .env.example como plantilla
- database/backups/ para respaldos"

# PUSH al remoto
git push origin master
```

---

## 🚨 Errores Comunes y Soluciones

### "Olvidé agregar un archivo al último commit"
```bash
git add archivo_olvidado.txt
git commit --amend --no-edit
```

### "Quiero cambiar el mensaje del último commit"
```bash
git commit --amend -m "nuevo mensaje correcto"
```

### "Hice commit en la rama equivocada"
```bash
# Guardar el commit
git log  # Copiar el hash (ej: abc123)

# Deshacer commit en rama actual
git reset --hard HEAD~1

# Ir a rama correcta
git checkout rama-correcta

# Aplicar commit
git cherry-pick abc123
```

### "Agregué archivo por error al staging"
```bash
git restore --staged archivo_no_deseado.txt
```

### "Quiero descartar todos los cambios"
```bash
git restore .
git clean -fd  # Eliminar archivos sin seguimiento
```

---

## 📏 Reglas de Oro

1. **Un commit = Un cambio lógico**
   - ✅ BIEN: "feat: Agregar validación de email"
   - ❌ MAL: "cambios varios en login, validación y estilos"

2. **Mensajes descriptivos**
   - ✅ BIEN: "fix(api): Corregir error 500 en endpoint /users cuando email es null"
   - ❌ MAL: "fix bug"

3. **Commitear frecuentemente**
   - No esperar días con cambios gigantes
   - Pequeños commits = fácil de revisar y revertir

4. **Nunca `git add .` sin revisar**
   - Siempre hacer `git status` primero
   - Usar `git diff` para ver cambios

5. **No modificar historial público**
   - `git push --force` solo en ramas personales
   - Nunca en `master` o `main` compartida

6. **Usar ramas para funcionalidades**
   - `master` siempre estable
   - Features en ramas separadas

7. **Pull antes de Push**
   ```bash
   git pull origin master
   git push origin master
   ```

---

## 🎯 Atajos Útiles

### Alias de Git (agregar a ~/.gitconfig)

```ini
[alias]
    st = status -s
    co = checkout
    br = branch
    cm = commit -m
    lg = log --graph --oneline --all
    last = log -1 HEAD
    unstage = restore --staged
    undo = reset --soft HEAD~1
```

**Uso:**
```bash
git st          # en vez de git status -s
git lg          # log gráfico
git last        # ver último commit
git unstage .   # deshacer git add .
git undo        # deshacer último commit
```

---

## 📚 Recursos Adicionales

- **Conventional Commits:** https://www.conventionalcommits.org/
- **Git Book:** https://git-scm.com/book/es/v2
- **GitHub Guides:** https://guides.github.com/
- **Visualizador interactivo:** https://learngitbranching.js.org/

---

## 💡 TL;DR (Resumen Ultra-Rápido)

```bash
# Ver cambios
git status
git diff

# Agregar archivos relacionados (NO git add .)
git add archivo1 archivo2

# Commit descriptivo con tipo
git commit -m "tipo: descripción clara"

# Subir
git push origin master
```

**Tipos comunes:** `feat`, `fix`, `docs`, `chore`, `refactor`, `test`

**Regla de oro:** Un commit = Un cambio lógico con mensaje descriptivo

---