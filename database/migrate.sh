#!/bin/bash
# filepath: database/migrate.sh
# ============================================================================
# SCRIPT DE MIGRACIÓN AUTOMÁTICA
# Ejecuta todos los archivos SQL en orden
# ============================================================================

set -e  # Detener si hay error

# Configuración
DB_NAME="ferreteria_frenad"
DB_USER="postgres"
DB_HOST="localhost"
DB_PORT="5432"

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Iniciando migración de base de datos...${NC}"
echo "Base de datos: $DB_NAME"
echo "Usuario: $DB_USER"
echo ""

# Función para ejecutar archivo SQL
execute_sql() {
    local file=$1
    echo -e "${YELLOW}📄 Ejecutando: $file${NC}"
    
    # Ejecutar en el contenedor Docker
    if docker exec -i ferreteria_postgres psql -U $DB_USER -d $DB_NAME < "$file" > /dev/null 2>&1; then
        echo -e "${GREEN}   ✅ Completado${NC}"
    else
        echo -e "${RED}   ❌ Error en: $file${NC}"
        echo -e "${YELLOW}   Detalles del error:${NC}"
        docker exec -i ferreteria_postgres psql -U $DB_USER -d $DB_NAME < "$file"
        exit 1
    fi
}

# 1. Extensiones
echo -e "\n${GREEN}═══ PASO 1: EXTENSIONES ═══${NC}"
execute_sql "00_extensions/extensions.sql"

# 2. Tablas (en orden de dependencias)
echo -e "\n${GREEN}═══ PASO 2: TABLAS ═══${NC}"
for file in 01_tables/*.sql; do
    execute_sql "$file"
done

# 3. Foreign Keys diferidas
echo -e "\n${GREEN}═══ PASO 3: FOREIGN KEYS ═══${NC}"
execute_sql "07_foreing_keys/foreign_keys.sql"

# 4. Índices
echo -e "\n${GREEN}═══ PASO 4: ÍNDICES ═══${NC}"
for file in 02_indixes/*.sql; do
    execute_sql "$file"
done

# 5. Funciones
echo -e "\n${GREEN}═══ PASO 5: FUNCIONES ═══${NC}"
for file in 03_functions/*.sql; do
    execute_sql "$file"
done

# 6. Triggers
echo -e "\n${GREEN}═══ PASO 6: TRIGGERS ═══${NC}"
for file in 04_triggers/*.sql; do
    execute_sql "$file"
done

# 7. Vistas
echo -e "\n${GREEN}═══ PASO 7: VISTAS ═══${NC}"
for file in 05_views/*.sql; do
    execute_sql "$file"
done

# 8. Seeders
echo -e "\n${GREEN}═══ PASO 8: DATOS INICIALES ═══${NC}"
for file in 06_seeders/*.sql; do
    execute_sql "$file"
done

echo -e "\n${GREEN}════════════════════════════════════${NC}"
echo -e "${GREEN}✅ MIGRACIÓN COMPLETADA EXITOSAMENTE${NC}"
echo -e "${GREEN}════════════════════════════════════${NC}"

# Verificación
echo -e "\n${YELLOW}📊 Verificando instalación...${NC}"
TABLE_COUNT=$(psql -U $DB_USER -h $DB_HOST -p $DB_PORT -d $DB_NAME -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';")
VIEW_COUNT=$(psql -U $DB_USER -h $DB_HOST -p $DB_PORT -d $DB_NAME -t -c "SELECT COUNT(*) FROM information_schema.views WHERE table_schema = 'public';")

echo "   Tablas creadas: $TABLE_COUNT (esperadas: 25)"
echo "   Vistas creadas: $VIEW_COUNT (esperadas: 8)"

echo -e "\n${GREEN}🎉 Base de datos lista para usar${NC}"