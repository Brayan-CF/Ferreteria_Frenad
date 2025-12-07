# ⚠️ Manejo de Errores

## Índice
1. [Introducción](#introducción)
2. [Tipos de Errores](#tipos-de-errores)
3. [Formato de Respuestas de Error](#formato-de-respuestas-de-error)
4. [Excepciones Personalizadas](#excepciones-personalizadas)
5. [Handler Global](#handler-global)
6. [Validación de Entrada](#validación-de-entrada)
7. [Logging de Errores](#logging-de-errores)
8. [Ejemplos por Módulo](#ejemplos-por-módulo)

---

## Introducción

Un manejo consistente de errores es **crucial** para:
- ✅ Proporcionar mensajes claros al frontend
- ✅ Facilitar debugging
- ✅ Mantener seguridad (no exponer información sensible)
- ✅ Registrar errores para análisis

---

## Tipos de Errores

### **1. Errores de Validación (422)**

Datos de entrada inválidos o incompletos.

**Ejemplo:**
```json
{
    "success": false,
    "message": "Errores de validación",
    "errors": {
        "nombre": [
            "El campo nombre es obligatorio"
        ],
        "precio_venta": [
            "El precio de venta debe ser mayor a 0"
        ],
        "email": [
            "El formato del email es inválido"
        ]
    }
}
```

---

### **2. Errores de Autenticación (401)**

Token inválido, expirado o no proporcionado.

**Ejemplo:**
```json
{
    "success": false,
    "message": "Token inválido o expirado",
    "error_code": "AUTH_TOKEN_INVALID"
}
```

---

### **3. Errores de Autorización (403)**

Usuario autenticado pero sin permisos.

**Ejemplo:**
```json
{
    "success": false,
    "message": "No tienes permisos para acceder a este recurso",
    "required_roles": ["admin", "gerente"],
    "your_roles": ["vendedor"]
}
```

---

### **4. Recurso No Encontrado (404)**

El recurso solicitado no existe.

**Ejemplo:**
```json
{
    "success": false,
    "message": "Producto no encontrado",
    "error_code": "RESOURCE_NOT_FOUND"
}
```

---

### **5. Conflictos de Negocio (409)**

Operación no permitida por reglas de negocio.

**Ejemplo:**
```json
{
    "success": false,
    "message": "No se puede eliminar el producto porque tiene ventas asociadas",
    "error_code": "BUSINESS_RULE_VIOLATION"
}
```

---

### **6. Errores del Servidor (500)**

Errores no controlados o problemas internos.

**Ejemplo:**
```json
{
    "success": false,
    "message": "Error interno del servidor",
    "error_id": "ERR-20251204-001"
}
```

---

## Formato de Respuestas de Error

### **Estructura Estándar**

```json
{
    "success": false,
    "message": "Mensaje legible para humanos",
    "error_code": "CODIGO_ERROR_MAQUINA",
    "errors": {
        "campo": ["Error específico"]
    },
    "debug": {
        // Solo en ambiente de desarrollo
        "exception": "Illuminate\\Database\\QueryException",
        "file": "/var/www/html/app/...",
        "line": 42,
        "trace": [...]
    }
}
```

### **Campos**

- **`success`** (boolean) - Siempre `false` para errores
- **`message`** (string) - Mensaje legible
- **`error_code`** (string, opcional) - Código de error para frontend
- **`errors`** (object, opcional) - Errores por campo (validación)
- **`debug`** (object, opcional) - Info de debugging (solo dev)

---

## Excepciones Personalizadas

### **BaseException**

**Ubicación:** `backend/app/Modules/Shared/Exceptions/BaseException.php`

```php
<?php

namespace Modules\Shared\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected $statusCode = 500;
    protected $errorCode = 'INTERNAL_ERROR';

    public function __construct($message = null, $statusCode = null, $errorCode = null)
    {
        parent::__construct($message ?? 'Error interno del servidor');
        
        if ($statusCode) {
            $this->statusCode = $statusCode;
        }
        
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
        ], $this->statusCode);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
```

---

### **ResourceNotFoundException**

```php
<?php

namespace Modules\Shared\Exceptions;

class ResourceNotFoundException extends BaseException
{
    protected $statusCode = 404;
    protected $errorCode = 'RESOURCE_NOT_FOUND';

    public function __construct($resource = 'Recurso')
    {
        parent::__construct("{$resource} no encontrado", 404, 'RESOURCE_NOT_FOUND');
    }
}
```

**Uso:**
```php
$producto = Producto::find($id);

if (!$producto) {
    throw new ResourceNotFoundException('Producto');
}
```

---

### **BusinessRuleException**

```php
<?php

namespace Modules\Shared\Exceptions;

class BusinessRuleException extends BaseException
{
    protected $statusCode = 409;
    protected $errorCode = 'BUSINESS_RULE_VIOLATION';

    public function __construct($message)
    {
        parent::__construct($message, 409, 'BUSINESS_RULE_VIOLATION');
    }
}
```

**Uso:**
```php
if ($venta->estado === 'anulada') {
    throw new BusinessRuleException('No se puede modificar una venta anulada');
}

if ($producto->inventario->sum('cantidad_actual') > 0) {
    throw new BusinessRuleException('No se puede eliminar el producto porque tiene stock disponible');
}
```

---

### **InsufficientStockException**

```php
<?php

namespace Modules\Shared\Exceptions;

class InsufficientStockException extends BaseException
{
    protected $statusCode = 409;
    protected $errorCode = 'INSUFFICIENT_STOCK';

    public function __construct($producto, $solicitado, $disponible)
    {
        $message = "Stock insuficiente para {$producto}. Solicitado: {$solicitado}, Disponible: {$disponible}";
        parent::__construct($message, 409, 'INSUFFICIENT_STOCK');
    }
}
```

**Uso:**
```php
$disponible = $producto->inventario->sum('cantidad_actual');

if ($cantidad > $disponible) {
    throw new InsufficientStockException(
        $producto->nombre,
        $cantidad,
        $disponible
    );
}
```

---

## Handler Global

### **Handler.php**

**Ubicación:** `backend/app/Exceptions/Handler.php`

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Solo para peticiones JSON (API)
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    protected function handleApiException($request, Throwable $e)
    {
        // ModelNotFoundException (Eloquent)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        // NotFoundHttpException (Ruta no existe)
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado',
                'error_code' => 'ENDPOINT_NOT_FOUND',
            ], 404);
        }

        // ValidationException
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        // AuthenticationException
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // AuthorizationException
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
                'error_code' => 'UNAUTHORIZED',
            ], 403);
        }

        // Excepciones personalizadas (BaseException y sus hijos)
        if (method_exists($e, 'render')) {
            return $e->render($request);
        }

        // Error genérico del servidor
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        $response = [
            'success' => false,
            'message' => $statusCode === 500 ? 'Error interno del servidor' : $e->getMessage(),
            'error_code' => 'INTERNAL_ERROR',
        ];

        // Agregar información de debugging en desarrollo
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ];
        }

        return response()->json($response, $statusCode);
    }
}
```

---

## Validación de Entrada

### **FormRequest**

```php
<?php

