# 🔧 Configuración Inicial del Backend

## Índice
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Instalación de Laravel](#instalación-de-laravel)
3. [Configuración JWT](#configuración-jwt)
4. [Dependencias Adicionales](#dependencias-adicionales)
5. [Variables de Entorno](#variables-de-entorno)
6. [Comandos de Verificación](#comandos-de-verificación)

---

## Resumen Ejecutivo

Este documento detalla la configuración inicial del backend Laravel para el sistema de Ferretería Frenad.

### **Versiones Instaladas**
- **Laravel Framework:** 10.50.0
- **PHP:** 8.2-fpm-alpine
- **PostgreSQL:** 15-alpine
- **Composer:** Latest

### **Fecha de Configuración**
- Instalación Laravel: 29 de noviembre de 2025
- Configuración JWT: 29 de noviembre de 2025
- Estructura Modular: 4 de diciembre de 2025

---

## Instalación de Laravel

### **Comando de Instalación**
```bash
# Instalación con Docker Composer
docker run --rm \
  -v $(pwd)/backend:/app \
  -w /app \
  composer:latest \
  create-project laravel/laravel . "10.*" --prefer-dist
```

### **Estructura Instalada**
```
backend/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Models/
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
├── routes/
│   ├── api.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── storage/
├── tests/
├── vendor/
├── .env
├── .env.example
├── artisan
├── composer.json
└── composer.lock
```

### **Configuración Inicial de Base de Datos**

**Archivo:** `backend/.env`

```env
# Base de Datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=postgres_ferreteria
DB_PORT=5432
DB_DATABASE=ferreteria_frenad
DB_USERNAME=postgres
DB_PASSWORD=frenad_postgres_2024

# Zona Horaria
APP_TIMEZONE=America/La_Paz
TZ=America/La_Paz

# Locale
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
```

### **Generación de APP_KEY**

```bash
docker exec ferreteria_backend php artisan key:generate
```

**Resultado:**
```
Application key set successfully.
```

**Verificación:**
```bash
docker exec ferreteria_backend grep APP_KEY .env
```

**Salida esperada:**
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## Configuración JWT

### **Instalación del Paquete**

```bash
docker exec ferreteria_backend composer require tymon/jwt-auth
```

**Versión instalada:** `tymon/jwt-auth v2.2.1`

### **Publicar Configuración**

```bash
docker exec ferreteria_backend php artisan vendor:publish \
  --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

**Resultado:**
```
Copying file [vendor/tymon/jwt-auth/config/config.php] to [config/jwt.php] DONE
```

### **Generar Secret Key**

```bash
docker exec ferreteria_backend php artisan jwt:secret
```

**Resultado:**
```
jwt-auth secret [SQENp9F8p7K2TumTIg4Xr6WaPVQrYLZw5oRCGVwgFGM5HMNrY9Y7Jfiut2hnzryr] set successfully.
```

### **Archivo de Configuración JWT**

**Archivo:** `backend/config/jwt.php`

**Configuraciones principales:**

```php
return [
    // Clave secreta (desde .env)
    'secret' => env('JWT_SECRET'),

    // Claves públicas/privadas (para RSA/ECDSA)
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    // Tiempo de vida del token (minutos)
    'ttl' => env('JWT_TTL', 60),

    // Tiempo de refresh del token (minutos)
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    // Algoritmo de encriptación
    'algo' => env('JWT_ALGO', 'HS256'),

    // Headers requeridos
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    // Lock subject
    'lock_subject' => true,
];
```

### **Configuración en auth.php**

**Archivo:** `backend/config/auth.php`

```php
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => Modules\Auth\Models\Usuario::class,
    ],
],
```

---

## Dependencias Adicionales

### **1. Laravel IDE Helper**

**Propósito:** Autocompletado y documentación para IDEs

```bash
docker exec ferreteria_backend composer require --dev barryvdh/laravel-ide-helper
```

**Versión:** `v3.1.0`

**Uso:**
```bash
# Generar helpers para facades
docker exec ferreteria_backend php artisan ide-helper:generate

# Generar PHPDoc para modelos
docker exec ferreteria_backend php artisan ide-helper:models --nowrite

# Generar PHPStorm Meta
docker exec ferreteria_backend php artisan ide-helper:meta
```

---

### **2. Spatie Query Builder**

**Propósito:** Filtros, ordenamiento y búsqueda en API

```bash
docker exec ferreteria_backend composer require spatie/laravel-query-builder
```

**Versión:** `v6.3.6`

**Ejemplo de uso:**
```php
use Spatie\QueryBuilder\QueryBuilder;

public function index(Request $request)
{
    $productos = QueryBuilder::for(Producto::class)
        ->allowedFilters(['nombre', 'categoria_id', 'activo'])
        ->allowedSorts('nombre', 'precio_venta', 'created_at')
        ->allowedIncludes('categoria', 'marca', 'unidades')
        ->paginate($request->get('per_page', 15));

    return response()->json($productos);
}
```

**Consultas desde frontend:**
```javascript
// Filtrar por categoría y ordenar
fetch('/api/product/productos?filter[categoria_id]=5&sort=nombre')

// Incluir relaciones
fetch('/api/product/productos?include=categoria,marca')

// Paginación
fetch('/api/product/productos?page=2&per_page=20')
```

---

### **3. Spatie Laravel Fractal**

**Propósito:** Transformación de respuestas API

```bash
docker exec ferreteria_backend composer require spatie/laravel-fractal
```

**Versión:** `v6.3.2`

**Dependencia:** `league/fractal v0.20.2`

**Ejemplo de uso con Resources:**
```php
namespace Modules\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'precio' => [
                'venta' => (float) $this->precio_venta,
                'compra' => (float) $this->precio_compra,
                'formatted' => format_currency($this->precio_venta),
            ],
            'stock' => $this->inventario_sum_cantidad_actual ?? 0,
            'categoria' => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
            ],
            'created_at' => $this->creado_en->format('Y-m-d H:i:s'),
            'updated_at' => $this->actualizado_en->format('Y-m-d H:i:s'),
        ];
    }
}
```

---

## Variables de Entorno

### **Archivo .env Principal**

**Ubicación:** `backend/.env`

```env
# ============================================================================
# APLICACIÓN
# ============================================================================
APP_NAME="Ferretería Frenad"
APP_ENV=local
APP_KEY=base64:5D2uYM592p+3pJsIwA1ZI9u/DYgAsVoLTD2eo/7wWss=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/La_Paz

