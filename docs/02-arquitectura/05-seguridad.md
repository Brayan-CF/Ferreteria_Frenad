# 🔒 Seguridad del Sistema

Consideraciones y mejores prácticas de seguridad implementadas.

---

## 1. 🔐 Autenticación

### JWT (JSON Web Tokens)

**Flujo**
```
1. Login → Backend verifica credenciales
2. Backend genera JWT firmado
3. Frontend guarda token (localStorage)
4. Requests subsecuentes incluyen token en header
5. Backend verifica firma del token
```

**Estructura del Token**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
eyJ1c2VyX2lkIjoxLCJlbWFpbCI6ImFkbWluQGZlcnJldGVyaWEuY29tIiwiZXhwIjoxNzAxMjA2NDAwfQ.
SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c

Header.Payload.Signature
```

**Ventajas**
- ✅ Stateless (no sesiones en servidor)
- ✅ Escalable (múltiples servidores)
- ✅ Seguro (firmado con clave secreta)
- ✅ Expirable (TTL configurable)

**Implementación**
```php
// config/jwt.php
return [
    'secret' => env('JWT_SECRET'),
    'ttl' => 60, // minutos
    'refresh_ttl' => 20160, // 2 semanas
];

// Generar token
$token = auth()->attempt($credentials);

// Verificar token
$user = auth()->userOrFail();

// Refrescar token
$newToken = auth()->refresh();
```

---

## 2. 🛡️ Autorización (RBAC)

### Roles y Permisos

**Tabla de Roles**
```sql
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    permisos JSONB
);

-- Roles predefinidos
INSERT INTO roles (nombre, descripcion, permisos) VALUES
('administrador', 'Acceso total', '["*"]'),
('cajero', 'Ventas y caja', '["ventas.*", "caja.*"]'),
('almacenero', 'Inventario', '["inventario.*", "productos.read"]'),
('auditor', 'Solo lectura', '["*.read"]');
```

**Middleware de Autorización**
```php
// app/Http/Middleware/CheckRole.php
class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        return $next($request);
    }
}

// Uso en rutas
Route::middleware(['auth:api', 'role:administrador'])->group(function () {
    Route::apiResource('usuarios', UserController::class);
});

Route::middleware(['auth:api', 'role:cajero,administrador'])->group(function () {
    Route::apiResource('ventas', VentaController::class);
});
```

**Modelo User**
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'usuario_roles');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('nombre', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->where('permisos', '@>', json_encode([$permission]))->exists();
    }
}
```

---

## 3. 🔑 Gestión de Contraseñas

### Hashing (bcrypt)

**Almacenamiento**
```php
use Illuminate\Support\Facades\Hash;

// Al registrar usuario
$user = User::create([
    'nombre' => 'Admin',
    'email' => 'admin@ferreteria.com',
    'password' => Hash::make('password123'), // Hasheado
]);

// Al verificar login
if (Hash::check($request->password, $user->password)) {
    // Contraseña correcta
}
```

**Características bcrypt**
- ✅ Irreversible (no se puede descifrar)
- ✅ Salt aleatorio por cada password
- ✅ Computacionalmente costoso (previene brute force)
- ✅ 60 caracteres de salida

### Políticas de Contraseñas

```php
// config/password.php
return [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special_chars' => true,
];

// Validación
class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',      // minúscula
                'regex:/[A-Z]/',      // mayúscula
                'regex:/[0-9]/',      // número
                'regex:/[@$!%*#?&]/', // especial
                'confirmed',
            ],
        ];
    }
}
```

### Recuperación de Contraseña

```php
// Enviar token de reset
$token = Str::random(60);
PasswordReset::create([
    'email' => $user->email,
    'token' => Hash::make($token),
    'expires_at' => now()->addHour(),
]);
// Enviar $token por email

// Verificar y cambiar
$reset = PasswordReset::where('email', $email)
    ->where('expires_at', '>', now())
    ->first();

if ($reset && Hash::check($token, $reset->token)) {
    $user->update(['password' => Hash::make($newPassword)]);
    $reset->delete();
}
```

---

## 4. 🚫 Prevención de Ataques

### SQL Injection

**✅ Eloquent (Seguro)**
```php
// Usa prepared statements automáticamente
$user = User::where('email', $email)->first();
$productos = Producto::whereIn('id', $ids)->get();
```

**✅ Query Builder con Bindings**
```php
DB::select('SELECT * FROM usuarios WHERE email = ?', [$email]);
DB::insert('INSERT INTO productos (nombre) VALUES (?)', [$nombre]);
```

**❌ SQL Directo (INSEGURO)**
```php
// NUNCA HACER ESTO
DB::select("SELECT * FROM usuarios WHERE email = '{$email}'");
```

### XSS (Cross-Site Scripting)

**Backend: Validación**
```php
class ProductoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    // Sanitización
    protected function prepareForValidation()
    {
        $this->merge([
            'nombre' => strip_tags($this->nombre),
            'descripcion' => strip_tags($this->descripcion, '<b><i><p>'),
        ]);
    }
}
```

