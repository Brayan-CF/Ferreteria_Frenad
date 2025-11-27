# 🎯 Feature: Autenticación (Auth) - Guía de Trabajo

## 📊 Estado Actual

**Rama:** `feature/bdd-auth`  
**Basada en:** `develop`  
**Commits adelante de develop:** 2

---

## 📂 Archivos Creados

```
docs/
├── 05-testing/
│   ├── 01-introduccion.md       ✅ Guía de BDD/TDD
│   ├── 02-bdd-guide.md          ✅ Guía avanzada BDD
│   └── 03-tdd-guide.md          ✅ Guía avanzada TDD
│
└── 06-features/
    └── 01-auth.feature.md        ✅ Especificación BDD Auth
```

---

## 🎯 Qué Hemos Logrado

### 1. ✅ **Metodología BDD/TDD Documentada**

- **Introducción completa** a BDD y TDD
- **Guías avanzadas** con ejemplos prácticos
- **Workflow integrado** Git + BDD + TDD
- **Buenas prácticas** y antipatrones

### 2. ✅ **Especificación BDD de Autenticación**

- **13 scenarios completos**:
  - Login exitoso y casos de error
  - Logout y gestión de tokens
  - Registro de usuarios
  - Cambio de contraseña
  
- **Reglas de negocio claras**
- **Criterios de aceptación** funcionales y no funcionales
- **Trazabilidad** con BD y API

### 3. ✅ **Feature Aislada Sin Afectar Producción**

- Rama `master` sigue en commit `43d989c` (estable)
- Rama `develop` sigue en commit `0c1625c` (sin cambios)
- Toda la documentación y specs están en `feature/bdd-auth`

---

## 🚀 Próximos Pasos

### **FASE 1: Implementar Tests (TDD - RED)**

```bash
# 1. Crear estructura de tests
mkdir -p backend/tests/Feature/Auth
mkdir -p backend/tests/Unit/Services
mkdir -p backend/tests/Unit/Models

# 2. Escribir test que falla (RED)
# Basado en Scenario 1 de 01-auth.feature.md
```

**Ejemplo - Test Feature:**

```php
// backend/tests/Feature/Auth/LoginTest.php
<?php

use function Pest\Laravel\{postJson, assertDatabaseHas};

describe('Login de Usuario', function () {
    it('permite login con credenciales válidas', function () {
        // GIVEN (Arrange)
        $user = User::factory()->create([
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@ferreteria.com',
            'password' => bcrypt('password123'),
            'estado' => 'activo',
        ]);
        
        // WHEN (Act)
        $response = postJson('/api/auth/login', [
            'email' => 'juan.perez@ferreteria.com',
            'password' => 'password123',
        ]);
        
        // THEN (Assert)
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'nombre', 'email', 'rol'],
        ]);
        
        expect($response->json('user.email'))->toBe('juan.perez@ferreteria.com');
        
        // Verificar log de auditoría
        assertDatabaseHas('logs_auditoria', [
            'usuario_id' => $user->id,
            'accion' => 'LOGIN_EXITOSO',
        ]);
    });
});
```

**Commit:**
```bash
git add backend/tests/Feature/Auth/LoginTest.php
git commit -m "test(auth): Agregar test para login exitoso (RED)

- Test basado en Scenario 1 de spec BDD
- Verifica: token JWT, datos usuario, log auditoría
- Estado: FALLA (endpoint /api/auth/login no existe)"
```

---

### **FASE 2: Implementar Código (TDD - GREEN)**

```bash
# 1. Crear controlador
php artisan make:controller Api/AuthController

# 2. Implementar endpoint login (código mínimo para pasar test)
```

**Ejemplo - Controlador:**

```php
// backend/app/Http/Controllers/Api/AuthController.php
<?php

namespace App\Http\Controllers\Api;

class AuthController extends Controller {
    public function login(Request $request) {
        $credentials = $request->only(['email', 'password']);
        
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
        
        $user = auth()->user();
        
        // Log de auditoría
        LogAuditoria::create([
            'usuario_id' => $user->id,
            'accion' => 'LOGIN_EXITOSO',
            'ip_address' => $request->ip(),
        ]);
        
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->rol->nombre,
            ],
        ]);
    }
}
```

**Commit:**
```bash
git add backend/app/Http/Controllers/Api/AuthController.php
git add backend/routes/api.php
git commit -m "feat(auth): Implementar endpoint de login (GREEN)

- POST /api/auth/login con JWT
- Validación de credenciales
- Log de auditoría automático
- Tests: ✅ PASSING"
```

---

### **FASE 3: Refactorizar (TDD - REFACTOR)**

```bash
# Extraer lógica a servicio
php artisan make:service AuthService
```

**Ejemplo - Refactorización:**

