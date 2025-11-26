# PROCESO DE MIGRACIÓN

## Descripción General

Documentación del proceso automatizado de migración de la base de datos PostgreSQL.

---

## Herramienta Principal: migrate.sh

**Ubicación:** `database/migrate.sh`

**Función:** Script bash que ejecuta todas las migraciones en orden correcto.

---

## Orden de Ejecución

El script ejecuta los SQL en este orden estricto:

### 1. Extensions (00_extensions/)
- Carga extensiones de PostgreSQL necesarias
- Archivo: `extensions.sql`

### 2. Tables (01_tables/)
- Crea todas las tablas sin foreign keys circulares
- 25 archivos en orden numérico (01-25)

### 3. Indexes (02_indexes/)
- Crea índices de optimización
- 9 archivos por módulo

### 4. Functions (03_functions/)
- Define funciones de negocio
- 4 archivos: timestamp, validation, transactional, query

### 5. Triggers (04_triggers/)
- Configura triggers automáticos
- 3 archivos: timestamp, validation, auto-number

### 6. Views (05_views/)
- Crea vistas materializadas
- 8 archivos de vistas por módulo

### 7. Seeders (06_seeders/)
- Inserta datos iniciales
- 7 archivos en orden de dependencias

### 8. Foreign Keys (07_foreign_keys/)
- Añade foreign keys circulares al final
- Resuelve dependencias de `movimientos_inventario`

---

## Uso del Script migrate.sh

### Sintaxis:

```bash
./migrate.sh <DB_NAME> <DB_USER> <DB_HOST> <DB_PORT> [DB_PASSWORD]
```

### Ejemplo:

```bash
cd database/
chmod +x migrate.sh
./migrate.sh ferreteria_frenad postgres localhost 5432 admin123
```

### Parámetros:

- `DB_NAME`: Nombre de la base de datos
- `DB_USER`: Usuario PostgreSQL
- `DB_HOST`: Servidor (localhost, 127.0.0.1, etc.)
- `DB_PORT`: Puerto (por defecto 5432)
- `DB_PASSWORD`: Contraseña (opcional, se pedirá si no se provee)

---

## Proceso Paso a Paso

### 1. Verificación de prerrequisitos:

```bash
# Verifica que psql esté instalado
which psql

# Verifica que la base de datos exista
psql -U postgres -l | grep ferreteria_frenad
```

---

### 2. Creación de base de datos (si no existe):

```bash
psql -U postgres -c "CREATE DATABASE ferreteria_frenad;"
```

---

### 3. Ejecución del script:

```bash
./migrate.sh ferreteria_frenad postgres localhost 5432
```

**Salida esperada:**

```
===========================================
MIGRACIÓN DE BASE DE DATOS
===========================================

Ejecutando: 00_extensions/extensions.sql
✓ Completado

Ejecutando: 01_tables/01_usuarios.sql
✓ Completado

Ejecutando: 01_tables/02_roles.sql
✓ Completado

...

Ejecutando: 07_foreign_keys/foreign_keys.sql
✓ Completado

===========================================
MIGRACIÓN COMPLETADA CON ÉXITO
===========================================
```

---

### 4. Verificación post-migración:

```bash
# Contar tablas
psql -U postgres -d ferreteria_frenad -c "
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = 'public';
"

# Resultado esperado: 25 tablas
```

---

## Manejo de Errores

### Error: "Base de datos no existe"

**Solución:**
```bash
createdb -U postgres ferreteria_frenad
```

---

### Error: "Permiso denegado"

**Solución:**
```bash
# Dar permisos al usuario
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE ferreteria_frenad TO tu_usuario;"
```

---

### Error: "Extensión uuid-ossp no disponible"

**Solución (Ubuntu/Debian):**
```bash
sudo apt-get install postgresql-contrib
```

**Solución (Red Hat/CentOS):**
```bash
sudo yum install postgresql-contrib
```

---

### Error: "Tabla ya existe"

**Solución:**
```bash
# Usar rollback.sh primero
./rollback.sh ferreteria_frenad postgres localhost 5432
# Luego migrar nuevamente
./migrate.sh ferreteria_frenad postgres localhost 5432
```

