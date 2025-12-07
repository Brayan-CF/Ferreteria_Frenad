# 🛡️ Middleware Compartido

## Índice
1. [Introducción](#introducción)
2. [CheckRole Middleware](#checkrole-middleware)
3. [Registro en Kernel](#registro-en-kernel)
4. [Ejemplos de Uso](#ejemplos-de-uso)
5. [Casos de Uso Comunes](#casos-de-uso-comunes)
6. [Testing](#testing)

---

## Introducción

El middleware compartido proporciona funcionalidades transversales que se aplican a múltiples módulos del sistema. Estos componentes están ubicados en el módulo **Shared** para su reutilización.

### **Middleware Disponible**
- ✅ **CheckRole** - Autorización basada en roles

---

## CheckRole Middleware

### **Ubicación**
```
backend/app/Modules/Shared/Middleware/CheckRole.php
```

### **Propósito**
Verificar que el usuario autenticado tenga **al menos uno** de los roles especificados antes de permitir el acceso a una ruta.

---

### **Código Completo**

```php
<?php

namespace Modules\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verificar si el usuario tiene el rol necesario
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles - Uno o más roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        $user = auth()->user();

        // Si no se especificaron roles, permitir acceso (solo requiere autenticación)
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los roles
        $userRoles = $user->roles->pluck('nombre')->toArray();
        
        $hasRole = !empty(array_intersect($roles, $userRoles));

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder a este recurso',
                'required_roles' => $roles,
                'your_roles' => $userRoles,
            ], 403);
        }

        return $next($request);
    }
}
```

---

### **Características**

#### **1. Múltiples Roles**
```php
// El usuario debe tener al menos uno de estos roles
Route::middleware(['auth:api', 'role:admin,gerente'])->group(function() {
    // Rutas accesibles para admin O gerente
});
```

#### **2. Respuestas JSON**
```json
// 401 - No autenticado
{
    "success": false,
    "message": "No autenticado"
}

// 403 - Sin permisos
{
    "success": false,
    "message": "No tienes permisos para acceder a este recurso",
    "required_roles": ["admin"],
    "your_roles": ["vendedor"]
}
```

#### **3. Verificación en Base de Datos**
- Consulta la relación `roles` del usuario
- Obtiene los nombres de los roles con `pluck('nombre')`
- Compara con los roles requeridos usando `array_intersect`

---

## Registro en Kernel

### **Paso 1: Abrir Kernel.php**

**Archivo:** `backend/app/Http/Kernel.php`

### **Paso 2: Agregar al Array de Alias**

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Auth\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        
        // ⭐ AGREGAR ESTA LÍNEA
        'role' => \Modules\Shared\Middleware\CheckRole::class,
    ];
}
```

### **Verificar Registro**

```bash
docker exec ferreteria_backend php artisan route:list
```

---

## Ejemplos de Uso

### **1. Rutas Protegidas por Un Solo Rol**

```php
// backend/app/Modules/Product/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Product\Controllers\ProductController;

// Solo administradores pueden crear/editar/eliminar productos
Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::post('/productos', [ProductController::class, 'store']);
    Route::put('/productos/{id}', [ProductController::class, 'update']);
    Route::delete('/productos/{id}', [ProductController::class, 'destroy']);
});

// Todos los autenticados pueden listar productos
Route::middleware(['auth:api'])->group(function() {
    Route::get('/productos', [ProductController::class, 'index']);
    Route::get('/productos/{id}', [ProductController::class, 'show']);
});
```

---

### **2. Rutas con Múltiples Roles Permitidos**

```php
// backend/app/Modules/Sales/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Controllers\VentaController;

// Admin, gerente y vendedor pueden registrar ventas
Route::middleware(['auth:api', 'role:admin,gerente,vendedor'])->group(function() {
    Route::post('/ventas', [VentaController::class, 'store']);
});

// Solo admin y gerente pueden anular ventas
Route::middleware(['auth:api', 'role:admin,gerente'])->group(function() {
    Route::post('/ventas/{id}/anular', [VentaController::class, 'anular']);
});

// Solo admin puede ver todas las ventas
Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::get('/ventas/todas', [VentaController::class, 'todas']);
});
```

---

### **3. Aplicar a Rutas Individuales**

```php
// Ruta individual con rol
Route::get('/reportes/financiero', [ReportController::class, 'financiero'])
    ->middleware(['auth:api', 'role:admin,gerente']);

