# 🧩 Componentes del Sistema

Detalle de cada componente del sistema Ferretería Frenad.

---

## 1. 🎨 Frontend (Capa de Presentación)

### Tecnologías
- **Nginx:** 1.25 Alpine - Web server ligero
- **HTML5:** Estructura semántica
- **CSS3:** Estilos + Flexbox/Grid
- **JavaScript:** ES6+ Vanilla (sin frameworks)
- **Bootstrap:** 5.3 - Framework UI

### Estructura de Carpetas
```
frontend/
├── Dockerfile           # Build del contenedor
├── nginx.conf          # Configuración Nginx
├── index.html          # Landing page
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   ├── pos.css
│   │   └── admin.css
│   ├── js/
│   │   ├── api.js      # Cliente HTTP
│   │   ├── auth.js     # Autenticación
│   │   ├── utils.js    # Utilidades
│   │   └── modules/
│   │       ├── ventas.js
│   │       ├── productos.js
│   │       └── inventario.js
│   └── img/
│       └── logo.png
├── pages/
│   ├── login.html
│   ├── pos.html
│   ├── productos.html
│   ├── inventario.html
│   └── reportes.html
└── components/
    ├── navbar.html
    ├── sidebar.html
    └── modal.html
```

### Responsabilidades
- ✅ Renderizar interfaz de usuario
- ✅ Validar formularios (client-side)
- ✅ Consumir API REST (fetch/axios)
- ✅ Gestionar estado local (localStorage)
- ✅ Manejar eventos de usuario
- ❌ NO contiene lógica de negocio
- ❌ NO accede directamente a BD

### Configuración Nginx
```nginx
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    # SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Proxy a API
    location /api {
        proxy_pass http://backend:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # Seguridad
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
}
```

### Cliente API (api.js)
```javascript
class APIClient {
    constructor(baseURL = '/api/v1') {
        this.baseURL = baseURL;
        this.token = localStorage.getItem('token');
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`,
                ...options.headers,
            },
        };

        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }

        return data;
    }

    get(endpoint) {
        return this.request(endpoint);
    }

    post(endpoint, body) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    }
}
```

---

## 2. ⚙️ Backend (Capa de Lógica de Negocio)

### Tecnologías
- **PHP:** 8.2 FPM Alpine
- **Laravel:** 10.x Framework
- **Composer:** Gestor de dependencias
- **JWT:** Autenticación stateless

### Estructura de Carpetas (Laravel)
```
backend/
├── Dockerfile
├── php.ini
├── composer.json
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php
│   │   │   ├── VentaController.php
│   │   │   ├── ProductoController.php
│   │   │   └── InventarioController.php
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   └── CheckRole.php
│   │   └── Requests/
│   │       ├── VentaRequest.php
│   │       └── ProductoRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Venta.php
│   │   ├── Producto.php
│   │   └── Inventario.php
│   ├── Services/
│   │   ├── VentaService.php
│   │   ├── InventarioService.php
│   │   └── ReporteService.php
│   └── Repositories/ (futuro)
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── config/
│   ├── database.php
│   ├── jwt.php
│   └── cors.php
└── tests/
    ├── Feature/
    └── Unit/
```

### Responsabilidades
- ✅ Exponer API REST
- ✅ Autenticar y autorizar
- ✅ Validar datos de entrada
- ✅ Ejecutar lógica de negocio
- ✅ Comunicar con base de datos
- ✅ Manejar transacciones
- ✅ Registrar logs
- ❌ NO renderiza HTML (API only)

### Ejemplo de Controller
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaRequest;
use App\Services\VentaService;

class VentaController extends Controller
{
    public function __construct(
        private VentaService $ventaService
    ) {}

    public function index(Request $request)
    {
        $ventas = $this->ventaService->listar(
            $request->query('page', 1),
            $request->query('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => $ventas,
        ]);
    }

    public function store(VentaRequest $request)
    {
        $venta = $this->ventaService->crear($request->validated());

        return response()->json([
            'success' => true,
            'data' => $venta,
            'message' => 'Venta registrada exitosamente',
        ], 201);
    }

    public function show(int $id)
    {
        $venta = $this->ventaService->obtener($id);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $venta,
        ]);
    }
}
```

### Ejemplo de Service
```php
<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function crear(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // 1. Verificar stock
            foreach ($data['productos'] as $item) {
                $this->verificarStock($item['producto_id'], $item['cantidad']);
            }

            // 2. Crear venta
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => auth()->id(),
                'tipo_venta' => $data['tipo'],
                'subtotal' => $data['subtotal'],
                'descuento' => $data['descuento'] ?? 0,
                'total' => $data['total'],
            ]);

            // 3. Crear detalles
            foreach ($data['productos'] as $item) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 4. Los triggers de BD actualizan el inventario automáticamente

            return $venta->load('detalles.producto');
        });
    }

    private function verificarStock(int $productoId, float $cantidad): void
    {
        $disponible = Inventario::where('producto_id', $productoId)
            ->sum('stock_actual');

        if ($disponible < $cantidad) {
            throw new \Exception("Stock insuficiente para el producto ID {$productoId}");
        }
    }
}
```

