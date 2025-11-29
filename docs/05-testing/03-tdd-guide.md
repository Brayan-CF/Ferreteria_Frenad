# 🧪 Guía Avanzada de TDD (Test-Driven Development)

## 🎯 Los 3 Pilares de TDD

### **1. RED 🔴 - Test que Falla**

Escribir un test que **debe fallar** porque la funcionalidad **no existe aún**.

```php
// RED 🔴
it('calcula el total de una venta con descuento', function () {
    $venta = new Venta();
    $venta->agregarItem(precio: 100, cantidad: 2); // 200
    $venta->aplicarDescuento(porcentaje: 10);       // -20
    
    expect($venta->getTotal())->toBe(180.0);
});

// Ejecutar: ./vendor/bin/pest
// ❌ Error: Method aplicarDescuento does not exist
```

**¿Por qué debe fallar?**
- Si pasa sin código → el test es inútil
- Confirma que estás testeando algo nuevo

---

### **2. GREEN 🟢 - Código Mínimo**

Escribir el **mínimo código posible** para que el test pase.

```php
// GREEN 🟢
class Venta {
    private array $items = [];
    private float $descuento = 0;
    
    public function agregarItem(float $precio, int $cantidad): void {
        $this->items[] = ['precio' => $precio, 'cantidad' => $cantidad];
    }
    
    public function aplicarDescuento(float $porcentaje): void {
        $this->descuento = $porcentaje;
    }
    
    public function getTotal(): float {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }
        
        return $subtotal - ($subtotal * $this->descuento / 100);
    }
}

// Ejecutar: ./vendor/bin/pest
// ✅ Test passed
```

**Reglas:**
- No agregar código "por si acaso"
- Solo lo necesario para pasar el test
- Si pasa, avanzar a REFACTOR

---

### **3. REFACTOR 🔵 - Mejorar Código**

Mejorar el código **sin cambiar funcionalidad** (tests siguen pasando).

```php
// REFACTOR 🔵
class Venta {
    private Collection $items;
    private Descuento $descuento;
    
    public function __construct() {
        $this->items = new Collection();
        $this->descuento = Descuento::ninguno();
    }
    
    public function agregarItem(float $precio, int $cantidad): void {
        $this->items->push(new ItemVenta($precio, $cantidad));
    }
    
    public function aplicarDescuento(float $porcentaje): void {
        $this->descuento = new Descuento($porcentaje);
    }
    
    public function getTotal(): float {
        $subtotal = $this->items->sum(fn($item) => $item->getSubtotal());
        return $this->descuento->aplicar($subtotal);
    }
}

class ItemVenta {
    public function __construct(
        private float $precio,
        private int $cantidad
    ) {}
    
    public function getSubtotal(): float {
        return $this->precio * $this->cantidad;
    }
}

class Descuento {
    public function __construct(private float $porcentaje) {}
    
    public static function ninguno(): self {
        return new self(0);
    }
    
    public function aplicar(float $monto): float {
        return $monto - ($monto * $this->porcentaje / 100);
    }
}

// Ejecutar: ./vendor/bin/pest
// ✅ Test passed (código más limpio y mantenible)
```

**Mejoras aplicadas:**
- ✅ Extracción de clases (ItemVenta, Descuento)
- ✅ Uso de Collections
- ✅ Patrón Value Object
- ✅ Código más legible

---

## 🔄 Ciclo Completo TDD

```
Requisito: "Calcular total de venta con descuento"
    ↓
1. RED 🔴
   ├─> Escribir test que falla
   └─> expect($venta->getTotal())->toBe(180.0)
       ❌ Method aplicarDescuento does not exist
    ↓
2. GREEN 🟢
   ├─> Implementar método aplicarDescuento()
   └─> ✅ Test passed
    ↓
3. REFACTOR 🔵
   ├─> Extraer clase Descuento
   ├─> Extraer clase ItemVenta
   └─> ✅ Tests siguen pasando
    ↓
Siguiente requisito: "Validar descuento no excede 50%"
    ↓
Repetir ciclo...
```

---

