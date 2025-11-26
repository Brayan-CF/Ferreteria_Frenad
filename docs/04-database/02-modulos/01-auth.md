# MÓDULO AUTH - AUTENTICACIÓN Y AUTORIZACIÓN

## Descripción General

Módulo encargado de la autenticación de usuarios y control de acceso mediante un sistema de roles flexible N:N.

---

## Tablas del Módulo

### 1. usuarios

**Propósito:** Registro de empleados y administradores del sistema.

**Estructura:**

```sql
CREATE TABLE usuarios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
```

**Columnas Clave:**
- `password_hash`: Hash bcrypt de la contraseña (nunca se almacena en texto plano)
- `activo`: Permite deshabilitar usuarios sin eliminarlos (soft delete)
- `email`: Único, usado para login

**Índices:**
```sql
-- Automático: PRIMARY KEY (id)
-- Automático: UNIQUE (email)
```

---

### 2. roles

**Propósito:** Definición de roles del sistema.

**Estructura:**

```sql
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
```

**Roles Predefinidos:**
1. **Administrador**: Acceso total al sistema
2. **Vendedor**: Realiza ventas, consulta inventario
3. **Bodeguero**: Gestiona compras y transferencias

**Nota:** Los roles son configurables y pueden extenderse.

---

### 3. usuario_roles

**Propósito:** Relación N:N entre usuarios y roles.

**Estructura:**

```sql
CREATE TABLE usuario_roles (
    usuario_id BIGINT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    rol_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    asignado_en TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    asignado_por BIGINT REFERENCES usuarios(id),
    PRIMARY KEY (usuario_id, rol_id)
);
```

**Características:**
- Un usuario puede tener múltiples roles
- Auditoría de quién asignó el rol y cuándo
- `ON DELETE CASCADE`: Si se elimina un usuario, se eliminan sus roles

---

## Diagrama de Relaciones

```
┌─────────────┐
│  usuarios   │
│  (id, nombre│
│   email)    │
└──────┬──────┘
       │
       │ usuario_id
       ▼
┌─────────────┐      ┌─────────────┐
│usuario_roles│──────│    roles    │
│  (N:N)      │ rol_id│(id, nombre) │
└─────────────┘      └─────────────┘
```

---

## Reglas de Negocio

### Autenticación:
1. El email debe ser único en el sistema
2. Las contraseñas se hashean con bcrypt (factor 10)
3. Los usuarios inactivos no pueden iniciar sesión

### Autorización:
1. Un usuario sin roles no tiene acceso al sistema
2. Los roles son acumulativos (un vendedor-bodeguero tiene ambos permisos)
3. Solo un Administrador puede asignar roles

### Seguridad:
1. **No se almacenan contraseñas en texto plano**
2. Las sesiones deben tener timeout de inactividad
3. Registro de intentos de login fallidos (implementar en API)

---

## Operaciones Comunes

### Crear Usuario:

```sql
-- Hash generado en backend (Laravel/PHP)
INSERT INTO usuarios (nombre, email, password_hash, activo)
VALUES ('Juan Pérez', 'juan@frenad.com', '$2y$10$...', TRUE);
```

### Asignar Rol:

```sql
-- Asignar rol de Vendedor al usuario 5
INSERT INTO usuario_roles (usuario_id, rol_id, asignado_por)
VALUES (5, 2, 1); -- 1 = ID del admin que asigna
```

### Verificar Roles de un Usuario:

```sql
SELECT u.nombre, r.nombre AS rol
FROM usuarios u
INNER JOIN usuario_roles ur ON u.id = ur.usuario_id
INNER JOIN roles r ON ur.rol_id = r.id
WHERE u.id = 5;
```

### Login (verificación):

```sql
SELECT id, nombre, email, password_hash, activo
FROM usuarios
WHERE email = 'juan@frenad.com'
AND activo = TRUE;

-- La verificación del password se hace en backend:
-- password_verify($input_password, $password_hash)
```

---

## Consideraciones de Seguridad

### Recomendaciones:
1. **Rate Limiting**: Limitar intentos de login (5 por minuto)
2. **Session Timeout**: 30 minutos de inactividad
3. **Password Policy**:
   - Mínimo 8 caracteres
   - Al menos 1 mayúscula, 1 minúscula, 1 número
4. **Two-Factor Authentication (Futuro)**: Implementar 2FA para administradores

### Auditoría:
- Todos los cambios en `usuario_roles` tienen `asignado_por`
- Los cambios en `usuarios` se registran con `actualizado_en`
- Implementar log de accesos en tabla separada (futuro)

---

## Seeders Iniciales

Ver archivo: [database/06_seeders/01_roles.sql](database/06_seeders/01_roles.sql)

```sql
-- Roles predefinidos
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema, gestión de usuarios, cierre de caja'),
('Vendedor', 'Realiza ventas, consulta inventario, genera reportes básicos'),
('Bodeguero', 'Gestiona compras, recibe mercadería, hace transferencias de inventario');
```

Ver archivo: [database/06_seeders/06_usuario_admin.sql](database/06_seeders/06_usuario_admin.sql)

```sql
-- Usuario admin por defecto
INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES
('Administrador Sistema', 'admin@frenad.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
-- Password: password

-- Asignar rol administrador
INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (1, 1);
```

**IMPORTANTE:** Cambiar la contraseña del administrador en producción.

---

## Integración con Laravel

### Modelos Eloquent:

```php
// app/Models/Usuario.php
class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $hidden = ['password_hash'];
    
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles', 'usuario_id', 'rol_id')
                    ->withTimestamps();
    }
}

// app/Models/Rol.php
class Rol extends Model
{
    protected $table = 'roles';
    
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_roles', 'rol_id', 'usuario_id');
    }
}
```

---

## Testing

### Casos de Prueba:

1. **Crear usuario con email duplicado** → Debe fallar
2. **Login con contraseña incorrecta** → Debe rechazar
3. **Login con usuario inactivo** → Debe rechazar
4. **Asignar rol inexistente** → Debe fallar (FK constraint)
5. **Usuario con múltiples roles** → Debe funcionar correctamente

---

**Siguiente:** [Módulo Productos](02-productos.md)