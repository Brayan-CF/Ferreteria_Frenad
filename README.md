# 🏪 Sistema POS Ferretería Frenad

Sistema completo de punto de venta y gestión administrativa para ferretería en El Alto, La Paz - Bolivia.

## 🎯 Características

- ✅ **Gestión de Ventas:** POS completo con soporte para créditos
- ✅ **Control de Inventario:** Multi-almacén con kardex automatizado
- ✅ **Gestión de Compras:** Órdenes y recepciones de mercadería
- ✅ **Gestión de Clientes:** CRM con límites de crédito
- ✅ **Arqueo de Caja:** Control diario de ingresos/egresos
- ✅ **Auditoría Completa:** Logs de todas las operaciones críticas

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 10 + PHP 8.2
- **Frontend:** HTML5 + CSS3 + JavaScript + Nginx
- **Base de Datos:** PostgreSQL 15
- **Containerización:** Docker + Docker Compose

## 📋 Requisitos Previos

- Docker 20.10+
- Docker Compose 2.0+
- Git
- 4GB RAM mínimo
- 10GB espacio en disco

## 🚀 Instalación Rápida

### Opción 1: Script Automático (Recomendado)

```bash
# 1. Clonar repositorio
git clone <tu-repo>
cd Ferreteria_Frenat

# 2. Ejecutar script de inicio
./start.sh
```

El script automáticamente:
- ✅ Verifica requisitos
- ✅ Crea directorios necesarios
- ✅ Levanta contenedores Docker
- ✅ Ejecuta migraciones de BD
- ✅ Verifica que todo funcione

### Opción 2: Manual

```bash
# 1. Clonar repositorio
git clone <tu-repo>
cd Ferreteria_Frenat

# 2. Verificar/crear .env
cp .env.example .env  # Si no existe

# 3. Crear directorios
mkdir -p database/backups database/pgadmin_data

# 4. Levantar contenedores
docker-compose up -d --build

# 5. Esperar a que PostgreSQL esté listo (30 segundos aprox)
sleep 30

# 6. Ejecutar migraciones
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024
exit

# 7. Verificar servicios
docker-compose ps
```

## 🌐 Acceso a Servicios

| Servicio | URL | Credenciales |
|----------|-----|--------------|
| **Frontend** | http://localhost:8080 | - |
| **Backend API** | http://localhost:8000 | - |
| **PostgreSQL** | localhost:5432 | postgres / frenad_postgres_2024 |
| **pgAdmin** (dev) | http://localhost:5050 | admin@frenad.local / admin123 |

## 📖 Documentación Completa

La documentación exhaustiva está en [`docs/04-database/`](docs/04-database/):

- **[00-introduccion.md](docs/04-database/00-introduccion.md)** - Visión general del sistema
- **[01-arquitectura.md](docs/04-database/01-arquitectura.md)** - Arquitectura de la base de datos
- **[07-instalacion.md](docs/04-database/07-instalacion.md)** - Guía de instalación detallada
- **[08-migracion.md](docs/04-database/08-migracion.md)** - Proceso de migraciones
- **[10-testing.md](docs/04-database/10-testing.md)** - Guía de testing
- **[importante.md](docs/04-database/importante.md)** - Documentación técnica académica (tablas, ER, normalización)

### Módulos Documentados

- **[02-modulos/01-auth.md](docs/04-database/02-modulos/01-auth.md)** - Autenticación y roles
- **[02-modulos/02-productos.md](docs/04-database/02-modulos/02-productos.md)** - Catálogo de productos
- **[02-modulos/03-inventario.md](docs/04-database/02-modulos/03-inventario.md)** - Control de stock
- **[02-modulos/04-compras.md](docs/04-database/02-modulos/04-compras.md)** - Gestión de compras
- **[02-modulos/05-ventas.md](docs/04-database/02-modulos/05-ventas.md)** - Punto de venta
- **[02-modulos/06-clientes.md](docs/04-database/02-modulos/06-clientes.md)** - CRM y créditos
- **[02-modulos/07-caja.md](docs/04-database/02-modulos/07-caja.md)** - Arqueo de caja
- **[02-modulos/08-auditoria.md](docs/04-database/02-modulos/08-auditoria.md)** - Logs y auditoría

## 🔧 Comandos Útiles

### Docker

```bash
# Levantar todos los servicios
docker-compose up -d

# Levantar CON pgAdmin (perfil dev)
docker-compose --profile dev up -d

# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend

# Reiniciar un servicio
docker-compose restart backend

# Detener servicios (mantiene datos)
docker-compose stop

# Detener y eliminar contenedores (mantiene volúmenes)
docker-compose down

# Detener y eliminar TODO incluidos volúmenes (⚠️ CUIDADO)
docker-compose down -v

# Acceder a un contenedor
docker exec -it ferreteria_postgres bash
docker exec -it ferreteria_backend bash

# Reconstruir contenedores
docker-compose up --build

# Acceder al contenedor del backend
docker exec -it ferreteria_backend sh

# Acceder a PostgreSQL
docker exec -it ferreteria_postgres psql -U postgres -d ferreteria_frenad
```

## Estructura del proyecto
```
FerreteriaFrenetProyecto/
├── backend/          # Laravel API
├── frontend/         # Interfaz POS
├── database/         # Scripts SQL
└── docs/             # Documentación
```

## Tecnologías

- Backend: Laravel 10 + PHP 8.2
- Frontend: HTML5 + JavaScript + Bootstrap
- Base de Datos: PostgreSQL 15
- Contenedores: Docker + Docker Compose