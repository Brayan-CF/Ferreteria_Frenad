# 🔐 Módulo Auth - Autenticación y Autorización

## Índice
1. [Descripción General](#descripción-general)
2. [Estructura del Módulo](#estructura-del-módulo)
3. [Modelos](#modelos)
4. [Endpoints](#endpoints)
5. [Servicios](#servicios)
6. [Validaciones](#validaciones)
7. [Recursos (Transformers)](#recursos-transformers)
8. [Testing](#testing)

---

## Descripción General

El módulo **Auth** es responsable de la autenticación y autorización de usuarios en el sistema. Implementa autenticación basada en **JWT (JSON Web Tokens)** y gestión de roles.

### **Responsabilidades**
- ✅ Autenticación de usuarios (Login/Logout)
- ✅ Renovación de tokens JWT
- ✅ Gestión de usuarios
- ✅ Gestión de roles y permisos
- ✅ Asignación de roles a usuarios
- ✅ Auditoría de accesos

### **Ruta Base**
```
/api/auth
```

---

## Estructura del Módulo

```
backend/app/Modules/Auth/
├── Controllers/
│   ├── AuthController.php
│   ├── UserController.php
│   └── RoleController.php
├── Models/
│   ├── Usuario.php
│   ├── Rol.php
│   └── UsuarioRol.php
├── Services/
│   ├── AuthService.php
│   ├── UserService.php
│   └── RoleService.php
├── Requests/
│   ├── LoginRequest.php
│   ├── StoreUserRequest.php
│   ├── UpdateUserRequest.php
│   └── AssignRoleRequest.php
├── Resources/
│   ├── UserResource.php
│   └── RoleResource.php
├── Routes/
│   └── api.php
├── Tests/
│   ├── LoginTest.php
│   ├── LogoutTest.php
│   ├── RefreshTest.php
│   └── UserManagementTest.php
├── README.md
└── module.json
```

---

## Modelos

### **Usuario**

**Tabla:** `usuarios`

```php
<?php

namespace Modules\Auth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Usuario extends Authenticatable implements JWTSubject
{
    use Notifiable, HasAudit, HasBolivianTimestamps;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'direccion',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in JWT subject claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array for JWT custom claims.
     */
    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'nombre' => $this->nombre,
        ];
    }

    /**
     * Relación muchos a muchos con roles
     */
    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_roles',
            'usuario_id',
            'rol_id'
        )->withTimestamps();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        $userRoles = $this->roles->pluck('nombre')->toArray();

        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados
     */
    public function hasAllRoles($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        $userRoles = $this->roles->pluck('nombre')->toArray();

        foreach ($roles as $role) {
            if (!in_array($role, $userRoles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mutator: Encriptar password al asignar
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
```

---

### **Rol**

**Tabla:** `roles`

```php
<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Rol extends Model
{
    use HasAudit, HasBolivianTimestamps;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación muchos a muchos con usuarios
     */
    public function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuario_roles',
            'rol_id',
            'usuario_id'
        )->withTimestamps();
    }
}
```

---

### **UsuarioRol (Pivot)**

**Tabla:** `usuario_roles`

```php
<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Shared\Traits\HasBolivianTimestamps;

class UsuarioRol extends Pivot
{
    use HasBolivianTimestamps;

    protected $table = 'usuario_roles';

    protected $fillable = [
        'usuario_id',
        'rol_id',
    ];
}
```

---

## Endpoints

### **1. Login**

```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "admin@frenad.com",
    "password": "password123"
}
```

**Respuesta 200 OK:**
```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "user": {
            "id": 1,
            "nombre": "Administrador",
            "email": "admin@frenad.com",
            "roles": [
                {
                    "id": 1,
                    "nombre": "admin",
                    "descripcion": "Administrador del sistema"
                }
            ]
        },
        "token": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
            "token_type": "bearer",
            "expires_in": 3600
        }
    }
}
```

---

### **2. Logout**

```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Respuesta 200 OK:**
```json
{
    "success": true,
    "message": "Sesión cerrada exitosamente"
}
```

---

### **3. Refresh Token**

```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

**Respuesta 200 OK:**
```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

---

### **4. Usuario Autenticado (Me)**

```http
GET /api/auth/me
Authorization: Bearer {token}
```

**Respuesta 200 OK:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nombre": "Administrador",
        "email": "admin@frenad.com",
        "telefono": "77123456",
        "direccion": "Av. 6 de Agosto #123",
        "activo": true,
        "roles": [
            {
                "id": 1,
                "nombre": "admin",
                "descripcion": "Administrador del sistema"
            }
        ],
        "created_at": "2025-01-15 10:00:00"
    }
}
```

---

### **5. Listar Usuarios**

```http
GET /api/auth/usuarios?page=1&per_page=15&filter[activo]=1&sort=-created_at
Authorization: Bearer {token}
```

**Middlewares:** `auth:api`, `role:admin`

**Respuesta 200 OK:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Juan Pérez",
            "email": "juan@frenad.com",
            "roles": ["vendedor"],
            "activo": true,
            "created_at": "2025-01-15 10:00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 23
    }
}
```

---

### **6. Crear Usuario**

```http
POST /api/auth/usuarios
Authorization: Bearer {token}
Content-Type: application/json

{
    "nombre": "Carlos Ramírez",
    "email": "carlos@frenad.com",
    "password": "password123",
    "telefono": "77654321",
    "direccion": "Calle Comercio #456",
    "activo": true,
    "roles": [2] // IDs de roles
}
```

**Middlewares:** `auth:api`, `role:admin`

**Respuesta 201 Created:**
```json
{
    "success": true,
    "message": "Usuario creado exitosamente",
    "data": {
        "id": 8,
        "nombre": "Carlos Ramírez",
        "email": "carlos@frenad.com",
        "roles": [
            {
                "id": 2,
                "nombre": "vendedor"
            }
        ]
    }
}
```

---

### **7. Actualizar Usuario**

```http
PUT /api/auth/usuarios/8
Authorization: Bearer {token}
Content-Type: application/json

{
    "nombre": "Carlos Ramírez López",
    "telefono": "77999888"
}
```

**Middlewares:** `auth:api`, `role:admin`

---

### **8. Eliminar Usuario**

```http
DELETE /api/auth/usuarios/8
Authorization: Bearer {token}
```

**Middlewares:** `auth:api`, `role:admin`

**Respuesta 204 No Content**

---

### **9. Asignar Rol a Usuario**

```http
POST /api/auth/usuarios/8/asignar-rol
Authorization: Bearer {token}
Content-Type: application/json

{
    "rol_id": 3
}
```

**Middlewares:** `auth:api`, `role:admin`

---

### **10. Remover Rol de Usuario**

```http
DELETE /api/auth/usuarios/8/remover-rol
Authorization: Bearer {token}
Content-Type: application/json

{
    "rol_id": 3
}
```

**Middlewares:** `auth:api`, `role:admin`

---

## Servicios

### **AuthService**

```php
<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $credentials)
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new \Exception('Credenciales inválidas', 401);
        }

        $user = auth()->user();

        if (!$user->activo) {
            auth()->logout();
            throw new \Exception('Usuario desactivado', 403);
        }

        Log::info('Usuario autenticado', [
            'usuario_id' => $user->id,
            'email' => $user->email,
        ]);

        return [
            'user' => $user->load('roles'),
            'token' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ],
        ];
    }

    public function logout()
    {
        $userId = auth()->id();
        
        auth()->logout();

        Log::info('Usuario cerró sesión', [
            'usuario_id' => $userId,
        ]);

        return true;
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh();

            return [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ];
        } catch (\Exception $e) {
            throw new \Exception('No se pudo renovar el token', 401);
        }
    }

    public function me()
    {
        return auth()->user()->load('roles');
    }
}
```

---

### **UserService**

```php
<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;
use Modules\Shared\Exceptions\ResourceNotFoundException;
use Modules\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;