// Ruta sin restricción de rol (solo autenticación)
Route::get('/productos/search', [ProductController::class, 'search'])
    ->middleware('auth:api');
```

---

### **4. En Controladores**

```php
namespace Modules\Customer\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware en el constructor
        $this->middleware('auth:api');
        $this->middleware('role:admin,gerente')->only(['destroy', 'restore']);
        $this->middleware('role:admin')->only(['forceDelete']);
    }

    public function index()
    {
        // Accesible para todos los autenticados
    }

    public function destroy($id)
    {
        // Solo admin y gerente
    }

    public function forceDelete($id)
    {
        // Solo admin
    }
}
```

---

## Casos de Uso Comunes

### **Caso 1: Módulo Auth**

```php
// backend/app/Modules/Auth/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\UserController;

// Rutas públicas (sin autenticación)
Route::post('/login', [AuthController::class, 'login']);

// Rutas autenticadas (sin restricción de rol)
Route::middleware('auth:api')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Solo administradores
Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    
    Route::post('/usuarios/{id}/asignar-rol', [UserController::class, 'asignarRol']);
    Route::delete('/usuarios/{id}/remover-rol', [UserController::class, 'removerRol']);
});
```

---

### **Caso 2: Módulo Inventory**

```php
// backend/app/Modules/Inventory/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Controllers\InventarioController;
use Modules\Inventory\Controllers\AlmacenController;

Route::middleware('auth:api')->group(function() {
    
    // Consultar inventario (todos los autenticados)
    Route::get('/inventario', [InventarioController::class, 'index']);
    Route::get('/inventario/{id}', [InventarioController::class, 'show']);
    Route::get('/alertas-stock', [InventarioController::class, 'alertasStock']);
    
    // Movimientos de inventario (admin, gerente, almacenero)
    Route::middleware('role:admin,gerente,almacenero')->group(function() {
        Route::post('/movimientos', [InventarioController::class, 'registrarMovimiento']);
        Route::post('/ajustes', [InventarioController::class, 'ajustarStock']);
        Route::post('/transferencias', [InventarioController::class, 'transferir']);
    });
    
    // Gestión de almacenes (solo admin y gerente)
    Route::middleware('role:admin,gerente')->group(function() {
        Route::get('/almacenes', [AlmacenController::class, 'index']);
        Route::post('/almacenes', [AlmacenController::class, 'store']);
        Route::put('/almacenes/{id}', [AlmacenController::class, 'update']);
        Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy']);
    });
});
```

---

### **Caso 3: Módulo Reports**

```php
// backend/app/Modules/Reports/Routes/api.php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Controllers\ReportController;

