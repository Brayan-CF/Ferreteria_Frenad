#!/bin/bash

# ============================================================================
# Script: Iniciar servicios Docker con verificación
# ============================================================================

set -e  # Detener en caso de error

echo "🚀 Iniciando servicios de Ferretería Frenad..."
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# ============================================================================
# PASO 1: Verificar docker-compose
# ============================================================================
echo -e "${YELLOW}📋 Verificando configuración Docker Compose...${NC}"
if ! docker-compose config > /dev/null 2>&1; then
    echo -e "${RED}❌ Error en docker-compose.yml${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Configuración válida${NC}"
echo ""

# ============================================================================
# PASO 2: Bajar contenedores existentes (si hay)
# ============================================================================
echo -e "${YELLOW}🛑 Deteniendo contenedores existentes...${NC}"
docker-compose down
echo ""

# ============================================================================
# PASO 3: Inicializar volumen de PostgreSQL con permisos correctos
# ============================================================================
echo -e "${YELLOW}💾 Inicializando volumen de PostgreSQL...${NC}"
cd database
./init-volume.sh
cd ..
echo ""

# ============================================================================
# PASO 4: Levantar PostgreSQL primero
# ============================================================================
echo -e "${YELLOW}🐘 Iniciando PostgreSQL...${NC}"
docker-compose up -d postgres_ferreteria

# Esperar a que PostgreSQL esté listo
echo -e "${YELLOW}⏳ Esperando a que PostgreSQL esté listo...${NC}"
RETRY=0
MAX_RETRIES=30
until docker exec ferreteria_postgres pg_isready -U postgres -d ferreteria_frenad > /dev/null 2>&1; do
    RETRY=$((RETRY+1))
    if [ $RETRY -eq $MAX_RETRIES ]; then
        echo -e "${RED}❌ PostgreSQL no respondió después de 30 intentos${NC}"
        docker-compose logs postgres_ferreteria
        exit 1
    fi
    echo -e "${YELLOW}   Intento $RETRY/$MAX_RETRIES...${NC}"
    sleep 2
done

echo -e "${GREEN}✅ PostgreSQL está listo y aceptando conexiones${NC}"
echo ""

# ============================================================================
# PASO 5: Verificar si la base de datos existe
# ============================================================================
echo -e "${YELLOW}🔍 Verificando base de datos...${NC}"
DB_EXISTS=$(docker exec ferreteria_postgres psql -U postgres -tAc "SELECT 1 FROM pg_database WHERE datname='ferreteria_frenad'" 2>/dev/null || echo "0")

if [ "$DB_EXISTS" = "1" ]; then
    echo -e "${GREEN}✅ Base de datos 'ferreteria_frenad' existe${NC}"
    
    # Verificar si tiene tablas
    TABLE_COUNT=$(docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -tAc "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public'" 2>/dev/null || echo "0")
    
    if [ "$TABLE_COUNT" -gt "0" ]; then
        echo -e "${GREEN}✅ Base de datos tiene $TABLE_COUNT tablas${NC}"
        echo -e "${YELLOW}💡 Si necesitas re-migrar, ejecuta: cd database && ./migrate.sh${NC}"
    else
        echo -e "${YELLOW}⚠️  Base de datos vacía. Ejecuta migraciones.${NC}"
        echo -e "${YELLOW}💡 Comando: cd database && ./migrate.sh${NC}"
    fi
else
    echo -e "${RED}❌ Base de datos no existe. Ejecuta migraciones.${NC}"
    echo -e "${YELLOW}💡 Comando: cd database && ./migrate.sh${NC}"
fi
echo ""

# ============================================================================
# PASO 6: Levantar Backend
# ============================================================================
echo -e "${YELLOW}🔧 Iniciando Backend (Laravel)...${NC}"
docker-compose up -d backend

echo -e "${YELLOW}⏳ Esperando a que Backend esté listo...${NC}"
sleep 5

if docker ps | grep -q "ferreteria_backend"; then
    echo -e "${GREEN}✅ Backend iniciado correctamente${NC}"
else
    echo -e "${RED}❌ Backend no se inició correctamente${NC}"
    docker-compose logs backend
    exit 1
fi
echo ""

# ============================================================================
# PASO 7: Levantar Frontend
# ============================================================================
echo -e "${YELLOW}🌐 Iniciando Frontend...${NC}"
docker-compose up -d frontend

sleep 3

if docker ps | grep -q "ferreteria_frontend"; then
    echo -e "${GREEN}✅ Frontend iniciado correctamente${NC}"
else
    echo -e "${RED}❌ Frontend no se inició correctamente${NC}"
    docker-compose logs frontend
    exit 1
fi
echo ""

# ============================================================================
# RESUMEN FINAL
# ============================================================================
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ TODOS LOS SERVICIOS INICIADOS${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📊 Servicios disponibles:${NC}"
echo ""
echo "   🐘 PostgreSQL:"
echo "      - Host: localhost:5432"
echo "      - Base de datos: ferreteria_frenad"
echo "      - Usuario: postgres"
echo ""
echo "   🔧 Backend (Laravel API):"
echo "      - URL: http://localhost:8000"
echo "      - API: http://localhost:8000/api"
echo ""
echo "   🌐 Frontend:"
echo "      - URL: http://localhost:8080"
echo ""
echo -e "${YELLOW}📝 Comandos útiles:${NC}"
echo ""
echo "   Ver logs:        docker-compose logs -f [servicio]"
echo "   Detener todo:    docker-compose down"
echo "   Reiniciar:       docker-compose restart [servicio]"
echo "   Entrar a bash:   docker exec -it ferreteria_backend bash"
echo ""
echo -e "${GREEN}========================================${NC}"