namespace Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Autorización manejada por middleware
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'categoria_id' => 'required|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0|gt:precio_compra',
            'stock_minimo' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'codigo.required' => 'El código del producto es obligatorio',
            'codigo.unique' => 'Ya existe un producto con este código',
            'categoria_id.required' => 'Debe seleccionar una categoría',
            'categoria_id.exists' => 'La categoría seleccionada no existe',
            'precio_venta.gt' => 'El precio de venta debe ser mayor al precio de compra',
        ];
    }
}
```

**Uso en Controlador:**
```php
public function store(StoreProductRequest $request)
{
    // Si llega aquí, la validación pasó
    $producto = $this->productService->create($request->validated());
    
    return response()->json([
        'success' => true,
        'message' => 'Producto creado exitosamente',
        'data' => new ProductResource($producto),
    ], 201);
}
```

---

### **Validación Manual**

```php
use Illuminate\Support\Facades\Validator;

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:255',
        'email' => 'required|email|unique:usuarios',
    ], [
        'nombre.required' => 'El nombre es obligatorio',
        'email.unique' => 'El email ya está registrado',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Continuar con la lógica...
}
```

---

## Logging de Errores

### **Configuración**

**Archivo:** `backend/config/logging.php`

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'daily'],
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

---

### **Logging Manual**

```php
use Illuminate\Support\Facades\Log;

// Info
Log::info('Producto creado', [
    'producto_id' => $producto->id,
    'usuario_id' => auth()->id(),
]);

// Warning
Log::warning('Stock bajo detectado', [
    'producto_id' => $producto->id,
    'stock_actual' => $stock,
    'stock_minimo' => $producto->stock_minimo,
]);

// Error
Log::error('Error al procesar venta', [
    'venta_id' => $venta->id,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
]);

// Critical
Log::critical('Base de datos no disponible', [
    'error' => $e->getMessage(),
]);
```

---

### **Logging Automático de Excepciones**

```php
// En Handler.php
public function report(Throwable $e)
{
    // Log automático de excepciones
    if ($this->shouldReport($e)) {
        Log::error('Exception occurred', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => auth()->id() ?? null,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);
    }

    parent::report($e);
}
```

---

## Ejemplos por Módulo

### **Módulo Product**

```php
namespace Modules\Product\Services;