## 🎯 Estrategias de Testing

### **1. AAA Pattern (Arrange-Act-Assert)**

```php
it('procesa venta a crédito correctamente', function () {
    // ARRANGE (Given) - Preparar contexto
    $cliente = Cliente::factory()->create([
        'es_frecuente' => true,
        'limite_credito' => 5000,
    ]);
    $producto = Producto::factory()->create(['precio' => 100]);
    
    // ACT (When) - Ejecutar acción
    $venta = $this->saleService->procesar([
        'cliente_id' => $cliente->id,
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 10]
        ],
        'tipo' => 'credito',
    ]);
    
    // ASSERT (Then) - Verificar resultado
    expect($venta->estado)->toBe('completada');
    expect($venta->total)->toBe(1000.0);
    $this->assertDatabaseHas('creditos_clientes', [
        'venta_id' => $venta->id,
        'monto_total' => 1000.0,
    ]);
});
```

---

### **2. Test Isolation (Aislamiento)**

Cada test debe ser **independiente** y **repetible**.

```php
// ❌ MAL - Tests dependientes
it('crea venta', function () {
    $this->venta = Venta::create([...]);  // ← Estado compartido
});

it('calcula total', function () {
    expect($this->venta->total)->toBe(100);  // ← Depende del anterior
});


// ✅ BIEN - Tests independientes
it('crea venta', function () {
    $venta = Venta::create([...]);
    expect($venta)->toBeInstanceOf(Venta::class);
});

it('calcula total correctamente', function () {
    $venta = Venta::factory()->create([total => 100]);  // ← Setup propio
    expect($venta->total)->toBe(100);
});
```

---

### **3. Test Data Builders (Factories)**

```php
// Usar Laravel Factories para crear datos de test
$cliente = Cliente::factory()->create();
$cliente = Cliente::factory()->frecuente()->create();
$cliente = Cliente::factory()->conDeuda(1000)->create();

// Factory personalizada
class ClienteFactory extends Factory {
    public function frecuente(): self {
        return $this->state(['es_frecuente' => true, 'limite_credito' => 5000]);
    }
    
    public function conDeuda(float $monto): self {
        return $this->afterCreating(function (Cliente $cliente) use ($monto) {
            CreditoCliente::factory()->create([
                'cliente_id' => $cliente->id,
                'saldo_pendiente' => $monto,
            ]);
        });
    }
}
```

---

### **4. Mocking y Stubbing**

```php
// Mockear dependencias externas
it('envía email cuando venta se completa', function () {
    // Arrange
    Mail::fake();
    $cliente = Cliente::factory()->create();
    
    // Act
    $this->saleService->procesar([...]);
    
    // Assert
    Mail::assertSent(VentaCompletadaMail::class, function ($mail) use ($cliente) {
        return $mail->hasTo($cliente->email);
    });
});

// Stub de servicios externos
it('consulta tipo de cambio del BCB', function () {
    // Stub del servicio externo
    Http::fake([
        'bcb.gob.bo/api/*' => Http::response(['tasa' => 6.96], 200)
    ]);
    
    $tasa = $this->currencyService->getTasaCambio();
    
    expect($tasa)->toBe(6.96);
});
```

---

## 🎨 Tipos de Tests

### **1. Unit Tests (Unitarios)**

Testean una **unidad aislada** (método, clase).

```php
// tests/Unit/Models/VentaTest.php
it('calcula subtotal de items correctamente', function () {
    $venta = new Venta();
    $venta->agregarItem(precio: 100, cantidad: 2);
    $venta->agregarItem(precio: 50, cantidad: 3);
    
    expect($venta->getSubtotal())->toBe(350.0);
});

it('aplica descuento porcentual correctamente', function () {
    $venta = new Venta();
    $venta->agregarItem(precio: 100, cantidad: 10);
    $venta->aplicarDescuento(10); // 10%
    
    expect($venta->getTotal())->toBe(900.0);
});
```

**Características:**
- ✅ Rápidos (< 1ms)
- ✅ No usan BD
- ✅ No usan red
- ✅ Aislados completamente

---

