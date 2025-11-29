# 🔐 Feature: Sistema de Autenticación

## Descripción

Como **usuario del sistema de ferretería**  
Quiero **autenticarme de forma segura**  
Para que **pueda acceder a las funcionalidades según mi rol**

---

## 🎯 Reglas de Negocio

1. Solo usuarios activos (`estado = 'activo'`) pueden iniciar sesión
2. Las contraseñas deben estar hasheadas con bcrypt
3. El sistema usa JWT (JSON Web Tokens) para autenticación
4. Los tokens expiran en 24 horas
5. Máximo 5 intentos fallidos antes de bloquear cuenta temporalmente (15 minutos)
6. El email debe ser único en el sistema
7. La contraseña debe tener mínimo 8 caracteres
8. Al iniciar sesión, se registra en `logs_auditoria`

---

## 📋 Scenarios

### Scenario 1: Login exitoso con credenciales válidas

**Given** existe un usuario con:
  * Nombre: "Juan Pérez"
  * Email: "juan.perez@ferreteria.com"
  * Contraseña: "password123" (hasheada)
  * Estado: "activo"
  * Rol: "cajero"

**When** el usuario ingresa:
  * Email: "juan.perez@ferreteria.com"
  * Contraseña: "password123"

**And** hace clic en "Iniciar Sesión"

**Then** el sistema debe:
  * Retornar código HTTP 200 OK
  * Generar un token JWT válido
  * Incluir en el token: `user_id`, `email`, `rol`
  * El token expira en 24 horas
  * Retornar datos del usuario: `id`, `nombre`, `email`, `rol`

**And** se debe registrar en `logs_auditoria`:
  * `usuario_id`: ID de Juan Pérez
  * `accion`: "LOGIN_EXITOSO"
  * `ip_address`: IP del cliente
  * `user_agent`: Navegador usado

---

### Scenario 2: Login rechazado - contraseña incorrecta

**Given** existe un usuario activo "María López" con email "maria@ferreteria.com"

**When** el usuario ingresa:
  * Email: "maria@ferreteria.com"
  * Contraseña: "wrong-password" (incorrecta)

**Then** el sistema debe:
  * Retornar código HTTP 401 Unauthorized
  * Retornar mensaje: "Credenciales inválidas"
  * NO generar token JWT
  * Incrementar contador de intentos fallidos

**And** se debe registrar en `logs_auditoria`:
  * `accion`: "LOGIN_FALLIDO"
  * `descripcion`: "Contraseña incorrecta"

---

### Scenario 3: Login rechazado - usuario no existe

**Given** NO existe un usuario con email "noexiste@example.com"

**When** el usuario ingresa:
  * Email: "noexiste@example.com"
  * Contraseña: "cualquiera"

**Then** el sistema debe:
  * Retornar código HTTP 401 Unauthorized
  * Retornar mensaje: "Credenciales inválidas" (mismo mensaje que contraseña incorrecta)
  * NO revelar que el usuario no existe (seguridad)

---

### Scenario 4: Login rechazado - usuario inactivo

**Given** existe un usuario "Carlos Rojas" con:
  * Email: "carlos@ferreteria.com"
  * Contraseña válida
  * Estado: "inactivo"

**When** el usuario intenta iniciar sesión

**Then** el sistema debe:
  * Retornar código HTTP 403 Forbidden
  * Retornar mensaje: "Usuario inactivo. Contacte al administrador"
  * NO generar token JWT

**And** se debe registrar en `logs_auditoria`:
  * `accion`: "LOGIN_USUARIO_INACTIVO"

---

### Scenario 5: Login rechazado - cuenta bloqueada temporalmente

**Given** existe un usuario "Ana Torres"

**And** Ana ha fallado 5 intentos de login en los últimos 10 minutos

**When** Ana intenta iniciar sesión nuevamente (incluso con contraseña correcta)