---

## Script de Rollback

**Ubicación:** `database/rollback.sh`

**Función:** Elimina todas las tablas y objetos de la base de datos.

### Uso:

```bash
./rollback.sh <DB_NAME> <DB_USER> <DB_HOST> <DB_PORT> [DB_PASSWORD]
```

### Ejemplo:

```bash
./rollback.sh ferreteria_frenad postgres localhost 5432
```

**⚠️ ADVERTENCIA:** Este script elimina TODOS los datos. Solo usar en desarrollo.

---

## Migraciones en Entornos

### 1. Desarrollo Local

```bash
# Base de datos: ferreteria_frenad_dev
./migrate.sh ferreteria_frenad_dev postgres localhost 5432 dev123

# Con seeders de prueba
psql -U postgres -d ferreteria_frenad_dev -f database/06_seeders/07_datos_ejemplo.sql
```

---

### 2. Testing

```bash
# Base de datos: ferreteria_frenad_test
./migrate.sh ferreteria_frenad_test postgres localhost 5433 test123

# Sin seeders de ejemplo
# Solo seeders esenciales (roles, unidades, almacenes)
```

---

### 3. Producción

```bash
# Base de datos: ferreteria_frenad_prod
./migrate.sh ferreteria_frenad_prod postgres db.example.com 5432

# NUNCA ejecutar datos_ejemplo.sql en producción
# NUNCA ejecutar rollback.sh en producción
```

**Checklist pre-producción:**
- ✅ Respaldo de base de datos actual
- ✅ Verificar conexión a servidor de producción
- ✅ Probar migración en staging primero
- ✅ Notificar downtime a usuarios
- ✅ Tener plan de rollback

---

## Migraciones con Docker

### Usando docker-compose:

**Ver:** `docker-compose.yml` en la raíz del proyecto.

**Comando:**
```bash
docker-compose up -d postgres

# Ejecutar migrate.sh dentro del contenedor
docker-compose exec postgres bash
cd /docker-entrypoint-initdb.d/
./migrate.sh ferreteria_frenad postgres localhost 5432 admin123
```

---

### Usando volumen de inicialización:

**Configuración en docker-compose.yml:**
```yaml
volumes:
  - ./database:/docker-entrypoint-initdb.d/
```

**Comportamiento:**
- PostgreSQL ejecuta automáticamente scripts *.sql en `/docker-entrypoint-initdb.d/` al crear el contenedor.
- Solo ocurre si la base de datos no existe.

**Alternativa:** Copiar `migrate.sh` a la carpeta y renombrarlo `001-migrate.sh` para que se ejecute primero.

---

## Migraciones Incrementales

### Futuras actualizaciones de schema:

**Estructura recomendada:**

```
database/
  migrations/
    v1.0.0/  (inicial)
      00_extensions/
      01_tables/
      ...
    v1.1.0/  (actualización)
      alter_productos_add_peso.sql
      create_table_proveedores_contactos.sql
    v1.2.0/
      ...
```

---

### Script de migración versionada:

```bash
#!/bin/bash
# migrate_version.sh

VERSION=$1
DB_NAME=$2

if [ -z "$VERSION" ]; then
  echo "Uso: ./migrate_version.sh <version> <db_name>"
  exit 1
fi

echo "Aplicando migración $VERSION..."

for sql_file in migrations/$VERSION/*.sql; do
  echo "Ejecutando: $sql_file"
  psql -U postgres -d $DB_NAME -f $sql_file
done

echo "Migración $VERSION completada."
```

**Uso:**
```bash
./migrate_version.sh v1.1.0 ferreteria_frenad
```

---

## Tabla de Control de Migraciones

### Recomendación para producción:

Crear tabla para registrar migraciones ejecutadas:

```sql
CREATE TABLE schema_migrations (
    version VARCHAR(20) PRIMARY KEY,
    descripcion TEXT,
    ejecutado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ejecutado_por VARCHAR(100)
);

-- Registrar migración inicial
INSERT INTO schema_migrations (version, descripcion, ejecutado_por)
VALUES ('1.0.0', 'Migración inicial completa', 'admin');
```

