# 🔗 Integración entre Componentes

Cómo se comunican los diferentes componentes del sistema.

---

## 1. 🌐 Frontend ↔ Backend (API REST)

### Protocolo
- **HTTP/HTTPS** sobre TCP
- **JSON** como formato de datos
- **JWT** para autenticación

### Flujo de Autenticación

```
1. Usuario ingresa credenciales
   └─> POST /api/v1/auth/login
       Body: {
         "email": "admin@ferreteria.com",
         "password": "password123"
       }

2. Backend valida y genera JWT
   └─> Response 200 OK
       Body: {
         "success": true,
         "data": {
           "token": "eyJ0eXAiOiJKV1QiLCJh...",
           "user": {...},
           "expires_in": 3600
         }
       }

3. Frontend guarda token
   └─> localStorage.setItem('token', token)

4. Requests subsecuentes incluyen token
   └─> Headers: {
         "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJh..."
       }

5. Backend verifica token en cada request
   └─> Middleware Authenticate
       ├─> Token válido: Continúa
       └─> Token inválido: 401 Unauthorized
```

### Ejemplo de Request Completo

**JavaScript (Frontend)**
```javascript
// Login
async function login(email, password) {
    const response = await fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    });

    const data = await response.json();
    
    if (data.success) {
        localStorage.setItem('token', data.data.token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
        return data.data;
    } else {
        throw new Error(data.message);
    }
}

// Request autenticado
async function getProductos() {
    const token = localStorage.getItem('token');
    
    const response = await fetch('/api/v1/productos', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
    });

    const data = await response.json();
    return data.data; // Array de productos
}

// Crear venta
async function crearVenta(ventaData) {
    const token = localStorage.getItem('token');
    
    const response = await fetch('/api/v1/ventas', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(ventaData),
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message);
    }

    const data = await response.json();
    return data.data;
}
```

**PHP Laravel (Backend)**
```php
// routes/api.php
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('ventas', VentaController::class);
});

// AuthController
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = auth()->attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'Credenciales inválidas',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'token' => $token,
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60,
        ],
    ]);
}
```

### Manejo de Errores

**Status Codes**
```javascript
async function apiRequest(url, options) {
    try {
        const response = await fetch(url, options);
        const data = await response.json();

        switch (response.status) {
            case 200:
            case 201:
                return data;
            
            case 400:
                throw new Error(`Bad Request: ${data.message}`);
            
            case 401:
                // Token expirado o inválido
                localStorage.removeItem('token');
                window.location.href = '/login.html';
                break;
            
            case 403:
                throw new Error('No tienes permisos para esta acción');
            
            case 404:
                throw new Error('Recurso no encontrado');
            
            case 422:
                // Errores de validación
                throw new ValidationError(data.errors);
            
            case 500:
                throw new Error('Error del servidor. Intenta más tarde');
            
            default:
                throw new Error('Error desconocido');
        }
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}
```

---

## 2. ⚙️ Backend ↔ Database (Eloquent ORM)

### Conexión

**config/database.php**
```php
'connections' => [
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST', 'postgres_ferreteria'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'ferreteria_frenad'),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
    ],
],
```

### Query Builder vs Eloquent

**Query Builder (SQL directo)**
```php
use Illuminate\Support\Facades\DB;

// Select
$productos = DB::table('productos')
    ->where('stock', '>', 0)
    ->orderBy('nombre')
    ->get();

// Insert
DB::table('ventas')->insert([
    'cliente_id' => 1,
    'total' => 150.50,
    'fecha' => now(),
]);

// Update
DB::table('inventario')
    ->where('producto_id', 5)
    ->decrement('stock_actual', 10);

// Delete
DB::table('productos')
    ->where('activo', false)
    ->delete();

// Transaction
DB::transaction(function () {
    DB::table('ventas')->insert([...]);
    DB::table('detalle_ventas')->insert([...]);
    DB::table('inventario')->decrement('stock_actual', 5);
});
```

**Eloquent ORM (Orientado a objetos)**
```php
// Select con relaciones
$venta = Venta::with(['cliente', 'detalles.producto'])
    ->find($id);

// Create
$producto = Producto::create([
    'nombre' => 'Martillo',
    'precio' => 25.00,
]);

// Update
$producto->update(['precio' => 30.00]);

// Delete
$producto->delete();

// Relaciones
$venta = Venta::find(1);
$cliente = $venta->cliente; // BelongsTo
$detalles = $venta->detalles; // HasMany

foreach ($detalles as $detalle) {
    echo $detalle->producto->nombre; // Through relationship
}

// Scopes
class Producto extends Model {
    public function scopeActivos($query) {
        return $query->where('activo', true);
    }
    
    public function scopeBajoStock($query) {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
}

// Uso
$productos = Producto::activos()->bajoStock()->get();
```

### Transactions (Seguridad de Datos)

```php
use Illuminate\Support\Facades\DB;
use Exception;

class VentaService
{
    public function crearVenta(array $data): Venta
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Crear venta
                $venta = Venta::create([
                    'cliente_id' => $data['cliente_id'],
                    'total' => $data['total'],
                ]);

                // 2. Crear detalles
                foreach ($data['productos'] as $item) {
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                    ]);

                    // 3. Actualizar inventario
                    Inventario::where('producto_id', $item['producto_id'])
                        ->decrement('stock_actual', $item['cantidad']);
                }

                // Si todo OK, COMMIT automático
                return $venta->load('detalles');
            });
        } catch (Exception $e) {
            // Si error, ROLLBACK automático
            Log::error('Error al crear venta: ' . $e->getMessage());
            throw $e;
        }
    }
}
```

### Eventos y Listeners

