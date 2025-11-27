# 🚀 Documentación de Deployment - Ferretería Frenad

Esta carpeta contiene la documentación de despliegue manual del proyecto.

> **⚠️ NOTA:** Esta documentación es de referencia histórica. El proyecto ahora usa **Docker Compose** para deployment automatizado.

---

## 📋 Contenido

### 1. **comandos.md**
Lista completa de comandos de deployment manual:
- ✅ Preparación del entorno
- ✅ Comandos Docker Compose
- ✅ Inicialización de base de datos
- ✅ Verificación de servicios
- ✅ Comandos de troubleshooting
- ✅ Respaldo y restauración

**Cuándo consultar:** Para entender comandos Docker o troubleshooting manual.

---

### 2. **CHECKLIST.md**
Checklist completo pre-deployment:
- ✅ Requisitos del sistema
- ✅ Verificación de archivos
- ✅ Configuración de variables
- ✅ Verificación post-deployment
- ✅ Soluciones a problemas comunes

**Cuándo consultar:** Para verificar requisitos antes de deployar.

---

## 🎯 Forma Recomendada de Deployment

### Opción 1: Script Automático (Recomendado) ⭐

```bash
# Desde la raíz del proyecto
./start.sh
```

**Lo que hace automáticamente:**
1. ✅ Verifica Docker instalado
2. ✅ Verifica puertos disponibles
3. ✅ Crea directorios necesarios
4. ✅ Levanta contenedores Docker
5. ✅ Espera a PostgreSQL listo
6. ✅ Ejecuta migraciones de BD
7. ✅ Verifica servicios funcionando
8. ✅ Muestra URLs de acceso

**Tiempo:** 3-5 minutos

---

### Opción 2: Docker Compose Manual

```bash
# 1. Levantar servicios
docker-compose up -d --build

# 2. Esperar a PostgreSQL
sleep 30

# 3. Ejecutar migraciones
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024
exit

# 4. Verificar servicios
docker-compose ps
curl http://localhost:8080  # Frontend
curl http://localhost:8000  # Backend
```

**Tiempo:** 10-15 minutos

---

## 🌐 Servicios Después del Deployment

| Servicio | URL | Puerto |
|----------|-----|--------|
| **Frontend** | http://localhost:8080 | 8080 |
| **Backend API** | http://localhost:8000 | 8000 |
| **PostgreSQL** | localhost:5432 | 5432 |
| **pgAdmin** (opcional) | http://localhost:5050 | 5050 |

---

## 🔧 Comandos Útiles Post-Deployment

### Ver Logs
```bash
# Todos los servicios
docker-compose logs -f

# Servicio específico
docker-compose logs -f backend
docker-compose logs -f postgres_ferreteria
```

### Reiniciar Servicios
```bash
# Todos
docker-compose restart

# Específico
docker-compose restart backend
```

### Detener y Limpiar
```bash
# Detener (mantiene volúmenes)
docker-compose down

# Detener y eliminar TODO (CUIDADO: borra datos)
docker-compose down -v
```

### Acceder a Contenedores
```bash
# Backend
docker exec -it ferreteria_backend bash

# PostgreSQL
docker exec -it ferreteria_postgres bash
```

---

## 📊 Verificación de Deployment

### 1. Contenedores Corriendo
```bash
docker-compose ps

# Esperado:
# ferreteria_postgres    Up (healthy)
# ferreteria_backend     Up
# ferreteria_frontend    Up
```

### 2. Base de Datos
```bash
# PostgreSQL responde
docker exec ferreteria_postgres pg_isready -U postgres

# Base de datos existe
docker exec ferreteria_postgres psql -U postgres -l | grep ferreteria_frenad

# Tablas creadas (25+)
docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -c "\dt"
```

### 3. Servicios Web
```bash
# Frontend
curl http://localhost:8080

# Backend
curl http://localhost:8000
```

---

## 🐛 Troubleshooting

### PostgreSQL no inicia
```bash
# Ver logs
docker-compose logs postgres_ferreteria

# Verificar puerto 5432 libre
sudo lsof -i :5432

# Reintentar
docker-compose restart postgres_ferreteria
```

### Backend no conecta a BD
```bash
# Verificar variables en .env
grep -E "^DB_" .env

# DB_HOST debe ser: postgres_ferreteria (nombre del contenedor)
# NO usar: localhost, 127.0.0.1
```

### Frontend no carga
```bash
# Ver logs de Nginx
docker-compose logs frontend

# Verificar puerto 8080 libre
sudo lsof -i :8080
```

### Migraciones fallan
```bash
# Ejecutar manualmente
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024

# Ver errores específicos
psql -U postgres -d ferreteria_frenad
```

---

## 💾 Respaldo y Restauración

### Crear Respaldo
```bash
# Respaldo completo
docker exec ferreteria_postgres pg_dump -U postgres ferreteria_frenad > \
  database/backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Respaldo comprimido
docker exec ferreteria_postgres pg_dump -U postgres -Fc ferreteria_frenad > \
  database/backups/backup_$(date +%Y%m%d_%H%M%S).dump
```

### Restaurar Respaldo
```bash
# Desde SQL
docker exec -i ferreteria_postgres psql -U postgres ferreteria_frenad < \
  database/backups/backup_20251127.sql

# Desde dump comprimido
docker exec -i ferreteria_postgres pg_restore -U postgres -d ferreteria_frenad \
  database/backups/backup_20251127.dump
```

---

## 🔐 Credenciales

### PostgreSQL
- **Host:** `postgres_ferreteria` (dentro de Docker) / `localhost` (desde host)
- **Puerto:** `5432`
- **Usuario:** `postgres`
- **Contraseña:** Ver `.env` → `DB_PASSWORD`
- **Base de datos:** `ferreteria_frenad`

### pgAdmin (si está habilitado)
- **URL:** http://localhost:5050
- **Email:** `admin@frenad.local`
- **Contraseña:** `admin123`

---

## 📖 Documentación Relacionada

### Arquitectura
- **`docs/02-arquitectura/`** - Arquitectura del sistema
- **`docker-compose.yml`** - Configuración de servicios

### Base de Datos
- **`docs/04-database/`** - Documentación completa de BD
- **`database/migrate.sh`** - Script de migración
- **`database/rollback.sh`** - Script de rollback

### Testing y Workflow
- **`docs/05-testing/`** - Metodología BDD/TDD
- **`docs/01-git/`** - Workflow de Git

---

## 📞 Ayuda

**Si algo falla:**

1. **Ver logs:** `docker-compose logs -f`
2. **Verificar estado:** `docker-compose ps`
3. **Consultar:** `comandos.md` para comandos específicos
4. **Consultar:** `CHECKLIST.md` para verificar requisitos
5. **Último recurso:** Limpiar y reiniciar
   ```bash
   docker-compose down -v
   ./start.sh
   ```

---

**Última actualización:** 27 de noviembre de 2025  
**Versión:** 2.0.0