use Modules\Product\Models\Producto;
use Modules\Shared\Exceptions\ResourceNotFoundException;
use Modules\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function delete($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw new ResourceNotFoundException('Producto');
        }

        // Verificar si tiene stock
        $stockTotal = $producto->inventario->sum('cantidad_actual');
        if ($stockTotal > 0) {
            throw new BusinessRuleException(
                'No se puede eliminar el producto porque tiene stock disponible. Stock actual: ' . $stockTotal
            );
        }

        // Verificar si tiene ventas
        if ($producto->detallesVenta()->exists()) {
            throw new BusinessRuleException(
                'No se puede eliminar el producto porque tiene ventas registradas'
            );
        }

        try {
            DB::beginTransaction();

            $producto->delete();

            Log::info('Producto eliminado', [
                'producto_id' => $id,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar producto', [
                'producto_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
```

---

### **Módulo Sales**

```php
namespace Modules\Sales\Services;

use Modules\Sales\Models\Venta;
use Modules\Shared\Exceptions\ResourceNotFoundException;
use Modules\Shared\Exceptions\BusinessRuleException;
use Modules\Shared\Exceptions\InsufficientStockException;

class VentaService
{
    public function anular($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            throw new ResourceNotFoundException('Venta');
        }

        if ($venta->estado === 'anulada') {
            throw new BusinessRuleException('La venta ya está anulada');
        }

        // Solo admin y gerente pueden anular después de 24 horas
        $horas = now()->diffInHours($venta->creado_en);
        if ($horas > 24 && !auth()->user()->hasRole(['admin', 'gerente'])) {
            throw new BusinessRuleException(
                'Solo administradores y gerentes pueden anular ventas con más de 24 horas'
            );
        }

        // Lógica de anulación...
    }

    public function create(array $data)
    {
        // Verificar stock antes de crear venta
        foreach ($data['detalles'] as $detalle) {
            $producto = Producto::find($detalle['producto_id']);
            
            $stockDisponible = $producto->inventario
                ->where('almacen_id', $data['almacen_id'])
                ->sum('cantidad_actual');

            if ($detalle['cantidad'] > $stockDisponible) {
                throw new InsufficientStockException(
                    $producto->nombre,
                    $detalle['cantidad'],
                    $stockDisponible
                );
            }
        }

        // Continuar con la creación...
    }
}
```

---

## Testing de Errores

```php
<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Modules\Auth\Models\Usuario;
use Modules\Product\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function retorna_404_si_producto_no_existe()
    {
        $admin = Usuario::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/product/productos/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ]);
    }

    /** @test */
    public function retorna_422_si_validacion_falla()
    {
        $admin = Usuario::factory()->admin()->create();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/product/productos', [
                'nombre' => '', // Requerido
                'precio_venta' => -10, // Debe ser positivo
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'nombre',
                    'precio_venta',
                ],
            ]);
    }

    /** @test */
    public function retorna_409_si_no_se_puede_eliminar_con_stock()
    {
        $admin = Usuario::factory()->admin()->create();
        
        $producto = Producto::factory()->withStock(50)->create();

        $response = $this->actingAs($admin, 'api')
            ->deleteJson("/api/product/productos/{$producto->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'error_code' => 'BUSINESS_RULE_VIOLATION',
            ]);
    }
}
```

---

## Mejores Prácticas

### ✅ **DO - Hacer**

```php
// Usar excepciones específicas
throw new ResourceNotFoundException('Producto');
throw new BusinessRuleException('Stock insuficiente');

// Proporcionar mensajes claros
throw new BusinessRuleException(
    'No se puede eliminar el producto porque tiene 3 ventas asociadas'
);

// Logging de errores importantes
Log::error('Error crítico en proceso de venta', [
    'venta_id' => $venta->id,
    'error' => $e->getMessage(),
]);

// Transacciones para operaciones críticas
DB::beginTransaction();
try {
    // Operaciones
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

---

### ❌ **DON'T - No Hacer**

```php
// No exponer información sensible
throw new Exception($e->getMessage()); // ❌ Puede incluir SQL, paths

// No retornar arrays vacíos en errores
return []; // ❌ Usar response()->json(['success' => false])

// No silenciar errores
try {
    // código
} catch (\Exception $e) {
    // ❌ No ignorar el error
}

// No mezclar códigos de estado
return response()->json(['error' => 'Not found'], 200); // ❌
```

---

## Resumen

- ✅ Usar excepciones personalizadas para errores de negocio
- ✅ Handler global maneja todas las excepciones de API
- ✅ Validación con FormRequest para datos de entrada
- ✅ Logging de errores importantes
- ✅ Mensajes claros y consistentes
- ✅ Códigos de estado HTTP apropiados
- ✅ Tests para casos de error

---

## Referencias

- [Laravel Error Handling](https://laravel.com/docs/10.x/errors)
- [HTTP Status Codes](https://httpstatuses.com/)
- [REST API Error Handling Best Practices](https://www.baeldung.com/rest-api-error-handling-best-practices)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