**Model Events**
```php
class Venta extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Antes de crear
        static::creating(function ($venta) {
            $venta->numero_venta = self::generarNumero();
        });

        // Después de crear
        static::created(function ($venta) {
            Log::info("Venta creada: {$venta->id}");
            event(new VentaCreada($venta));
        });

        // Antes de eliminar
        static::deleting(function ($venta) {
            // Validar que no tenga créditos pendientes
            if ($venta->creditos()->pendientes()->exists()) {
                throw new Exception('No se puede eliminar venta con créditos pendientes');
            }
        });
    }
}
```

---

## 3. 🐳 Docker Compose (Orquestación)

### Comunicación entre Contenedores

**Red Docker Bridge**
```yaml
networks:
  ferreteria_network:
    driver: bridge
```

**Servicios se comunican por nombre**
```yaml
# Backend accede a DB por nombre: postgres_ferreteria
services:
  backend:
    environment:
      DB_HOST: postgres_ferreteria  # ← Nombre del servicio
```

**Orden de Inicio**
```yaml
services:
  backend:
    depends_on:
      postgres_ferreteria:
        condition: service_healthy  # Espera a que esté "healthy"
  
  frontend:
    depends_on:
      - backend  # Espera a que backend inicie
```

**Health Checks**
```yaml
postgres_ferreteria:
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U postgres"]
    interval: 10s  # Revisar cada 10s
    timeout: 5s
    retries: 5     # 5 intentos antes de marcar como "unhealthy"
```

### Volúmenes (Persistencia)

```yaml
volumes:
  # Volumen nombrado (gestionado por Docker)
  postgres_data:
    driver: local

services:
  postgres_ferreteria:
    volumes:
      # Datos persisten en volumen nombrado
      - postgres_data:/var/lib/postgresql/data
      
      # Scripts SQL (solo lectura)
      - ./database:/database:ro
```

### Variables de Entorno

**.env**
```bash
DB_HOST=postgres_ferreteria
DB_PORT=5432
DB_DATABASE=ferreteria_frenad
DB_USERNAME=postgres
DB_PASSWORD=frenad_postgres_2024
```

**docker-compose.yml**
```yaml
services:
  backend:
    environment:
      DB_HOST: ${DB_HOST}          # Desde .env
      DB_PORT: ${DB_PORT}
      DB_DATABASE: ${DB_DATABASE}
```

---

## 4. 🔒 Seguridad en la Integración

### CORS (Cross-Origin Resource Sharing)

**Backend (Laravel)**
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:8080',
        'https://ferreteria-frenad.com',
    ],
    'allowed_headers' => ['*'],
    'max_age' => 86400,
];
```

### CSRF Protection

**Para API REST: Deshabilitado**
```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'api/*',  // Excluir rutas API (usan JWT en su lugar)
];
```

### Rate Limiting

**Limitar requests por IP/usuario**
```php
// routes/api.php
Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
    // Máximo 60 requests por minuto
    Route::apiResource('ventas', VentaController::class);
});

// Para endpoints públicos
Route::middleware('throttle:10,1')->post('/auth/login', ...);
// Máximo 10 intentos de login por minuto
```

### SQL Injection Prevention

**Eloquent automáticamente escapa valores**
```php
// ✅ SEGURO (Eloquent)
$productos = Producto::where('nombre', 'LIKE', "%{$busqueda}%")->get();

// ✅ SEGURO (Query Builder con bindings)
$productos = DB::select('SELECT * FROM productos WHERE nombre LIKE ?', ["%{$busqueda}%"]);

// ❌ INSEGURO (SQL directo sin escape)
$productos = DB::select("SELECT * FROM productos WHERE nombre LIKE '%{$busqueda}%'");
// NUNCA hacer esto!
```

### XSS Prevention

**Frontend: Sanitizar HTML**
```javascript
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

// Uso
const nombreProducto = sanitizeHTML(userInput);
element.innerHTML = `<h3>${nombreProducto}</h3>`; // Seguro
```

---

## 5. 📊 Monitoreo y Logging

### Logs de Backend (Laravel)

```php
use Illuminate\Support\Facades\Log;

// Diferentes niveles
Log::emergency('Sistema caído');
Log::alert('Acción requerida');
Log::critical('Componente crítico falló');
Log::error('Error en operación');
Log::warning('Advertencia');
Log::notice('Evento notable');
Log::info('Información general');
Log::debug('Información de debug');

// Con contexto
Log::error('Error al crear venta', [
    'user_id' => auth()->id(),
    'data' => $request->all(),
    'exception' => $e->getMessage(),
]);
```

**Almacenamiento**
```
backend/storage/logs/
├── laravel.log           # Log general
├── laravel-2025-11-29.log  # Log diario
└── query.log             # Queries SQL (dev)
```

### Logs de Nginx

```nginx
# nginx.conf
http {
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log warn;
}
```

### Logs de PostgreSQL

```yaml
# docker-compose.yml
postgres_ferreteria:
  command: 
    - "postgres"
    - "-c"
    - "log_statement=all"  # Loggear todas las queries (dev only)
```

### Monitoreo Docker

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Logs de un servicio
docker-compose logs -f backend

# Estadísticas de recursos
docker stats

# Ver procesos
docker-compose ps
```

---

## 📚 Resumen de Integraciones

| Integración | Protocolo | Formato | Autenticación |
|-------------|-----------|---------|---------------|
| Frontend ↔ Backend | HTTP/JSON | JSON | JWT Bearer Token |
| Backend ↔ Database | PDO/TCP | SQL | Usuario/Password |
| Contenedores | Docker Network | - | Network isolation |
| API Externa (futuro) | HTTP/REST | JSON | API Key |

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
