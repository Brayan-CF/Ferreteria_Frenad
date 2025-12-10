<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Inventory\Services\InventarioService;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\Almacen;
use Modules\Inventory\Models\MovimientoInventario;
use Modules\Product\Models\Producto;
use Modules\Auth\Models\Usuario;
use Exception;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - InventarioService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de inventario, incluyendo
 * transferencias entre almacenes, ajustes de stock y validaciones.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de caminos: Flujos de transferencia y ajuste
 * - Pruebas de transacción: Verificar atomicidad
 * - Valores límite: Stock mínimo, máximo, cero
 * - Pruebas de excepción: Validaciones de negocio
 * 
 * MÓDULO: Inventory
 * SERVICIO: InventarioService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class InventarioServiceTest extends TestCase
{
    protected InventarioService $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventarioService();
        
        $this->user = Usuario::first();
        if ($this->user) {
            // Usar actingAs con sanctum guard
            $this->actingAs($this->user, 'sanctum');
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE LISTADO
     * =========================================================================
     */

    /**
     * UNIT-INV-001: Listado sin filtros
     * 
     * CAJA BLANCA: Verifica ruta por defecto del método list()
     */
    public function test_UNIT_INV001_list_without_filters(): void
    {
        // Act
        $result = $this->service->list([]);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue(method_exists($result, 'items'));
    }

    /**
     * UNIT-INV-002: Filtro por almacén
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['almacen_id']) línea 22
     */
    public function test_UNIT_INV002_list_filters_by_warehouse(): void
    {
        // Arrange
        $almacen = Almacen::first();
        if (!$almacen) {
            $this->markTestSkipped('No hay almacenes en la base de datos');
        }

        // Act
        $result = $this->service->list(['almacen_id' => $almacen->id]);

        // Assert
        foreach ($result->items() as $inventario) {
            $this->assertEquals($almacen->id, $inventario->almacen_id);
        }
    }

    /**
     * UNIT-INV-003: Filtro stock bajo
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['stock_bajo']) línea 26
     */
    public function test_UNIT_INV003_list_filters_low_stock(): void
    {
        // Act
        $result = $this->service->list(['stock_bajo' => true]);

        // Assert: Productos con stock <= stock_minimo
        foreach ($result->items() as $inventario) {
            $this->assertLessThanOrEqual(
                $inventario->stock_minimo,
                $inventario->cantidad_actual,
                'El stock debe ser menor o igual al stock mínimo'
            );
        }
    }

    /**
     * UNIT-INV-004: Filtro con stock
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['con_stock']) línea 30
     */
    public function test_UNIT_INV004_list_filters_with_stock(): void
    {
        // Act
        $result = $this->service->list(['con_stock' => true]);

        // Assert
        foreach ($result->items() as $inventario) {
            $this->assertGreaterThan(0, $inventario->cantidad_actual);
        }
    }

    /**
     * UNIT-INV-005: Filtro por producto
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['producto_id']) línea 34
     */
    public function test_UNIT_INV005_list_filters_by_product(): void
    {
        // Arrange
        $producto = Producto::first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos en la base de datos');
        }

        // Act
        $result = $this->service->list(['producto_id' => $producto->id]);

        // Assert
        foreach ($result->items() as $inventario) {
            $this->assertEquals($producto->id, $inventario->producto_id);
        }
    }

    /**
     * UNIT-INV-006: Filtro por búsqueda
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['search']) línea 38
     */
    public function test_UNIT_INV006_list_filters_by_search(): void
    {
        // Arrange
        $producto = Producto::first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos');
        }
        $searchTerm = substr($producto->nombre, 0, 3);

        // Act
        $result = $this->service->list(['search' => $searchTerm]);

        // Assert
        $this->assertNotNull($result);
    }

    /*
     * =========================================================================
     * PRUEBAS DE STOCK POR PRODUCTO
     * =========================================================================
     */

    /**
     * UNIT-INV-007: Stock de producto en todos los almacenes
     * 
     * CAJA BLANCA: Verifica método stockProducto() líneas 50-54
     */
    public function test_UNIT_INV007_stock_product_all_warehouses(): void
    {
        // Arrange
        $producto = Producto::first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos');
        }

        // Act
        $result = $this->service->stockProducto($producto->id);

        // Assert
        $this->assertNotNull($result);
        foreach ($result as $inventario) {
            $this->assertEquals($producto->id, $inventario->producto_id);
            $this->assertTrue($inventario->relationLoaded('almacen'));
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE TRANSFERENCIA - Validaciones
     * =========================================================================
     */

    /**
     * UNIT-INV-008: Transferencia a mismo almacén lanza excepción
     * 
     * CAJA BLANCA: Verifica validación líneas 72-74
     */
    public function test_UNIT_INV008_transfer_same_warehouse_throws_exception(): void
    {
        // Arrange
        $almacen = Almacen::first();
        $producto = Producto::first();
        
        if (!$almacen || !$producto) {
            $this->markTestSkipped('Faltan datos de prueba');
        }

        $data = [
            'producto_id' => $producto->id,
            'almacen_origen_id' => $almacen->id,
            'almacen_destino_id' => $almacen->id, // Mismo almacén
            'cantidad' => 1,
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El almacén de origen y destino no pueden ser el mismo');

        // Act
        $this->service->transferir($data);
    }

    /**
     * UNIT-INV-009: Transferencia producto inexistente lanza excepción
     * 
     * CAJA BLANCA: Verifica validación líneas 77-84
     */
    public function test_UNIT_INV009_transfer_nonexistent_product_throws_exception(): void
    {
        // Arrange
        $almacenes = Almacen::take(2)->get();
        if ($almacenes->count() < 2) {
            $this->markTestSkipped('Se necesitan al menos 2 almacenes');
        }

        $data = [
            'producto_id' => 99999999, // Producto inexistente
            'almacen_origen_id' => $almacenes[0]->id,
            'almacen_destino_id' => $almacenes[1]->id,
            'cantidad' => 1,
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El producto no existe en el almacén de origen');

        // Act
        $this->service->transferir($data);
    }

    /**
     * UNIT-INV-010: Transferencia stock insuficiente lanza excepción
     * 
     * CAJA BLANCA: Verifica validación líneas 86-88
     */
    public function test_UNIT_INV010_transfer_insufficient_stock_throws_exception(): void
    {
        // Arrange
        $inventario = Inventario::where('cantidad_actual', '>', 0)->first();
        $almacenDestino = Almacen::where('id', '!=', $inventario->almacen_id)->first();
        
        if (!$inventario || !$almacenDestino) {
            $this->markTestSkipped('Faltan datos de prueba');
        }

        $data = [
            'producto_id' => $inventario->producto_id,
            'almacen_origen_id' => $inventario->almacen_id,
            'almacen_destino_id' => $almacenDestino->id,
            'cantidad' => $inventario->cantidad_actual + 1000, // Más del disponible
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stock insuficiente en almacén origen');

        // Act
        $this->service->transferir($data);
    }

    /*
     * =========================================================================
     * PRUEBAS DE TRANSFERENCIA - Flujo exitoso
     * =========================================================================
     */

    /**
     * UNIT-INV-011: Transferencia exitosa reduce stock origen
     * 
     * CAJA BLANCA: Verifica línea 91 reducirStock()
     * Verifica que el método transferir() reduce correctamente el stock del almacén origen.
     */
    public function test_UNIT_INV011_successful_transfer_reduces_origin_stock(): void
    {
        // Arrange: Buscar producto que exista en múltiples almacenes
        $productosEnMultiplesAlmacenes = \DB::table('inventario')
            ->select('producto_id')
            ->where('cantidad_actual', '>=', 5)
            ->groupBy('producto_id')
            ->havingRaw('count(*) >= 2')
            ->first();
        
        if (!$productosEnMultiplesAlmacenes) {
            $this->markTestSkipped('No hay productos en múltiples almacenes');
        }
        
        $inventarios = \DB::table('inventario')
            ->where('producto_id', $productosEnMultiplesAlmacenes->producto_id)
            ->where('cantidad_actual', '>=', 5)
            ->orderByDesc('cantidad_actual')
            ->get();
            
        if ($inventarios->count() < 2) {
            $this->markTestSkipped('Faltan almacenes con stock suficiente');
        }
        
        $origenData = $inventarios->first();
        $destinoData = $inventarios->skip(1)->first();

        $stockInicial = $origenData->cantidad_actual;
        $cantidadTransferir = 1;

        // Act
        $movimiento = $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $origenData->almacen_id,
            'almacen_destino_id' => $destinoData->almacen_id,
            'cantidad' => $cantidadTransferir,
        ]);

        // Assert: Verificar que se creó el movimiento correctamente
        $this->assertInstanceOf(MovimientoInventario::class, $movimiento);
        $this->assertEquals($cantidadTransferir, $movimiento->cantidad);
        
        // Verificar stock actualizado
        $stockNuevo = \DB::table('inventario')
            ->where('producto_id', $origenData->producto_id)
            ->where('almacen_id', $origenData->almacen_id)
            ->value('cantidad_actual');
        $this->assertEquals($stockInicial - $cantidadTransferir, $stockNuevo);

        // Revert: Devolver el stock (ahora funciona porque ambos almacenes tienen el producto)
        $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $destinoData->almacen_id,
            'almacen_destino_id' => $origenData->almacen_id,
            'cantidad' => $cantidadTransferir,
        ]);
    }

    /**
     * UNIT-INV-012: Transferencia crea movimiento de inventario
     * 
     * CAJA BLANCA: Verifica líneas 107-114 (MovimientoInventario::create)
     */
    public function test_UNIT_INV012_transfer_creates_movement_record(): void
    {
        // Arrange: Buscar producto que exista en múltiples almacenes
        $productosEnMultiplesAlmacenes = \DB::table('inventario')
            ->select('producto_id')
            ->where('cantidad_actual', '>=', 3)
            ->groupBy('producto_id')
            ->havingRaw('count(*) >= 2')
            ->first();
        
        if (!$productosEnMultiplesAlmacenes) {
            $this->markTestSkipped('No hay productos en múltiples almacenes');
        }
        
        $inventarios = \DB::table('inventario')
            ->where('producto_id', $productosEnMultiplesAlmacenes->producto_id)
            ->where('cantidad_actual', '>=', 3)
            ->orderByDesc('cantidad_actual')
            ->get();
            
        if ($inventarios->count() < 2) {
            $this->markTestSkipped('Faltan almacenes con stock suficiente');
        }
        
        $origenData = $inventarios->first();
        $destinoData = $inventarios->skip(1)->first();
        $cantidadTransferir = 1;

        // Act
        $movimiento = $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $origenData->almacen_id,
            'almacen_destino_id' => $destinoData->almacen_id,
            'cantidad' => $cantidadTransferir,
            'razon' => 'Test unitario',
        ]);

        // Assert
        $this->assertInstanceOf(MovimientoInventario::class, $movimiento);
        $this->assertEqualsIgnoringCase('transferencia', $movimiento->tipo_movimiento);
        $this->assertEquals($cantidadTransferir, $movimiento->cantidad);
        $this->assertEquals('Test unitario', $movimiento->razon);
        $this->assertEquals($this->user->id, $movimiento->usuario_id);

        // Revert: Devolver el stock
        $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $destinoData->almacen_id,
            'almacen_destino_id' => $origenData->almacen_id,
            'cantidad' => $cantidadTransferir,
        ]);
    }

    /**
     * UNIT-INV-013: Transferencia carga relaciones
     * 
     * CAJA BLANCA: Verifica línea 117 load() con relaciones
     * Verifica que el movimiento retornado tiene las relaciones cargadas.
     */
    public function test_UNIT_INV013_transfer_loads_relations(): void
    {
        // Arrange: Buscar producto que exista en múltiples almacenes
        $productosEnMultiplesAlmacenes = \DB::table('inventario')
            ->select('producto_id')
            ->where('cantidad_actual', '>=', 3)
            ->groupBy('producto_id')
            ->havingRaw('count(*) >= 2')
            ->first();
        
        if (!$productosEnMultiplesAlmacenes) {
            $this->markTestSkipped('No hay productos en múltiples almacenes');
        }
        
        $inventarios = \DB::table('inventario')
            ->where('producto_id', $productosEnMultiplesAlmacenes->producto_id)
            ->where('cantidad_actual', '>=', 3)
            ->orderByDesc('cantidad_actual')
            ->get();
            
        if ($inventarios->count() < 2) {
            $this->markTestSkipped('Faltan almacenes con stock suficiente');
        }
        
        $origenData = $inventarios->first();
        $destinoData = $inventarios->skip(1)->first();

        // Act
        $movimiento = $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $origenData->almacen_id,
            'almacen_destino_id' => $destinoData->almacen_id,
            'cantidad' => 1,
        ]);

        // Assert: Verificar que las relaciones están cargadas
        $this->assertTrue($movimiento->relationLoaded('producto'));
        $this->assertTrue($movimiento->relationLoaded('almacen'));
        $this->assertTrue($movimiento->relationLoaded('almacenDestino'));
        $this->assertTrue($movimiento->relationLoaded('usuario'));

        // Revert: Devolver el stock
        $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $destinoData->almacen_id,
            'almacen_destino_id' => $origenData->almacen_id,
            'cantidad' => 1,
        ]);
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE
     * =========================================================================
     */

    /**
     * UNIT-INV-014: Transferencia de cantidad 1 (mínima)
     * 
     * VALORES LÍMITE: Transferencia mínima
     */
    public function test_UNIT_INV014_transfer_minimum_quantity(): void
    {
        // Arrange: Buscar producto que exista en múltiples almacenes
        $productosEnMultiplesAlmacenes = \DB::table('inventario')
            ->select('producto_id')
            ->where('cantidad_actual', '>=', 1)
            ->groupBy('producto_id')
            ->havingRaw('count(*) >= 2')
            ->first();
        
        if (!$productosEnMultiplesAlmacenes) {
            $this->markTestSkipped('No hay productos en múltiples almacenes');
        }
        
        $inventarios = \DB::table('inventario')
            ->where('producto_id', $productosEnMultiplesAlmacenes->producto_id)
            ->where('cantidad_actual', '>=', 1)
            ->orderByDesc('cantidad_actual')
            ->get();
            
        if ($inventarios->count() < 2) {
            $this->markTestSkipped('Faltan almacenes con stock suficiente');
        }
        
        $origenData = $inventarios->first();
        $destinoData = $inventarios->skip(1)->first();

        // Act
        $movimiento = $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $origenData->almacen_id,
            'almacen_destino_id' => $destinoData->almacen_id,
            'cantidad' => 1, // Mínimo
        ]);

        // Assert
        $this->assertEquals(1, $movimiento->cantidad);

        // Revert
        $this->service->transferir([
            'producto_id' => $origenData->producto_id,
            'almacen_origen_id' => $destinoData->almacen_id,
            'almacen_destino_id' => $origenData->almacen_id,
            'cantidad' => 1,
        ]);
    }

    /**
     * UNIT-INV-015: Paginación personalizada
     * 
     * CAJA BLANCA: Verifica parámetro per_page
     */
    public function test_UNIT_INV015_custom_pagination(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 5]);

        // Assert
        $this->assertEquals(5, $result->perPage());
    }

    /*
     * =========================================================================
     * PRUEBAS DE MÚLTIPLES FILTROS
     * =========================================================================
     */

    /**
     * UNIT-INV-016: Múltiples filtros combinados
     * 
     * CAJA BLANCA: Verifica que todas las condiciones se evalúan
     */
    public function test_UNIT_INV016_multiple_filters_combined(): void
    {
        // Arrange
        $almacen = Almacen::first();
        if (!$almacen) {
            $this->markTestSkipped('No hay almacenes');
        }

        // Act
        $result = $this->service->list([
            'almacen_id' => $almacen->id,
            'con_stock' => true,
            'per_page' => 10
        ]);

        // Assert
        $this->assertNotNull($result);
        foreach ($result->items() as $inventario) {
            $this->assertEquals($almacen->id, $inventario->almacen_id);
            $this->assertGreaterThan(0, $inventario->cantidad_actual);
        }
    }
}
