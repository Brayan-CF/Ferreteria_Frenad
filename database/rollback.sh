#!/bin/bash
# filepath: database/rollback.sh
# ============================================================================
# SCRIPT DE ROLLBACK
# Elimina todo en orden inverso
# ============================================================================

set -e

DB_NAME="ferreteria_frenad"
DB_USER="postgres"
DB_HOST="localhost"
DB_PORT="5432"

RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${RED}⚠️  ADVERTENCIA: Esto eliminará TODA la base de datos${NC}"
read -p "¿Estás seguro? (escribe 'SI' para confirmar): " confirmacion

if [ "$confirmacion" != "SI" ]; then
    echo "Operación cancelada"
    exit 0
fi

echo -e "${YELLOW}🗑️  Eliminando base de datos...${NC}"

psql -U $DB_USER -h $DB_HOST -p $DB_PORT -d $DB_NAME <<EOF
-- Eliminar vistas
DROP VIEW IF EXISTS vista_utilidad_ventas CASCADE;
DROP VIEW IF EXISTS vista_kardex_producto CASCADE;
DROP VIEW IF EXISTS vista_caja_hoy CASCADE;
DROP VIEW IF EXISTS vista_clientes_deuda CASCADE;
DROP VIEW IF EXISTS vista_productos_mas_vendidos CASCADE;
DROP VIEW IF EXISTS vista_ventas_hoy CASCADE;
DROP VIEW IF EXISTS vista_productos_stock_bajo CASCADE;
DROP VIEW IF EXISTS vista_stock_actual CASCADE;

-- Eliminar funciones
DROP FUNCTION IF EXISTS productos_proximos_vencer CASCADE;
DROP FUNCTION IF EXISTS calcular_utilidad_periodo CASCADE;
DROP FUNCTION IF EXISTS obtener_deuda_cliente CASCADE;
DROP FUNCTION IF EXISTS tiene_stock_suficiente CASCADE;
DROP FUNCTION IF EXISTS obtener_stock_total_producto CASCADE;
DROP FUNCTION IF EXISTS cerrar_caja CASCADE;
DROP FUNCTION IF EXISTS ajustar_inventario CASCADE;
DROP FUNCTION IF EXISTS registrar_pago_credito CASCADE;
DROP FUNCTION IF EXISTS transferir_producto CASCADE;
DROP FUNCTION IF EXISTS procesar_compra CASCADE;
DROP FUNCTION IF EXISTS procesar_venta CASCADE;
DROP FUNCTION IF EXISTS generar_numero_devolucion CASCADE;
DROP FUNCTION IF EXISTS generar_numero_orden CASCADE;
DROP FUNCTION IF EXISTS generar_numero_compra CASCADE;
DROP FUNCTION IF EXISTS generar_numero_venta CASCADE;
DROP FUNCTION IF EXISTS validar_stock_venta CASCADE;
DROP FUNCTION IF EXISTS calcular_saldo_credito CASCADE;
DROP FUNCTION IF EXISTS actualizar_timestamp CASCADE;

-- Eliminar tablas en orden inverso
DROP TABLE IF EXISTS logs_auditoria CASCADE;
DROP TABLE IF EXISTS movimientos_caja CASCADE;
DROP TABLE IF EXISTS arqueos_caja CASCADE;
DROP TABLE IF EXISTS detalle_devoluciones_venta CASCADE;
DROP TABLE IF EXISTS devoluciones_venta CASCADE;
DROP TABLE IF EXISTS pagos_credito CASCADE;
DROP TABLE IF EXISTS creditos_clientes CASCADE;
DROP TABLE IF EXISTS detalle_ventas CASCADE;
DROP TABLE IF EXISTS ventas CASCADE;
DROP TABLE IF EXISTS clientes CASCADE;
DROP TABLE IF EXISTS detalle_compras CASCADE;
DROP TABLE IF EXISTS compras CASCADE;
DROP TABLE IF EXISTS ordenes_compra CASCADE;
DROP TABLE IF EXISTS proveedores CASCADE;
DROP TABLE IF EXISTS movimientos_inventario CASCADE;
DROP TABLE IF EXISTS inventario CASCADE;
DROP TABLE IF EXISTS almacenes CASCADE;
DROP TABLE IF EXISTS producto_unidades CASCADE;
DROP TABLE IF EXISTS productos CASCADE;
DROP TABLE IF EXISTS unidades_medida CASCADE;
DROP TABLE IF EXISTS marcas CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS usuario_roles CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

EOF

echo -e "${RED}✅ Base de datos eliminada completamente${NC}"