class UserService
{
    public function getAll($request)
    {
        return QueryBuilder::for(Usuario::class)
            ->with('roles')
            ->allowedFilters(['nombre', 'email', 'activo'])
            ->allowedSorts('nombre', 'email', 'created_at')
            ->paginate($request->get('per_page', 15));
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $usuario = Usuario::create($data);

            if (!empty($roles)) {
                $usuario->roles()->attach($roles);
            }

            Log::info('Usuario creado', [
                'usuario_id' => $usuario->id,
                'creado_por' => auth()->id(),
            ]);

            return $usuario->load('roles');
        });
    }

    public function update($id, array $data)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            throw new ResourceNotFoundException('Usuario');
        }

        // No permitir que el usuario se desactive a sí mismo
        if ($id == auth()->id() && isset($data['activo']) && !$data['activo']) {
            throw new BusinessRuleException('No puedes desactivarte a ti mismo');
        }

        $usuario->update($data);

        Log::info('Usuario actualizado', [
            'usuario_id' => $usuario->id,
            'actualizado_por' => auth()->id(),
        ]);

        return $usuario->load('roles');
    }

    public function delete($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            throw new ResourceNotFoundException('Usuario');
        }

        if ($id == auth()->id()) {
            throw new BusinessRuleException('No puedes eliminarte a ti mismo');
        }

        $usuario->delete();

        Log::warning('Usuario eliminado', [
            'usuario_id' => $id,
            'eliminado_por' => auth()->id(),
        ]);

        return true;
    }

    public function assignRole($userId, $roleId)
    {
        $usuario = Usuario::find($userId);
        $rol = Rol::find($roleId);

        if (!$usuario) {
            throw new ResourceNotFoundException('Usuario');
        }

        if (!$rol) {
            throw new ResourceNotFoundException('Rol');
        }

        if ($usuario->roles()->where('rol_id', $roleId)->exists()) {
            throw new BusinessRuleException('El usuario ya tiene este rol');
        }

        $usuario->roles()->attach($roleId);

        Log::info('Rol asignado a usuario', [
            'usuario_id' => $userId,
            'rol_id' => $roleId,
            'asignado_por' => auth()->id(),
        ]);

        return $usuario->load('roles');
    }

    public function removeRole($userId, $roleId)
    {
        $usuario = Usuario::find($userId);

        if (!$usuario) {
            throw new ResourceNotFoundException('Usuario');
        }

        if (!$usuario->roles()->where('rol_id', $roleId)->exists()) {
            throw new BusinessRuleException('El usuario no tiene este rol');
        }

        $usuario->roles()->detach($roleId);

        Log::info('Rol removido de usuario', [
            'usuario_id' => $userId,
            'rol_id' => $roleId,
            'removido_por' => auth()->id(),
        ]);

        return $usuario->load('roles');
    }
}
```

---

## Validaciones

### **LoginRequest**

```php
<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El formato del email es inválido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }
}
```

---

### **StoreUserRequest**

```php
<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'activo' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe',
        ];
    }
}
```

---

## Recursos (Transformers)

### **UserResource**

```php
<?php