# ============================================================================
# BASE DE DATOS
# ============================================================================
DB_CONNECTION=pgsql
DB_HOST=postgres_ferreteria
DB_PORT=5432
DB_DATABASE=ferreteria_frenad
DB_USERNAME=postgres
DB_PASSWORD=frenad_postgres_2024

# ============================================================================
# JWT AUTHENTICATION
# ============================================================================
JWT_SECRET=SQENp9F8p7K2TumTIg4Xr6WaPVQrYLZw5oRCGVwgFGM5HMNrY9Y7Jfiut2hnzryr
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

# ============================================================================
# LOCALIZACIÓN
# ============================================================================
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
TZ=America/La_Paz

# ============================================================================
# LOGGING
# ============================================================================
LOG_CHANNEL=stack
LOG_LEVEL=debug

# ============================================================================
# CACHE Y SESSION
# ============================================================================
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ============================================================================
# QUEUE
# ============================================================================
QUEUE_CONNECTION=sync
```

### **Variables Docker Compose**

**Ubicación:** `.env` (raíz del proyecto)

```env
# Backend
BACKEND_PORT=8000
BACKEND_HOST=0.0.0.0

# Frontend
FRONTEND_PORT=8080
FRONTEND_HOST=0.0.0.0

# PostgreSQL
DB_DATABASE=ferreteria_frenad
DB_USERNAME=postgres
DB_PASSWORD=frenad_postgres_2024

# Seguridad (Usuario no-root)
USER_ID=1000
GROUP_ID=1000

# PgAdmin
PGADMIN_DEFAULT_EMAIL=admin@frenad.local
PGADMIN_DEFAULT_PASSWORD=admin123
PGADMIN_PORT=5050
```

---

## Comandos de Verificación

### **1. Verificar Laravel**

```bash
# Versión de Laravel
docker exec ferreteria_backend php artisan --version
# Salida: Laravel Framework 10.50.0

