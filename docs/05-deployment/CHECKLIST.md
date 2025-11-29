# ✅ CHECKLIST PRE-DEPLOYMENT

**Verificar antes de ejecutar `./start.sh` o `docker-compose up`**

---

## 📋 REQUISITOS DEL SISTEMA

- [ ] Docker instalado (versión 20.10+)
  ```bash
  docker --version
  ```

- [ ] Docker Compose instalado (versión 2.0+)
  ```bash
  docker-compose --version
  ```

- [ ] Git instalado
  ```bash
  git --version
  ```

- [ ] Puertos disponibles:
  - [ ] Puerto 5432 (PostgreSQL)
  - [ ] Puerto 8000 (Backend)
  - [ ] Puerto 8080 (Frontend)
  - [ ] Puerto 5050 (pgAdmin - opcional)
  
  ```bash
  # Verificar puertos en uso
  sudo lsof -i :5432
  sudo lsof -i :8000
  sudo lsof -i :8080
  sudo lsof -i :5050
  ```

- [ ] Espacio en disco: mínimo 10GB libres
  ```bash
  df -h
  ```

- [ ] Memoria RAM: mínimo 4GB
  ```bash
  free -h
  ```

---

## 📂 ESTRUCTURA DE ARCHIVOS

### Archivos Críticos

- [ ] `.env` existe
  ```bash
  ls -la .env
  ```

- [ ] `docker-compose.yml` existe
  ```bash
  ls -la docker-compose.yml
  ```

- [ ] `backend/Dockerfile` existe
  ```bash
  ls -la backend/Dockerfile
  ```

- [ ] `backend/php.ini` existe
  ```bash
  ls -la backend/php.ini
  ```

- [ ] `frontend/Dockerfile` existe
  ```bash
  ls -la frontend/Dockerfile
  ```

- [ ] `frontend/nginx.conf` existe
  ```bash
  ls -la frontend/nginx.conf
  ```

- [ ] `database/migrate.sh` existe y es ejecutable
  ```bash
  ls -la database/migrate.sh
  ```

- [ ] `start.sh` existe y es ejecutable
  ```bash
  ls -la start.sh
  ```

---

### Directorios Necesarios

- [ ] `backend/` existe
  ```bash
  ls -ld backend/
  ```

- [ ] `frontend/` existe
  ```bash
  ls -ld frontend/
  ```

- [ ] `database/` existe
  ```bash
  ls -ld database/
  ```

- [ ] `database/backups/` existe
  ```bash
  ls -ld database/backups/
  ```

- [ ] `database/pgadmin_data/` existe
  ```bash
  ls -ld database/pgadmin_data/
  ```

- [ ] `docs/` existe
  ```bash
  ls -ld docs/
  ```

---

### Scripts SQL (database/)

- [ ] `00_extensions/` existe
- [ ] `01_tables/` existe (25 archivos SQL)
- [ ] `02_indexes/` existe (9 archivos SQL)
- [ ] `03_functions/` existe (4 archivos SQL)
- [ ] `04_triggers/` existe (3 archivos SQL)
- [ ] `05_views/` existe (8 archivos SQL)
- [ ] `06_seeders/` existe (7 archivos SQL)
- [ ] `07_foreign_keys/` existe (1 archivo SQL)

```bash
# Verificar todos
ls -ld database/0*/ database/migrate.sh database/rollback.sh
```

---

## 🔧 CONFIGURACIÓN

### Variables de Entorno (.env)

- [ ] `DB_HOST=postgres_ferreteria` (nombre del contenedor)
- [ ] `DB_PORT=5432`
- [ ] `DB_DATABASE=ferreteria_frenad`
- [ ] `DB_USERNAME=postgres`
- [ ] `DB_PASSWORD` está configurado (no vacío)
- [ ] `TZ=America/La_Paz`

```bash
# Verificar variables críticas
grep -E "^DB_|^TZ=" .env
```

---

### Permisos de Scripts

- [ ] `start.sh` tiene permisos de ejecución
  ```bash
  chmod +x start.sh
  ```

