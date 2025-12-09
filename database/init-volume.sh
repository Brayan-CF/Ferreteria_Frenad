#!/bin/bash

# ============================================================================
# Script: Inicializar volumen de PostgreSQL con permisos correctos
# ============================================================================

set -e

echo "🔧 Inicializando volumen de PostgreSQL..."

# Verificar si el volumen existe
if ! docker volume ls | grep -q "ferreteria_frenat_postgres_data"; then
    echo "📦 Creando volumen postgres_data..."
    docker volume create ferreteria_frenat_postgres_data
fi

# Verificar si necesita inicialización (está vacío o tiene permisos incorrectos)
NEEDS_INIT=$(docker run --rm -v ferreteria_frenat_postgres_data:/data alpine:3.20 sh -c '
    if [ ! -d "/data/pgdata" ]; then
        echo "true"
    else
        # Verificar permisos
        if [ "$(stat -c %u /data/pgdata 2>/dev/null || echo 0)" != "70" ]; then
            echo "true"
        else
            echo "false"
        fi
    fi
')

if [ "$NEEDS_INIT" = "true" ]; then
    echo "🔨 Configurando permisos del volumen..."
    
    # Crear el directorio pgdata con el usuario correcto (postgres UID 70)
    docker run --rm \
        -v ferreteria_frenat_postgres_data:/data \
        alpine:3.20 \
        sh -c '
            # Crear directorio pgdata
            mkdir -p /data/pgdata
            
            # Cambiar propietario a UID 70 (postgres en Alpine)
            chown -R 70:70 /data
            
            # Establecer permisos correctos (700 para PostgreSQL)
            chmod -R 700 /data/pgdata
            
            echo "✅ Permisos configurados correctamente"
            ls -la /data
        '
    
    echo "✅ Volumen inicializado correctamente"
else
    echo "✅ Volumen ya está correctamente inicializado"
fi

echo "📊 Estado del volumen:"
docker run --rm -v ferreteria_frenat_postgres_data:/data alpine:3.20 ls -la /data
