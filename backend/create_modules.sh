#!/bin/bash

# ============================================================================
# Script para crear estructura modular - Ferretería Frenad
# Uso: ./create_modules.sh
# ============================================================================

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Creando estructura modular${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

MODULES=(
    "Auth"
    "Product"
    "Inventory"
    "Sales"
    "Purchase"
    "Customer"
    "Reports"
    "Configuration"
)

BASE_DIR="app/Modules"

for module in "${MODULES[@]}"; do
    echo -e "${YELLOW}📦 Creando módulo: $module${NC}"
    
    # Crear directorios
    mkdir -p "$BASE_DIR/$module/Controllers"
    mkdir -p "$BASE_DIR/$module/Models"
    mkdir -p "$BASE_DIR/$module/Services"
    mkdir -p "$BASE_DIR/$module/Requests"
    mkdir -p "$BASE_DIR/$module/Resources"
    mkdir -p "$BASE_DIR/$module/Routes"
    mkdir -p "$BASE_DIR/$module/Tests"
    
    # Crear archivo de rutas
    cat > "$BASE_DIR/$module/Routes/api.php" << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

// Rutas del módulo
Route::middleware('auth:api')->group(function () {
    // Agrega tus rutas aquí
});
EOF
    
    # Crear archivo de configuración del módulo
    cat > "$BASE_DIR/$module/module.json" << EOF
{
    "name": "$module",
    "alias": "$(echo $module | tr '[:upper:]' '[:lower:]')",
    "description": "Módulo de $module",
    "keywords": [],
    "active": true,
    "order": 0
}
EOF
    
    # Crear README para cada módulo
    cat > "$BASE_DIR/$module/README.md" << EOF
# Módulo: $module

## Descripción
Módulo para gestión de $module

## Estructura
- \`Controllers/\`: Controladores del módulo
- \`Models/\`: Modelos Eloquent
- \`Services/\`: Lógica de negocio
- \`Requests/\`: Validaciones de requests
- \`Resources/\`: Transformadores de respuestas
- \`Routes/\`: Rutas API del módulo
- \`Tests/\`: Tests unitarios y de integración

## Rutas
Las rutas están disponibles en: \`/api/$(echo $module | tr '[:upper:]' '[:lower:]')\`
EOF
    
    echo -e "${GREEN}✓ Módulo $module creado${NC}"
done

echo ""
echo -e "${YELLOW}📦 Creando módulo Shared${NC}"

# Crear carpeta Shared
mkdir -p "$BASE_DIR/Shared/Traits"
mkdir -p "$BASE_DIR/Shared/Helpers"
mkdir -p "$BASE_DIR/Shared/Middleware"
mkdir -p "$BASE_DIR/Shared/Exceptions"
mkdir -p "$BASE_DIR/Shared/Services"

# Crear archivo helpers.php
cat > "$BASE_DIR/Shared/Helpers/helpers.php" << 'EOF'
<?php

/**
 * Funciones helper compartidas entre módulos
 * Ferretería Frenad
 */

if (!function_exists('format_currency')) {
    /**
     * Formatear cantidad como moneda boliviana
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    function format_currency($amount, $currency = 'Bs'): string
    {
        return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('format_bolivian_date')) {
    /**
     * Formatear fecha con zona horaria de Bolivia
     *
     * @param mixed $date
     * @return string
     */
    function format_bolivian_date($date): string
    {
        return \Carbon\Carbon::parse($date)
            ->timezone('America/La_Paz')
            ->format('d/m/Y H:i:s');
    }
}

if (!function_exists('generate_voucher_number')) {
    /**
     * Generar número de comprobante
     *
     * @param string $prefix
     * @return string
     */
    function generate_voucher_number(string $prefix = 'VTA'): string
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('clean_ruc_nit')) {
    /**
     * Limpiar RUC/NIT (quitar guiones y espacios)
     *
     * @param string $value
     * @return string
     */
    function clean_ruc_nit(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}

if (!function_exists('format_ruc_nit')) {
    /**
     * Formatear RUC/NIT boliviano
     *
     * @param string $value
     * @return string
     */
    function format_ruc_nit(string $value): string
    {
        $cleaned = clean_ruc_nit($value);
        
        if (strlen($cleaned) >= 7) {
            return substr($cleaned, 0, -1) . '-' . substr($cleaned, -1);
        }
        
        return $cleaned;
    }
}
EOF

# Crear README para Shared
cat > "$BASE_DIR/Shared/README.md" << 'EOF'
# Módulo Shared

## Descripción
Contiene código compartido entre todos los módulos.

## Contenido

### Helpers
Funciones auxiliares globales disponibles en toda la aplicación:
- `format_currency()` - Formatear moneda boliviana
- `format_bolivian_date()` - Formatear fechas con zona horaria de Bolivia
- `generate_voucher_number()` - Generar números de comprobantes
- `clean_ruc_nit()` - Limpiar RUC/NIT
- `format_ruc_nit()` - Formatear RUC/NIT

### Traits
Traits reutilizables para modelos y clases.

### Middleware
Middleware compartido entre módulos.

### Exceptions
Excepciones personalizadas.

### Services
Servicios compartidos (auditoría, notificaciones, etc.)
EOF

echo -e "${GREEN}✓ Módulo Shared creado${NC}"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Estructura modular creada exitosamente${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}📂 Estructura creada en: $BASE_DIR/${NC}"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo -e "  1. Actualizar composer.json con autoload de módulos"
echo -e "  2. Ejecutar: composer dump-autoload"
echo -e "  3. Registrar rutas de módulos en RouteServiceProvider"
echo ""