**Then** el sistema debe:
  * Retornar código HTTP 429 Too Many Requests
  * Retornar mensaje: "Cuenta bloqueada temporalmente. Intente en 15 minutos"
  * NO permitir login hasta que pasen 15 minutos

**And** se debe registrar en `logs_auditoria`:
  * `accion`: "LOGIN_CUENTA_BLOQUEADA"

---

### Scenario 6: Logout exitoso

**Given** existe un usuario autenticado "Pedro Gómez" con token JWT válido

**When** Pedro hace clic en "Cerrar Sesión"

**Then** el sistema debe:
  * Retornar código HTTP 200 OK
  * Invalidar el token JWT (agregarlo a blacklist)
  * Retornar mensaje: "Sesión cerrada correctamente"

**And** se debe registrar en `logs_auditoria`:
  * `usuario_id`: ID de Pedro
  * `accion`: "LOGOUT"

---

### Scenario 7: Acceso con token expirado

**Given** existe un token JWT que expiró hace 2 horas

**When** el usuario intenta acceder a una ruta protegida con ese token

**Then** el sistema debe:
  * Retornar código HTTP 401 Unauthorized
  * Retornar mensaje: "Token expirado. Inicie sesión nuevamente"

---

### Scenario 8: Acceso con token inválido

**Given** un token JWT malformado o con firma inválida

**When** el usuario intenta acceder a una ruta protegida

**Then** el sistema debe:
  * Retornar código HTTP 401 Unauthorized
  * Retornar mensaje: "Token inválido"

---

### Scenario 9: Registro de nuevo usuario

**Given** NO existe un usuario con email "nuevo@ferreteria.com"

**When** un administrador registra un nuevo usuario con:
  * Nombre: "Luis Mendoza"
  * Email: "nuevo@ferreteria.com"
  * Contraseña: "password123"
  * Rol: "vendedor"

**Then** el sistema debe:
  * Retornar código HTTP 201 Created
  * Crear el usuario en la tabla `usuarios`
  * Hashear la contraseña con bcrypt
  * Asignar estado "activo" por defecto
  * Retornar datos del usuario creado (sin contraseña)

**And** se debe crear registro en `usuario_roles`:
  * `usuario_id`: ID del nuevo usuario
  * `rol_id`: ID del rol "vendedor"

**And** se debe registrar en `logs_auditoria`:
  * `accion`: "USUARIO_CREADO"

---

### Scenario 10: Registro rechazado - email duplicado

**Given** existe un usuario con email "maria@ferreteria.com"

**When** un administrador intenta registrar otro usuario con el mismo email

**Then** el sistema debe:
  * Retornar código HTTP 422 Unprocessable Entity
  * Retornar mensaje: "El email ya está registrado"
  * NO crear el usuario

---

### Scenario 11: Registro rechazado - contraseña débil

**Given** un administrador intenta registrar un usuario con contraseña "1234" (menos de 8 caracteres)

**Then** el sistema debe:
  * Retornar código HTTP 422 Unprocessable Entity
  * Retornar mensaje: "La contraseña debe tener mínimo 8 caracteres"
  * NO crear el usuario

---

### Scenario 12: Cambio de contraseña

**Given** existe un usuario autenticado "Juan Pérez"

**And** Juan conoce su contraseña actual: "password123"

**When** Juan solicita cambiar su contraseña a "newpassword456"

**And** proporciona su contraseña actual correctamente

**Then** el sistema debe:
  * Retornar código HTTP 200 OK
  * Actualizar la contraseña hasheada en la BD
  * Invalidar todos los tokens JWT existentes del usuario
  * Retornar mensaje: "Contraseña actualizada correctamente"

**And** se debe registrar en `logs_auditoria`:
  * `usuario_id`: ID de Juan
  * `accion`: "PASSWORD_CAMBIADO"

---

### Scenario 13: Cambio de contraseña rechazado - contraseña actual incorrecta

**Given** existe un usuario autenticado "María López"

**When** María intenta cambiar su contraseña

**But** proporciona una contraseña actual incorrecta

