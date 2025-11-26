#!/bin/bash

# ============================================================================
# Script de Inicialización Rápida - Ferretería Frenad
# Ejecuta todos los pasos necesarios para levantar el proyecto
# ============================================================================

set -e  # Detener si hay error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================================
# FUNCIONES
# ============================================================================

print_header() {
    echo ""
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# ============================================================================
# INICIO
# ============================================================================

print_header "FERRETERÍA FRENAD - DEPLOYMENT AUTOMÁTICO"

# ============================================================================
# 1. VERIFICAR REQUISITOS
# ============================================================================

print_info "Verificando requisitos..."

# Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker no está instalado. Instalar desde: https://docs.docker.com/get-docker/"
    exit 1
fi
print_success "Docker instalado: $(docker --version)"

# Docker Compose
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose no está instalado"
    exit 1
fi
print_success "Docker Compose instalado: $(docker-compose --version)"

# ============================================================================
# 2. VERIFICAR ESTRUCTURA
# ============================================================================

print_info "Verificando estructura del proyecto..."

if [ ! -f ".env" ]; then
    print_warning ".env no existe, copiando desde .env.example"
    cp .env.example .env
    print_success ".env creado"
else
    print_success ".env ya existe"
fi

if [ ! -f "docker-compose.yml" ]; then
    print_error "docker-compose.yml no encontrado"
    exit 1
fi
print_success "docker-compose.yml encontrado"

if [ ! -f "backend/Dockerfile" ]; then
    print_error "backend/Dockerfile no encontrado"
    exit 1
fi
print_success "backend/Dockerfile encontrado"

if [ ! -f "frontend/Dockerfile" ]; then
    print_error "frontend/Dockerfile no encontrado"
    exit 1
fi
print_success "frontend/Dockerfile encontrado"

# ============================================================================
# 3. CREAR DIRECTORIOS NECESARIOS
# ============================================================================

print_info "Creando directorios necesarios..."

mkdir -p database/backups
mkdir -p database/pgadmin_data

print_success "Directorios creados"

# ============================================================================
# 4. DETENER CONTENEDORES ANTERIORES
# ============================================================================

print_info "Deteniendo contenedores previos..."

docker-compose down 2>/dev/null || true

print_success "Contenedores previos detenidos"

# ============================================================================
# 5. CONSTRUIR Y LEVANTAR CONTENEDORES
# ============================================================================

print_header "CONSTRUYENDO Y LEVANTANDO CONTENEDORES"

print_info "Esto puede tardar varios minutos en la primera ejecución..."

docker-compose up -d --build

print_success "Contenedores levantados"

# ============================================================================
# 6. ESPERAR A QUE POSTGRESQL ESTÉ LISTO
# ============================================================================

print_info "Esperando a que PostgreSQL esté listo..."

max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if docker exec ferreteria_postgres pg_isready -U postgres -d ferreteria_frenad &> /dev/null; then
        print_success "PostgreSQL está listo"
        break
    fi
    
    attempt=$((attempt + 1))
    echo -n "."
    sleep 2
    
    if [ $attempt -eq $max_attempts ]; then
        print_error "PostgreSQL no respondió después de 60 segundos"
        print_info "Ver logs: docker-compose logs postgres_ferreteria"
        exit 1
    fi
done

# ============================================================================
# 7. EJECUTAR MIGRACIONES
# ============================================================================

print_header "EJECUTANDO MIGRACIONES DE BASE DE DATOS"

print_info "Ejecutando migrate.sh dentro del contenedor..."

# Copiar credenciales desde .env
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

docker exec ferreteria_postgres bash -c "
    cd /database && \
    chmod +x migrate.sh && \
    ./migrate.sh ferreteria_frenad postgres localhost 5432 ${DB_PASSWORD}
"

if [ $? -eq 0 ]; then
    print_success "Migraciones ejecutadas correctamente"
else
    print_error "Error al ejecutar migraciones"
    print_info "Puedes ejecutarlas manualmente: docker exec -it ferreteria_postgres bash"
fi

# ============================================================================
# 8. VERIFICAR TABLAS CREADAS
# ============================================================================

print_info "Verificando tablas creadas..."

table_count=$(docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -tAc "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';")

if [ "$table_count" -gt 20 ]; then
    print_success "Base de datos inicializada correctamente ($table_count tablas)"
else
    print_warning "Se esperaban más tablas (encontradas: $table_count)"
fi

# ============================================================================
# 9. VERIFICAR SERVICIOS
# ============================================================================

print_header "VERIFICANDO SERVICIOS"

sleep 5

# PostgreSQL
if docker exec ferreteria_postgres pg_isready -U postgres &> /dev/null; then
    print_success "PostgreSQL: OK (puerto 5432)"
else
    print_error "PostgreSQL: FALLO"
fi

# Backend
if curl -s http://localhost:8000 &> /dev/null; then
    print_success "Backend API: OK (http://localhost:8000)"
else
    print_warning "Backend API: No responde aún (puede tardar unos segundos más)"
fi

# Frontend
if curl -s http://localhost:8080 &> /dev/null; then
    print_success "Frontend: OK (http://localhost:8080)"
else
    print_warning "Frontend: No responde aún"
fi

# ============================================================================
# 10. MOSTRAR ESTADO
# ============================================================================

print_header "ESTADO DE CONTENEDORES"

docker-compose ps

# ============================================================================
# 11. INFORMACIÓN FINAL
# ============================================================================

print_header "✅ DEPLOYMENT COMPLETADO"

echo ""
echo -e "${GREEN}📍 Servicios disponibles:${NC}"
echo -e "  ${BLUE}•${NC} PostgreSQL:  localhost:5432"
echo -e "  ${BLUE}•${NC} Backend API: ${GREEN}http://localhost:8000${NC}"
echo -e "  ${BLUE}•${NC} Frontend:    ${GREEN}http://localhost:8080${NC}"
echo ""
echo -e "${YELLOW}📖 Documentación:${NC}"
echo -e "  ${BLUE}•${NC} Base de datos: docs/04-database/"
echo -e "  ${BLUE}•${NC} Comandos útiles: comandos.md"
echo ""
echo -e "${YELLOW}🔧 Comandos útiles:${NC}"
echo -e "  ${BLUE}•${NC} Ver logs:       ${GREEN}docker-compose logs -f${NC}"
echo -e "  ${BLUE}•${NC} Detener:        ${GREEN}docker-compose stop${NC}"
echo -e "  ${BLUE}•${NC} Reiniciar:      ${GREEN}docker-compose restart${NC}"
echo -e "  ${BLUE}•${NC} Eliminar todo:  ${GREEN}docker-compose down -v${NC}"
echo ""
echo -e "${YELLOW}🐘 Acceso a pgAdmin (opcional):${NC}"
echo -e "  ${BLUE}•${NC} Levantar:       ${GREEN}docker-compose --profile dev up -d pgadmin${NC}"
echo -e "  ${BLUE}•${NC} URL:            ${GREEN}http://localhost:5050${NC}"
echo -e "  ${BLUE}•${NC} Email:          admin@frenad.local"
echo -e "  ${BLUE}•${NC} Password:       admin123"
echo ""
echo -e "${GREEN}============================================${NC}"
echo ""