### Ejemplo de Model (Eloquent)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'tipo_venta',
        'subtotal',
        'descuento',
        'total',
        'estado',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    // Accessor
    public function getTotalConDescuentoAttribute(): float
    {
        return $this->subtotal - $this->descuento;
    }
}
```

---

## 3. 🗄️ Base de Datos (Capa de Datos)

### Tecnología
- **PostgreSQL:** 15.x Alpine
- **Extensiones:** uuid-ossp, pgcrypto

### Estructura
```
25 Tablas organizadas en 8 módulos:
├── Autenticación (3 tablas)
│   ├── usuarios
│   ├── roles
│   └── usuario_roles
├── Productos (4 tablas)
│   ├── categorias
│   ├── marcas
│   ├── productos
│   └── producto_unidades
├── Inventario (3 tablas)
│   ├── almacenes
│   ├── inventario
│   └── movimientos_inventario
├── Compras (4 tablas)
│   ├── proveedores
│   ├── ordenes_compra
│   ├── compras
│   └── detalle_compras
├── Ventas (3 tablas)
│   ├── ventas
│   ├── detalle_ventas
│   └── devoluciones_venta
├── Clientes (3 tablas)
│   ├── clientes
│   ├── creditos_clientes
│   └── pagos_credito
├── Caja (2 tablas)
│   ├── arqueos_caja
│   └── movimientos_caja
└── Auditoría (1 tabla)
    └── logs_auditoria

43+ Índices de optimización
12 Triggers automáticos
8 Vistas materializadas
11+ Funciones de negocio
```

### Responsabilidades
- ✅ Almacenar datos persistentes
- ✅ Garantizar integridad referencial
- ✅ Ejecutar triggers automáticos
- ✅ Optimizar queries (índices)
- ✅ Calcular datos agregados (vistas)
- ✅ Ejecutar lógica compleja (funciones)

### Ejemplo de Tabla
```sql
CREATE TABLE ventas (
    id BIGSERIAL PRIMARY KEY,
    numero_venta VARCHAR(20) UNIQUE NOT NULL,
    cliente_id BIGINT NOT NULL,
    usuario_id BIGINT NOT NULL,
    tipo_venta VARCHAR(20) NOT NULL CHECK (tipo_venta IN ('contado', 'credito')),
    subtotal NUMERIC(12, 2) NOT NULL,
    descuento NUMERIC(12, 2) DEFAULT 0,
    total NUMERIC(12, 2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'completada',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_venta_cliente FOREIGN KEY (cliente_id)
        REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_venta_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE RESTRICT
);

CREATE INDEX idx_ventas_fecha ON ventas(fecha DESC);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);
```

### Ejemplo de Trigger
```sql
CREATE OR REPLACE FUNCTION actualizar_inventario_venta()
RETURNS TRIGGER AS $$
BEGIN
    -- Reducir stock cuando se crea detalle de venta
    UPDATE inventario
    SET stock_actual = stock_actual - NEW.cantidad
    WHERE producto_id = NEW.producto_id
      AND almacen_id = (SELECT almacen_principal FROM configuracion LIMIT 1);
    
    -- Registrar movimiento
    INSERT INTO movimientos_inventario (
        producto_id,
        tipo_movimiento,
        cantidad,
        referencia_tipo,
        referencia_id
    ) VALUES (
        NEW.producto_id,
        'salida',
        NEW.cantidad,
        'venta',
        NEW.venta_id
    );
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_actualizar_inventario_venta
    AFTER INSERT ON detalle_ventas
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_inventario_venta();
```

### Ejemplo de Vista
```sql
CREATE OR REPLACE VIEW vista_productos_mas_vendidos AS
SELECT 
    p.id,
    p.nombre,
    p.codigo,
    c.nombre as categoria,
    SUM(dv.cantidad) as total_vendido,
    SUM(dv.subtotal) as ingresos_totales,
    COUNT(DISTINCT v.id) as numero_ventas
FROM productos p
INNER JOIN detalle_ventas dv ON p.id = dv.producto_id
INNER JOIN ventas v ON dv.venta_id = v.id
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE v.fecha >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY p.id, p.nombre, p.codigo, c.nombre
ORDER BY total_vendido DESC
LIMIT 20;
```

---

## 4. 🐳 Docker (Orquestación)

### docker-compose.yml
```yaml
version: '3.8'

services:
  postgres_ferreteria:
    image: postgres:15-alpine
    container_name: ferreteria_postgres
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      TZ: America/La_Paz
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database:/database:ro
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - ferreteria_network

  backend:
    build: ./backend
    container_name: ferreteria_backend
    ports:
      - "8000:8000"
    volumes:
      - ./backend:/var/www/html
    depends_on:
      postgres_ferreteria:
        condition: service_healthy
    environment:
      DB_HOST: postgres_ferreteria
      DB_PORT: 5432
      DB_DATABASE: ${DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD}
    networks:
      - ferreteria_network

  frontend:
    build: ./frontend
    container_name: ferreteria_frontend
    ports:
      - "8080:80"
    volumes:
      - ./frontend:/usr/share/nginx/html:ro
    depends_on:
      - backend
    networks:
      - ferreteria_network

volumes:
  postgres_data:
    driver: local

networks:
  ferreteria_network:
    driver: bridge
```

### backend/Dockerfile
```dockerfile
FROM php:8.2-fpm-alpine

# Instalar dependencias
RUN apk add --no-cache \
    postgresql-dev \
    bash \
    git \
    curl

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar código
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto
EXPOSE 8000

# Comando de inicio
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### frontend/Dockerfile
```dockerfile
FROM nginx:alpine

# Copiar configuración Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copiar archivos estáticos
COPY . /usr/share/nginx/html

# Exponer puerto
EXPOSE 80

# Nginx se inicia automáticamente
```

---

## 📊 Resumen de Responsabilidades

| Componente | Responsabilidad Principal | No Debe Hacer |
|------------|---------------------------|---------------|
| **Frontend** | UI/UX, validación cliente | Lógica de negocio, acceso BD |
| **Backend** | API REST, lógica negocio | Renderizar HTML, lógica de UI |
| **Database** | Persistencia, integridad | Lógica de presentación |
| **Docker** | Orquestación, aislamiento | Lógica de aplicación |

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