Route::middleware('auth:api')->group(function() {
    
    // Reportes básicos (todos los autenticados)
    Route::get('/ventas-diarias', [ReportController::class, 'ventasDiarias']);
    Route::get('/productos-mas-vendidos', [ReportController::class, 'productosMasVendidos']);
    
    // Reportes financieros (admin y gerente)
    Route::middleware('role:admin,gerente')->group(function() {
        Route::get('/ventas-mensuales', [ReportController::class, 'ventasMensuales']);
        Route::get('/cuentas-por-cobrar', [ReportController::class, 'cuentasPorCobrar']);
        Route::get('/rentabilidad', [ReportController::class, 'rentabilidad']);
        Route::get('/stock-valorizado', [ReportController::class, 'stockValorizado']);
    });
    
    // Reportes administrativos (solo admin)
    Route::middleware('role:admin')->group(function() {
        Route::get('/auditoria', [ReportController::class, 'auditoria']);
        Route::get('/usuarios-actividad', [ReportController::class, 'actividadUsuarios']);
    });
});
```

---

## Testing

### **Test de Middleware**

```php
<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;
use Modules\Shared\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_sin_autenticar_recibe_401()
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new CheckRole();

        $response = $middleware->handle($request, function() {}, 'admin');

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /** @test */
    public function usuario_con_rol_correcto_puede_acceder()
    {
        // Crear usuario con rol admin
        $admin = Usuario::factory()->create();
        $rolAdmin = Rol::create(['nombre' => 'admin']);
        $admin->roles()->attach($rolAdmin);

        $this->actingAs($admin, 'api');

        $request = Request::create('/api/test', 'GET');
        $middleware = new CheckRole();

        $next = function ($request) {
            return response()->json(['success' => true]);
        };

        $response = $middleware->handle($request, $next, 'admin');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function usuario_sin_rol_requerido_recibe_403()
    {
        // Crear usuario con rol vendedor
        $vendedor = Usuario::factory()->create();
        $rolVendedor = Rol::create(['nombre' => 'vendedor']);
        $vendedor->roles()->attach($rolVendedor);

        $this->actingAs($vendedor, 'api');

        $request = Request::create('/api/test', 'GET');
        $middleware = new CheckRole();

        $response = $middleware->handle($request, function() {}, 'admin');

        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals(['admin'], $data['required_roles']);
        $this->assertEquals(['vendedor'], $data['your_roles']);
    }

    /** @test */
    public function usuario_con_uno_de_varios_roles_puede_acceder()
    {
        // Crear usuario con rol gerente
        $gerente = Usuario::factory()->create();
        $rolGerente = Rol::create(['nombre' => 'gerente']);
        $gerente->roles()->attach($rolGerente);

        $this->actingAs($gerente, 'api');

        $request = Request::create('/api/test', 'GET');
        $middleware = new CheckRole();

        $next = function ($request) {
            return response()->json(['success' => true]);
        };

        // Requiere admin O gerente
        $response = $middleware->handle($request, $next, 'admin', 'gerente');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

---

### **Test de Integración**

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_puede_crear_usuarios()
    {
        $admin = Usuario::factory()->create();
        $rolAdmin = Rol::create(['nombre' => 'admin']);
        $admin->roles()->attach($rolAdmin);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/auth/usuarios', [
                'nombre' => 'Nuevo Usuario',
                'email' => 'nuevo@test.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function vendedor_no_puede_crear_usuarios()
    {
        $vendedor = Usuario::factory()->create();
        $rolVendedor = Rol::create(['nombre' => 'vendedor']);
        $vendedor->roles()->attach($rolVendedor);

        $response = $this->actingAs($vendedor, 'api')
            ->postJson('/api/auth/usuarios', [
                'nombre' => 'Nuevo Usuario',
                'email' => 'nuevo@test.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'No tienes permisos para acceder a este recurso',
        ]);
    }
}
```

---

## Mejoras Futuras

### **1. Permisos Granulares**

```php
// Además de roles, verificar permisos específicos
Route::middleware(['auth:api', 'permission:productos.crear'])->group(function() {
    Route::post('/productos', [ProductController::class, 'store']);
});
```

### **2. Cache de Roles**

```php
// Cachear roles del usuario para mejorar rendimiento
$userRoles = Cache::remember("user.{$user->id}.roles", 3600, function() use ($user) {
    return $user->roles->pluck('nombre')->toArray();
});
```

### **3. Logging de Accesos Denegados**

```php
// Registrar intentos de acceso no autorizados
if (!$hasRole) {
    Log::warning('Acceso denegado', [
        'usuario_id' => $user->id,
        'ruta' => $request->path(),
        'roles_requeridos' => $roles,
        'roles_usuario' => $userRoles,
    ]);
}
```

---

## Resumen

### **Ventajas del CheckRole Middleware**
- ✅ Centraliza la lógica de autorización
- ✅ Fácil de usar y leer
- ✅ Soporta múltiples roles
- ✅ Respuestas JSON claras
- ✅ Fácilmente testeable

### **Roles del Sistema**
- **admin** - Acceso total
- **gerente** - Reportes y configuración
- **vendedor** - Ventas y clientes
- **almacenero** - Inventario y productos

---

## Referencias

- [Laravel Middleware](https://laravel.com/docs/10.x/middleware)
- [Laravel Authorization](https://laravel.com/docs/10.x/authorization)
- [Role-Based Access Control](https://en.wikipedia.org/wiki/Role-based_access_control)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
