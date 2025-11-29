# 🏗️ Arquitectura General del Sistema

## 📐 Patrón Arquitectónico

El sistema utiliza una **Arquitectura de 3 Capas** (Three-tier Architecture) con separación clara de responsabilidades.

```
┌─────────────────────────────────────────────────────────┐
│                  CAPA DE PRESENTACIÓN                    │
│                     (Frontend Layer)                     │
│                                                          │
│  ┌────────────────────────────────────────────────┐    │
│  │  Nginx + HTML5 + CSS3 + JavaScript             │    │
│  │  • Interfaz de usuario                         │    │
│  │  • Validación de formularios                   │    │
│  │  • Consumo de API REST                         │    │
│  └────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
                         ▼ HTTP/JSON
┌─────────────────────────────────────────────────────────┐
│                 CAPA DE LÓGICA DE NEGOCIO               │
│                    (Business Layer)                      │
│                                                          │
│  ┌────────────────────────────────────────────────┐    │
│  │  Laravel 10 + PHP 8.2                          │    │
│  │  • Controllers (endpoints REST)                │    │
│  │  • Services (lógica de negocio)                │    │
│  │  • Middleware (auth, validación)               │    │
│  │  • Models (Eloquent ORM)                       │    │
│  └────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
                         ▼ PDO/SQL
┌─────────────────────────────────────────────────────────┐
│                   CAPA DE DATOS                         │
│                    (Data Layer)                          │
│                                                          │
│  ┌────────────────────────────────────────────────┐    │
│  │  PostgreSQL 15                                 │    │
│  │  • 25 Tablas (3FN)                             │    │
│  │  • Triggers y funciones                        │    │
│  │  • Vistas materializadas                       │    │
│  └────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 Principios de Diseño

### 1. **Separation of Concerns (SoC)**
Cada capa tiene responsabilidades específicas:
- **Presentación:** Solo interfaz de usuario
- **Negocio:** Solo lógica de la aplicación
- **Datos:** Solo persistencia

### 2. **Layered Architecture**
- Comunicación unidireccional (top-down)
- Cada capa solo conoce la capa inferior
- Cambios en una capa no afectan otras

### 3. **API-First Design**
- Backend expone API REST
- Frontend es solo un cliente más
- Permite múltiples clientes (web, móvil, etc.)

### 4. **Containerización**
- Cada componente en su propio contenedor Docker
- Independencia de plataforma
- Facilita deployment

---

## 🐳 Arquitectura Docker

```yaml
┌─────────────────────────────────────────────────┐
│           Docker Compose Network                 │
│              (ferreteria_network)                │
│                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌────────┐│
│  │   Frontend   │  │   Backend    │  │  DB    ││
│  │              │  │              │  │        ││
│  │ Nginx:Alpine │  │ PHP:8.2-FPM  │  │ PG:15  ││
│  │ Puerto: 8080 │  │ Puerto: 8000 │  │ :5432  ││
│  │              │  │              │  │        ││
│  │ Volumen:     │  │ Volumen:     │  │Volume: ││
│  │ ./frontend   │  │ ./backend    │  │postgres││
│  └──────────────┘  └──────────────┘  └────────┘│
│         │                 │                │    │
│         └─────────────────┴────────────────┘    │
│              Network: bridge mode               │
└─────────────────────────────────────────────────┘
```

### Contenedores

**1. ferreteria_frontend**
- Imagen: `nginx:alpine`
- Puerto: `8080:80`
- Función: Servir archivos estáticos
- Configuración: `nginx.conf` custom

**2. ferreteria_backend**
- Imagen: `php:8.2-fpm-alpine`
- Puerto: `8000:8000`
- Función: API REST con Laravel
- Extensiones: pgsql, pdo_pgsql, mbstring

**3. ferreteria_postgres**
- Imagen: `postgres:15-alpine`
- Puerto: `5432:5432`
- Función: Base de datos
- Volumen persistente: `postgres_data`

**4. pgadmin (opcional - dev)**
- Imagen: `dpage/pgadmin4`
- Puerto: `5050:80`
- Función: Administración de BD
- Solo perfil `dev`

---

## 🔄 Flujo de Datos

### Ejemplo: Crear una Venta

```
1. Usuario llena formulario de venta
   └─> Interfaz HTML/JS valida datos

