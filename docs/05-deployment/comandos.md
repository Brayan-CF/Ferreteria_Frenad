# ============================================================================
# COMANDOS DE DEPLOYMENT - FERRETERÍA FRENAD
# Última actualización: 2024-12-15
# ============================================================================

# ============================================================================
# FASE 1: PREPARACIÓN DEL ENTORNO
# ============================================================================

# 1. Asegúrate de estar en la raíz del proyecto
cd /home/lemon/Documentos/unifranz/sexto_semestre/ProyectoIntegrador_II/Hito5/Ferreteria_Frenat

# 2. Verificar que .env existe (ya debería existir)
ls -la .env
# Si no existe:
# cp .env.example .env

# 3. Verificar directorios necesarios (ya creados)
ls -ld database/backups database/pgadmin_data
# Si no existen:
# mkdir -p database/backups database/pgadmin_data

# 4. Verificar que Dockerfile de backend existe
ls -la backend/Dockerfile

# 5. Verificar que Dockerfile de frontend existe
ls -la frontend/Dockerfile

echo "✅ Preparación completada"

# ============================================================================
# FASE 2: LEVANTAR CONTENEDORES DOCKER
# ============================================================================

# 6. Limpiar contenedores anteriores (si existen)
docker-compose down -v
# CUIDADO: -v elimina volúmenes, solo usar en desarrollo

# 7. Construir y levantar contenedores (SIN pgAdmin)
docker-compose up -d --build

# Alternativa: Levantar CON pgAdmin (perfil dev)
# docker-compose --profile dev up -d --build

echo "⏳ Esperando a que los contenedores estén listos..."
sleep 15

# ============================================================================
# FASE 3: VERIFICACIÓN DE SERVICIOS
# ============================================================================

# 8. Ver estado de contenedores
docker-compose ps

echo ""
echo "✅ Deberías ver:"
echo "  ferreteria_postgres    Up (healthy)  0.0.0.0:5432->5432/tcp"
echo "  ferreteria_backend     Up            0.0.0.0:8000->8000/tcp"
echo "  ferreteria_frontend    Up            0.0.0.0:8080->80/tcp"
echo ""

# 9. Ver logs de PostgreSQL
docker-compose logs postgres_ferreteria

# 10. Ver logs de Backend
docker-compose logs backend

# 11. Ver logs de Frontend
docker-compose logs frontend

# ============================================================================
# FASE 4: INICIALIZAR BASE DE DATOS
# ============================================================================

echo "🗄️  Inicializando base de datos..."

# 12. Acceder al contenedor de PostgreSQL
docker exec -it ferreteria_postgres bash

# DENTRO DEL CONTENEDOR (ejecutar uno por uno):
# --------------------------------------------

# Verificar que la base de datos existe
psql -U postgres -l | grep ferreteria_frenad

# Ejecutar script de migración
cd /database
chmod +x migrate.sh
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024

# Verificar que las tablas se crearon
psql -U postgres -d ferreteria_frenad -c "\dt"

# Debería mostrar 25+ tablas

# Salir del contenedor
exit

# ============================================================================
# ALTERNATIVA: Ejecutar migrate.sh DESDE TU MÁQUINA
# ============================================================================

# Si tienes PostgreSQL client instalado en tu máquina:
cd database
./migrate.sh ferreteria_frenad postgres localhost 5432 frenad_postgres_2024

# ============================================================================
# FASE 5: VERIFICACIÓN FINAL
# ============================================================================

# 13. Verificar PostgreSQL
echo "🔍 Verificando PostgreSQL..."
docker exec ferreteria_postgres psql -U postgres -d ferreteria_frenad -c "SELECT COUNT(*) as tablas FROM information_schema.tables WHERE table_schema='public';"

# 14. Verificar Backend API
echo "🔍 Verificando Backend..."
curl http://localhost:8000
# O abrir en navegador: http://localhost:8000

# 15. Verificar Frontend
echo "🔍 Verificando Frontend..."
curl http://localhost:8080
# O abrir en navegador: http://localhost:8080

# 16. Si levantaste pgAdmin, acceder a:
# http://localhost:5050
# Email: admin@frenad.local
# Password: admin123

# ============================================================================
# COMANDOS ÚTILES POST-DEPLOYMENT
# ============================================================================

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend
docker-compose logs -f postgres_ferreteria

# Reiniciar un servicio
docker-compose restart backend

# Detener todos los servicios
docker-compose stop

# Detener y eliminar contenedores (mantiene volúmenes)
docker-compose down

# Detener y eliminar TODO (incluye volúmenes - CUIDADO)
docker-compose down -v

# Acceder a un contenedor
docker exec -it ferreteria_backend bash
docker exec -it ferreteria_postgres bash

# Ver uso de recursos
docker stats

# ============================================================================
# TROUBLESHOOTING
# ============================================================================

# Si PostgreSQL no inicia:
docker-compose logs postgres_ferreteria
# Verificar variables de entorno en .env

# Si Backend no conecta a BD:
docker-compose logs backend
# Verificar DB_HOST=postgres_ferreteria en .env

# Si Frontend no carga:
docker-compose logs frontend
# Verificar que backend esté corriendo

# Reconstruir un servicio específico:
docker-compose up -d --build backend

# Limpiar imágenes huérfanas:
docker image prune -f

# Limpiar volúmenes no usados:
docker volume prune -f

# ============================================================================
# RESPALDO Y RESTAURACIÓN
# ============================================================================

# Crear respaldo de la base de datos
docker exec ferreteria_postgres pg_dump -U postgres ferreteria_frenad > database/backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar desde respaldo
docker exec -i ferreteria_postgres psql -U postgres ferreteria_frenad < database/backups/backup_20241215_143000.sql

# ============================================================================
# DESARROLLO - COMANDOS LARAVEL (Backend)
# ============================================================================

# Ejecutar migraciones de Laravel
docker exec ferreteria_backend php artisan migrate

# Limpiar caché
docker exec ferreteria_backend php artisan cache:clear
docker exec ferreteria_backend php artisan config:clear

# Ver rutas
docker exec ferreteria_backend php artisan route:list

# Generar APP_KEY
docker exec ferreteria_backend php artisan key:generate

# Instalar dependencias Composer
docker exec ferreteria_backend composer install

# ============================================================================
# FIN DE COMANDOS
# ============================================================================

echo ""
echo "============================================"
echo "✅ DEPLOYMENT COMPLETADO"
echo "============================================"
echo ""
echo "📍 Servicios disponibles:"
echo "  - PostgreSQL:  localhost:5432"
echo "  - Backend API: http://localhost:8000"
echo "  - Frontend:    http://localhost:8080"
echo "  - pgAdmin:     http://localhost:5050 (si usaste --profile dev)"
echo ""
echo "📖 Documentación: docs/04-database/"
echo "============================================"
echo ""