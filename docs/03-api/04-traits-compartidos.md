# 🔧 Traits Compartidos

## Índice
1. [Introducción](#introducción)
2. [HasAudit Trait](#hasaudit-trait)
3. [HasBolivianTimestamps Trait](#hasboliviantimestamps-trait)
4. [Uso Combinado](#uso-combinado)
5. [Testing](#testing)

---

## Introducción

Los **traits** son mecanismos de reutilización de código en PHP que permiten compartir comportamientos entre múltiples clases. En el sistema de Ferretería Frenad, utilizamos traits para agregar funcionalidades comunes a los modelos Eloquent.

### **Traits Disponibles**
- ✅ **HasAudit** - Auditoría de cambios (quién creó/actualizó)
- ✅ **HasBolivianTimestamps** - Timestamps con zona horaria de Bolivia

### **Ubicación**
```
backend/app/Modules/Shared/Traits/
├── HasAudit.php
└── HasBolivianTimestamps.php
```

---

## HasAudit Trait

### **Propósito**
Registrar automáticamente **quién** creó y actualizó cada registro en la base de datos para mantener trazabilidad y cumplir con requisitos de auditoría.

### **Ubicación**
```
backend/app/Modules/Shared/Traits/HasAudit.php
```

---

### **Código Completo**

```php
<?php

namespace Modules\Shared\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    /**
     * Boot del trait - Se ejecuta cuando se inicializa el modelo
     */
    protected static function bootHasAudit()
    {
        // Evento: Antes de crear un registro
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->creado_por = Auth::id();
                $model->actualizado_por = Auth::id();
            }
        });

        // Evento: Antes de actualizar un registro
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->actualizado_por = Auth::id();
            }
        });
    }
}
```

---

### **Campos en Base de Datos**

Los modelos que usen este trait deben tener las siguientes columnas:

```sql
-- Agregar a la tabla
creado_por INTEGER,
actualizado_por INTEGER,

-- Relaciones (Foreign Keys)
FOREIGN KEY (creado_por) REFERENCES usuarios(id),
FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
```

**Ejemplo en SQL:**
```sql
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    precio_venta NUMERIC(10, 2),
    
    -- Campos de auditoría
    creado_por INTEGER,
    actualizado_por INTEGER,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (creado_por) REFERENCES usuarios(id),
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
);
```

---

### **Uso en Modelos**

```php
<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Producto extends Model
{
    use HasAudit, HasBolivianTimestamps;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'categoria_id',
        'marca_id',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'activo',
    ];

    /**
     * Relación: Usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(\Modules\Auth\Models\Usuario::class, 'creado_por');
    }

    /**
     * Relación: Usuario que actualizó el registro por última vez
     */
    public function actualizador()
    {
        return $this->belongsTo(\Modules\Auth\Models\Usuario::class, 'actualizado_por');
    }
}
```

---

### **Comportamiento Automático**

#### **Al Crear un Registro:**

```php
// En el controlador o servicio
$producto = Producto::create([
    'nombre' => 'Cemento Portland',
    'codigo' => 'CEM-001',
    'precio_venta' => 55.00,
    'categoria_id' => 1,
]);

// Automáticamente se registra:
// creado_por = ID del usuario autenticado
// actualizado_por = ID del usuario autenticado
```

**Verificación en base de datos:**
```sql
SELECT id, nombre, creado_por, actualizado_por, creado_en
FROM productos
WHERE id = 1;

-- Resultado:
-- id | nombre            | creado_por | actualizado_por | creado_en
-- 1  | Cemento Portland  | 5          | 5               | 2025-12-04 10:30:00
```

---

#### **Al Actualizar un Registro:**

```php
// Usuario con ID 7 actualiza el producto
$producto = Producto::find(1);
$producto->precio_venta = 60.00;
$producto->save();

// Automáticamente se actualiza:
// actualizado_por = 7 (nuevo usuario)
// creado_por = 5 (permanece sin cambios)
```

**Verificación en base de datos:**
```sql
SELECT id, nombre, creado_por, actualizado_por, actualizado_en
FROM productos
WHERE id = 1;

-- Resultado:
-- id | nombre            | creado_por | actualizado_por | actualizado_en
-- 1  | Cemento Portland  | 5          | 7               | 2025-12-04 15:45:00
```

---

### **Acceso a la Información de Auditoría**

```php
// Obtener el producto con información de auditoría
$producto = Producto::with(['creador', 'actualizador'])->find(1);

// Acceder al usuario que lo creó
echo $producto->creador->nombre; // "Juan Pérez"
echo $producto->creador->email;  // "juan@frenad.com"

// Acceder al usuario que lo actualizó
echo $producto->actualizador->nombre; // "María López"

// En un Resource/Transformer
public function toArray($request)
{
    return [
        'id' => $this->id,
        'nombre' => $this->nombre,
        'precio_venta' => $this->precio_venta,
        'auditoria' => [
            'creado_por' => [
                'id' => $this->creador->id ?? null,
                'nombre' => $this->creador->nombre ?? 'Sistema',
            ],
            'creado_en' => $this->creado_en,
            'actualizado_por' => [
                'id' => $this->actualizador->id ?? null,
                'nombre' => $this->actualizador->nombre ?? 'Sistema',
            ],
            'actualizado_en' => $this->actualizado_en,
        ],
    ];
}
```

**Respuesta JSON:**
```json
{
    "id": 1,
    "nombre": "Cemento Portland",
    "precio_venta": 60.00,
    "auditoria": {
        "creado_por": {
            "id": 5,
            "nombre": "Juan Pérez"
        },
        "creado_en": "2025-12-04 10:30:00",
        "actualizado_por": {
            "id": 7,
            "nombre": "María López"
        },
        "actualizado_en": "2025-12-04 15:45:00"
    }
}
```

---

### **Casos Especiales**

#### **Usuario No Autenticado**

```php
// Si no hay usuario autenticado (ej: seeders, comandos artisan)
Auth::logout();

$producto = Producto::create([
    'nombre' => 'Producto de Prueba',
    'codigo' => 'TEST-001',
]);

// Los campos quedan como NULL:
// creado_por = NULL
// actualizado_por = NULL
```

#### **Sobrescribir Manualmente**

```php
// Forzar un usuario específico
$producto = new Producto([
    'nombre' => 'Producto Especial',
    'codigo' => 'ESP-001',
]);

$producto->creado_por = 1; // Usuario administrador
$producto->actualizado_por = 1;
$producto->save();
```

---

## HasBolivianTimestamps Trait

### **Propósito**
Configurar automáticamente los timestamps de Laravel para usar la zona horaria de **Bolivia (America/La_Paz)** y nombres de columnas en español.

### **Ubicación**
```
backend/app/Modules/Shared/Traits/HasBolivianTimestamps.php
```

---

### **Código Completo**

```php
<?php

namespace Modules\Shared\Traits;

use DateTimeInterface;

trait HasBolivianTimestamps
{
    /**
     * Preparar fecha para serialización con zona horaria de Bolivia
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone('America/La_Paz')->format('Y-m-d H:i:s');
    }

    /**
     * Nombre de la columna para "created_at"
     */
    public const CREATED_AT = 'creado_en';

    /**
     * Nombre de la columna para "updated_at"
     */
    public const UPDATED_AT = 'actualizado_en';
}
```

---

### **Campos en Base de Datos**

```sql
-- En lugar de created_at y updated_at
creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Ejemplo completo:**
```sql
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    
    -- Timestamps en español con zona horaria Bolivia
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Auditoría
    creado_por INTEGER,
    actualizado_por INTEGER,
    
    FOREIGN KEY (creado_por) REFERENCES usuarios(id),
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
);
```

---

### **Uso en Modelos**

```php
<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Categoria extends Model
{
    use HasAudit, HasBolivianTimestamps;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Las columnas de timestamps son automáticamente:
    // creado_en (en lugar de created_at)
    // actualizado_en (en lugar de updated_at)
}
```

---

### **Comportamiento**

#### **1. Nombres de Columnas en Español**

```php
// Laravel busca automáticamente:
$categoria->creado_en;      // En lugar de created_at
$categoria->actualizado_en;  // En lugar de updated_at

// Métodos de Eloquent funcionan normalmente:
$categorias = Categoria::orderBy('creado_en', 'desc')->get();
$categorias = Categoria::whereDate('actualizado_en', '2025-12-04')->get();
```

---

#### **2. Zona Horaria Bolivia**

```php
// Al crear
$categoria = Categoria::create([
    'nombre' => 'Herramientas',
]);

// La fecha se guarda en zona horaria Bolivia
echo $categoria->creado_en;
// Output: 2025-12-04 10:30:00 (hora de Bolivia)

// Al serializar a JSON
return response()->json($categoria);

// JSON automáticamente usa zona horaria Bolivia:
{
    "id": 1,
    "nombre": "Herramientas",
    "creado_en": "2025-12-04 10:30:00",
    "actualizado_en": "2025-12-04 10:30:00"
}
```

---

#### **3. Formato de Fechas**

```php
// Formato por defecto: Y-m-d H:i:s
$categoria->creado_en; // "2025-12-04 10:30:00"

// Formatear manualmente
$categoria->creado_en->format('d/m/Y');        // "04/12/2025"
$categoria->creado_en->format('d/m/Y H:i');    // "04/12/2025 10:30"

// Usar helper global
echo format_bolivian_date($categoria->creado_en);
// Output: "04/12/2025 10:30:00"
```

---

### **Configuración Global de Zona Horaria**

**Archivo:** `backend/config/app.php`

```php
return [
    'timezone' => 'America/La_Paz',
    
    'locale' => 'es',
    
    'fallback_locale' => 'es',
    
    'faker_locale' => 'es_ES',
];
```

**Archivo:** `backend/.env`

```env
APP_TIMEZONE=America/La_Paz
TZ=America/La_Paz
```

---

## Uso Combinado

### **Modelo Completo con Ambos Traits**

```php
<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Shared\Traits\HasBolivianTimestamps;

class Venta extends Model
{
    use HasAudit, HasBolivianTimestamps;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'tipo_comprobante_id',
        'numero_comprobante',
        'fecha_venta',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'metodo_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(\Modules\Customer\Models\Cliente::class);
    }

    /**
     * Relación con usuario (vendedor)
     */
    public function usuario()
    {
        return $this->belongsTo(\Modules\Auth\Models\Usuario::class);
    }

    /**
     * Relación con detalles de venta
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    /**
     * Relación: Usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(\Modules\Auth\Models\Usuario::class, 'creado_por');
    }

    /**
     * Relación: Usuario que actualizó el registro
     */
    public function actualizador()
    {
        return $this->belongsTo(\Modules\Auth\Models\Usuario::class, 'actualizado_por');
    }
}
```

---

### **Resource con Auditoría Completa**

```php
<?php

namespace Modules\Sales\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'numero_comprobante' => $this->numero_comprobante,
            'fecha_venta' => $this->fecha_venta->format('Y-m-d'),
            'cliente' => [
                'id' => $this->cliente->id,
                'nombre' => $this->cliente->nombre_completo,
                'nit' => $this->cliente->nit,
            ],
            'vendedor' => [
                'id' => $this->usuario->id,
                'nombre' => $this->usuario->nombre,
            ],
            'totales' => [
                'subtotal' => (float) $this->subtotal,
                'descuento' => (float) $this->descuento,
                'impuesto' => (float) $this->impuesto,
                'total' => (float) $this->total,
                'total_formatted' => format_currency($this->total),
            ],
            'estado' => $this->estado,
            'metodo_pago' => $this->metodo_pago,
            
            // Información de auditoría
            'auditoria' => [
                'creado_por' => [
                    'id' => $this->creador->id ?? null,
                    'nombre' => $this->creador->nombre ?? 'Sistema',
                ],
                'creado_en' => $this->creado_en->format('d/m/Y H:i:s'),
                'actualizado_por' => [
                    'id' => $this->actualizador->id ?? null,
                    'nombre' => $this->actualizador->nombre ?? 'Sistema',
                ],
                'actualizado_en' => $this->actualizado_en->format('d/m/Y H:i:s'),
            ],
            
            // Detalles de venta
            'detalles' => DetalleVentaResource::collection($this->whenLoaded('detalles')),
        ];
    }
}
```

---

### **Respuesta JSON Completa**

```json
{
    "id": 123,
    "numero_comprobante": "FAC-00123",
    "fecha_venta": "2025-12-04",
    "cliente": {
        "id": 45,
        "nombre": "Constructora ABC S.R.L.",
        "nit": "1234567890"
    },
    "vendedor": {
        "id": 8,
        "nombre": "Carlos Ramírez"
    },
    "totales": {
        "subtotal": 1500.00,
        "descuento": 150.00,
        "impuesto": 175.50,
        "total": 1525.50,
        "total_formatted": "Bs 1,525.50"
    },
    "estado": "completada",
    "metodo_pago": "efectivo",
    "auditoria": {
        "creado_por": {
            "id": 8,
            "nombre": "Carlos Ramírez"
        },
        "creado_en": "04/12/2025 10:30:00",
        "actualizado_por": {
            "id": 3,
            "nombre": "Gerente General"
        },
        "actualizado_en": "04/12/2025 15:45:00"
    }
}
```

---

## Testing

### **Test de HasAudit Trait**

```php
<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Modules\Product\Models\Producto;
use Modules\Auth\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasAuditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registra_usuario_al_crear()
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario, 'api');

        $producto = Producto::create([
            'nombre' => 'Test Producto',
            'codigo' => 'TEST-001',
            'precio_venta' => 100.00,
            'categoria_id' => 1,
        ]);

        $this->assertEquals($usuario->id, $producto->creado_por);
        $this->assertEquals($usuario->id, $producto->actualizado_por);
    }

    /** @test */
    public function registra_usuario_al_actualizar()
    {
        // Usuario que crea
        $creador = Usuario::factory()->create();
        $this->actingAs($creador, 'api');

        $producto = Producto::create([
            'nombre' => 'Test Producto',
            'codigo' => 'TEST-001',
            'precio_venta' => 100.00,
            'categoria_id' => 1,
        ]);

        // Usuario que actualiza
        $actualizador = Usuario::factory()->create();
        $this->actingAs($actualizador, 'api');

        $producto->precio_venta = 150.00;
        $producto->save();

        // Verificar
        $producto->refresh();
        $this->assertEquals($creador->id, $producto->creado_por);
        $this->assertEquals($actualizador->id, $producto->actualizado_por);
    }

    /** @test */
    public function permite_null_si_no_hay_usuario()
    {
        Auth::logout();

        $producto = Producto::create([
            'nombre' => 'Test Producto',
            'codigo' => 'TEST-001',
            'precio_venta' => 100.00,
            'categoria_id' => 1,
        ]);

        $this->assertNull($producto->creado_por);
        $this->assertNull($producto->actualizado_por);
    }
}
```

---

### **Test de HasBolivianTimestamps Trait**

```php
<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Modules\Product\Models\Categoria;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasBolivianTimestampsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usa_nombres_en_espanol()
    {
        $categoria = Categoria::create([
            'nombre' => 'Test Categoría',
        ]);

        // Verificar que existen los campos en español
        $this->assertNotNull($categoria->creado_en);
        $this->assertNotNull($categoria->actualizado_en);
        
        // Verificar que son instancias de Carbon
        $this->assertInstanceOf(Carbon::class, $categoria->creado_en);
        $this->assertInstanceOf(Carbon::class, $categoria->actualizado_en);
    }

    /** @test */
    public function usa_zona_horaria_bolivia()
    {
        $categoria = Categoria::create([
            'nombre' => 'Test Categoría',
        ]);

        $json = $categoria->toArray();

        // Verificar formato de fecha
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $json['creado_en']
        );
    }

    /** @test */
    public function actualiza_timestamp_al_modificar()
    {
        $categoria = Categoria::create([
            'nombre' => 'Original',
        ]);

        $creadoEn = $categoria->creado_en;
        
        sleep(1); // Esperar 1 segundo

        $categoria->nombre = 'Modificado';
        $categoria->save();

        $categoria->refresh();

        // creado_en no cambia
        $this->assertEquals($creadoEn->timestamp, $categoria->creado_en->timestamp);
        
        // actualizado_en sí cambia
        $this->assertGreaterThan($creadoEn->timestamp, $categoria->actualizado_en->timestamp);
    }
}
```

---

## Mejores Prácticas

### ✅ **DO - Hacer**

```php
// Usar ambos traits en modelos de negocio
class Venta extends Model
{
    use HasAudit, HasBolivianTimestamps;
}