2. Frontend hace request
   POST /api/ventas
   Headers: {
     Authorization: Bearer <JWT_TOKEN>
     Content-Type: application/json
   }
   Body: {
     cliente_id: 1,
     productos: [{id: 5, cantidad: 2, precio: 50}],
     tipo_venta: "contado"
   }

3. Backend recibe request
   ├─> Middleware Auth: Verifica JWT
   ├─> Middleware CORS: Permite origen
   └─> VentaController@store

4. Controller procesa
   ├─> VentaRequest: Valida datos
   ├─> VentaService: Lógica de negocio
   │   ├─> Verifica stock disponible
   │   ├─> Calcula totales
   │   └─> Prepara transaction
   └─> DB Transaction BEGIN

5. Eloquent ejecuta queries
   INSERT INTO ventas (...)
   INSERT INTO detalle_ventas (...)
   UPDATE inventario SET stock = stock - cantidad

6. Database ejecuta triggers
   ├─> Trigger: after_insert_detalle_ventas
   │   └─> INSERT INTO movimientos_inventario
   ├─> Trigger: update_timestamps
   └─> Function: calcular_total_venta()

7. Transaction COMMIT

8. Backend responde
   Response 201 Created
   Body: {
     success: true,
     data: { venta: {...} },
     message: "Venta registrada exitosamente"
   }

9. Frontend actualiza UI
   ├─> Muestra mensaje de éxito
   ├─> Limpia formulario
   └─> Opcional: Imprime ticket
```

---

## 🔐 Arquitectura de Seguridad

```
┌────────────────────────────────────────────────┐
│              FRONTEND (Browser)                 │
│  • HTTPS (producción)                          │
│  • XSS Protection                              │
│  • CSRF Token                                  │
└────────────────────────────────────────────────┘
                    ▼
┌────────────────────────────────────────────────┐
│                   NGINX                         │
│  • Rate Limiting                               │
│  • Request Size Limit                          │
│  • Headers Security                            │
└────────────────────────────────────────────────┘
                    ▼
┌────────────────────────────────────────────────┐
│              BACKEND (Laravel)                  │
│  • JWT Authentication                          │
│  • Role-Based Access Control (RBAC)            │
│  • Input Validation                            │
│  • SQL Injection Prevention (Eloquent)         │
│  • Password Hashing (bcrypt)                   │
└────────────────────────────────────────────────┘
                    ▼
┌────────────────────────────────────────────────┐
│            DATABASE (PostgreSQL)                │
│  • User Permissions                            │
│  • Connection Encryption                       │
│  • Backup Encryption                           │
└────────────────────────────────────────────────┘
```

---

## 📡 API REST Design

### Convenciones

**Endpoints:** `/api/v1/{recurso}`

**Métodos HTTP:**
- `GET` - Listar/Obtener
- `POST` - Crear
- `PUT/PATCH` - Actualizar
- `DELETE` - Eliminar

**Status Codes:**
- `200` - OK
- `201` - Created
- `204` - No Content
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

**Response Format:**
```json
{
  "success": true|false,
  "data": {...} | [...],
  "message": "Mensaje descriptivo",
  "errors": {...} (solo si hay errores)
}
```

### Ejemplo de Endpoints

```
Auth:
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh

Productos:
GET    /api/v1/productos
GET    /api/v1/productos/{id}
POST   /api/v1/productos
PUT    /api/v1/productos/{id}
DELETE /api/v1/productos/{id}

Ventas:
GET    /api/v1/ventas
GET    /api/v1/ventas/{id}
POST   /api/v1/ventas
GET    /api/v1/ventas/hoy
GET    /api/v1/ventas/reporte

Inventario:
GET    /api/v1/inventario
GET    /api/v1/inventario/producto/{id}
GET    /api/v1/inventario/stock-bajo
POST   /api/v1/inventario/ajuste
```

---

## 🗄️ Patrón de Base de Datos

### Normalización 3FN

Todas las tablas están normalizadas a Tercera Forma Normal:

**1FN:** Valores atómicos, sin grupos repetitivos
**2FN:** Dependencia funcional completa de la clave
**3FN:** Sin dependencias transitivas

### Integridad Referencial

```sql
-- Todas las FK con ON DELETE/UPDATE explícito
ALTER TABLE detalle_ventas
  ADD CONSTRAINT fk_detalle_ventas_venta
  FOREIGN KEY (venta_id)
  REFERENCES ventas(id)
  ON DELETE RESTRICT  -- No permitir borrar venta con detalles
  ON UPDATE CASCADE;  -- Actualizar en cascada
