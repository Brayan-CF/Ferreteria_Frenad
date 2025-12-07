# 📦 Estructura Modular del Backend

## Índice
1. [Introducción](#introducción)
2. [Arquitectura Modular](#arquitectura-modular)
3. [Estructura de Directorios](#estructura-de-directorios)
4. [Módulos del Sistema](#módulos-del-sistema)
5. [Módulo Shared](#módulo-shared)
6. [Convenciones de Módulos](#convenciones-de-módulos)
7. [Carga Automática de Rutas](#carga-automática-de-rutas)
8. [Script de Creación](#script-de-creación)

---

## Introducción

El backend de Ferretería Frenad implementa una **arquitectura modular** basada en principios de **Domain-Driven Design (DDD)** y **separación de responsabilidades**.

### **Objetivos de la Arquitectura**
- ✅ **Escalabilidad:** Agregar nuevas funcionalidades sin afectar módulos existentes
- ✅ **Mantenibilidad:** Código organizado por dominio de negocio
- ✅ **Testabilidad:** Cada módulo es una unidad independiente testeable
- ✅ **Reusabilidad:** Componentes compartidos en módulo Shared
- ✅ **Colaboración:** Equipos pueden trabajar en módulos diferentes en paralelo

---

## Arquitectura Modular

```
┌─────────────────────────────────────────────────────────────┐
│                        FRONTEND                             │
│                   (React/Vue/Angular)                       │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP/JSON
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    API GATEWAY (Nginx)                      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL BACKEND                          │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              RouteServiceProvider                     │  │
│  │         (Carga automática de módulos)                 │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌─────────┬─────────┬─────────┬─────────┬─────────────┐  │
│  │  Auth   │Product  │Inventory│ Sales   │  Shared     │  │
│  │ Module  │ Module  │ Module  │ Module  │   Module    │  │
│  ├─────────┼─────────┼─────────┼─────────┼─────────────┤  │
│  │Purchase │Customer │ Reports │Config   │   (Core)    │  │
│  │ Module  │ Module  │ Module  │ Module  │             │  │
│  └─────────┴─────────┴─────────┴─────────┴─────────────┘  │
│                          │                                  │
│                          ▼                                  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              Eloquent ORM                             │  │
│  └───────────────────────────────────────────────────────┘  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    PostgreSQL 15                            │
│              (Base de Datos Relacional)                     │
└─────────────────────────────────────────────────────────────┘
```

---

## Estructura de Directorios

```
backend/app/Modules/
├── Auth/                          # Módulo de Autenticación
│   ├── Controllers/               # Controladores HTTP
│   │   └── AuthController.php
│   ├── Models/                    # Modelos Eloquent
│   │   ├── Usuario.php
│   │   ├── Rol.php
│   │   └── UsuarioRol.php
│   ├── Services/                  # Lógica de Negocio
│   │   └── AuthService.php
│   ├── Requests/                  # Validación de Entrada
│   │   ├── LoginRequest.php
│   │   └── RegisterRequest.php
│   ├── Resources/                 # Transformadores API
│   │   └── UserResource.php
│   ├── Routes/                    # Rutas del Módulo
│   │   └── api.php
│   ├── Tests/                     # Tests TDD
│   │   ├── LoginTest.php
│   │   └── LogoutTest.php
│   ├── README.md                  # Documentación del Módulo
│   └── module.json                # Metadata del Módulo
│
├── Product/                       # Módulo de Productos
│   ├── Controllers/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── BrandController.php
│   ├── Models/
│   │   ├── Producto.php
│   │   ├── Categoria.php
│   │   └── Marca.php
│   ├── Services/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Tests/
│   ├── README.md
│   └── module.json
│
├── Inventory/                     # Módulo de Inventario
├── Sales/                         # Módulo de Ventas
├── Purchase/                      # Módulo de Compras
├── Customer/                      # Módulo de Clientes
├── Reports/                       # Módulo de Reportes
├── Configuration/                 # Módulo de Configuración
│
└── Shared/                        # Módulo Compartido
    ├── Middleware/                # Middleware común
    │   └── CheckRole.php
    ├── Traits/                    # Traits reutilizables
    │   ├── HasAudit.php
    │   └── HasBolivianTimestamps.php
    ├── Helpers/                   # Funciones globales
    │   └── helpers.php
    ├── Exceptions/                # Excepciones personalizadas
    ├── Services/                  # Servicios compartidos
    └── README.md
```

---

## Módulos del Sistema

### **1. Auth (Autenticación)**
**Ruta base:** `/api/auth`

**Responsabilidades:**
- Gestión de usuarios
- Autenticación JWT (Login, Logout, Refresh)
- Gestión de roles y permisos
- Auditoría de accesos

**Endpoints principales:**
```
POST   /api/auth/login          # Iniciar sesión
POST   /api/auth/logout         # Cerrar sesión
POST   /api/auth/refresh        # Renovar token
GET    /api/auth/me             # Usuario autenticado
```

**Modelos:**
- `Usuario` - Usuarios del sistema
- `Rol` - Roles (Admin, Vendedor, etc.)
- `UsuarioRol` - Relación muchos a muchos

---

### **2. Product (Productos)**
**Ruta base:** `/api/product`

**Responsabilidades:**
- CRUD de productos
- Gestión de categorías
- Gestión de marcas
- Unidades de medida
- Control de stock mínimo

**Endpoints principales:**
```
GET    /api/product/productos           # Listar productos
POST   /api/product/productos           # Crear producto
GET    /api/product/productos/{id}      # Ver producto
PUT    /api/product/productos/{id}      # Actualizar producto
DELETE /api/product/productos/{id}      # Eliminar producto

GET    /api/product/categorias          # Listar categorías
GET    /api/product/marcas              # Listar marcas
```

**Modelos:**
- `Producto` - Productos de la ferretería
- `Categoria` - Categorías de productos
- `Marca` - Marcas de productos
- `UnidadMedida` - Unidades (kg, m, unidad)
- `ProductoUnidad` - Conversiones de unidades

---

### **3. Inventory (Inventario)**
**Ruta base:** `/api/inventory`

**Responsabilidades:**
- Control de inventario por almacén
- Movimientos de inventario
- Ajustes de stock
- Transferencias entre almacenes
- Alertas de stock mínimo

**Endpoints principales:**
```
GET    /api/inventory/inventario                  # Stock actual
POST   /api/inventory/movimientos                 # Registrar movimiento
GET    /api/inventory/almacenes                   # Listar almacenes
POST   /api/inventory/transferencias              # Transferir stock
GET    /api/inventory/alertas-stock               # Productos con stock bajo
```

**Modelos:**
- `Inventario` - Stock actual por almacén
- `Almacen` - Almacenes/bodegas
- `MovimientoInventario` - Histórico de movimientos

---

### **4. Sales (Ventas)**
**Ruta base:** `/api/sales`

**Responsabilidades:**
- Registro de ventas
- Detalles de venta
- Tipos de comprobantes
- Métodos de pago
- Anulación de ventas

**Endpoints principales:**
```
GET    /api/sales/ventas                  # Listar ventas
POST   /api/sales/ventas                  # Registrar venta
GET    /api/sales/ventas/{id}             # Ver venta
POST   /api/sales/ventas/{id}/anular      # Anular venta
GET    /api/sales/comprobantes            # Tipos de comprobante
```

**Modelos:**
- `Venta` - Cabecera de venta
- `DetalleVenta` - Líneas de venta
- `TipoComprobante` - Factura, Recibo, Nota de Venta
- `MetodoPago` - Efectivo, Tarjeta, QR

---

### **5. Purchase (Compras)**
**Ruta base:** `/api/purchase`

**Responsabilidades:**
- Órdenes de compra
- Registro de compras
- Gestión de proveedores
- Seguimiento de pedidos

**Endpoints principales:**
```
GET    /api/purchase/ordenes              # Listar órdenes
POST   /api/purchase/ordenes              # Crear orden
GET    /api/purchase/compras              # Listar compras
POST   /api/purchase/compras              # Registrar compra
GET    /api/purchase/proveedores          # Listar proveedores
```

**Modelos:**
- `OrdenCompra` - Órdenes a proveedores
- `Compra` - Compras realizadas
- `DetalleCompra` - Líneas de compra
- `Proveedor` - Proveedores

---

### **6. Customer (Clientes)**
**Ruta base:** `/api/customer`

**Responsabilidades:**
- CRUD de clientes
- Gestión de datos fiscales (NIT/CI)
- Historial de compras
- Créditos y cuentas por cobrar

**Endpoints principales:**
```
GET    /api/customer/clientes             # Listar clientes
POST   /api/customer/clientes             # Crear cliente
GET    /api/customer/clientes/{id}        # Ver cliente
PUT    /api/customer/clientes/{id}        # Actualizar cliente
GET    /api/customer/clientes/{id}/ventas # Historial de compras
```

**Modelos:**
- `Cliente` - Clientes de la ferretería

---

### **7. Reports (Reportes)**
**Ruta base:** `/api/reports`

**Responsabilidades:**
- Reportes de ventas
- Reportes de inventario
- Reportes financieros
- Exportación PDF/Excel

**Endpoints principales:**
```
GET    /api/reports/ventas-diarias           # Ventas del día
GET    /api/reports/ventas-mensuales         # Ventas del mes
GET    /api/reports/productos-mas-vendidos   # Top productos
GET    /api/reports/stock-valorizado         # Valor de inventario
GET    /api/reports/cuentas-por-cobrar       # Cuentas pendientes
```

---

### **8. Configuration (Configuración)**
**Ruta base:** `/api/configuration`

**Responsabilidades:**
- Configuración del sistema
- Parámetros globales
- Respaldos
- Logs del sistema

**Endpoints principales:**
```
GET    /api/configuration/parametros          # Parámetros del sistema
PUT    /api/configuration/parametros          # Actualizar parámetros
GET    /api/configuration/respaldos           # Listar respaldos
POST   /api/configuration/respaldos           # Crear respaldo
```

---

## Módulo Shared

El módulo **Shared** contiene componentes **reutilizables** entre todos los módulos.

### **Middleware**
```php
// backend/app/Modules/Shared/Middleware/CheckRole.php
namespace Modules\Shared\Middleware;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Verificar si el usuario tiene uno de los roles especificados
    }
}
```

**Uso:**
```php
Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::get('/usuarios', [UserController::class, 'index']);
});
```

---

### **Traits**
```php
// backend/app/Modules/Shared/Traits/HasAudit.php
namespace Modules\Shared\Traits;

trait HasAudit
{
    protected static function bootHasAudit()
    {
        static::creating(function ($model) {
            $model->creado_por = auth()->id();
        });

        static::updating(function ($model) {
            $model->actualizado_por = auth()->id();
        });
    }
}
```

```php
// backend/app/Modules/Shared/Traits/HasBolivianTimestamps.php
namespace Modules\Shared\Traits;

trait HasBolivianTimestamps
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone('America/La_Paz')->format('Y-m-d H:i:s');
    }

    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = 'actualizado_en';
}
```

**Uso en modelos:**
```php
namespace Modules\Product\Models;

use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Producto extends Model
{
    use HasAudit, HasBolivianTimestamps;
    
    // El modelo automáticamente registra quién creó/actualizó
    // y usa zona horaria de Bolivia
}
```

---

### **Helpers**
```php
// backend/app/Modules/Shared/Helpers/helpers.php

// Formatear moneda boliviana
function format_currency($amount, $currency = 'Bs')
{
    return $currency . ' ' . number_format($amount, 2, '.', ',');
}

// Formatear fecha con zona horaria de Bolivia
function format_bolivian_date($date)
{
    return Carbon::parse($date)
        ->timezone('America/La_Paz')
        ->format('d/m/Y H:i:s');
}

// Generar número de comprobante
function generate_voucher_number($prefix = 'VTA')
{
    $date = now()->format('Ymd');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return "{$prefix}-{$date}-{$random}";
}

// Limpiar RUC/NIT (remover guiones)
function clean_ruc_nit($value)
{
    return preg_replace('/[^0-9]/', '', $value);
}

// Formatear RUC/NIT (agregar guiones)
function format_ruc_nit($value)
{
    $clean = clean_ruc_nit($value);
    if (strlen($clean) >= 7) {
        return substr($clean, 0, -1) . '-' . substr($clean, -1);
    }
    return $clean;
}
```

---

## Convenciones de Módulos

### **1. Namespace**
```php
namespace Modules\{ModuleName}\{Layer};

// Ejemplos:
namespace Modules\Auth\Controllers;
namespace Modules\Product\Services;
namespace Modules\Sales\Models;
```

---

### **2. Estructura de Controladores**
```php
namespace Modules\Product\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Services\ProductService;
use Modules\Product\Requests\StoreProductRequest;
use Modules\Product\Resources\ProductResource;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $productos = $this->productService->getAllProducts($request);
        return ProductResource::collection($productos);
    }

    public function store(StoreProductRequest $request)
    {
        $producto = $this->productService->createProduct($request->validated());
        return new ProductResource($producto);
    }
}
```

---

### **3. Estructura de Servicios**
```php
namespace Modules\Product\Services;

use Modules\Product\Models\Producto;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getAllProducts($request)
    {
        return QueryBuilder::for(Producto::class)
            ->allowedFilters(['nombre', 'categoria_id'])
            ->allowedSorts('nombre', 'precio_venta')
            ->paginate($request->get('per_page', 15));
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function() use ($data) {
            $producto = Producto::create($data);
            
            // Lógica adicional (crear inventario inicial, etc.)
            
            return $producto;
        });
    }
}
```

---

### **4. Estructura de Modelos**
```php
namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Producto extends Model
{
    use HasAudit, HasBolivianTimestamps;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'categoria_id',
        'marca_id',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class);
    }
}
```

---

## Carga Automática de Rutas

### **RouteServiceProvider Modificado**

**Archivo:** `backend/app/Providers/RouteServiceProvider.php`

```php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->routes(function () {
            // Rutas API base
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rutas Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Cargar rutas de módulos automáticamente
            $this->loadModuleRoutes();
        });
    }

    /**
     * Cargar rutas de todos los módulos automáticamente
     */
    protected function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            
            // Excluir el módulo Shared
            if ($moduleName === 'Shared') {
                continue;
            }

            $routeFile = $modulePath . '/Routes/api.php';

            if (File::exists($routeFile)) {
                Route::middleware('api')
                    ->prefix('api/' . strtolower($moduleName))
                    ->group($routeFile);
            }
        }
    }
}
```

### **Funcionamiento:**
1. Escanea el directorio `app/Modules/`
2. Para cada módulo (excepto Shared):
   - Busca `Routes/api.php`
   - Registra rutas con prefijo `/api/{module}`
   - Aplica middleware `api`

**Resultado:**
```
Auth Module      → /api/auth/*
Product Module   → /api/product/*
Inventory Module → /api/inventory/*
Sales Module     → /api/sales/*
Purchase Module  → /api/purchase/*
Customer Module  → /api/customer/*
Reports Module   → /api/reports/*
Configuration    → /api/configuration/*
```

---

## Script de Creación

### **create_modules.sh**

**Ubicación:** `backend/create_modules.sh`

```bash
#!/bin/bash

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Array de módulos
MODULES=(
    "Auth"
    "Product"
    "Inventory"
    "Sales"
    "Purchase"
    "Customer"
    "Reports"
    "Configuration"
    "Shared"
)

# Directorio base
BASE_DIR="app/Modules"

echo -e "${BLUE}🚀 Creando estructura modular...${NC}\n"

# Crear cada módulo
for MODULE in "${MODULES[@]}"; do
    echo -e "${GREEN}📦 Creando módulo: $MODULE${NC}"
    
    # Crear directorios
    mkdir -p "$BASE_DIR/$MODULE/Controllers"
    mkdir -p "$BASE_DIR/$MODULE/Models"
    mkdir -p "$BASE_DIR/$MODULE/Services"
    mkdir -p "$BASE_DIR/$MODULE/Requests"
    mkdir -p "$BASE_DIR/$MODULE/Resources"
    mkdir -p "$BASE_DIR/$MODULE/Routes"
    mkdir -p "$BASE_DIR/$MODULE/Tests"
    
    # Crear archivo de rutas
    cat > "$BASE_DIR/$MODULE/Routes/api.php" << 'EOF'
<?php

use Illuminate\Support\Facades\Route;

// Rutas del módulo
Route::get('/', function() {
    return response()->json([
        'message' => 'Module API',
        'version' => '1.0.0'
    ]);
});
EOF

    # Crear module.json
    cat > "$BASE_DIR/$MODULE/module.json" << EOF
{
    "name": "$MODULE",
    "description": "Módulo $MODULE del sistema",
    "version": "1.0.0",
    "active": true
}
EOF

    # Crear README.md
    cat > "$BASE_DIR/$MODULE/README.md" << EOF
# Módulo $MODULE

## Descripción
Módulo encargado de...

## Estructura
- **Controllers/**: Controladores HTTP
- **Models/**: Modelos Eloquent
- **Services/**: Lógica de negocio
- **Requests/**: Validación de entrada
- **Resources/**: Transformadores de respuesta
- **Routes/**: Rutas del módulo
- **Tests/**: Tests TDD

## Endpoints
(Documentar aquí)

## Tests
\`\`\`bash
php artisan test --filter=$MODULE
\`\`\`
EOF

    echo -e "   ✅ Módulo $MODULE creado\n"
done

echo -e "${GREEN}✨ Estructura modular creada exitosamente!${NC}"
```

**Ejecución:**
```bash
chmod +x backend/create_modules.sh
./backend/create_modules.sh
```

---

## Autoload en Composer

### **composer.json**

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/",
            "Modules\\": "app/Modules/"
        },
        "files": [
            "app/Modules/Shared/Helpers/helpers.php"
        ]
    }
}
```

**Regenerar autoload:**
```bash
docker exec ferreteria_backend composer dump-autoload
```

**Resultado:** 6,652 clases registradas

---

## Ventajas de esta Arquitectura

### ✅ **Escalabilidad**
- Agregar nuevos módulos sin modificar código existente
- Cada módulo es independiente

### ✅ **Mantenibilidad**
- Código organizado por dominio de negocio
- Fácil localizar y modificar funcionalidades

### ✅ **Testabilidad**
- Cada módulo se prueba de forma aislada
- Tests más rápidos y enfocados

### ✅ **Reusabilidad**
- Traits y helpers compartidos
- Evitar duplicación de código

### ✅ **Colaboración**
- Equipos trabajan en módulos diferentes
- Menos conflictos en Git

---

## Referencias

- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Modular Laravel](https://github.com/nWidart/laravel-modules)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
