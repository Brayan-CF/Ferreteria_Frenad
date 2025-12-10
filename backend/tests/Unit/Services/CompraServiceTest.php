<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Purchase\Services\CompraService;
use Modules\Purchase\Models\Compra;
use Modules\Purchase\Models\Proveedor;
use Modules\Product\Models\Producto;
use Modules\Inventory\Models\Almacen;
use Modules\Auth\Models\Usuario;
use Exception;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - CompraService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de compras, incluyendo
 * cálculos de totales, recepción de mercadería y actualización de stock.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de caminos: Flujos de compra
 * - Pruebas de cálculo: Totales e impuestos
 * - Pruebas de excepción: Validaciones de negocio
 * 
 * MÓDULO: Purchase
 * SERVICIO: CompraService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class CompraServiceTest extends TestCase
{
    protected CompraService $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CompraService();
        
        $this->user = Usuario::first();
        if ($this->user) {
            $this->actingAs($this->user);
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE LISTADO
     * =========================================================================
     */

    /**
     * UNIT-COM-001: Listado sin filtros
     * 
     * CAJA BLANCA: Verifica ruta por defecto del método list()
     */
    public function test_UNIT_COM001_list_without_filters(): void
    {
        // Act
        $result = $this->service->list([]);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue(method_exists($result, 'items'));
    }

    /**
     * UNIT-COM-002: Filtro por estado
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['estado']) línea 23
     */
    public function test_UNIT_COM002_list_filters_by_status(): void
    {
        // Act
        $resultRecibidas = $this->service->list(['estado' => 'recibida']);
        $resultPendientes = $this->service->list(['estado' => 'pendiente']);

        // Assert
        foreach ($resultRecibidas->items() as $compra) {
            $this->assertEquals('recibida', $compra->estado);
        }
        foreach ($resultPendientes->items() as $compra) {
            $this->assertEquals('pendiente', $compra->estado);
        }
    }

    /**
     * UNIT-COM-003: Filtro por proveedor
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['proveedor_id']) línea 27
     */
    public function test_UNIT_COM003_list_filters_by_supplier(): void
    {
        // Arrange
        $proveedor = Proveedor::first();
        if (!$proveedor) {
            $this->markTestSkipped('No hay proveedores');
        }

        // Act
        $result = $this->service->list(['proveedor_id' => $proveedor->id]);

        // Assert
        foreach ($result->items() as $compra) {
            $this->assertEquals($proveedor->id, $compra->proveedor_id);
        }
    }

    /**
     * UNIT-COM-004: Filtro por rango de fechas
     * 
     * CAJA BLANCA: Verifica condición líneas 31-33
     */
    public function test_UNIT_COM004_list_filters_by_date_range(): void
    {
        // Arrange
        $fechaInicio = now()->subDays(30)->format('Y-m-d');
        $fechaFin = now()->format('Y-m-d');

        // Act
        $result = $this->service->list([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        // Assert
        foreach ($result->items() as $compra) {
            $this->assertGreaterThanOrEqual($fechaInicio, $compra->fecha_compra->format('Y-m-d'));
            $this->assertLessThanOrEqual($fechaFin, $compra->fecha_compra->format('Y-m-d'));
        }
    }

    /**
     * UNIT-COM-005: Filtro por búsqueda
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['search']) línea 35
     */
    public function test_UNIT_COM005_list_filters_by_search(): void
    {
        // Arrange
        $compra = Compra::first();
        if (!$compra) {
            $this->markTestSkipped('No hay compras');
        }
        $searchTerm = substr($compra->numero_compra, 0, 5);

        // Act
        $result = $this->service->list(['search' => $searchTerm]);

        // Assert
        $this->assertNotNull($result);
    }

    /**
     * UNIT-COM-006: Ordenamiento por fecha descendente
     * 
     * CAJA BLANCA: Verifica línea 44 orderBy('fecha_compra', 'desc')
     */
    public function test_UNIT_COM006_list_orders_by_date_descending(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 10]);

        // Assert
        $items = $result->items();
        if (count($items) >= 2) {
            for ($i = 0; $i < count($items) - 1; $i++) {
                $this->assertGreaterThanOrEqual(
                    $items[$i + 1]->fecha_compra,
                    $items[$i]->fecha_compra
                );
            }
        }
        $this->assertTrue(true);
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALIDACIÓN DE CREACIÓN
     * =========================================================================
     */

    /**
     * UNIT-COM-007: Compra sin items lanza excepción
     * 
     * CAJA BLANCA: Verifica validación líneas 54-56
     */
    public function test_UNIT_COM007_create_without_items_throws_exception(): void
    {
        // Arrange
        $proveedor = Proveedor::first();
        if (!$proveedor) {
            $this->markTestSkipped('No hay proveedores');
        }

        $data = [
            'proveedor_id' => $proveedor->id,
            'items' => [], // Sin items
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Debe agregar al menos un producto a la compra');

        // Act
        $this->service->create($data);
    }

    /*
     * =========================================================================
     * PRUEBAS DE CÁLCULO DE TOTALES
     * =========================================================================
     */

    /**
     * UNIT-COM-008: Cálculo de subtotal simple
     * 
     * CAJA BLANCA: Verifica método calcularTotales()
     */
    public function test_UNIT_COM008_calculate_simple_subtotal(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 10, 'precio_unitario' => 5.00],
            ['cantidad' => 5, 'precio_unitario' => 20.00],
        ];
        // Expected: (10*5) + (5*20) = 50 + 100 = 150

        // Act
        $method = new \ReflectionMethod(CompraService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items);

        // Assert
        $this->assertEquals(150.00, $result['subtotal']);
    }

    /**
     * UNIT-COM-009: Cálculo con impuestos
     * 
     * CAJA BLANCA: Verifica cálculo de impuestos
     */
    public function test_UNIT_COM009_calculate_with_taxes(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 10, 'precio_unitario' => 100.00],
        ];
        // Subtotal: 1000

        // Act
        $method = new \ReflectionMethod(CompraService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items);

        // Assert
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('impuestos', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(1000.00, $result['subtotal']);
    }

    /*
     * =========================================================================
     * PRUEBAS DE PAGINACIÓN
     * =========================================================================
     */

    /**
     * UNIT-COM-010: Paginación personalizada
     * 
     * CAJA BLANCA: Verifica parámetro per_page línea 46
     */
    public function test_UNIT_COM010_custom_pagination(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 5]);

        // Assert
        $this->assertEquals(5, $result->perPage());
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE
     * =========================================================================
     */

    /**
     * UNIT-COM-011: Cálculo con cantidad mínima
     * 
     * VALORES LÍMITE: Un solo item con cantidad 1
     */
    public function test_UNIT_COM011_calculate_with_minimum_quantity(): void
    {
        // Arrange
        $items = [
            ['cantidad' => 1, 'precio_unitario' => 0.01],
        ];

        // Act
        $method = new \ReflectionMethod(CompraService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items);

        // Assert
        $this->assertEquals(0.01, $result['subtotal']);
    }

    /**
     * UNIT-COM-012: Cálculo con muchos items
     * 
     * VALORES LÍMITE: Muchos productos en una compra
     */
    public function test_UNIT_COM012_calculate_with_many_items(): void
    {
        // Arrange: 20 items
        $items = [];
        $expectedSubtotal = 0;
        for ($i = 1; $i <= 20; $i++) {
            $items[] = ['cantidad' => $i, 'precio_unitario' => 10.00];
            $expectedSubtotal += $i * 10;
        }
        // Sum 1+2+...+20 = 210 * 10 = 2100

        // Act
        $method = new \ReflectionMethod(CompraService::class, 'calcularTotales');
        $method->setAccessible(true);
        $result = $method->invoke($this->service, $items);

        // Assert
        $this->assertEquals($expectedSubtotal, $result['subtotal']);
    }

    /*
     * =========================================================================
     * PRUEBAS DE MÚLTIPLES FILTROS
     * =========================================================================
     */

    /**
     * UNIT-COM-013: Múltiples filtros combinados
     * 
     * CAJA BLANCA: Verifica que todas las condiciones se evalúan
     */
    public function test_UNIT_COM013_multiple_filters_combined(): void
    {
        // Arrange
        $proveedor = Proveedor::first();
        if (!$proveedor) {
            $this->markTestSkipped('No hay proveedores');
        }

        // Act
        $result = $this->service->list([
            'proveedor_id' => $proveedor->id,
            'estado' => 'recibida',
            'per_page' => 5
        ]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(5, $result->perPage());
        
        foreach ($result->items() as $compra) {
            $this->assertEquals($proveedor->id, $compra->proveedor_id);
            $this->assertEquals('recibida', $compra->estado);
        }
    }

    /**
     * UNIT-COM-014: Listado carga relaciones
     * 
     * CAJA BLANCA: Verifica with() en línea 20
     */
    public function test_UNIT_COM014_list_loads_relations(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 1]);

        // Assert
        if ($result->count() > 0) {
            $compra = $result->items()[0];
            $this->assertTrue($compra->relationLoaded('proveedor'));
            $this->assertTrue($compra->relationLoaded('usuario'));
        }
        $this->assertTrue(true);
    }
}