# Verificar que la aplicación funciona
docker exec ferreteria_backend php artisan about
```

---

### **2. Verificar Conexión a Base de Datos**

```bash
docker exec ferreteria_backend php artisan tinker

# En Tinker:
>>> DB::connection()->getPdo();
# Debe retornar objeto PDO

>>> DB::select('SELECT version()');
# Debe retornar versión de PostgreSQL

>>> exit
```

---

### **3. Verificar JWT**

```bash
docker exec ferreteria_backend php artisan tinker

# Verificar que JWT está instalado
>>> class_exists('Tymon\JWTAuth\JWTAuth');
# Debe retornar: true

>>> config('jwt.secret');
# Debe retornar: tu JWT_SECRET

>>> exit
```

---

### **4. Verificar Autoload de Módulos**

```bash
docker exec ferreteria_backend composer dump-autoload

# Verificar clases de módulos
docker exec ferreteria_backend php artisan tinker

>>> class_exists('Modules\Shared\Middleware\CheckRole');
# Debe retornar: true

>>> class_exists('Modules\Shared\Traits\HasAudit');
# Debe retornar: true

>>> exit
```

---

### **5. Verificar Helpers Globales**

```bash
docker exec ferreteria_backend php artisan tinker

>>> format_currency(1234.56);
# Debe retornar: "Bs 1,234.56"

>>> format_bolivian_date(now());
# Debe retornar fecha formateada

>>> generate_voucher_number('VTA');
# Debe retornar: "VTA-20241204-xxxx"

>>> exit
```

---

### **6. Limpiar Cachés**

```bash
# Limpiar caché de configuración
docker exec ferreteria_backend php artisan config:clear

# Limpiar caché de rutas
docker exec ferreteria_backend php artisan route:clear

# Limpiar caché de vistas
docker exec ferreteria_backend php artisan view:clear

# Limpiar caché de aplicación
docker exec ferreteria_backend php artisan cache:clear

# Limpiar TODO
docker exec ferreteria_backend php artisan optimize:clear
```

---

### **7. Verificar Rutas Registradas**

```bash
docker exec ferreteria_backend php artisan route:list
```

**Salida esperada:**
```
GET|HEAD   / .......................................................
POST       _ignition/execute-solution .............................
GET|HEAD   _ignition/health-check .................................
POST       _ignition/update-config ................................
GET|HEAD   api/user ...............................................
GET|HEAD   sanctum/csrf-cookie ....................................
```

---

## Troubleshooting

### **Problema: APP_KEY no generada**

```bash
docker exec ferreteria_backend php artisan key:generate
```

---

### **Problema: JWT_SECRET no generada**

```bash
docker exec ferreteria_backend php artisan jwt:secret
```

---

### **Problema: Permisos en storage/**

```bash
docker exec ferreteria_backend chmod -R 775 storage bootstrap/cache
```

---

### **Problema: Autoload no actualizado**

```bash
docker exec ferreteria_backend composer dump-autoload
docker exec ferreteria_backend php artisan config:clear
```

---

### **Problema: Contenedor no levanta**

```bash
# Ver logs
docker logs ferreteria_backend

# Reiniciar contenedores
docker-compose down
docker-compose up -d
```

---

## Checklist de Configuración

- [✅] Laravel 10.50.0 instalado
- [✅] APP_KEY generada
- [✅] Conexión PostgreSQL configurada
- [✅] JWT instalado y configurado (v2.2.1)
- [✅] JWT_SECRET generada
- [✅] Dependencias adicionales instaladas:
  - [✅] Laravel IDE Helper (dev)
  - [✅] Spatie Query Builder
  - [✅] Spatie Laravel Fractal
- [✅] Variables de entorno configuradas
- [✅] Zona horaria Bolivia (America/La_Paz)
- [✅] Locale español (es)
- [✅] Autoload de módulos configurado
- [✅] Cachés limpiados
- [✅] Rutas base registradas

---

## Referencias

- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)
- [Spatie Query Builder](https://spatie.be/docs/laravel-query-builder/)
- [Spatie Fractal](https://github.com/spatie/laravel-fractal)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
