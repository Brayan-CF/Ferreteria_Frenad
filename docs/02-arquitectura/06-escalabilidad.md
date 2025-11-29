# 📈 Escalabilidad y Performance

Estrategias para optimizar y escalar el sistema.

---

## 1. 🗄️ Optimización de Base de Datos

### Índices Estratégicos

**Tablas con más queries**
```sql
-- ventas: buscar por fecha, cliente
CREATE INDEX idx_ventas_fecha ON ventas(fecha DESC);
CREATE INDEX idx_ventas_cliente ON ventas(cliente_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);

-- productos: buscar por nombre, código
CREATE INDEX idx_productos_nombre ON productos(nombre);
CREATE INDEX idx_productos_codigo ON productos(codigo);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);

-- inventario: consultas frecuentes
CREATE INDEX idx_inventario_producto ON inventario(producto_id);
CREATE INDEX idx_inventario_almacen ON inventario(almacen_id);
CREATE INDEX idx_inventario_stock ON inventario(stock_actual);
```

**Índices Compuestos**
```sql
-- Búsquedas combinadas
CREATE INDEX idx_productos_categoria_activo 
  ON productos(categoria_id, activo) 
  WHERE activo = true;

-- Reportes de ventas
CREATE INDEX idx_ventas_fecha_estado 
  ON ventas(fecha DESC, estado) 
  WHERE estado = 'completada';
```

### Vistas Materializadas

**Performance en reportes complejos**
```sql
CREATE MATERIALIZED VIEW mv_productos_mas_vendidos AS
SELECT 
    p.id,
    p.nombre,
    p.codigo,
    SUM(dv.cantidad) as total_vendido,
    SUM(dv.subtotal) as ingresos
FROM productos p
INNER JOIN detalle_ventas dv ON p.id = dv.producto_id
INNER JOIN ventas v ON dv.venta_id = v.id
WHERE v.fecha >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY p.id, p.nombre, p.codigo
ORDER BY total_vendido DESC
LIMIT 50;

-- Índice en vista materializada
CREATE INDEX ON mv_productos_mas_vendidos(total_vendido DESC);

-- Refrescar diariamente (cron)
REFRESH MATERIALIZED VIEW mv_productos_mas_vendidos;
```

### Query Optimization

**N+1 Problem Solution**
```php
// ❌ BAD: 1 query + N queries
$ventas = Venta::all();
foreach ($ventas as $venta) {
    echo $venta->cliente->nombre; // Query por cada venta
}

// ✅ GOOD: 2 queries total
$ventas = Venta::with('cliente')->get();
foreach ($ventas as $venta) {
    echo $venta->cliente->nombre; // Sin query adicional
}
```

**Paginación**
```php
// ❌ BAD: Cargar todo
$productos = Producto::all(); // Miles de registros

// ✅ GOOD: Paginar
$productos = Producto::paginate(15); // Solo 15 por página
```

**Select Específico**
```php
// ❌ BAD: SELECT *
$productos = Producto::all();

// ✅ GOOD: Solo campos necesarios
$productos = Producto::select('id', 'nombre', 'precio')->get();
```

---

## 2. ⚡ Cache Strategies

### Redis (Futuro)

**Implementación**
```yaml
# docker-compose.yml (agregar)
services:
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    networks:
      - ferreteria_network
```

**Uso en Laravel**
```php
use Illuminate\Support\Facades\Cache;

// Cachear productos por 1 hora
$productos = Cache::remember('productos.activos', 3600, function () {
    return Producto::where('activo', true)->get();
});

// Invalidar cache al actualizar
public function update(Request $request, $id)
{
    $producto = Producto::findOrFail($id);
    $producto->update($request->all());
    
    Cache::forget('productos.activos'); // Limpiar cache
    
    return response()->json($producto);
}

// Cache tags (Laravel)
Cache::tags(['productos', 'catalogo'])->put('lista', $productos, 3600);
Cache::tags(['productos'])->flush(); // Limpiar todos los productos
```

### Query Cache (PostgreSQL)

**Prepared Statements**
```php
// Laravel automáticamente usa prepared statements
$ventas = DB::select('SELECT * FROM ventas WHERE fecha >= ?', [$fecha]);
// PostgreSQL cachea el plan de ejecución
```

---

## 3. 🚀 Escalabilidad Horizontal

### Load Balancer (Nginx)

**Múltiples instancias de backend**
```nginx
upstream backend_pool {
    least_conn;  # Algoritmo: menos conexiones
    
    server backend_1:8000 weight=3;
    server backend_2:8000 weight=2;
    server backend_3:8000 weight=1 backup;  # Backup
}

server {
    listen 80;
    
    location /api {
        proxy_pass http://backend_pool;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

**Docker Compose con réplicas**
```yaml
services:
  backend:
    image: ferreteria_backend
    deploy:
      replicas: 3  # 3 instancias
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
```

### Database Read Replicas

**Master-Slave Replication**
```yaml
# docker-compose.yml
services:
  postgres_master:
    image: postgres:15-alpine
    environment:
      POSTGRES_REPLICATION_MODE: master
    volumes:
      - master_data:/var/lib/postgresql/data

  postgres_replica:
    image: postgres:15-alpine
    environment:
      POSTGRES_REPLICATION_MODE: slave
      POSTGRES_MASTER_HOST: postgres_master
    volumes:
      - replica_data:/var/lib/postgresql/data
```

**Laravel: Read/Write Splitting**
```php
// config/database.php
'pgsql' => [
    'read' => [
        'host' => ['postgres_replica_1', 'postgres_replica_2'],
    ],
    'write' => [
        'host' => ['postgres_master'],
    ],
    'driver' => 'pgsql',
    'database' => 'ferreteria_frenad',
    'username' => 'postgres',
    'password' => env('DB_PASSWORD'),
];

