<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Sales\Services\VentaService;
use Modules\Sales\Models\Venta;
use Modules\Product\Models\Producto;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\Almacen;
use Modules\Customer\Models\Cliente;
use Modules\Auth\Models\Usuario;
use Exception;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - VentaService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de ventas, especialmente
 * los cálculos de totales, descuentos, IVA y manejo de stock.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de caminos: Cada ruta posible del código
 * - Pruebas de cálculo: Verificar operaciones matemáticas
 * - Pruebas de transacciones: Verificar rollback en errores
 * - Valores límite: Cantidades y precios extremos
 * 
 * MÓDULO: Sales
 * SERVICIO: VentaService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class VentaServiceTest extends TestCase
{
    protected VentaService $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VentaService();
        
        $this->user = Usuario::first();
        if ($this->user) {
            $this->actingAs($this->user);
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE CÁLCULO DE TOTALES (calcularTotales)
     * =========================================================================
     */

    /**
     * UNIT-VTA-001: Cálculo de subtotal simple
     * 
     * CAJA BLANCA: Verifica líneas 153-159 del método calcularTotales
     * FÓRMULA: subtotal = Σ(cantidad * precio_unitario - descuento_item)
     */
    public function test_UNIT_VTA001_calculate_simple_subtotal(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 2, 'precio_unitario' => 10.00, 'descuento' => 0],
            ['cantidad' => 3, 'precio_unitario' => 15.00, 'descuento' => 0],
        ];
        // Expected: (2*10) + (3*15) = 20 + 45 = 65

        // Act: Usar reflexión para acceder al método protegido
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, 0);

        // Assert
        $this->assertEquals(65.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['descuento_total']);
        $this->assertEquals(65.00, $result['total']);
    }

    /**
     * UNIT-VTA-002: Cálculo con descuento por item
     * 
     * CAJA BLANCA: Verifica línea 157 ($descuentoItem = $item['descuento'] ?? 0)
     */
    public function test_UNIT_VTA002_calculate_with_item_discount(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 2, 'precio_unitario' => 100.00, 'descuento' => 20.00],
            ['cantidad' => 1, 'precio_unitario' => 50.00, 'descuento' => 5.00],
        ];
        // Expected: (2*100 - 20) + (1*50 - 5) = 180 + 45 = 225

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, 0);

        // Assert
        $this->assertEquals(225.00, $result['subtotal']);
    }

    /**
     * UNIT-VTA-003: Cálculo con descuento global porcentual
     * 
     * CAJA BLANCA: Verifica líneas 161-162 (descuento global)
     * FÓRMULA: descuentoGlobal = (subtotal * descuentoPorcentaje) / 100
     */
    public function test_UNIT_VTA003_calculate_with_global_discount(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 10, 'precio_unitario' => 10.00, 'descuento' => 0],
        ];
        $descuentoPorcentaje = 10; // 10%
        // Subtotal: 100, Descuento: 10, Total: 90

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, $descuentoPorcentaje);

        // Assert
        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(10.00, $result['descuento_total']);
        $this->assertEquals(90.00, $result['total']);
    }

    /**
     * UNIT-VTA-004: Verificar redondeo a 2 decimales
     * 
     * CAJA BLANCA: Verifica round() en líneas 172-175
     */
    public function test_UNIT_VTA004_verify_rounding_to_two_decimals(): void
    {
        // Arrange: Valores que generan muchos decimales
        $items = [
            ['cantidad' => 3, 'precio_unitario' => 7.33, 'descuento' => 0],
        ];
        // 3 * 7.33 = 21.99

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, 0);

        // Assert: Verificar máximo 2 decimales
        $this->assertEquals(21.99, $result['subtotal']);
        $this->assertIsFloat($result['subtotal']);
    }

    /**
     * UNIT-VTA-005: Cálculo combinado descuentos item + global
     * 
     * CAJA BLANCA: Verifica flujo completo del cálculo
     */
    public function test_UNIT_VTA005_calculate_combined_discounts(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 5, 'precio_unitario' => 20.00, 'descuento' => 10.00], // 100 - 10 = 90
            ['cantidad' => 2, 'precio_unitario' => 25.00, 'descuento' => 5.00],  // 50 - 5 = 45
        ];
        $descuentoPorcentaje = 5; // 5%
        // Subtotal: 90 + 45 = 135
        // Descuento global: 135 * 0.05 = 6.75
        // Total: 135 - 6.75 = 128.25

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, $descuentoPorcentaje);

        // Assert
        $this->assertEquals(135.00, $result['subtotal']);
        $this->assertEquals(6.75, $result['descuento_total']);
        $this->assertEquals(128.25, $result['total']);
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE EN CÁLCULOS
     * =========================================================================
     */

    /**
     * UNIT-VTA-006: Cálculo con cantidad 1 (valor mínimo)
     * 
     * VALORES LÍMITE: Cantidad mínima vendible
     */
    public function test_UNIT_VTA006_calculate_with_minimum_quantity(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 1, 'precio_unitario' => 0.01, 'descuento' => 0],
        ];

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, 0);

        // Assert
        $this->assertEquals(0.01, $result['total']);
    }

    /**
     * UNIT-VTA-007: Cálculo con descuento 100%
     * 
     * VALORES LÍMITE: Descuento máximo posible
     */
    public function test_UNIT_VTA007_calculate_with_100_percent_discount(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 10, 'precio_unitario' => 100.00, 'descuento' => 0],
        ];
        $descuentoPorcentaje = 100; // 100%

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, $descuentoPorcentaje);

        // Assert
        $this->assertEquals(1000.00, $result['subtotal']);
        $this->assertEquals(1000.00, $result['descuento_total']);
        $this->assertEquals(0.00, $result['total']);
    }

    /**
     * UNIT-VTA-008: Cálculo con múltiples items (>10)
     * 
     * VALORES LÍMITE: Muchos items en una venta
     */
    public function test_UNIT_VTA008_calculate_with_many_items(): void
    {
        // Arrange: 15 items diferentes
        $items = [];
        $expectedSubtotal = 0;
        for ($i = 1; $i <= 15; $i++) {
            $items[] = [
                'cantidad' => $i,
                'precio_unitario' => $i * 10.00,
                'descuento' => 0
            ];
            $expectedSubtotal += $i * $i * 10; // i * (i*10)
        }

        // Act
        $method = new \ReflectionMethod(VentaService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items, 0);

        // Assert
        $this->assertEquals($expectedSubtotal, $result['subtotal']);
    }

    /*
     * =========================================================================
     * PRUEBAS DE LISTADO
     * =========================================================================
     */

    /**
     * UNIT-VTA-009: Listado sin filtros
     * 
     * CAJA BLANCA: Verifica ruta por defecto del método list()
     */
    public function test_UNIT_VTA009_list_without_filters(): void
    {
        // Act
        $result = $this->service->list([]);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue(method_exists($result, 'items'));
    }

    /**
     * UNIT-VTA-010: Filtro por estado
     * 
     * CAJA BLANCA: Verifica condición isset($filters['estado']) línea 22
     */
    public function test_UNIT_VTA010_list_filters_by_status(): void
    {
        // Act
        $resultCompletadas = $this->service->list(['estado' => 'completada']);
        $resultAnuladas = $this->service->list(['estado' => 'anulada']);

        // Assert
        foreach ($resultCompletadas->items() as $venta) {
            $this->assertEquals('completada', $venta->estado);
        }
        foreach ($resultAnuladas->items() as $venta) {
            $this->assertEquals('anulada', $venta->estado);
        }
    }

    /**
     * UNIT-VTA-011: Filtro por tipo de venta
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['tipo_venta']) línea 26
     */
    public function test_UNIT_VTA011_list_filters_by_sale_type(): void
    {
        // Act
        $resultContado = $this->service->list(['tipo_venta' => 'contado']);
        $resultCredito = $this->service->list(['tipo_venta' => 'credito']);

        // Assert
        foreach ($resultContado->items() as $venta) {
            $this->assertEquals('contado', $venta->tipo_venta);
        }
        foreach ($resultCredito->items() as $venta) {
            $this->assertEquals('credito', $venta->tipo_venta);
        }
    }

    /**
     * UNIT-VTA-012: Filtro por método de pago
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['metodo_pago']) línea 30
     */
    public function test_UNIT_VTA012_list_filters_by_payment_method(): void
    {
        // Act
        $resultEfectivo = $this->service->list(['metodo_pago' => 'efectivo']);

        // Assert
        foreach ($resultEfectivo->items() as $venta) {
            $this->assertEquals('efectivo', $venta->metodo_pago);
        }
    }

    /**
     * UNIT-VTA-013: Filtro por cliente
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['cliente_id']) línea 38
     */
    public function test_UNIT_VTA013_list_filters_by_customer(): void
    {
        // Arrange
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay clientes en la base de datos');
        }

        // Act
        $result = $this->service->list(['cliente_id' => $cliente->id]);

        // Assert
        foreach ($result->items() as $venta) {
            $this->assertEquals($cliente->id, $venta->cliente_id);
        }
    }

    /**
     * UNIT-VTA-014: Filtro ventas de hoy
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['hoy']) línea 46
     */
    public function test_UNIT_VTA014_list_filters_by_today(): void
    {
        // Act
        $result = $this->service->list(['hoy' => true]);

        // Assert
        $today = now()->format('Y-m-d');
        foreach ($result->items() as $venta) {
            $this->assertEquals($today, $venta->fecha_venta->format('Y-m-d'));
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALIDACIÓN DE NEGOCIO
     * =========================================================================
     */

    /**
     * UNIT-VTA-015: Venta sin items lanza excepción
     * 
     * CAJA BLANCA: Verifica validación líneas 67-69
     */
    public function test_UNIT_VTA015_create_without_items_throws_exception(): void
    {
        // Arrange
        $data = [
            'tipo_venta' => 'contado',
            'metodo_pago' => 'efectivo',
            'items' => [], // Sin items
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Debe agregar al menos un producto a la venta');

        // Act
        $this->service->create($data);
    }

    /**
     * UNIT-VTA-016: Venta a crédito sin cliente debe ser validada
     * 
     * CAJA BLANCA: Verifica validación en crearCreditoCliente() línea 260
     * 
     * NOTA: Esta prueba verifica que el sistema rechaza ventas a crédito
     * sin cliente, ya sea por validación del servicio o restricción de BD.
     */
    public function test_UNIT_VTA016_credit_sale_without_customer_throws_exception(): void
    {
        // Arrange
        $producto = Producto::whereHas('inventarios', function($q) {
            $q->where('cantidad_actual', '>', 0);
        })->first();
        
        if (!$producto) {
            $this->markTestSkipped('No hay productos con stock');
        }

        $inventario = $producto->inventarios->first();

        $data = [
            'tipo_venta' => 'credito',
            'metodo_pago' => 'efectivo', // Método de pago válido
            'cliente_id' => null,
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'almacen_id' => $inventario->almacen_id,
                    'cantidad' => 1,
                    'precio_unitario' => $producto->precio_venta,
                    'unidad_id' => $producto->unidad_base_id,
                ]
            ],
        ];

        // Assert: El sistema debe rechazar ventas a crédito sin cliente
        $this->expectException(Exception::class);
        
        // Act
        $this->service->create($data);
    }

    /*
     * =========================================================================
     * PRUEBAS DE ORDENAMIENTO
     * =========================================================================
     */

    /**
     * UNIT-VTA-017: Ventas ordenadas por fecha descendente
     * 
     * CAJA BLANCA: Verifica línea 53 orderBy('fecha_venta', 'desc')
     */
    public function test_UNIT_VTA017_list_orders_by_date_descending(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 10]);

        // Assert
        $items = $result->items();
        if (count($items) >= 2) {
            for ($i = 0; $i < count($items) - 1; $i++) {
                $this->assertGreaterThanOrEqual(
                    $items[$i + 1]->fecha_venta,
                    $items[$i]->fecha_venta,
                    'Las ventas deben estar ordenadas por fecha descendente'
                );
            }
        }
        $this->assertTrue(true);
    }

    /*
     * =========================================================================
     * PRUEBAS DE PAGINACIÓN
     * =========================================================================
     */

    /**
     * UNIT-VTA-018: Paginación personalizada
     * 
     * CAJA BLANCA: Verifica parámetro per_page línea 55
     */
    public function test_UNIT_VTA018_custom_pagination(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 5]);

        // Assert
        $this->assertEquals(5, $result->perPage());
    }
}
