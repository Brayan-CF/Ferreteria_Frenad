# 🏪 Sistema POS - Ferretería FRENAD

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.50-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
  <img src="https://img.shields.io/badge/PostgreSQL-15-316192?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL"/>
  <img src="https://img.shields.io/badge/Docker-24+-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker"/>
</p>

<p align="center">
  <strong>Sistema completo de Punto de Venta y Gestión Administrativa</strong><br>
  Desarrollado para ferreterías en El Alto, La Paz - Bolivia 🇧🇴
</p>

<p align="center">
  <a href="#características">Características</a> •
  <a href="#instalación-rápida">Instalación</a> •
  <a href="#comandos-manuales-opcional">Comandos</a> •
  <a href="#pruebas">Pruebas</a> •
  <a href="#documentación">Documentación</a>
</p>

---

## ✨ Características

| Módulo | Descripción | Estado |
|--------|-------------|--------|
| 🛒 **Punto de Venta** | POS completo con soporte para ventas al contado y crédito | ✅ |
| 📦 **Inventario** | Control multi-almacén con kardex automatizado | ✅ |
| 🛍️ **Compras** | Gestión de órdenes y recepciones de mercadería | ✅ |
| 👥 **Clientes** | CRM con límites de crédito y estados de cuenta | ✅ |
| 💰 **Caja** | Arqueo diario de ingresos y egresos | ✅ |
| 📊 **Reportes** | Ventas, compras, inventario y financiero | ✅ |
| 🔐 **Seguridad** | Autenticación JWT con roles y permisos | ✅ |
| 📝 **Auditoría** | Logs completos de operaciones críticas | ✅ |

---

## 🛠️ Stack Tecnológico

| Capa | Tecnología |
|------|------------|
| 🌐 Frontend | HTML5 + CSS3 + JavaScript + Nginx |
| 🔧 Backend API | Laravel 10.50 + PHP 8.2 |
| 🐘 Base de Datos | PostgreSQL 15.8 |
| 🐳 Containerización | Docker + Docker Compose |
| 📖 API Docs | Scribe (OpenAPI 3.0) |
| 🧪 Testing | PHPUnit (192 tests) |

---

## 📋 Requisitos Previos