// Uso automático
$producto = Producto::find($id);  // Lee de replica
$producto->update([...]);         // Escribe en master
```

---

## 4. 📊 Monitoreo y Métricas

### Laravel Telescope (Dev)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Monitorea:**
- Requests HTTP
- Queries SQL
- Jobs/Queues
- Exceptions
- Cache hits/misses
- Mail sent

### PostgreSQL Statistics

```sql
-- Queries lentas
SELECT 
    query,
    calls,
    total_time,
    mean_time,
    max_time
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;

-- Índices no usados
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan
FROM pg_stat_user_indexes
WHERE idx_scan = 0
ORDER BY tablename;

-- Tamaño de tablas
SELECT 
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### Docker Stats

```bash
# Recursos en tiempo real
docker stats

# Logs con timestamp
docker-compose logs -f --timestamps

# Healthchecks
docker ps --format "table {{.Names}}\t{{.Status}}"
```

---

## 5. 🎯 Optimización Frontend

### Lazy Loading

```javascript
// Cargar componentes solo cuando se necesitan
const VentasModule = {
    async load() {
        const module = await import('./modules/ventas.js');
        return module.default;
    }
};

// Uso
document.getElementById('link-ventas').addEventListener('click', async () => {
    const ventasModule = await VentasModule.load();
    ventasModule.init();
});
```

### Debouncing (Búsquedas)

```javascript
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Uso en búsqueda de productos
const searchInput = document.getElementById('search');
searchInput.addEventListener('input', debounce(async (e) => {
    const results = await api.get(`/productos?search=${e.target.value}`);
    renderResults(results);
}, 300)); // Esperar 300ms después de dejar de escribir
```

### Pagination en Frontend

```javascript
class PaginatedList {
    constructor(apiEndpoint) {
        this.endpoint = apiEndpoint;
        this.currentPage = 1;
        this.perPage = 15;
    }

    async loadPage(page) {
        const response = await fetch(
            `${this.endpoint}?page=${page}&per_page=${this.perPage}`
        );
        const data = await response.json();
        return data;
    }

    renderPagination(total, current) {
        const totalPages = Math.ceil(total / this.perPage);
        // Renderizar botones de paginación
    }
}
```

### Compresión

**Nginx gzip**
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types 
    text/plain 
    text/css 
    text/javascript 
    application/json 
    application/javascript 
    application/x-javascript 
    text/xml 
    application/xml;
```

---

## 6. 🔄 Background Jobs (Futuro)

### Laravel Queues

**Procesos pesados en background**
```php
// app/Jobs/GenerarReporteMensual.php
class GenerarReporteMensual implements ShouldQueue
{
    public function handle()
    {
        $data = $this->calcularDatosMes();
        $pdf = PDF::loadView('reportes.mensual', $data);
        $pdf->save(storage_path('reportes/mensual_'.date('Y-m').'.pdf'));
    }
}

// Despachar job
GenerarReporteMensual::dispatch()->onQueue('reportes');

// Worker
php artisan queue:work --queue=reportes
```

**Redis como queue driver**
```php
// config/queue.php
'default' => 'redis',
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
    ],
],
```

---

## 7. 📦 CDN para Estáticos (Producción)

**Separar assets**
```nginx
# Servir assets desde CDN
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    
    # En producción, proxy a CDN
    # proxy_pass https://cdn.ferreteria-frenad.com;
}
```

---

## 8. 🎯 Límites de Recursos

### Docker Resource Limits

```yaml
services:
  backend:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M

  postgres:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
```

### PostgreSQL Connection Pooling

```php
// config/database.php
'pgsql' => [
    'pool' => [
        'min' => 2,
        'max' => 20,
    ],
],
```

**PgBouncer (Futuro)**
```yaml
services:
  pgbouncer:
    image: pgbouncer/pgbouncer
    environment:
      DATABASES_HOST: postgres_master
      DATABASES_DBNAME: ferreteria_frenad
      PGBOUNCER_MAX_CLIENT_CONN: 100
      PGBOUNCER_DEFAULT_POOL_SIZE: 25
```

---

## 9. 📊 Benchmarks y Metas

### Performance Targets

| Métrica | Target | Actual |
|---------|--------|--------|
| Response time API | < 200ms | ~150ms |
| Database queries | < 50ms | ~30ms |
| Page load | < 2s | ~1.5s |
| Concurrent users | 50+ | TBD |
| Uptime | 99.5% | TBD |

### Herramientas de Medición

```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost:8000/api/productos

# Artillery (Node.js)
artillery quick --count 10 -n 20 http://localhost:8000/api/ventas

# PostgreSQL EXPLAIN
EXPLAIN ANALYZE SELECT * FROM ventas WHERE fecha >= '2025-01-01';
```

---

## 10. 🚀 Roadmap de Escalabilidad

### Fase 1 (Actual)
- [x] Índices básicos
- [x] Paginación
- [x] Query optimization
- [x] Docker containerization

### Fase 2 (3-6 meses)
- [ ] Redis cache
- [ ] Laravel Queues
- [ ] Read replicas
- [ ] CDN para assets

### Fase 3 (6-12 meses)
- [ ] Load balancer
- [ ] Múltiples instancias backend
- [ ] PgBouncer
- [ ] Monitoring completo (Grafana)

### Fase 4 (1+ años)
- [ ] Kubernetes orchestration
- [ ] Auto-scaling
- [ ] Multi-region deployment
- [ ] Disaster recovery

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