// Incluir auditoría en Resources
public function toArray($request)
{
    return [
        'id' => $this->id,
        'auditoria' => [
            'creado_por' => $this->creador->nombre ?? 'Sistema',
            'creado_en' => $this->creado_en,
        ],
    ];
}

// Verificar permisos basados en auditoría
if ($producto->creado_por === auth()->id()) {
    // El usuario puede editar su propio producto
}
```

---

### ❌ **DON'T - No Hacer**

```php
// No modificar manualmente creado_por/actualizado_por en updates
$producto->creado_por = 999; // ❌ Esto rompe la auditoría
$producto->save();

// No omitir los traits en modelos importantes
class Venta extends Model
{
    // ❌ Sin auditoría = no hay trazabilidad
}

// No mezclar nombres de columnas
protected $fillable = [
    'created_at', // ❌ Usar 'creado_en'
    'updated_at', // ❌ Usar 'actualizado_en'
];
```

---

## Resumen

### **HasAudit**
- ✅ Registra automáticamente quién creó/actualizó
- ✅ Requiere columnas `creado_por` y `actualizado_por`
- ✅ Funciona con usuarios autenticados
- ✅ Permite NULL si no hay sesión

### **HasBolivianTimestamps**
- ✅ Usa nombres en español (`creado_en`, `actualizado_en`)
- ✅ Zona horaria America/La_Paz
- ✅ Formato Y-m-d H:i:s
- ✅ Compatible con todos los métodos de Eloquent

### **Uso Recomendado**
Todos los modelos de negocio deben usar **ambos traits** para mantener trazabilidad completa y consistencia en fechas.

---

## Referencias

- [Laravel Traits](https://www.php.net/manual/en/language.oop5.traits.php)
- [Eloquent Events](https://laravel.com/docs/10.x/eloquent#events)
- [Carbon Timezone](https://carbon.nesbot.com/docs/#api-timezone)

---

**Documento actualizado:** 4 de diciembre de 2025  
**Autor:** Equipo de Desarrollo Ferretería Frenad