**Frontend: Escapar HTML**
```javascript
function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Uso
const nombreProducto = escapeHTML(userInput);
element.innerHTML = `<h3>${nombreProducto}</h3>`;
```

### CSRF (Cross-Site Request Forgery)

**API REST: JWT en lugar de CSRF**
```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'api/*', // Rutas API usan JWT
];
```

### Clickjacking

**Nginx: X-Frame-Options**
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
```

### Rate Limiting

**Prevenir Brute Force**
```php
// routes/api.php
Route::middleware('throttle:5,1')->post('/auth/login', ...);
// Máximo 5 intentos por minuto

Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
    // 60 requests por minuto para usuarios autenticados
});
```

---

## 5. 🔒 Seguridad de Base de Datos

### Conexión Segura

```yaml
# docker-compose.yml
postgres_ferreteria:
  environment:
    POSTGRES_PASSWORD: ${DB_PASSWORD} # Desde .env
  # NO exponer puerto en producción
  # ports:
  #   - "5432:5432"
```

### Usuario con Permisos Limitados

```sql
-- Crear usuario de aplicación (no usar postgres root)
CREATE USER ferreteria_app WITH PASSWORD 'strong_password';

-- Dar solo permisos necesarios
GRANT CONNECT ON DATABASE ferreteria_frenad TO ferreteria_app;
GRANT USAGE ON SCHEMA public TO ferreteria_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ferreteria_app;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA public TO ferreteria_app;

-- NO dar permisos DDL (CREATE, DROP, ALTER)
```

### Backup Encriptado

```bash
# Backup con compresión
docker exec ferreteria_postgres pg_dump -U postgres -Fc ferreteria_frenad > backup.dump

# Encriptar backup
gpg --symmetric --cipher-algo AES256 backup.dump

# Desencriptar y restaurar
gpg --decrypt backup.dump.gpg > backup.dump
docker exec -i ferreteria_postgres pg_restore -U postgres -d ferreteria_frenad < backup.dump
```

---

## 6. 🌐 Seguridad de Red

### HTTPS (Producción)

**Nginx con SSL**
```nginx
server {
    listen 443 ssl http2;
    server_name ferreteria-frenad.com;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Redirigir HTTP a HTTPS
    server {
        listen 80;
        return 301 https://$server_name$request_uri;
    }
}
```

### CORS Restrictivo

```php
// config/cors.php
return [
    'allowed_origins' => [
        'https://ferreteria-frenad.com',
        // NO usar '*' en producción
    ],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
];
```

### Firewall Docker

```yaml
# docker-compose.yml
services:
  backend:
    networks:
      - internal_network  # Red interna solo

  postgres:
    networks:
      - internal_network  # No expuesto externamente

  frontend:
    networks:
      - external_network  # Expuesto al público
      - internal_network  # Puede hablar con backend

networks:
  external_network:
  internal_network:
    internal: true  # No accesible desde fuera
```

---

## 7. 📝 Auditoría y Logging

### Logs de Auditoría

```php
// app/Models/LogAuditoria.php
class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';

    public static function registrar(string $accion, string $tabla, $datos = null)
    {
        self::create([
            'usuario_id' => auth()->id(),
            'accion' => $accion,  // CREATE, UPDATE, DELETE
            'tabla' => $tabla,
            'datos' => json_encode($datos),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// Uso en controllers
public function destroy($id)
{
    $producto = Producto::findOrFail($id);
    
    LogAuditoria::registrar('DELETE', 'productos', $producto->toArray());
    
    $producto->delete();
}
```

### Logs Sensibles

```php
// NO loggear información sensible
Log::info('Login exitoso', [
    'user_id' => $user->id,
    // ❌ 'password' => $request->password,
]);

// Enmascarar datos sensibles
Log::info('Pago procesado', [
    'card_number' => '****' . substr($cardNumber, -4),
]);
```

---

## 8. 🔄 Actualizaciones y Mantenimiento

### Dependencias Actualizadas

```bash
# Backend
composer update --with-dependencies

# Frontend (si usa npm)
npm audit fix

# Docker images
docker-compose pull
docker-compose up -d --build
```

### Monitoreo de Vulnerabilidades

```bash
# PHP
composer audit

# PostgreSQL - Ver changelog
docker exec ferreteria_postgres psql --version
```

---

## 📊 Checklist de Seguridad

### Desarrollo
- [x] JWT para autenticación
- [x] Contraseñas hasheadas (bcrypt)
- [x] Validación de entrada
- [x] Eloquent ORM (previene SQL injection)
- [x] Rate limiting
- [x] Logs de auditoría

### Producción (Pendiente)
- [ ] HTTPS con certificado SSL
- [ ] CORS restrictivo
- [ ] Firewall configurado
- [ ] Backups automáticos encriptados
- [ ] Monitoreo de vulnerabilidades
- [ ] Passwords fuertes en .env
- [ ] No exponer puertos innecesarios
- [ ] Actualizaciones regulares

---

**Última actualización:** 29 de noviembre de 2025  
**Versión:** 1.0.0