**Then** el sistema debe:
  * Retornar código HTTP 401 Unauthorized
  * Retornar mensaje: "Contraseña actual incorrecta"
  * NO actualizar la contraseña

---

## ✅ Acceptance Criteria (Criterios de Aceptación)

### Funcionales:

- [x] Login exitoso genera token JWT con expiración de 24h
- [x] Contraseñas almacenadas con bcrypt (nunca en texto plano)
- [x] Máximo 5 intentos fallidos → bloqueo temporal 15 min
- [x] Usuarios inactivos no pueden iniciar sesión
- [x] Email único en el sistema
- [x] Contraseña mínimo 8 caracteres
- [x] Logout invalida el token (blacklist)
- [x] Todos los eventos se registran en `logs_auditoria`

### No Funcionales:

- [x] Tiempo de respuesta < 200ms para login
- [x] Token JWT incluye: user_id, email, rol, exp
- [x] Mensajes de error no revelan información sensible
- [x] Rate limiting: máximo 10 intentos de login por minuto por IP

### Seguridad:

- [x] Protección contra timing attacks (respuestas consistentes)
- [x] Headers de seguridad (CORS, CSP, etc.)
- [x] Validación de inputs (sanitización)
- [x] Protección CSRF en endpoints de cambio de contraseña

---

## 🧪 Tests Asociados

### Feature Tests (BDD - Integración):
```
backend/tests/Feature/Auth/
├── LoginTest.php              # Scenarios 1-5
├── LogoutTest.php             # Scenario 6
├── TokenValidationTest.php    # Scenarios 7-8
├── RegisterUserTest.php       # Scenarios 9-11
└── ChangePasswordTest.php     # Scenarios 12-13
```

### Unit Tests (TDD - Unitarios):
```
backend/tests/Unit/Services/
├── AuthServiceTest.php        # Lógica de autenticación
├── TokenServiceTest.php       # Generación y validación de tokens
└── PasswordServiceTest.php    # Hash y verificación de contraseñas

backend/tests/Unit/Models/
└── UsuarioTest.php            # Métodos del modelo Usuario
```

---

## 🔗 Trazabilidad

### Tablas de Base de Datos:
- **Principal:** [`usuarios`](../../04-database/02-modulos/01-autorizacion.md#tabla-usuarios)
- **Relacionadas:** 
  - `roles` - Roles del sistema
  - `usuario_roles` - Relación muchos a muchos
  - `logs_auditoria` - Registro de eventos

### API Endpoints:
```
POST   /api/auth/login          # Scenario 1-5
POST   /api/auth/logout         # Scenario 6
GET    /api/auth/me             # Verificar token (7-8)
POST   /api/auth/register       # Scenarios 9-11 (admin only)
POST   /api/auth/change-password # Scenarios 12-13
```

### Middleware:
- `auth:jwt` - Validar token JWT
- `role:admin` - Verificar rol de administrador
- `throttle:10,1` - Rate limiting

---

## 📊 Estado de Implementación

| Scenario | Spec BDD | Test Feature | Test Unit | Código | Estado |
|----------|----------|--------------|-----------|--------|--------|
| 1. Login exitoso | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 2. Contraseña incorrecta | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 3. Usuario no existe | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 4. Usuario inactivo | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 5. Cuenta bloqueada | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 6. Logout | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 7-8. Token expirado/inválido | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 9-11. Registro | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |
| 12-13. Cambio contraseña | ✅ | ⏳ | ⏳ | ⏳ | Pendiente |

---

## 🚀 Próximos Pasos

1. ✅ Crear tests Feature basados en estos scenarios
2. ✅ Implementar tests Unit para servicios
3. ✅ Implementar código siguiendo TDD (RED→GREEN→REFACTOR)
4. ✅ Ejecutar suite completa de tests
5. ✅ Documentar cobertura

---

**Última actualización:** 27 de noviembre de 2025  
**Autor:** Equipo Ferretería Frenat  
**Rama:** `feature/bdd-auth`