- [ ] `database/migrate.sh` tiene permisos de ejecución
  ```bash
  chmod +x database/migrate.sh
  ```

- [ ] `database/rollback.sh` tiene permisos de ejecución
  ```bash
  chmod +x database/rollback.sh
  ```

---

## 🐳 DOCKER

### Verificar Docker

- [ ] Docker daemon está corriendo
  ```bash
  docker info
  ```

- [ ] Docker Compose está disponible
  ```bash
  docker-compose version
  ```

- [ ] Usuario tiene permisos Docker (sin sudo)
  ```bash
  docker ps
  # Si falla, agregar usuario al grupo docker:
  # sudo usermod -aG docker $USER
  # Luego: newgrp docker
  ```

---

### Limpiar Docker (Opcional)

- [ ] Detener contenedores previos
  ```bash
  docker-compose down
  ```

- [ ] Limpiar imágenes huérfanas (opcional)
  ```bash
  docker image prune -f
  ```

- [ ] Limpiar volúmenes no usados (opcional)
  ```bash
  docker volume prune -f
  ```

---

## 📚 DOCUMENTACIÓN

- [ ] `README.md` revisado
- [ ] `comandos.md` revisado
- [ ] `SOLUCIONES.md` revisado
- [ ] `docs/04-database/` existe con documentación

```bash
# Verificar documentación
ls -la README.md comandos.md SOLUCIONES.md
ls -ld docs/04-database/
```

---

## 🚀 LISTO PARA DEPLOYMENT

### Opción 1: Script Automático

```bash
./start.sh
```

### Opción 2: Manual

```bash
# Ver comandos.md para pasos detallados
docker-compose up -d --build
```

---

## ✅ VERIFICACIÓN POST-DEPLOYMENT

Después de ejecutar, verificar:

- [ ] Contenedores corriendo
  ```bash
  docker-compose ps
  # Resultado esperado:
  # ferreteria_postgres    Up (healthy)
  # ferreteria_backend     Up
  # ferreteria_frontend    Up
  ```

- [ ] PostgreSQL responde
  ```bash
  docker exec ferreteria_postgres pg_isready -U postgres
  ```

- [ ] Base de datos existe
  ```bash
  docker exec ferreteria_postgres psql -U postgres -l | grep ferreteria_frenad
  ```

- [ ] Tablas creadas (25+ tablas)
  ```bash
  docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -c "\dt"
  ```

- [ ] Backend responde
  ```bash
  curl http://localhost:8000
  ```

- [ ] Frontend responde
  ```bash
  curl http://localhost:8080
  ```

---

## 🐛 SI ALGO FALLA

### Ver Logs

```bash
# Logs de todos los servicios
docker-compose logs -f

# Logs de un servicio específico
docker-compose logs -f postgres_ferreteria
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Reiniciar

```bash
# Reiniciar servicios
docker-compose restart

# Reiniciar servicio específico
docker-compose restart backend
```

### Limpiar y Empezar de Nuevo

```bash
# Detener y eliminar todo
docker-compose down -v

# Volver a empezar
./start.sh
```

---

## 📞 AYUDA

- **Documentación:** `docs/04-database/`
- **Comandos útiles:** `comandos.md`
- **Soluciones implementadas:** `SOLUCIONES.md`
- **README principal:** `README.md`

---

## ✅ CHECKLIST RÁPIDO

Antes de ejecutar `./start.sh`:

1. ✅ Docker instalado y corriendo
2. ✅ Puertos 5432, 8000, 8080 libres
3. ✅ `.env` existe y configurado
4. ✅ `start.sh` tiene permisos de ejecución
5. ✅ Directorios `database/backups/` y `database/pgadmin_data/` existen
6. ✅ Archivos `backend/Dockerfile` y `frontend/Dockerfile` existen

**Si todos están ✅, ejecutar:**

```bash
./start.sh
```

**Tiempo estimado:** 3-5 minutos

---

**¡Buena suerte con el deployment! 🚀**
