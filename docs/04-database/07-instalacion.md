# GUÍA DE INSTALACIÓN - BASE DE DATOS

## Requisitos Previos

### Software Requerido:

- **PostgreSQL**: Versión 14 o superior
- **Sistema Operativo**: Linux (Ubuntu 20.04+), macOS, Windows 10+
- **Git**: Para clonar el repositorio
- **Terminal/Shell**: Bash, Zsh o PowerShell

### Acceso Requerido:

- Usuario con permisos `CREATEDB` en PostgreSQL
- Acceso a terminal con permisos sudo (Linux/macOS)

---

## OPCIÓN 1: Instalación con Script Automático (Recomendado)

### Paso 1: Instalar PostgreSQL

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

**macOS (con Homebrew):**
```bash
brew install postgresql@14
brew services start postgresql@14
```

**Windows:**
Descargar instalador desde: https://www.postgresql.org/download/windows/

---

### Paso 2: Configurar Usuario PostgreSQL

```bash
# Cambiar a usuario postgres
sudo -u postgres psql

# Crear usuario para la aplicación (opcional)
CREATE USER frenad_user WITH PASSWORD 'tu_password_seguro';
ALTER USER frenad_user CREATEDB;

# Salir
\q
```

---

### Paso 3: Crear Base de Datos

```bash
# Como usuario postgres
sudo -u postgres psql -c "CREATE DATABASE ferreteria_frenad;"

# O como usuario creado
psql -U frenad_user -h localhost -c "CREATE DATABASE ferreteria_frenad;"
```

---

### Paso 4: Clonar Repositorio

```bash
cd /home/tu_usuario/proyectos
git clone https://github.com/tu-usuario/ferreteria-frenad.git
cd ferreteria-frenad/database
```

---

### Paso 5: Ejecutar Script de Migración

```bash
# Dar permisos de ejecución
chmod +x migrate.sh

# Configurar variables (editar script si es necesario)
# Por defecto: DB_NAME="ferreteria_frenad", DB_USER="postgres"

# Ejecutar migración
./migrate.sh
```

**Salida Esperada:**

```
🚀 Iniciando migración de base de datos...
Base de datos: ferreteria_frenad
Usuario: postgres

═══ PASO 1: EXTENSIONES ═══
📄 Ejecutando: 00_extensions/extensions.sql
   ✅ Completado

═══ PASO 2: TABLAS ═══
📄 Ejecutando: 01_tables/01_usuarios.sql
   ✅ Completado
📄 Ejecutando: 01_tables/02_roles.sql
   ✅ Completado
...
[continúa con todos los archivos]
...

═══ PASO 8: DATOS INICIALES ═══
📄 Ejecutando: 06_seeders/01_roles.sql
   ✅ Completado
...

════════════════════════════════════
✅ MIGRACIÓN COMPLETADA EXITOSAMENTE
════════════════════════════════════

📊 Verificando instalación...
   Tablas creadas: 25 (esperadas: 25)
   Vistas creadas: 8 (esperadas: 8)

🎉 Base de datos lista para usar
```

---

### Paso 6: Verificar Instalación

```bash
psql -U postgres -d ferreteria_frenad -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';"

# Resultado esperado: 25
```

---

## OPCIÓN 2: Instalación Manual

### Paso 1: Crear Base de Datos

```bash
sudo -u postgres createdb ferreteria_frenad
```

---

### Paso 2: Ejecutar Archivos SQL en Orden

```bash
cd database

# 1. Extensiones
psql -U postgres -d ferreteria_frenad -f 00_extensions/extensions.sql

# 2. Tablas (ejecutar todos en orden)
for file in 01_tables/*.sql; do
    echo "Ejecutando: $file"
    psql -U postgres -d ferreteria_frenad -f "$file"
done

# 3. Foreign Keys
psql -U postgres -d ferreteria_frenad -f 07_foreign_keys/foreign_keys.sql

# 4. Índices
for file in 02_indexes/*.sql; do
    psql -U postgres -d ferreteria_frenad -f "$file"
done

# 5. Funciones
for file in 03_functions/*.sql; do
    psql -U postgres -d ferreteria_frenad -f "$file"
done

# 6. Triggers
for file in 04_triggers/*.sql; do
    psql -U postgres -d ferreteria_frenad -f "$file"
done

# 7. Vistas
for file in 05_views/*.sql; do
    psql -U postgres -d ferreteria_frenad -f "$file"
done

# 8. Seeders
for file in 06_seeders/*.sql; do
    psql -U postgres -d ferreteria_frenad -f "$file"
done
```

---

## OPCIÓN 3: Instalación con Docker (Futuro)

**Nota:** Archivo `docker-compose.yml` en desarrollo.

```yaml
version: '3.8'

services:
  postgres:
    image: postgres:14
    environment:
      POSTGRES_DB: ferreteria_frenad
      POSTGRES_USER: frenad_user
      POSTGRES_PASSWORD: password_seguro
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database:/docker-entrypoint-initdb.d

volumes:
  postgres_data:
```

```bash
docker-compose up -d
```

---

## Configuración Post-Instalación

### 1. Cambiar Contraseña del Administrador

**IMPORTANTE:** La contraseña por defecto es `password`. Cambiarla inmediatamente:

```sql
-- Generar hash bcrypt con tu contraseña (en PHP/Laravel)
-- password_hash('tu_nueva_contraseña', PASSWORD_BCRYPT)

UPDATE usuarios
SET password_hash = '$2y$10$...' -- Tu hash aquí
WHERE email = 'admin@frenad.com';
```

---

