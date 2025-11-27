#!/bin/bash

# Script guiado para hacer commits profesionales
# Autor: Asistente GitHub Copilot
# Fecha: 26 de noviembre de 2025

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║           GUÍA DE COMMITS PROFESIONALES                    ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Función para confirmar
confirm() {
    read -p "$(echo -e ${YELLOW}¿Ejecutar este commit? [s/N]: ${NC})" response
    case "$response" in
        [sS][iI]|[sS]) 
            return 0
            ;;
        *)
            echo -e "${RED}❌ Commit cancelado${NC}"
            return 1
            ;;
    esac
}

# COMMIT 1: Infraestructura Docker
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}COMMIT 1: Infraestructura Docker${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Archivos:"
echo "  - docker-compose.yml (modificado)"
echo "  - backend/Dockerfile (nuevo)"
echo "  - backend/php.ini (nuevo)"
echo "  - frontend/Dockerfile (nuevo)"
echo "  - frontend/nginx.conf (nuevo)"
echo ""
echo "Mensaje:"
echo "feat(docker): Configurar infraestructura completa con Docker Compose"
echo ""
echo "Descripción:"
echo "- PostgreSQL 15 Alpine con persistencia de datos"
echo "- Backend PHP 8.2 FPM con extensiones PDO PostgreSQL"
echo "- Frontend Nginx Alpine para archivos estáticos"
echo "- Red personalizada ferreteria_network (172.20.0.0/16)"
echo "- Volúmenes para base de datos y scripts de migración"
echo ""

if confirm; then
    git add docker-compose.yml backend/Dockerfile backend/php.ini frontend/Dockerfile frontend/nginx.conf
    git commit -m "feat(docker): Configurar infraestructura completa con Docker Compose

- PostgreSQL 15 Alpine con persistencia de datos
- Backend PHP 8.2 FPM con extensiones PDO PostgreSQL
- Frontend Nginx Alpine para archivos estáticos
- Red personalizada ferreteria_network (172.20.0.0/16)
- Volúmenes para base de datos y scripts de migración"
    echo -e "${GREEN}✅ Commit 1 completado${NC}\n"
else
    echo -e "${YELLOW}⏭️  Saltando commit 1${NC}\n"
fi

# COMMIT 2: Script de automatización
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}COMMIT 2: Script de automatización${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Archivos:"
echo "  - start.sh (nuevo)"
echo "  - comandos.md (nuevo)"
echo ""
echo "Mensaje:"
echo "feat(automation): Agregar script de despliegue automatizado"
echo ""
echo "Descripción:"
echo "- start.sh: Script bash con verificación de requisitos"
echo "- Construcción y inicio automático de contenedores"
echo "- Ejecución de migraciones de base de datos"
echo "- Salida con colores y manejo de errores"
echo "- comandos.md: Documentación de comandos útiles"
echo ""

if confirm; then
    git add start.sh comandos.md
    git commit -m "feat(automation): Agregar script de despliegue automatizado

- start.sh: Script bash con verificación de requisitos
- Construcción y inicio automático de contenedores
- Ejecución de migraciones de base de datos
- Salida con colores y manejo de errores
- comandos.md: Documentación de comandos útiles"
    echo -e "${GREEN}✅ Commit 2 completado${NC}\n"
else
    echo -e "${YELLOW}⏭️  Saltando commit 2${NC}\n"
fi

# COMMIT 3: Documentación
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}COMMIT 3: Documentación del proyecto${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Archivos:"
echo "  - README.md (modificado)"
echo "  - RESUMEN.md (nuevo)"
echo "  - SOLUCIONES.md (nuevo)"
echo "  - CHECKLIST.md (nuevo)"
echo ""
echo "Mensaje:"
echo "docs: Actualizar documentación completa del proyecto"
echo ""
echo "Descripción:"
echo "- README.md: Guía completa de instalación y uso"
echo "- RESUMEN.md: Resumen ejecutivo para presentación académica"
echo "- SOLUCIONES.md: Documentación de problemas resueltos"
echo "- CHECKLIST.md: Lista de verificación pre-despliegue"
echo ""

if confirm; then
    git add README.md RESUMEN.md SOLUCIONES.md CHECKLIST.md
    git commit -m "docs: Actualizar documentación completa del proyecto

- README.md: Guía completa de instalación y uso
- RESUMEN.md: Resumen ejecutivo para presentación académica
- SOLUCIONES.md: Documentación de problemas resueltos
- CHECKLIST.md: Lista de verificación pre-despliegue"
    echo -e "${GREEN}✅ Commit 3 completado${NC}\n"
else
    echo -e "${YELLOW}⏭️  Saltando commit 3${NC}\n"
fi

# COMMIT 4: Configuración
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}COMMIT 4: Configuración de entorno${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Archivos:"
echo "  - .gitignore (modificado)"
echo "  - .env.example (nuevo)"
echo "  - database/backups/ (nuevo directorio)"
echo ""
echo "Mensaje:"
echo "chore: Configurar archivos de entorno y .gitignore"
echo ""
echo "Descripción:"
echo "- .gitignore: Excluir archivos sensibles y generados"
echo "- .env.example: Plantilla de variables de entorno"
echo "- database/backups/: Directorio para respaldos de BD"
echo ""

if confirm; then
    # Crear .gitkeep en backups si no existe
    touch database/backups/.gitkeep
    git add .gitignore .env.example database/backups/
    git commit -m "chore: Configurar archivos de entorno y .gitignore

- .gitignore: Excluir archivos sensibles y generados
- .env.example: Plantilla de variables de entorno
- database/backups/: Directorio para respaldos de BD"
    echo -e "${GREEN}✅ Commit 4 completado${NC}\n"
else
    echo -e "${YELLOW}⏭️  Saltando commit 4${NC}\n"
fi

# Resumen final
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                    RESUMEN FINAL                           ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
git log --oneline -5
echo ""
echo -e "${GREEN}✅ Proceso de commits completado${NC}"
echo -e "${YELLOW}📝 Para hacer push al repositorio remoto:${NC}"
echo -e "   git push origin master"
echo ""
echo -e "${YELLOW}📚 Comandos útiles:${NC}"
echo -e "   git log --oneline          # Ver historial resumido"
echo -e "   git log --graph --oneline  # Ver historial con gráfico"
echo -e "   git show                   # Ver último commit en detalle"
echo -e "   git diff HEAD~1            # Ver cambios del último commit"
echo ""
