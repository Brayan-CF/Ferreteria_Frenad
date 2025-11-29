# 📚 Introducción a BDD y TDD

## 🎯 ¿Por Qué Necesitamos Testing?

### **Problema Sin Tests:**

```
Implementar Feature → Deployar → Bug en producción → Cliente enojado 😡
                    ↓
                Parchar rápido → Romper otra cosa → Más bugs
                    ↓
                Deuda técnica → Código difícil de mantener
```

### **Solución Con Tests:**

```
Escribir Spec BDD → Escribir Tests TDD → Implementar → Tests Pasan ✅
                                                      ↓
                                                   Deploy seguro
                                                      ↓
                                              Sin bugs, código limpio
```

---

## 🎭 BDD (Behaviour-Driven Development)

### **¿Qué es?**

**BDD** es una metodología que describe el **comportamiento del sistema** en lenguaje natural, entendible por todos (stakeholders, devs, QA).

### **Formato: Given-When-Then**

```gherkin
Given [contexto inicial - precondiciones]
When [acción que se ejecuta]
Then [resultado esperado - postcondiciones]
```

### **Ejemplo Real:**

```markdown
## Feature: Login de Usuario

Como usuario registrado
Quiero iniciar sesión con mi email y contraseña
Para acceder al sistema de ferretería

### Scenario: Login exitoso con credenciales válidas

**Given** existe un usuario con email "juan@example.com" y contraseña "password123"
**And** el usuario está activo
**When** el usuario ingresa email "juan@example.com" y contraseña "password123"
**And** hace clic en "Iniciar Sesión"
**Then** el sistema debe redirigir al dashboard
**And** debe mostrar mensaje "Bienvenido, Juan Pérez"
**And** debe generar un token JWT válido
```

### **Beneficios:**

| Beneficio | Descripción |
|-----------|-------------|
| 📖 **Documentación Viva** | La spec es ejecutable, siempre actualizada |
| 🤝 **Colaboración** | Todos entienden qué hace el sistema |
| 🎯 **Foco en Usuario** | Se piensa desde la perspectiva del usuario |
| ✅ **Criterios Claros** | Se sabe exactamente cuándo está "terminado" |

---

## 🧪 TDD (Test-Driven Development)

### **¿Qué es?**

**TDD** es una metodología de desarrollo donde escribes **tests ANTES que el código**.

### **Ciclo: RED → GREEN → REFACTOR**

```
1. RED 🔴
   ├─> Escribir test que falla
   └─> Ejecutar test (debe fallar)

2. GREEN 🟢
   ├─> Escribir código MÍNIMO para pasar test
   └─> Ejecutar test (debe pasar)

3. REFACTOR 🔵
   ├─> Mejorar código manteniendo tests verdes
   └─> Ejecutar tests (deben seguir pasando)

Repetir para cada funcionalidad
```

### **Ejemplo Visual:**

```php
// PASO 1: RED 🔴 - Test que falla
it('calcula el total de una venta correctamente', function () {
    $venta = new Venta();
    $venta->agregarItem(precio: 100, cantidad: 2); // 200
    $venta->agregarItem(precio: 50, cantidad: 3);  // 150
    
    expect($venta->getTotal())->toBe(350.0);
});

// Ejecutar: ./vendor/bin/pest
// ❌ Error: Class Venta does not exist


// PASO 2: GREEN 🟢 - Código mínimo
class Venta {
    private $items = [];
    
    public function agregarItem($precio, $cantidad) {
        $this->items[] = ['precio' => $precio, 'cantidad' => $cantidad];
    }
    
    public function getTotal() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }
}

// Ejecutar: ./vendor/bin/pest
// ✅ Test passed


// PASO 3: REFACTOR 🔵 - Mejorar código
class Venta {
    private array $items = [];
    
    public function agregarItem(float $precio, int $cantidad): void {
        $this->items[] = new ItemVenta($precio, $cantidad);
    }
    
    public function getTotal(): float {
        return array_reduce(
            $this->items,
            fn($total, $item) => $total + $item->getSubtotal(),
            0.0
        );
    }
}

// Ejecutar: ./vendor/bin/pest
// ✅ Test passed (código más limpio)
```

### **Beneficios:**

| Beneficio | Descripción |
|-----------|-------------|
| 🎯 **Cobertura 100%** | Todo el código tiene tests |
| 🛡️ **Menos Bugs** | Los tests detectan problemas temprano |
| 🏗️ **Mejor Diseño** | TDD fuerza código modular y testeable |
| 🔄 **Refactoring Seguro** | Puedes mejorar código sin miedo |

---

## 🔗 BDD + TDD Juntos

### **Workflow Integrado:**