### 2. Configurar Límites de Conexión (Producción)

```bash
# Editar postgresql.conf
sudo nano /etc/postgresql/14/main/postgresql.conf

# Ajustar:
max_connections = 100
shared_buffers = 256MB
effective_cache_size = 1GB
```

---

### 3. Habilitar Conexiones Remotas (si es necesario)

```bash
# Editar pg_hba.conf
sudo nano /etc/postgresql/14/main/pg_hba.conf

# Agregar:
host    ferreteria_frenad    frenad_user    192.168.1.0/24    md5

# Reiniciar
sudo systemctl restart postgresql
```

---

### 4. Configurar Backup Automático

```bash
# Crear script de backup
sudo nano /usr/local/bin/backup_ferreteria.sh

#!/bin/bash
BACKUP_DIR="/var/backups/postgresql"
DATE=$(date +%Y%m%d_%H%M%S)
pg_dump -U postgres ferreteria_frenad | gzip > "$BACKUP_DIR/ferreteria_$DATE.sql.gz"

# Eliminar backups de más de 30 días
find $BACKUP_DIR -name "ferreteria_*.sql.gz" -mtime +30 -delete

# Dar permisos
sudo chmod +x /usr/local/bin/backup_ferreteria.sh

# Agregar a cron (diario a las 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/backup_ferreteria.sh
```

---

## Pruebas Básicas Post-Instalación

### Prueba 1: Contar Tablas

```sql
SELECT COUNT(*) 
FROM information_schema.tables 
WHERE table_schema = 'public' AND table_type = 'BASE TABLE';

-- Resultado esperado: 25
```

---

### Prueba 2: Verificar Vistas

```sql
SELECT COUNT(*) 
FROM information_schema.views 
WHERE table_schema = 'public';

-- Resultado esperado: 8
```

---

### Prueba 3: Verificar Funciones

```sql
SELECT COUNT(*) 
FROM pg_proc p
JOIN pg_namespace n ON p.pronamespace = n.oid
WHERE n.nspname = 'public';

-- Resultado esperado: ~15+
```

---

### Prueba 4: Verificar Seeders

```sql
SELECT COUNT(*) FROM roles; -- Esperado: 3
SELECT COUNT(*) FROM usuarios; -- Esperado: 1 (admin)
SELECT COUNT(*) FROM almacenes; -- Esperado: 2
SELECT COUNT(*) FROM unidades_medida; -- Esperado: 14
```

---

### Prueba 5: Verificar Stock

```sql
SELECT * FROM vista_stock_actual LIMIT 5;
-- Debe mostrar productos de ejemplo
```

---

### Prueba 6: Probar Función

```sql
SELECT obtener_stock_total_producto(1);
-- Debe retornar el stock total del producto 1
```

---

## Solución de Problemas Comunes

### Error: "database does not exist"

```bash
# Crear la base de datos manualmente
sudo -u postgres createdb ferreteria_frenad
```

---

### Error: "permission denied"

```bash
# Otorgar permisos al usuario
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ferreteria_frenad TO frenad_user;"
```

---

### Error: "extension uuid-ossp does not exist"

```sql
-- Instalar extensión manualmente
sudo -u postgres psql -d ferreteria_frenad -c "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";"
```

---

### Error: "peer authentication failed"

```bash
# Cambiar método de autenticación
sudo nano /etc/postgresql/14/main/pg_hba.conf

# Cambiar de "peer" a "md5":
local   all             all                                     md5

# Reiniciar
sudo systemctl restart postgresql
```

---

### Error: Scripts no se ejecutan en orden

```bash
# Usar el script de migración que garantiza el orden
./migrate.sh
```

---

## Rollback (Eliminar Todo)

**PRECAUCIÓN:** Esto eliminará TODA la base de datos.

```bash
# Opción 1: Usar script
./rollback.sh
# Confirmar escribiendo: SI

# Opción 2: Manual
sudo -u postgres dropdb ferreteria_frenad
sudo -u postgres createdb ferreteria_frenad
```

---

## Migración a Producción

### Checklist Pre-Producción:

- [ ] Cambiar contraseña del administrador
- [ ] Configurar backup automático
- [ ] Configurar límites de conexión
- [ ] Habilitar SSL en PostgreSQL
- [ ] Configurar firewall (solo puerto 5432 desde servidor app)
- [ ] Revisar permisos de usuarios
- [ ] Probar restore de backup
- [ ] Documentar credenciales en gestor seguro
- [ ] Configurar monitoring (pg_stat_statements)

---

### Exportar Datos de Desarrollo a Producción:

```bash
# En desarrollo: Exportar solo datos
pg_dump -U postgres -d ferreteria_frenad --data-only --inserts > datos_desarrollo.sql

# En producción: Importar
psql -U postgres -d ferreteria_frenad -f datos_desarrollo.sql
```

---

## Conexión desde Laravel

### Archivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ferreteria_frenad
DB_USERNAME=frenad_user
DB_PASSWORD=tu_password_seguro
```

---

### Probar Conexión:

```bash
php artisan migrate:status
# Debe mostrar las migraciones (si usas Laravel migrations)

# O probar directamente
php artisan tinker
>>> DB::select('SELECT COUNT(*) FROM productos');
```

---

## Recursos Adicionales

- [Documentación PostgreSQL](https://www.postgresql.org/docs/)
- [pgAdmin](https://www.pgadmin.org/) - Herramienta GUI
- [DBeaver](https://dbeaver.io/) - Cliente universal

---

**Instalación Completada**

**Siguiente:** [Guía de Testing](10-testing.md)
