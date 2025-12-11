<div align="center">

# 🏪 Sistema POS - Ferretería FRENAD

![Laravel](https://img.shields.io/badge/Laravel-10.50-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15.8-316192?style=for-the-badge&logo=postgresql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-24+-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-1.25-009639?style=for-the-badge&logo=nginx&logoColor=white)

![Tests](https://img.shields.io/badge/Tests-192%20Passed-success?style=flat-square)
![Coverage](https://img.shields.io/badge/Coverage-BDD%2FTDD-blue?style=flat-square)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=flat-square)

**Sistema completo de Punto de Venta y Gestión Administrativa**  
*Desarrollado para ferreterías en El Alto, La Paz - Bolivia 🇧🇴*

[Características](#-características) •
[Instalación Rápida](#-instalación-rápida-automática) •
[Instalación Manual](#-instalación-manual-paso-a-paso) •
[Documentación](#-documentación) •
[Pruebas](#-pruebas)

</div>

---

## 📑 Tabla de Contenidos

<details>
<summary><b>📖 Expandir Tabla de Contenidos</b></summary>

- [🏪 Sistema POS - Ferretería FRENAD](#-sistema-pos---ferretería-frenad)
  - [📑 Tabla de Contenidos](#-tabla-de-contenidos)
  - [✨ Características](#-características)
    - [�� Funcionalidades Principales](#-funcionalidades-principales)
  - [🛠️ Stack Tecnológico](#️-stack-tecnológico)
  - [📋 Requisitos Previos](#-requisitos-previos)
  - [🚀 Instalación Rápida (Automática)](#-instalación-rápida-automática)
    - [🎯 ¡Instala el sistema completo en 5 minutos!](#-instala-el-sistema-completo-en-5-minutos)
    - [Paso 1: Clonar el Repositorio](#paso-1-clonar-el-repositorio)
    - [Paso 2: Ejecutar Script de Instalación](#paso-2-ejecutar-script-de-instalación)
    - [✅ ¿Qué hace el script `start.sh`?](#-qué-hace-el-script-startsh)
  - [📝 Instalación Manual (Paso a Paso)](#-instalación-manual-paso-a-paso)
    - [1. Clonar Repositorio y Configurar Entorno](#1-clonar-repositorio-y-configurar-entorno)
    - [2. Construir Imágenes Docker](#2-construir-imágenes-docker)
    - [3. Levantar Contenedores](#3-levantar-contenedores)
    - [4. Instalar Dependencias de Laravel](#4-instalar-dependencias-de-laravel)
    - [5. Ejecutar Migraciones de Base de Datos](#5-ejecutar-migraciones-de-base-de-datos)
    - [6. Verificar Instalación](#6-verificar-instalación)
    - [🎉 ¡Instalación Completa!](#-instalación-completa)
  - [🔄 Scripts Disponibles](#-scripts-disponibles)
    - [📌 Flujo de Trabajo Típico](#-flujo-de-trabajo-típico)
  - [🌐 URLs de Acceso](#-urls-de-acceso)
    - [🔐 Credenciales de Prueba](#-credenciales-de-prueba)
    - [🔗 Conexión PostgreSQL](#-conexión-postgresql)
  - [🧪 Pruebas](#-pruebas)
    - [▶️ Ejecutar Pruebas](#️-ejecutar-pruebas)
    - [📊 Cobertura por Módulo](#-cobertura-por-módulo)
  - [🔧 Comandos Docker Útiles](#-comandos-docker-útiles)
    - [🐳 Gestión de Contenedores](#-gestión-de-contenedores)
    - [🔌 Acceso a Contenedores](#-acceso-a-contenedores)
    - [🔍 Inspección y Debug](#-inspección-y-debug)
    - [🔄 Reconstrucción](#-reconstrucción)
    - [🐘 pgAdmin (Opcional)](#-pgadmin-opcional)
  - [📁 Estructura del Proyecto](#-estructura-del-proyecto)
  - [📖 Documentación](#-documentación)
    - [📚 Documentación por Categoría](#-documentación-por-categoría)
    - [🔍 Documentación de Base de Datos](#-documentación-de-base-de-datos)
    - [📖 API Interactiva](#-api-interactiva)
  - [❓ Solución de Problemas](#-solución-de-problemas)
    - [⚠️ El backend no responde](#️-el-backend-no-responde)
    - [⚠️ Error de conexión a PostgreSQL](#️-error-de-conexión-a-postgresql)
    - [⚠️ Las migraciones fallan](#️-las-migraciones-fallan)
    - [⚠️ Error: "Port already in use"](#️-error-port-already-in-use)
    - [⚠️ Composer install falla](#️-composer-install-falla)
    - [⚠️ Reinstalar desde cero](#️-reinstalar-desde-cero)
  - [👥 Equipo de Desarrollo](#-equipo-de-desarrollo)
    - [🎯 Objetivos del Proyecto](#-objetivos-del-proyecto)
  - [🤝 Contribuciones](#-contribuciones)
  - [📞 Soporte](#-soporte)
  - [⭐ ¡Si este proyecto te fue útil, dale una estrella!](#-si-este-proyecto-te-fue-útil-dale-una-estrella)

</details>

---

## ✨ Características

<div align="center">

| Módulo | Descripción | Estado |
|:------:|:-----------|:------:|
| 🛒 **Punto de Venta** | POS completo con ventas al contado y crédito | ![Status](https://img.shields.io/badge/-Completado-success) |
| 📦 **Inventario** | Control multi-almacén con kardex automatizado | ![Status](https://img.shields.io/badge/-Completado-success) |
| 🛍️ **Compras** | Gestión de órdenes y recepciones de mercadería | ![Status](https://img.shields.io/badge/-Completado-success) |
| 👥 **Clientes** | CRM con límites de crédito y estados de cuenta | ![Status](https://img.shields.io/badge/-Completado-success) |
| �� **Caja** | Arqueo diario de ingresos y egresos | ![Status](https://img.shields.io/badge/-Completado-success) |
| 📊 **Reportes** | Ventas, compras, inventario y financiero | ![Status](https://img.shields.io/badge/-Completado-success) |
| 🔐 **Seguridad** | Autenticación JWT con roles y permisos | ![Status](https://img.shields.io/badge/-Completado-success) |
| 📝 **Auditoría** | Logs completos de operaciones críticas | ![Status](https://img.shields.io/badge/-Completado-success) |

</div>

### �� Funcionalidades Principales

- ✅ **Gestión Completa de Inventario:** Control de stock en múltiples almacenes con kardex automatizado
- ✅ **Sistema de Créditos:** Manejo de límites de crédito por cliente con estados de cuenta
- ✅ **Punto de Venta Robusto:** Interface rápida y eficiente para ventas diarias
- ✅ **Módulo de Compras:** Gestión completa desde órdenes hasta recepción de mercadería
- ✅ **Reportes Financieros:** Dashboards con análisis de ventas, compras y rentabilidad
- ✅ **Auditoría Completa:** Logs automáticos de todas las operaciones críticas
- ✅ **API REST Documentada:** Endpoints documentados con Scribe (OpenAPI 3.0)
- ✅ **192 Tests Automatizados:** Cobertura completa con metodología BDD/TDD

---

## 🛠️ Stack Tecnológico

<div align="center">

```
┌─────────────────────────────────────────────────────────────────┐
│                     ARQUITECTURA DEL SISTEMA                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────────┐      ┌──────────────┐      ┌─────────────┐ │
│  │   Frontend    │◄────►│  Backend API │◄────►│  PostgreSQL │ │
│  │  Nginx + JS   │      │  Laravel 10  │      │     15.8    │ │
│  └───────────────┘      └──────────────┘      └─────────────┘ │
│         ▲                       ▲                     ▲        │
│         │                       │                     │        │
│         └───────────────────────┴─────────────────────┘        │
│                    Docker Compose Network                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

</div>

| Capa | Tecnología | Versión | Descripción |
|:----:|:-----------|:-------:|:------------|
| 🌐 | **Frontend** | - | HTML5 + CSS3 + JavaScript + Nginx 1.25 |
| 🔧 | **Backend API** | 10.50 | Laravel con arquitectura modular |
| 🐘 | **Base de Datos** | 15.8 | PostgreSQL con funciones, triggers y vistas |
| 🐳 | **Containerización** | 24+ | Docker + Docker Compose para portabilidad |
| 📖 | **API Docs** | 3.0 | Scribe (OpenAPI/Swagger) |
| 🧪 | **Testing** | 10.x | PHPUnit con 192 tests (Feature + Unit) |
| 🔐 | **Autenticación** | 2.0 | JWT (JSON Web Tokens) |

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

<div align="center">

| Requisito | Versión Mínima | Comando de Verificación | Instalación |
|:---------:|:--------------:|:----------------------:|:-----------:|
| 🐳 **Docker** | 20.10+ | `docker --version` | [Instalar Docker](https://docs.docker.com/get-docker/) |
| 🐳 **Docker Compose** | 2.0+ | `docker-compose --version` | [Instalar Compose](https://docs.docker.com/compose/install/) |
| 📂 **Git** | 2.0+ | `git --version` | [Instalar Git](https://git-scm.com/downloads) |
| 💾 **RAM** | 4GB mínimo | - | - |
| 💽 **Espacio en Disco** | 10GB libres | `df -h` | - |

</div>

> **💡 Nota:** Este proyecto está completamente dockerizado. No necesitas instalar PHP, Composer, PostgreSQL ni Nginx en tu máquina local.

---

## 🚀 Instalación Rápida (Automática)

<div align="center">

### 🎯 ¡Instala el sistema completo en 5 minutos!

</div>

### Paso 1: Clonar el Repositorio

```bash
# Clonar el repositorio desde GitHub
git clone https://github.com/Brayan-CF/Ferreteria_Frenad.git

# Entrar al directorio del proyecto
cd Ferreteria_Frenad
```

### Paso 2: Ejecutar Script de Instalación

```bash
# Dar permisos de ejecución a los scripts
chmod +x start.sh start-docker.sh database/migrate.sh

# Ejecutar instalación completa automática
./start.sh
```

> **⏱️ Tiempo estimado:** 5-10 minutos en la primera ejecución (depende de tu conexión a internet para descargar las imágenes Docker)

### ✅ ¿Qué hace el script `start.sh`?

El script `start.sh` automatiza **TODO** el proceso de instalación. Aquí está el detalle:

<details>
<summary><b>📋 Ver pasos detallados del script</b></summary>

| Paso | Descripción | Lo que hace |
|:----:|:-----------|:------------|
| 1️⃣ | **Verificar Requisitos** | Comprueba que Docker y Docker Compose estén instalados |
| 2️⃣ | **Crear Archivos .env** | Copia `.env.example` → `.env` y `backend/.env.example` → `backend/.env` |
| 3️⃣ | **Crear Directorios** | Crea `database/backups` y `database/pgadmin_data` |
| 4️⃣ | **Detener Contenedores Previos** | Ejecuta `docker-compose down` para limpiar contenedores anteriores |
| 5️⃣ | **Construir Imágenes** | Ejecuta `docker-compose up -d --build` para construir todas las imágenes |
| 6️⃣ | **Esperar PostgreSQL** | Espera hasta 60 segundos a que PostgreSQL esté completamente listo |
| 7️⃣ | **Instalar Dependencias** | Ejecuta `composer install` dentro del contenedor backend |
| 8️⃣ | **Generar APP_KEY** | Ejecuta `php artisan key:generate` para Laravel |
| 9️⃣ | **Limpiar Caché** | Ejecuta `php artisan config:clear` y `cache:clear` |
| 🔟 | **Ejecutar Migraciones** | Ejecuta `database/migrate.sh` para crear todas las tablas, funciones, triggers, etc. |
| 1️⃣1️⃣ | **Verificar Tablas** | Cuenta las tablas creadas en PostgreSQL |
| 1️⃣2️⃣ | **Verificar Servicios** | Prueba que Frontend, Backend y PostgreSQL respondan correctamente |
| ✅ | **Mostrar Info Final** | Muestra URLs, credenciales y comandos útiles |

</details>

Al finalizar, verás un mensaje como este:

```
============================================
✅ DEPLOYMENT COMPLETADO
============================================

📍 Servicios disponibles:
  • PostgreSQL:  localhost:5432
  • Backend API: http://localhost:8000
  • API Docs:    http://localhost:8000/docs
  • Frontend:    http://localhost:8080

🔑 Credenciales de prueba:
  • Email:    admin@frenad.com
  • Password: password
```

---

## 📝 Instalación Manual (Paso a Paso)

Si prefieres hacer la instalación paso a paso o el script automático falla, sigue esta guía detallada:

### 1. Clonar Repositorio y Configurar Entorno

```bash
# 1.1 Clonar el repositorio
git clone https://github.com/Brayan-CF/Ferreteria_Frenad.git
cd Ferreteria_Frenad

# 1.2 Crear archivo de entorno raíz
cp .env.example .env

# 1.3 Crear archivo de entorno del backend (Laravel)
cp backend/.env.example backend/.env

# 1.4 Crear directorios necesarios
mkdir -p database/backups
mkdir -p database/pgadmin_data

# 1.5 Dar permisos a scripts
chmod +x start.sh start-docker.sh
chmod +x database/migrate.sh database/init-volume.sh
```

> **📝 Nota:** Los archivos `.env` contienen las configuraciones de base de datos y aplicación. El script `start.sh` los crea automáticamente si no existen.

### 2. Construir Imágenes Docker

```bash
# 2.1 Construir las imágenes de Docker
# Esto descargará las imágenes base y construirá las personalizadas
# Puede tardar 5-10 minutos la primera vez
docker-compose build

# 2.2 Verificar que las imágenes se construyeron correctamente
docker images | grep ferreteria
```

Deberías ver algo como:

```
ferreteria_frenat-backend    latest    abc123def456   2 minutes ago   500MB
ferreteria_frenat-frontend   latest    def456ghi789   2 minutes ago   50MB
```

### 3. Levantar Contenedores

```bash
# 3.1 Levantar todos los contenedores en modo detached
docker-compose up -d

# 3.2 Verificar que los contenedores estén corriendo
docker-compose ps
```

Deberías ver 3 contenedores corriendo:

```
NAME                   STATUS    PORTS
ferreteria_backend     Up        0.0.0.0:8000->80/tcp
ferreteria_frontend    Up        0.0.0.0:8080->80/tcp
ferreteria_postgres    Up        0.0.0.0:5432->5432/tcp
```

```bash
# 3.3 Esperar a que PostgreSQL esté completamente listo
# Esto puede tomar 20-30 segundos
sleep 30

# 3.4 Verificar que PostgreSQL esté listo para aceptar conexiones
docker exec ferreteria_postgres pg_isready -U postgres
```

Deberías ver: `postgres:5432 - accepting connections`

### 4. Instalar Dependencias de Laravel

```bash
# 4.1 Instalar dependencias de Composer dentro del contenedor
docker exec ferreteria_backend composer install --no-interaction --optimize-autoloader

# 4.2 Generar la clave de aplicación de Laravel (APP_KEY)
docker exec ferreteria_backend php artisan key:generate --force

# 4.3 Verificar que la clave se generó correctamente
docker exec ferreteria_backend grep APP_KEY /var/www/html/.env
```

Deberías ver algo como: `APP_KEY=base64:randomKeyHere123...`

```bash
# 4.4 Limpiar cachés de configuración
docker exec ferreteria_backend php artisan config:clear
docker exec ferreteria_backend php artisan cache:clear
docker exec ferreteria_backend php artisan route:clear
```

### 5. Ejecutar Migraciones de Base de Datos

Ahora vamos a ejecutar el script `migrate.sh` que creará todas las tablas, funciones, triggers, vistas y seeders:

```bash
# 5.1 Entrar al contenedor de PostgreSQL
docker exec -it ferreteria_postgres bash

# Deberías estar dentro del contenedor, el prompt cambiará a algo como:
# root@abc123:/# 

# 5.2 Navegar al directorio de migraciones
cd /database

# 5.3 Dar permisos de ejecución al script (por si no los tiene)
chmod +x migrate.sh

# 5.4 Ejecutar el script de migraciones
# Sintaxis: ./migrate.sh <database> <user> <host> <port> <password>
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024

# 5.5 Salir del contenedor
exit
```

<details>
<summary><b>🔍 Ver detalles de lo que hace migrate.sh</b></summary>

El script `migrate.sh` ejecuta los archivos SQL en el siguiente orden:

1. **00_extensions/** - Instala extensiones de PostgreSQL (`uuid-ossp`, `pgcrypto`)
2. **01_tables/** - Crea todas las tablas del sistema (usuarios, productos, ventas, etc.)
3. **02_indixes/** - Crea índices para optimizar consultas
4. **03_functions/** - Crea funciones SQL reutilizables
5. **04_triggers/** - Crea triggers para auditoría automática
6. **05_views/** - Crea vistas para consultas complejas
7. **06_seeders/** - Inserta datos iniciales (usuarios, roles, categorías)
8. **07_foreing_keys/** - Crea las claves foráneas (relaciones entre tablas)

</details>

```bash
# 5.6 Verificar que las tablas se crearon correctamente
docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -c "\dt"

# 5.7 Contar las tablas creadas (deberías tener más de 20)
docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';"
```

### 6. Verificar Instalación

```bash
# 6.1 Verificar estado de todos los contenedores
docker-compose ps

# 6.2 Probar el backend (debería devolver HTML o JSON)
curl -I http://localhost:8000

# 6.3 Probar el frontend (debería devolver código 200)
curl -I http://localhost:8080

# 6.4 Probar la API con un endpoint específico
curl http://localhost:8000/api/health

# 6.5 Ver logs del backend para detectar errores
docker-compose logs backend

# 6.6 Ver logs de PostgreSQL
docker-compose logs postgres_ferreteria
```

### 🎉 ¡Instalación Completa!

Si todo salió bien, ahora puedes acceder a:

- **Frontend:** http://localhost:8080
- **Backend API:** http://localhost:8000
- **Documentación API:** http://localhost:8000/docs
- **PostgreSQL:** `localhost:5432`

---

## 🔄 Scripts Disponibles

El proyecto incluye scripts de shell para facilitar la gestión:

<div align="center">

| Script | Descripción | Cuándo Usar | Comando |
|:------:|:-----------|:------------|:--------|
| 🚀 | **start.sh** | Instalación completa desde cero | `./start.sh` |
| 🔄 | **start-docker.sh** | Levantar contenedores existentes | `./start-docker.sh` |
| 🗄️ | **database/migrate.sh** | Ejecutar/Re-ejecutar migraciones SQL | `./database/migrate.sh` |
| 🔧 | **database/init-volume.sh** | Inicializar volumen de PostgreSQL | `./database/init-volume.sh` |

</div>

### 📌 Flujo de Trabajo Típico

```
┌────────────────────────────────────────────────────────────────┐
│                    PRIMERA VEZ (Setup Inicial)                 │
├────────────────────────────────────────────────────────────────┤
│  1. git clone https://github.com/Brayan-CF/Ferreteria_Frenad  │
│  2. cd Ferreteria_Frenad                                       │
│  3. chmod +x *.sh database/*.sh                                │
│  4. ./start.sh                                                 │
│     └─► Hace TODO automáticamente ✅                           │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                  DESPUÉS DE REINICIAR LA PC                    │
├────────────────────────────────────────────────────────────────┤
│  1. cd Ferreteria_Frenad                                       │
│  2. ./start-docker.sh                                          │
│     └─► Solo levanta los contenedores (muy rápido) ⚡          │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│              SI ACTUALIZASTE LOS SCRIPTS SQL                   │
├────────────────────────────────────────────────────────────────┤
│  1. cd Ferreteria_Frenad/database                              │
│  2. ./migrate.sh ferreteria_frenad postgres localhost 5432 ... │
│     └─► Re-ejecuta las migraciones 🗄️                          │
└────────────────────────────────────────────────────────────────┘
```

---

## 🌐 URLs de Acceso

Una vez que la instalación esté completa, los servicios estarán disponibles en:

<div align="center">

| Servicio | URL | Puerto | Descripción |
|:---------|:----|:------:|:------------|
| 🖥️ **Frontend** | http://localhost:8080 | 8080 | Interfaz web del sistema POS |
| 🔧 **Backend API** | http://localhost:8000 | 8000 | API REST de Laravel |
| 📖 **Documentación API** | http://localhost:8000/docs | 8000 | Swagger/OpenAPI interactivo |
| 🐘 **PostgreSQL** | localhost:5432 | 5432 | Base de datos (conexión directa) |
| 🔍 **pgAdmin** (opcional) | http://localhost:5050 | 5050 | Administrador de BD web |

</div>

### 🔐 Credenciales de Prueba

```
📧 Email:    admin@frenad.com
🔑 Password: password
👤 Rol:      Administrador
```

### 🔗 Conexión PostgreSQL

Si quieres conectarte a PostgreSQL desde un cliente externo (DBeaver, pgAdmin desktop, etc.):

```
Host:     localhost
Port:     5432
Database: ferreteria_frenad
User:     postgres
Password: frenad_postgres_2024
```

---

## 🧪 Pruebas

El proyecto incluye **192 pruebas automatizadas** con metodología **BDD/TDD**:

<div align="center">

| Tipo de Prueba | Cantidad | Metodología | Estado |
|:--------------|:--------:|:-----------:|:------:|
| 🔲 **Feature Tests** (Caja Negra) | 94 | BDD | ![](https://img.shields.io/badge/94-Passed-success) |
| ⚪ **Unit Tests** (Caja Blanca) | 98 | TDD | ![](https://img.shields.io/badge/97-Passed-success) ![](https://img.shields.io/badge/1-Skipped-yellow) |
| **TOTAL** | **192** | **BDD+TDD** | ![](https://img.shields.io/badge/191-Passed-success) |

</div>

### ▶️ Ejecutar Pruebas

```bash
# 🧪 Todas las pruebas
docker exec ferreteria_backend php artisan test

# 🔲 Solo pruebas Feature (integración/endpoints)
docker exec ferreteria_backend php artisan test tests/Feature

# ⚪ Solo pruebas Unit (lógica de negocio)
docker exec ferreteria_backend php artisan test tests/Unit

# 📊 Con formato detallado
docker exec ferreteria_backend php artisan test --testdox

# 🎯 Prueba específica
docker exec ferreteria_backend php artisan test --filter=ProductoServiceTest
```

### 📊 Cobertura por Módulo

<details>
<summary><b>🔍 Ver cobertura detallada de pruebas</b></summary>

| Módulo | Feature Tests | Unit Tests | Total | Cobertura |
|:-------|:-------------:|:----------:|:-----:|:---------:|
| 🔐 **Autenticación** | 8 | 7 | 15+ | ✅ Alta |
| 🛒 **Productos** | 15 | 16 | 31+ | ✅ Alta |
| 📦 **Inventario** | 12 | 16 | 28+ | ✅ Alta |
| 💰 **Ventas** | 18 | 18 | 36+ | ✅ Alta |
| 🛍️ **Compras** | 15 | 12 | 27+ | ✅ Alta |
| 👥 **Clientes** | 14 | 19 | 33+ | ✅ Alta |
| 📊 **Reportes** | 10 | 8 | 18+ | ✅ Media |
| 💵 **Caja** | 2 | 2 | 4+ | ✅ Básica |

**Metodologías Aplicadas:**
- ✅ BDD (Behavior Driven Development) - Feature Tests
- ✅ TDD (Test Driven Development) - Unit Tests
- ✅ Arrange-Act-Assert (AAA Pattern)
- ✅ Given-When-Then (Gherkin Style)

</details>

> 📖 Ver resultados completos en [`docs/RESULTADOS-PRUEBAS.md`](docs/RESULTADOS-PRUEBAS.md)  
> 📋 Ver comandos de testing en [`Comandos_Pruebas.md`](Comandos_Pruebas.md)

---

## 🔧 Comandos Docker Útiles

### 🐳 Gestión de Contenedores

```bash
# Ver estado de todos los contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend
docker-compose logs -f postgres_ferreteria
docker-compose logs -f frontend

# Reiniciar un servicio específico
docker-compose restart backend

# Detener todos los servicios (mantiene datos)
docker-compose stop

# Detener y eliminar contenedores (mantiene volúmenes)
docker-compose down

# ⚠️ PELIGRO: Eliminar TODO incluyendo volúmenes (¡Pierdes datos!)
docker-compose down -v
```

### 🔌 Acceso a Contenedores

```bash
# Acceder al contenedor del backend (Laravel)
docker exec -it ferreteria_backend sh

# Acceder al contenedor de PostgreSQL
docker exec -it ferreteria_postgres bash

# Acceder a la consola de PostgreSQL (psql)
docker exec -it ferreteria_postgres psql -U postgres -d ferreteria_frenad
```

### 🔍 Inspección y Debug

```bash
# Ver variables de entorno del backend
docker exec ferreteria_backend env

# Ver procesos corriendo en un contenedor
docker top ferreteria_backend

# Inspeccionar configuración de un contenedor
docker inspect ferreteria_backend

# Ver uso de recursos
docker stats

# Ver espacio usado por Docker
docker system df
```

### 🔄 Reconstrucción

```bash
# Reconstruir solo una imagen específica
docker-compose build backend

# Reconstruir todas las imágenes sin caché
docker-compose build --no-cache

# Forzar recreación de contenedores
docker-compose up -d --force-recreate
```

### 🐘 pgAdmin (Opcional)

Si quieres usar pgAdmin para gestionar la base de datos visualmente:

```bash
# Levantar pgAdmin (perfil dev)
docker-compose --profile dev up -d pgadmin

# Acceder en: http://localhost:5050
# Email: admin@frenad.local
# Password: admin123
```

---

## 📁 Estructura del Proyecto

```
Ferreteria_Frenad/
│
├── 📂 backend/                     # 🔧 API Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/        # Controladores por módulo
│   │   │   ├── Requests/           # Validación de requests
│   │   │   └── Resources/          # Transformación de responses
│   │   ├── Models/                 # Modelos Eloquent
│   │   └── Modules/                # Lógica de negocio modular
│   │       ├── Auth/               # Autenticación y autorización
│   │       ├── Product/            # Gestión de productos
│   │       ├── Inventory/          # Control de inventario
│   │       ├── Sale/               # Módulo de ventas
│   │       ├── Purchase/           # Módulo de compras
│   │       ├── Customer/           # Gestión de clientes
│   │       ├── Report/             # Generación de reportes
│   │       └── CashRegister/       # Arqueo de caja
│   ├── config/                     # Configuraciones
│   ├── database/
│   │   ├── factories/              # Factories para testing
│   │   └── seeders/                # Seeders de Laravel
│   ├── routes/
│   │   └── api.php                 # ⚡ Definición de rutas API
│   ├── tests/
│   │   ├── Feature/                # 🔲 Tests de integración (94 tests)
│   │   │   ├── Auth/
│   │   │   ├── Product/
│   │   │   ├── Inventory/
│   │   │   ├── Sale/
│   │   │   ├── Purchase/
│   │   │   ├── Customer/
│   │   │   ├── Report/
│   │   │   └── CashRegister/
│   │   └── Unit/                   # ⚪ Tests unitarios (98 tests)
│   │       ├── Services/
│   │       └── Helpers/
│   ├── Dockerfile                  # 🐳 Imagen Docker del backend
│   └── composer.json               # 📦 Dependencias PHP
│
├── 📂 frontend/                    # 🌐 Interfaz de Usuario
│   ├── assets/                     # Recursos estáticos
│   ├── components/                 # Componentes reutilizables
│   ├── css/                        # Estilos CSS
│   ├── js/                         # JavaScript modules
│   │   ├── api/                    # Cliente API
│   │   ├── auth/                   # Lógica de autenticación
│   │   ├── pos/                    # Punto de venta
│   │   ├── inventory/              # Gestión de inventario
│   │   └── reports/                # Visualización de reportes
│   ├── *.html                      # Páginas del sistema
│   ├── Dockerfile                  # 🐳 Imagen Docker del frontend
│   └── nginx.conf                  # ⚙️ Configuración Nginx
│
├── 📂 database/                    # 🗄️ Scripts SQL
│   ├── 00_extensions/              # Extensiones PostgreSQL
│   │   └── 01_uuid_pgcrypto.sql
│   ├── 01_tables/                  # 📋 Definición de tablas (25+ archivos)
│   │   ├── 01_usuarios.sql
│   │   ├── 02_roles.sql
│   │   ├── 03_productos.sql
│   │   ├── 04_categorias.sql
│   │   ├── 05_almacenes.sql
│   │   ├── 06_inventario.sql
│   │   ├── 07_movimientos.sql
│   │   ├── 08_ventas.sql
│   │   ├── 09_detalle_ventas.sql
│   │   ├── 10_compras.sql
│   │   ├── 11_clientes.sql
│   │   ├── 12_creditos.sql
│   │   ├── 13_caja.sql
│   │   └── ...
│   ├── 02_indixes/                 # 📊 Índices para optimización
│   ├── 03_functions/               # ⚙️ Funciones SQL reutilizables
│   ├── 04_triggers/                # 🔔 Triggers para auditoría
│   ├── 05_views/                   # 👁️ Vistas para reportes
│   ├── 06_seeders/                 # 🌱 Datos iniciales
│   ├── 07_foreing_keys/            # 🔗 Relaciones entre tablas
│   ├── migrate.sh                  # ⚡ Script principal de migración
│   ├── init-volume.sh              # 🔧 Inicialización de volumen
│   ├── rollback.sh                 # ↩️ Rollback de migraciones
│   └── backups/                    # 💾 Respaldos de la BD
│
├── 📂 docs/                        # 📖 Documentación completa
│   ├── 01-git/                     # Control de versiones
│   │   ├── GIT_BEST_PRACTICES.md
│   │   └── BRANCHES.md
│   ├── 02-arquitectura/            # Arquitectura del sistema
│   ├── 03-api/                     # Documentación API
│   ├── 04-database/                # Base de datos
│   │   ├── 00-introduccion.md
│   │   ├── 01-arquitectura.md
│   │   ├── importante.md           # 📊 ERD y normalización
│   │   └── 02-modulos/             # Documentación por módulo
│   ├── 05-testing/                 # 🧪 Metodología BDD/TDD
│   │   ├── 00-workflow-maestro.md
│   │   ├── 01-introduccion.md
│   │   ├── 02-bdd-guide.md
│   │   ├── 03-tdd-guide.md
│   │   └── PLAN-DE-PRUEBAS-SOFTWARE.md
│   ├── 06-features/                # Especificaciones BDD
│   │   └── *.feature.md
│   └── RESULTADOS-PRUEBAS.md       # ✅ Resultados de pruebas
│
├── 📄 docker-compose.yml           # 🐳 Orquestación de servicios
├── 📄 .env.example                 # 🔧 Variables de entorno (plantilla)
├── 📄 start.sh                     # 🚀 Instalación completa automática
├── 📄 start-docker.sh              # 🔄 Reinicio rápido de contenedores
├── 📄 Comandos_Pruebas.md          # 📋 Comandos de testing
├── 📄 README.md                    # 📖 Este archivo
└── 📄 .gitignore                   # �� Archivos ignorados por Git
```

---

## 📖 Documentación

### 📚 Documentación por Categoría

| Categoría | Ubicación | Descripción |
|:----------|:----------|:------------|
| 🔀 **Git & Workflow** | [`docs/01-git/`](docs/01-git/) | Control de versiones y estrategia de ramas |
| 🏗️ **Arquitectura** | [`docs/02-arquitectura/`](docs/02-arquitectura/) | Diseño del sistema y patrones |
| 🔌 **API REST** | [`docs/03-api/`](docs/03-api/) | Endpoints y especificaciones |
| 🗄️ **Base de Datos** | [`docs/04-database/`](docs/04-database/) | ERD, tablas, funciones, triggers |
| 🧪 **Testing BDD/TDD** | [`docs/05-testing/`](docs/05-testing/) | Metodología de pruebas |
| 🚀 **Deployment** | [`docs/05-deployment/`](docs/05-deployment/) | Guías de despliegue |
| 📋 **Features** | [`docs/06-features/`](docs/06-features/) | Especificaciones en Gherkin |
| ✅ **Resultados** | [`docs/RESULTADOS-PRUEBAS.md`](docs/RESULTADOS-PRUEBAS.md) | Reporte de pruebas |

### 🔍 Documentación de Base de Datos

La documentación exhaustiva de la base de datos está en [`docs/04-database/`](docs/04-database/):

- **[00-introduccion.md](docs/04-database/00-introduccion.md)** - Visión general del sistema
- **[01-arquitectura.md](docs/04-database/01-arquitectura.md)** - Arquitectura de la BD
- **[importante.md](docs/04-database/importante.md)** - 📊 ERD, Normalización, Tablas
- **[07-instalacion.md](docs/04-database/07-instalacion.md)** - Guía de instalación
- **[08-migracion.md](docs/04-database/08-migracion.md)** - Proceso de migraciones
- **[10-testing.md](docs/04-database/10-testing.md)** - Testing de BD

### 📖 API Interactiva

La API está documentada con **Scribe (OpenAPI 3.0)**:

| Formato | URL | Descripción |
|:--------|:----|:------------|
| 📄 **HTML Interactivo** | http://localhost:8000/docs | Explorador interactivo tipo Swagger |
| 📬 **Postman** | http://localhost:8000/docs.postman | Colección para Postman |
| 📋 **OpenAPI JSON** | http://localhost:8000/docs.openapi | Especificación OpenAPI 3.0 |

```bash
# Regenerar documentación de API
docker exec ferreteria_backend php artisan scribe:generate
```

---

## ❓ Solución de Problemas

### ⚠️ El backend no responde

```bash
# Paso 1: Ver logs del backend
docker-compose logs backend

# Paso 2: Verificar que el contenedor esté corriendo
docker-compose ps

# Paso 3: Reiniciar el backend
docker-compose restart backend

# Paso 4: Verificar errores de Laravel
docker exec ferreteria_backend cat storage/logs/laravel.log | tail -n 50
```

### ⚠️ Error de conexión a PostgreSQL

```bash
# Paso 1: Verificar que PostgreSQL esté corriendo
docker exec ferreteria_postgres pg_isready

# Paso 2: Ver logs de PostgreSQL
docker-compose logs postgres_ferreteria

# Paso 3: Verificar conexión desde el backend
docker exec ferreteria_backend php artisan db:show

# Paso 4: Reiniciar PostgreSQL
docker-compose restart postgres_ferreteria
```

### ⚠️ Las migraciones fallan

```bash
# Paso 1: Verificar que PostgreSQL esté listo
docker exec ferreteria_postgres pg_isready

# Paso 2: Verificar que la base de datos existe
docker exec ferreteria_postgres psql -U postgres -lqt | cut -d \| -f 1 | grep ferreteria_frenad

# Paso 3: Ejecutar migraciones manualmente
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024
exit

# Paso 4: Si persiste, eliminar y recrear la BD
docker exec ferreteria_postgres psql -U postgres -c "DROP DATABASE IF EXISTS ferreteria_frenad;"
docker exec ferreteria_postgres psql -U postgres -c "CREATE DATABASE ferreteria_frenad;"
# Luego volver a ejecutar migrate.sh
```

### ⚠️ Error: "Port already in use"

```bash
# Ver qué está usando el puerto
sudo lsof -i :8000   # Backend
sudo lsof -i :8080   # Frontend
sudo lsof -i :5432   # PostgreSQL

# Matar el proceso
sudo kill -9 <PID>

# O cambiar el puerto en .env
# BACKEND_PORT=8001
# FRONTEND_PORT=8081
```

### ⚠️ Composer install falla

```bash
# Paso 1: Limpiar caché de Composer
docker exec ferreteria_backend composer clear-cache

# Paso 2: Reinstalar dependencias
docker exec ferreteria_backend rm -rf vendor
docker exec ferreteria_backend composer install --no-interaction

# Paso 3: Si persiste, verificar permisos
docker exec ferreteria_backend chown -R www-data:www-data /var/www/html
```

### ⚠️ Reinstalar desde cero

Si nada funciona, elimina todo y empieza de nuevo:

```bash
# ⚠️ CUIDADO: Esto eliminará TODOS los datos

# Paso 1: Detener y eliminar todo
docker-compose down -v

# Paso 2: Eliminar imágenes
docker rmi ferreteria_frenat-backend ferreteria_frenat-frontend

# Paso 3: Limpiar volúmenes huérfanos
docker volume prune

# Paso 4: Reinstalar
./start.sh
```

---

## 👥 Equipo de Desarrollo

<div align="center">

| Rol | Nombre | Contacto |
|:----|:-------|:---------|
| 👨‍💻 **Desarrollador Full Stack** | Brayan CF | [@Brayan-CF](https://github.com/Brayan-CF) |
| 🏫 **Institución** | UNIFRANZ | El Alto, La Paz - Bolivia |
| 📚 **Materia** | Proyecto Integrador II | - |
| 🎓 **Semestre** | Sexto Semestre | Gestión 2025 |
| 👨‍🏫 **Docente** | [Nombre del Docente] | - |

</div>

### 🎯 Objetivos del Proyecto

- ✅ Aplicar arquitectura modular y patrones de diseño
- ✅ Implementar metodologías BDD/TDD para testing
- ✅ Usar Docker para portabilidad y despliegue
- ✅ Crear API REST documentada con OpenAPI
- ✅ Gestionar base de datos con PostgreSQL avanzado
- ✅ Seguir mejores prácticas de Git y versionado

---


## 🤝 Contribuciones

Este es un proyecto académico. Si encuentras algún error o tienes sugerencias:

1. Abre un **Issue** describiendo el problema
2. Fork el repositorio
3. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
4. Commit tus cambios (`git commit -m 'Add: AmazingFeature'`)
5. Push a la rama (`git push origin feature/AmazingFeature`)
6. Abre un **Pull Request**

---

## 📞 Soporte

Si tienes problemas con la instalación o uso del sistema:

1. 📖 Consulta la [Documentación](#-documentación)
2. ❓ Revisa [Solución de Problemas](#-solución-de-problemas)
3. 🐛 Abre un [Issue en GitHub](https://github.com/Brayan-CF/Ferreteria_Frenad/issues)
4. 💬 Contacta al equipo de desarrollo

---

<div align="center">

## ⭐ ¡Si este proyecto te fue útil, dale una estrella!

**🏪 Ferretería FRENAD - Sistema POS**  
*Sistema de gestión integral para ferreterías*

---

**Hecho con ❤️ en El Alto, La Paz - Bolivia 🇧🇴**

![Made with Love](https://img.shields.io/badge/Made%20with-❤️-red?style=flat-square)
![Bolivia](https://img.shields.io/badge/Made%20in-Bolivia%20🇧🇴-yellow?style=flat-square)
![UNIFRANZ](https://img.shields.io/badge/UNIFRANZ-2025-blue?style=flat-square)

[⬆️ Volver arriba](#-sistema-pos---ferretería-frenad)

</div>