namespace Modules\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'activo' => $this->activo,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'created_at' => $this->creado_en->format('Y-m-d H:i:s'),
            'updated_at' => $this->actualizado_en->format('Y-m-d H:i:s'),
        ];
    }
}
```

---

## Testing

### **LoginTest**

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_puede_hacer_login_con_credenciales_validas()
    {
        $usuario = Usuario::factory()->create([
            'email' => 'test@frenad.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@frenad.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'nombre', 'email', 'roles'],
                    'token' => ['access_token', 'token_type', 'expires_in'],
                ],
            ]);

        $this->assertNotNull($response->json('data.token.access_token'));
    }

    /** @test */
    public function login_falla_con_credenciales_invalidas()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'noexiste@frenad.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function usuario_desactivado_no_puede_hacer_login()
    {
        $usuario = Usuario::factory()->create([
            'email' => 'test@frenad.com',
            'password' => 'password123',
            'activo' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@frenad.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }
}
```

---

## Roles del Sistema

| ID | Nombre | Descripción |
|----|--------|-------------|
| 1 | admin | Acceso total al sistema |
| 2 | gerente | Reportes y configuración |
| 3 | vendedor | Ventas y clientes |
| 4 | almacenero | Inventario y productos |

---

## Referencias

- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)
- [Laravel Authentication](https://laravel.com/docs/10.x/authentication)
- [Role-Based Access Control](https://en.wikipedia.org/wiki/Role-based_access_control)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