```

### Triggers Automáticos

```sql
-- Trigger para timestamps
CREATE TRIGGER set_timestamp_ventas
  BEFORE INSERT OR UPDATE ON ventas
  FOR EACH ROW
  EXECUTE FUNCTION update_timestamp();

-- Trigger para inventario
CREATE TRIGGER actualizar_stock_venta
  AFTER INSERT ON detalle_ventas
  FOR EACH ROW
  EXECUTE FUNCTION actualizar_inventario_venta();
```

---

## 🔄 Patrón MVC en Backend

```
Request → Routes → Middleware → Controller → Service → Model → DB
                                    ↓
                               Response ← View/JSON
```

### Capas del Backend

**1. Routes (`routes/api.php`)**
```php
Route::middleware('auth:api')->group(function () {
    Route::apiResource('ventas', VentaController::class);
});
```

**2. Middleware**
```php
class Authenticate {
    public function handle($request, Closure $next) {
        // Verificar JWT token
        if (!$token = $request->bearerToken()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
```

**3. Controller**
```php
class VentaController extends Controller {
    public function store(VentaRequest $request) {
        $venta = $this->ventaService->crear($request->validated());
        return response()->json(['data' => $venta], 201);
    }
}
```

**4. Service (Lógica de Negocio)**
```php
class VentaService {
    public function crear(array $data) {
        DB::transaction(function() use ($data) {
            // Lógica compleja aquí
            $venta = Venta::create($data);
            $this->actualizarInventario($venta);
            return $venta;
        });
    }
}
```

**5. Model (Eloquent ORM)**
```php
class Venta extends Model {
    protected $fillable = ['cliente_id', 'total', 'tipo'];
    
    public function detalles() {
        return $this->hasMany(DetalleVenta::class);
    }
}
```

---

## 📊 Diagrama de Componentes

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                          │
│ ┌─────────────┐ ┌─────────────┐ ┌──────────────┐  │
│ │ POS Module  │ │ Admin Panel │ │ Reports      │  │
│ │ (Ventas)    │ │ (Config)    │ │ (Analytics)  │  │
│ └─────────────┘ └─────────────┘ └──────────────┘  │
│         │               │                │          │
│         └───────────────┴────────────────┘          │
│                      HTTP                           │
└─────────────────────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                  API GATEWAY                         │
│              (Laravel Routes + Middleware)           │
│  • Authentication (JWT)                             │
│  • Authorization (Roles)                            │
│  • Rate Limiting                                    │
│  • Logging                                          │
└─────────────────────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│               BUSINESS SERVICES                      │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐│
│ │ Venta    │ │ Producto │ │ Invent.  │ │ Cliente ││
│ │ Service  │ │ Service  │ │ Service  │ │ Service ││
│ └──────────┘ └──────────┘ └──────────┘ └─────────┘│
└─────────────────────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                  DATA LAYER                          │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐│
│ │ Venta    │ │ Producto │ │ Invent.  │ │ Cliente ││
│ │ Model    │ │ Model    │ │ Model    │ │ Model   ││
│ └──────────┘ └──────────┘ └──────────┘ └─────────┘│
└─────────────────────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│                   DATABASE                           │
│        PostgreSQL 15 (25 Tables, 3FN)               │
└─────────────────────────────────────────────────────┘
```

---

## 🚀 Escalabilidad

### Horizontal Scaling
- Frontend: Múltiples instancias Nginx detrás de load balancer
- Backend: Múltiples instancias PHP-FPM
- Database: Read replicas para consultas

### Vertical Scaling
- Aumentar recursos Docker (CPU, RAM)
- Optimizar queries (índices, vistas)
- Cache (Redis - futuro)

### Performance Optimization
- Lazy loading en frontend
- Paginación en API
- Índices en BD
- Query optimization
- Connection pooling

---

## 📚 Patrones Utilizados

### Backend
- **MVC** - Separación vista/lógica/datos
- **Repository** - Abstracción de acceso a datos (futuro)
- **Service Layer** - Lógica de negocio
- **Dependency Injection** - IoC Container Laravel
- **Factory** - Seeders y testing

### Frontend
- **Module Pattern** - Organización de código JS
- **Observer** - Event listeners
- **Fetch API** - Comunicación asíncrona

### Database
- **Active Record** - Eloquent ORM
- **Trigger** - Automatización de lógica
- **Stored Procedures** - Funciones complejas

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