**Ventajas:**
- Saber qué versión está en producción
- Evitar ejecutar migraciones duplicadas
- Trazabilidad de cambios

---

## Respaldos y Recuperación

### Crear respaldo antes de migrar:

```bash
pg_dump -U postgres -d ferreteria_frenad > backup_pre_migracion_$(date +%Y%m%d_%H%M%S).sql
```

---

### Restaurar respaldo si falla migración:

```bash
# 1. Eliminar base de datos actual
dropdb -U postgres ferreteria_frenad

# 2. Recrear base de datos
createdb -U postgres ferreteria_frenad

# 3. Restaurar respaldo
psql -U postgres -d ferreteria_frenad < backup_pre_migracion_20250101_143000.sql
```

---

## Verificación Post-Migración

### Checklist completo:

```bash
# 1. Contar objetos
psql -U postgres -d ferreteria_frenad -c "
SELECT 
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public') AS tablas,
    (SELECT COUNT(*) FROM information_schema.views WHERE table_schema='public') AS vistas,
    (SELECT COUNT(*) FROM pg_indexes WHERE schemaname='public') AS indices,
    (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='public') AS funciones;
"

# Resultado esperado:
# tablas: 25
# vistas: 8
# indices: 43+
# funciones: 11+
```

---

```bash
# 2. Verificar foreign keys
psql -U postgres -d ferreteria_frenad -c "
SELECT COUNT(*) FROM information_schema.table_constraints 
WHERE constraint_type = 'FOREIGN KEY';
"

# Resultado esperado: ~35 foreign keys
```

---

```bash
# 3. Verificar triggers
psql -U postgres -d ferreteria_frenad -c "
SELECT COUNT(*) FROM information_schema.triggers 
WHERE trigger_schema = 'public';
"

# Resultado esperado: 12+ triggers
```

---

```bash
# 4. Verificar seeders
psql -U postgres -d ferreteria_frenad -c "
SELECT 
    (SELECT COUNT(*) FROM roles) AS roles,
    (SELECT COUNT(*) FROM unidades_medida) AS unidades,
    (SELECT COUNT(*) FROM almacenes) AS almacenes;
"

# Resultado esperado:
# roles: 4
# unidades: 10+
# almacenes: 2
```

---

## Buenas Prácticas

### En desarrollo:
✅ Usar `rollback.sh` y `migrate.sh` libremente
✅ Probar migraciones múltiples veces
✅ Usar datos de ejemplo (`07_datos_ejemplo.sql`)

### En producción:
✅ Siempre respaldar antes de migrar
✅ Probar migración en staging primero
✅ Ejecutar en horario de bajo tráfico
✅ Tener plan de rollback documentado
✅ Monitorear logs durante migración
❌ NUNCA ejecutar `rollback.sh`
❌ NUNCA cargar `07_datos_ejemplo.sql`

---

## Troubleshooting

### Migración se queda colgada:

**Causa:** Queries bloqueadas o tablas grandes.

**Solución:**
```sql
-- Ver procesos activos
SELECT pid, state, query FROM pg_stat_activity 
WHERE datname = 'ferreteria_frenad';

-- Matar proceso bloqueado (si es necesario)
SELECT pg_terminate_backend(pid);
```

---

### Errores de permisos:

**Solución:**
```sql
-- Dar todos los permisos al usuario
GRANT ALL PRIVILEGES ON DATABASE ferreteria_frenad TO tu_usuario;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO tu_usuario;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO tu_usuario;
```

---

### Extensiones no se cargan:

**Solución:**
```bash
# Instalar postgresql-contrib
sudo apt-get install postgresql-contrib

# Reiniciar PostgreSQL
sudo systemctl restart postgresql
```

---

## Referencias

- **Script principal:** `database/migrate.sh`
- **Script de rollback:** `database/rollback.sh`
- **Instalación inicial:** `docs/04-database/07-instalacion.md`
- **Testing post-migración:** `docs/04-database/10-testing.md`
- **Documentación PostgreSQL:** https://www.postgresql.org/docs/current/backup-dump.html