### **2. Integration Tests (Integración)**

Testean **interacción entre componentes**.

```php
// tests/Feature/Sales/ProcessSaleTest.php
it('procesa venta y actualiza inventario', function () {
    // Arrange
    $producto = Producto::factory()->create();
    Inventario::factory()->create([
        'producto_id' => $producto->id,
        'cantidad_actual' => 100,
    ]);
    
    // Act
    $venta = $this->saleService->procesar([
        'productos' => [
            ['id' => $producto->id, 'cantidad' => 10]
        ]
    ]);
    
    // Assert
    expect($venta->estado)->toBe('completada');
    
    // Verifica que el inventario se actualizó
    $inventario = Inventario::where('producto_id', $producto->id)->first();
    expect($inventario->cantidad_actual)->toBe(90.0);
    
    // Verifica que se creó movimiento
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $producto->id,
        'tipo_movimiento' => 'SALIDA_VENTA',
        'cantidad' => 10,
    ]);
});
```

**Características:**
- ⏱️ Más lentos (10-100ms)
- 💾 Usan BD (transactions)
- 🔗 Múltiples componentes
- 🎯 Validan flujos completos

---

### **3. API Tests (HTTP)**

Testean **endpoints de la API**.

```php
// tests/Feature/Api/Auth/LoginTest.php
it('permite login con credenciales válidas', function () {
    // Arrange
    $user = User::factory()->create([
        'email' => 'juan@example.com',
        'password' => bcrypt('password123'),
    ]);
    
    // Act
    $response = $this->postJson('/api/auth/login', [
        'email' => 'juan@example.com',
        'password' => 'password123',
    ]);
    
    // Assert
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'token',
        'user' => ['id', 'nombre', 'email'],
    ]);
    
    expect($response->json('user.email'))->toBe('juan@example.com');
});

it('rechaza login con contraseña incorrecta', function () {
    $user = User::factory()->create();
    
    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    
    $response->assertStatus(401);
    $response->assertJson([
        'message' => 'Credenciales inválidas',
    ]);
});
```

---

## 🛡️ Test Coverage (Cobertura)

### **Ejecutar con Cobertura:**

```bash
# Generar reporte de cobertura
./vendor/bin/pest --coverage

# Reporte en HTML
./vendor/bin/pest --coverage-html coverage/

# Ver en navegador
xdg-open coverage/index.html
```

### **Meta de Cobertura:**

| Tipo de Código | Meta | Razón |
|----------------|------|-------|
| **Services** | 90-100% | Lógica de negocio crítica |
| **Models** | 80-90% | Validaciones y relaciones |
| **Controllers** | 70-80% | Principalmente delegación |
| **Repositories** | 80-90% | Queries complejas |

---

## 🎯 Buenas Prácticas TDD

### **DO ✅**

1. **Tests pequeños y enfocados:**
```php
✅ BIEN:
it('valida email requerido')
it('valida formato de email')
it('valida email único')

❌ MAL:
it('valida todos los campos del usuario')
```

2. **Nombres descriptivos:**
```php
✅ BIEN:
it('rechaza venta cuando stock es insuficiente')

❌ MAL:
it('test venta')
it('test 1')
```

3. **Un concepto por test:**
```php
✅ BIEN:
it('calcula total correctamente')
it('aplica descuento correctamente')

❌ MAL:
it('calcula total y aplica descuento y valida stock')
```

4. **Tests independientes:**
```php
✅ BIEN:
beforeEach(function () {
    $this->venta = new Venta();
});

❌ MAL:
// Compartir estado entre tests
```

### **DON'T ❌**

1. **No testear implementación, testear comportamiento:**
```php
❌ MAL:
it('llama al método calcularSubtotal()')  // ← Implementación

✅ BIEN:
it('calcula el total correctamente')       // ← Comportamiento
```

2. **No tests frágiles:**
```php
❌ MAL:
expect($venta->items[0]['precio'])->toBe(100);  // ← Dependiente de estructura

✅ BIEN:
expect($venta->getItems()->first()->getPrecio())->toBe(100);
```