```
1. ESCRIBIR SPEC BDD (Lenguaje natural)
   docs/06-features/auth.feature.md
   "Given usuario existe, When login, Then acceso concedido"

2. ESCRIBIR TEST FEATURE (BDD - Integración)
   backend/tests/Feature/Auth/LoginTest.php
   it('permite login con credenciales válidas')
   ❌ RED

3. ESCRIBIR TESTS UNIT (TDD - Unitarios)
   backend/tests/Unit/Services/AuthServiceTest.php
   it('valida credenciales correctamente')
   ❌ RED

4. IMPLEMENTAR CÓDIGO MÍNIMO
   backend/app/Services/AuthService.php
   ✅ GREEN

5. REFACTORIZAR
   Mejorar código manteniendo tests verdes
   ✅ GREEN

6. DOCUMENTAR COBERTURA
   Actualizar docs/05-testing/README.md
```

---

## 🎯 Niveles de Testing

### **Pirámide de Testing:**

```
                    /\
                   /  \
                  / E2E \           ← Pocos, lentos, costosos
                 /______\
                /        \
               /  Integr  \         ← Algunos, medianos
              /____________\
             /              \
            /    Unitarios   \      ← Muchos, rápidos, baratos
           /__________________\
```

### **En Nuestro Proyecto:**

| Nivel | Framework | Ubicación | Ejemplo |
|-------|-----------|-----------|---------|
| **E2E** | Manual/Selenium | frontend/ | Click en "Login" → Ver dashboard |
| **Integración** | Pest Feature | tests/Feature/ | HTTP POST /login → 200 OK |
| **Unitarios** | Pest Unit | tests/Unit/ | AuthService::validate() → true |
| **Base Datos** | SQL Scripts | database/tests/ | Trigger validación → Error |

---

## 📊 Comparación BDD vs TDD

| Aspecto | BDD | TDD |
|---------|-----|-----|
| **Enfoque** | Comportamiento del sistema | Implementación técnica |
| **Lenguaje** | Natural (Given-When-Then) | Código (assertions) |
| **Audiencia** | Todos (stakeholders, devs, QA) | Desarrolladores |
| **Nivel** | Integración (Feature tests) | Unitario (Unit tests) |
| **Cuándo** | Al inicio (definir requisitos) | Durante desarrollo |

---

## 🛠️ Herramientas en Este Proyecto

### **Para BDD:**

```bash
# Pest con sintaxis BDD
it('permite login con credenciales válidas', function () {
    // Given
    $user = User::factory()->create();
    
    // When
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    // Then
    $response->assertStatus(200);
    $response->assertJsonStructure(['token']);
});
```

### **Para TDD:**

```bash
# Pest con sintaxis de expectations
expect($venta->getTotal())->toBe(350.0);
expect($producto->estaActivo())->toBeTrue();
expect($stock)->toBeGreaterThan(0);
```

---

## 📝 Convenciones en Este Proyecto

### **Nomenclatura de Tests:**

```
✅ BIEN:
it('permite login con credenciales válidas')
it('rechaza login con contraseña incorrecta')
it('genera token JWT con 24h de expiración')

❌ MAL:
it('test login')
it('test 1')
it('funciona')
```

### **Estructura de Test (AAA Pattern):**

```php
it('descripción clara del comportamiento', function () {
    // ARRANGE (Given) - Preparar contexto
    $user = User::factory()->create();
    
    // ACT (When) - Ejecutar acción
    $response = $this->post('/login', [...]);
    
    // ASSERT (Then) - Verificar resultado
    expect($response->status())->toBe(200);
});
```

---

## 🎓 Reglas de Oro

### **BDD:**

1. ✅ Usa lenguaje natural y simple
2. ✅ Un scenario = un flujo completo
3. ✅ Evita detalles técnicos en specs
4. ✅ Enfócate en el "qué", no el "cómo"

### **TDD:**

1. ✅ NUNCA escribas código sin test primero
2. ✅ Escribe el test más simple que falle
3. ✅ Escribe el código más simple que pase
4. ✅ Refactoriza solo con tests verdes

---

## 🚀 Próximos Pasos

1. Leer [`02-bdd-guide.md`](02-bdd-guide.md) - Guía avanzada de BDD
2. Leer [`03-tdd-guide.md`](03-tdd-guide.md) - Guía avanzada de TDD
3. Seguir [`04-workflow.md`](04-workflow.md) - Workflow completo
4. Empezar con primera feature: `feature/bdd-auth`

---

## 📚 Referencias

- **Pest PHP:** https://pestphp.com/
- **BDD by Cucumber:** https://cucumber.io/docs/bdd/
- **TDD by Example:** Martin Fowler
- **Given-When-Then:** https://martinfowler.com/bliki/GivenWhenThen.html

---

**Última actualización:** 27 de noviembre de 2025