```php
// backend/app/Services/AuthService.php
<?php

namespace App\Services;

class AuthService {
    public function login(array $credentials): array {
        if (!$token = auth()->attempt($credentials)) {
            throw new InvalidCredentialsException('Credenciales inválidas');
        }
        
        $user = auth()->user();
        
        $this->logAuditoria('LOGIN_EXITOSO', $user->id);
        
        return [
            'token' => $token,
            'user' => $this->formatUserData($user),
        ];
    }
    
    private function formatUserData(User $user): array {
        return [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'rol' => $user->rol->nombre,
        ];
    }
    
    private function logAuditoria(string $accion, int $usuarioId): void {
        LogAuditoria::create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

**Commit:**
```bash
git add backend/app/Services/AuthService.php
git commit -m "refactor(auth): Extraer lógica de login a servicio

- Crear AuthService con método login()
- Extraer formateo de datos de usuario
- Extraer logging de auditoría
- Tests: ✅ PASSING (sin cambios en comportamiento)"
```

---

### **FASE 4: Completar Todos los Scenarios**

Repetir ciclo **RED → GREEN → REFACTOR** para cada scenario:

- [ ] Scenario 2: Contraseña incorrecta
- [ ] Scenario 3: Usuario no existe
- [ ] Scenario 4: Usuario inactivo
- [ ] Scenario 5: Cuenta bloqueada
- [ ] Scenario 6: Logout
- [ ] Scenarios 7-8: Token expirado/inválido
- [ ] Scenarios 9-11: Registro
- [ ] Scenarios 12-13: Cambio contraseña

---

### **FASE 5: Verificar Cobertura**

```bash
# Ejecutar todos los tests
./vendor/bin/pest

# Generar reporte de cobertura
./vendor/bin/pest --coverage --min=80

# Ver reporte HTML
./vendor/bin/pest --coverage-html coverage/
xdg-open coverage/index.html
```

---

### **FASE 6: Merge a Develop**

```bash
# 1. Asegurar que todos los tests pasan
./vendor/bin/pest
# ✅ All tests passed

# 2. Ver commits de la feature
git log --oneline develop..feature/bdd-auth

# 3. Cambiar a develop
git checkout develop

# 4. Merge de feature
git merge feature/bdd-auth --no-ff

# 5. Push a remoto (si existe)
git push origin develop

# 6. Eliminar rama feature
git branch -d feature/bdd-auth
```

---

## 📊 Ventajas de Esta Metodología

### **1. Feature Aislada ✅**

```
master (producción estable)
  │
  └── develop (integración)
       │
       └── feature/bdd-auth (desarrollo aislado)
            ├── Specs BDD
            ├── Tests TDD
            └── Implementación
```

**Beneficios:**
- ✅ `master` nunca se rompe
- ✅ `develop` solo recibe código probado
- ✅ Puedes experimentar sin miedo
- ✅ Rollback fácil si algo falla

---

### **2. BDD + TDD Integrados ✅**

```
Spec BDD (Lenguaje natural)
    ↓
Test Feature (BDD - Integración)
    ↓
Test Unit (TDD - Unitarios)
    ↓
Código (Implementación)
```

**Beneficios:**
- ✅ Documentación = Código
- ✅ Todos entienden qué hace el sistema
- ✅ Tests automatizan verificación
- ✅ Cobertura 100% garantizada

---

### **3. Historial Limpio ✅**

```bash
git log --oneline feature/bdd-auth

b77fdd3 docs(bdd-tdd): Agregar guías BDD/TDD
bd1c6e2 docs(bdd): Agregar spec BDD Auth
abc1234 test(auth): Test login (RED)
def5678 feat(auth): Implementar login (GREEN)
ghi9012 refactor(auth): Extraer servicio (REFACTOR)
```

**Beneficios:**
- ✅ Historial cuenta una historia
- ✅ Fácil de revisar en Pull Request
- ✅ Fácil de revertir si es necesario

---

## 🎓 Comandos Útiles

### **Ver estado:**
```bash
git status
git branch
git log --oneline --graph
```

### **Comparar con develop:**
```bash
# Ver diferencias
git diff develop..feature/bdd-auth

# Ver commits únicos de feature
git log develop..feature/bdd-auth --oneline
```

### **Ejecutar tests:**
```bash
# Todos
./vendor/bin/pest

# Solo Auth
./vendor/bin/pest tests/Feature/Auth/
./vendor/bin/pest tests/Unit/Services/AuthServiceTest.php

# Con filtro
./vendor/bin/pest --filter="login exitoso"
```

---

## 📚 Referencias

- **Spec BDD:** [`docs/06-features/01-auth.feature.md`](../06-features/01-auth.feature.md)
- **Guía BDD:** [`docs/05-testing/02-bdd-guide.md`](02-bdd-guide.md)
- **Guía TDD:** [`docs/05-testing/03-tdd-guide.md`](03-tdd-guide.md)
- **Workflow:** [`docs/05-testing/04-workflow.md`](04-workflow.md)

---

**Última actualización:** 27 de noviembre de 2025  
**Rama actual:** `feature/bdd-auth`  
**Estado:** 📝 Documentación completa - Listo para implementar tests