3. **No ignorar tests que fallan:**
```bash
❌ MAL:
it('test temporal', function () { 
    expect(true)->toBeTrue();
})->skip('arreglar después');  // ← NUNCA hacer esto
```

---

## 🔧 Mantener Suite de Tests

### **Ejecutar Tests:**

```bash
# Todos los tests
./vendor/bin/pest

# Solo unitarios
./vendor/bin/pest --testsuite=Unit

# Solo features
./vendor/bin/pest --testsuite=Feature

# Un archivo específico
./vendor/bin/pest tests/Unit/Models/VentaTest.php

# Un test específico
./vendor/bin/pest --filter "calcula total correctamente"

# Con cobertura
./vendor/bin/pest --coverage --min=80
```

### **Tests Rápidos:**

```bash
# Ejecutar solo tests modificados
./vendor/bin/pest --dirty

# Ejecutar en paralelo
./vendor/bin/pest --parallel
```

---

## 📊 Ejemplo Real: Servicio de Ventas

### **1. RED 🔴 - Test**

```php
// tests/Unit/Services/SaleServiceTest.php
it('valida crédito disponible antes de procesar venta', function () {
    // Arrange
    $cliente = Cliente::factory()->create([
        'limite_credito' => 1000,
    ]);
    CreditoCliente::factory()->create([
        'cliente_id' => $cliente->id,
        'saldo_pendiente' => 900,  // Deuda actual
    ]);
    
    $saleService = new SaleService();
    
    // Act & Assert
    expect(fn() => $saleService->validarCredito($cliente, 200))
        ->toThrow(CreditoInsuficienteException::class, 'Crédito disponible: 100, Solicitado: 200');
});
```

### **2. GREEN 🟢 - Implementación Mínima**

```php
// app/Services/SaleService.php
class SaleService {
    public function validarCredito(Cliente $cliente, float $monto): void {
        $deudaActual = CreditoCliente::where('cliente_id', $cliente->id)
            ->sum('saldo_pendiente');
        
        $creditoDisponible = $cliente->limite_credito - $deudaActual;
        
        if ($monto > $creditoDisponible) {
            throw new CreditoInsuficienteException(
                "Crédito disponible: {$creditoDisponible}, Solicitado: {$monto}"
            );
        }
    }
}
```

### **3. REFACTOR 🔵 - Mejorar**

```php
// app/Services/SaleService.php
class SaleService {
    public function __construct(
        private CreditService $creditService
    ) {}
    
    public function validarCredito(Cliente $cliente, float $monto): void {
        $creditoDisponible = $this->creditService->getDisponible($cliente);
        
        if ($monto > $creditoDisponible) {
            throw CreditoInsuficienteException::paraCliente(
                $cliente,
                $creditoDisponible,
                $monto
            );
        }
    }
}

// app/Services/CreditService.php
class CreditService {
    public function getDisponible(Cliente $cliente): float {
        $deudaActual = $this->getDeudaTotal($cliente);
        return $cliente->limite_credito - $deudaActual;
    }
    
    private function getDeudaTotal(Cliente $cliente): float {
        return CreditoCliente::where('cliente_id', $cliente->id)
            ->where('estado', 'pendiente')
            ->sum('saldo_pendiente');
    }
}

// app/Exceptions/CreditoInsuficienteException.php
class CreditoInsuficienteException extends Exception {
    public static function paraCliente(
        Cliente $cliente,
        float $disponible,
        float $solicitado
    ): self {
        return new self(
            "Cliente '{$cliente->nombre}' excede límite de crédito. " .
            "Disponible: Bs. " . number_format($disponible, 2) . ", " .
            "Solicitado: Bs. " . number_format($solicitado, 2)
        );
    }
}
```

---

## 📚 Referencias

- **Pest PHP:** https://pestphp.com/docs/
- **TDD by Example:** Kent Beck
- **Test Doubles:** https://martinfowler.com/bliki/TestDouble.html
- **Testing Best Practices:** https://github.com/testdouble/contributing-tests/wiki

---

**Última actualización:** 27 de noviembre de 2025