| Requisito | Versión Mínima | Verificar |
|-----------|----------------|-----------|
| 🐳 Docker | 20.10+ | \`docker --version\` |
| 🐳 Docker Compose | 2.0+ | \`docker-compose --version\` |
| 📂 Git | 2.0+ | \`git --version\` |
| 💾 RAM | 4GB mínimo | - |
| 💽 Disco | 10GB libres | - |

---

## 🚀 Instalación Rápida

### Paso 1: Clonar el Repositorio

\`\`\`bash
git clone https://github.com/Brayan-CF/Ferreteria_Frenad.git
cd Ferreteria_Frenad
\`\`\`

### Paso 2: Ejecutar Script de Instalación

\`\`\`bash
# Dar permisos de ejecución
chmod +x start.sh start-docker.sh database/*.sh

# Ejecutar instalación completa
./start.sh
\`\`\`

> ⏱️ **Tiempo estimado:** 5-10 minutos en la primera ejecución

---

## ✅ ¿Qué hace el script \`start.sh\`?

El script automatiza **TODO** el proceso de instalación:

| Paso | Descripción |
|------|-------------|
| 1️⃣ | Verifica requisitos (Docker, Docker Compose) |
| 2️⃣ | Crea archivos \`.env\` y \`backend/.env\` si no existen |
| 3️⃣ | Crea directorios necesarios |
| 4️⃣ | Construye las imágenes Docker |
| 5️⃣ | Levanta todos los contenedores |
| 6️⃣ | Espera a que PostgreSQL esté listo |
| 7️⃣ | **Instala dependencias de Composer** |
| 8️⃣ | Ejecuta migraciones de base de datos |
| 9️⃣ | Genera seeders con datos iniciales |
| 🔟 | Verifica que todos los servicios estén activos |

**Al finalizar tendrás el sistema completamente funcional** 🎉

---

## 🔄 Scripts Disponibles

| Script | Descripción | Cuándo Usar |
|--------|-------------|-------------|
| \`./start.sh\` | **Instalación completa** desde cero | Primera vez o reinstalación |
| \`./start-docker.sh\` | Levantar contenedores existentes | Después de apagar la máquina |
| \`./database/migrate.sh\` | Ejecutar migraciones SQL | Actualizar estructura BD |

### 📌 Orden de Ejecución

\`\`\`
┌──────────────────────────────────────────────────────────────┐
│  🆕 PRIMERA INSTALACIÓN                                      │
├──────────────────────────────────────────────────────────────┤
│  1. git clone https://github.com/Brayan-CF/Ferreteria_Frenad │
│  2. cd Ferreteria_Frenad                                     │
│  3. chmod +x start.sh start-docker.sh database/*.sh          │
│  4. ./start.sh              ← Hace TODO automáticamente      │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│  🔄 REINICIAR (después de apagar la máquina)                 │
├──────────────────────────────────────────────────────────────┤
│  1. cd Ferreteria_Frenad                                     │
│  2. ./start-docker.sh       ← Solo levanta los contenedores  │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│  🗄️ ACTUALIZAR BD (si hay cambios en SQL)                   │
├──────────────────────────────────────────────────────────────┤
│  1. cd Ferreteria_Frenad/database                            │
│  2. ./migrate.sh            ← Re-ejecuta migraciones         │
└──────────────────────────────────────────────────────────────┘
\`\`\`

---

## 🌐 URLs de Acceso

Una vez instalado, accede a los servicios:

| Servicio | URL | Descripción |
|----------|-----|-------------|
| 🖥️ **Frontend** | http://localhost:8080 | Interfaz de usuario POS |
| 🔧 **Backend API** | http://localhost:8000 | API REST Laravel |
| 📖 **Documentación API** | http://localhost:8000/docs | Swagger/OpenAPI |
| 🐘 **PostgreSQL** | localhost:5432 | Base de datos |

### 🔐 Credenciales de Prueba

\`\`\`
Email:    admin@frenad.com
Password: password
\`\`\`

---

## 🛠️ Comandos Manuales (Opcional)

Si prefieres ejecutar paso a paso o el script falla:

### 1. Configuración Inicial

\`\`\`bash
# Clonar repositorio
git clone https://github.com/Brayan-CF/Ferreteria_Frenad.git
cd Ferreteria_Frenad

# Crear archivos de configuración
cp .env.example .env
cp backend/.env.example backend/.env

# Crear directorios necesarios
mkdir -p database/backups database/pgadmin_data
\`\`\`

### 2. Construir y Levantar Contenedores

\`\`\`bash
# Construir imágenes y levantar servicios
docker-compose up -d --build

# Verificar contenedores
docker-compose ps

# Esperar ~30 segundos a que PostgreSQL inicie
sleep 30
\`\`\`

### 3. Instalar Dependencias de Laravel

\`\`\`bash
# Instalar dependencias de Composer
docker exec ferreteria_backend composer install --no-interaction --optimize-autoloader

# Generar clave de aplicación
docker exec ferreteria_backend php artisan key:generate

# Limpiar caché
docker exec ferreteria_backend php artisan config:clear
docker exec ferreteria_backend php artisan cache:clear
\`\`\`

### 4. Ejecutar Migraciones de Base de Datos

\`\`\`bash
# Desde dentro del contenedor PostgreSQL
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024
exit
\`\`\`

### 5. Verificar Instalación

\`\`\`bash
# Ver estado de contenedores
docker-compose ps

# Probar backend
curl http://localhost:8000

# Probar frontend
curl http://localhost:8080
\`\`\`

---

## 🧪 Pruebas

El proyecto incluye **192 pruebas automatizadas** (Feature + Unit):

\`\`\`bash
# Ejecutar todas las pruebas
docker exec ferreteria_backend php artisan test

# Solo pruebas Feature (integración)
docker exec ferreteria_backend php artisan test tests/Feature

# Solo pruebas Unit (unitarias)
docker exec ferreteria_backend php artisan test tests/Unit

# Prueba específica
docker exec ferreteria_backend php artisan test --filter=ProductoServiceTest
\`\`\`

### 📊 Cobertura por Módulo

| Módulo | Feature Tests | Unit Tests | Total |
|--------|--------------|------------|-------|
| Autenticación | ✅ | ✅ | 15+ |
| Productos | ✅ | ✅ | 30+ |
| Inventario | ✅ | ✅ | 25+ |
| Ventas | ✅ | ✅ | 35+ |
| Compras | ✅ | ✅ | 25+ |
| Clientes | ✅ | ✅ | 30+ |
| Reportes | ✅ | ✅ | 20+ |
| Caja | ✅ | ✅ | 12+ |

> Ver resultados detallados en [\`docs/RESULTADOS-PRUEBAS.md\`](docs/RESULTADOS-PRUEBAS.md)

---

## 🔧 Comandos Docker Útiles

\`\`\`bash
# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend

# Reiniciar un servicio
docker-compose restart backend

# Detener servicios (mantiene datos)
docker-compose stop

# Detener y eliminar contenedores
docker-compose down

# ⚠️ Eliminar TODO incluyendo volúmenes (CUIDADO)
docker-compose down -v

# Acceder al contenedor del backend
docker exec -it ferreteria_backend sh

# Acceder a PostgreSQL
docker exec -it ferreteria_postgres psql -U postgres -d ferreteria_frenad
\`\`\`

---

## 🐘 pgAdmin (Opcional)

Para gestionar la base de datos visualmente:

\`\`\`bash
# Levantar pgAdmin
docker-compose --profile dev up -d pgadmin
\`\`\`

| Campo | Valor |
|-------|-------|
| URL | http://localhost:5050 |
| Email | admin@frenad.local |
| Password | admin123 |

---

## 📁 Estructura del Proyecto

\`\`\`
Ferreteria_Frenad/
│
├── 📂 backend/                 # API Laravel
│   ├── app/
│   │   └── Modules/            # Auth, Product, Sales, Inventory, etc.
│   ├── tests/
│   │   ├── Feature/            # Tests de endpoints (caja negra)
│   │   └── Unit/               # Tests de servicios (caja blanca)
│   ├── Dockerfile
│   └── composer.json
│
├── 📂 frontend/                # Interfaz web (Nginx)
│   ├── js/
│   ├── css/
│   ├── *.html
│   ├── Dockerfile
│   └── nginx.conf
│
├── 📂 database/                # Scripts SQL
│   ├── 00_extensions/          # Extensiones PostgreSQL
│   ├── 01_tables/              # Definición de tablas
│   ├── 02_indixes/             # Índices
│   ├── 03_functions/           # Funciones SQL
│   ├── 04_triggers/            # Triggers
│   ├── 05_views/               # Vistas
│   ├── 06_seeders/             # Datos iniciales
│   ├── 07_foreing_keys/        # Claves foráneas
│   ├── migrate.sh              # ⚡ Script de migración
│   └── init-volume.sh          # Inicializar volumen
│
├── 📂 docs/                    # Documentación completa
│   ├── 01-git/
│   ├── 02-arquitectura/
│   ├── 03-api/
│   ├── 04-database/
│   ├── 05-testing/
│   └── RESULTADOS-PRUEBAS.md
│
├── 📄 docker-compose.yml       # Orquestación de servicios
├── 📄 start.sh                 # ⚡ Instalación completa
├── 📄 start-docker.sh          # ⚡ Levantar servicios
├── 📄 .env.example             # Variables de entorno (plantilla)
├── 📄 Comandos_Pruebas.md      # Referencia de comandos de testing
└── 📄 README.md                # Este archivo
\`\`\`

---

## 📖 Documentación

| Categoría | Ruta | Descripción |
|-----------|------|-------------|
| 📊 Base de Datos | \`docs/04-database/\` | Arquitectura, módulos, ERD |
| 🧪 Testing | \`docs/05-testing/\` | Metodología BDD/TDD |
| 🚀 Deployment | \`docs/05-deployment/\` | Guías de despliegue |
| 📋 Features | \`docs/06-features/\` | Especificaciones BDD |
| ✅ Resultados | \`docs/RESULTADOS-PRUEBAS.md\` | Resultados de pruebas |

---

## ❓ Solución de Problemas

### El backend no responde

\`\`\`bash
# Ver logs del backend
docker-compose logs backend

# Reiniciar backend
docker-compose restart backend
\`\`\`

### Error de conexión a PostgreSQL

\`\`\`bash
# Verificar que PostgreSQL esté corriendo
docker exec ferreteria_postgres pg_isready

# Ver logs de PostgreSQL
docker-compose logs postgres_ferreteria
\`\`\`

### Las migraciones fallan

\`\`\`bash
# Ejecutar migraciones manualmente
docker exec -it ferreteria_postgres bash
cd /database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024
\`\`\`

### Reinstalar desde cero

\`\`\`bash
# Eliminar todo y empezar de nuevo
docker-compose down -v
./start.sh
\`\`\`

---

## 👥 Equipo de Desarrollo

| Rol | Nombre |
|-----|--------|
| 👨‍💻 Desarrollador | Brayan CF |
| 🏫 Institución | UNIFRANZ - El Alto |
| 📚 Materia | Proyecto Integrador II |
| 📅 Gestión | 2025 - Sexto Semestre |

---

## 📄 Licencia

Este proyecto fue desarrollado con fines académicos para la Universidad Franz Tamayo (UNIFRANZ).

---

<p align="center">
  <strong>🏪 Ferretería FRENAD</strong><br>
  <em>Sistema de gestión integral para ferreterías</em><br><br>
  <img src="https://img.shields.io/badge/Made%20with-❤️-red?style=flat-square" alt="Made with love"/>
  <img src="https://img.shields.io/badge/UNIFRANZ-El%20Alto-blue?style=flat-square" alt="UNIFRANZ"/>
</p>
