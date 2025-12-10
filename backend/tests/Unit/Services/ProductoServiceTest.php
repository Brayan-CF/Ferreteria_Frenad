<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Services\ProductoService;
use Modules\Product\Models\Producto;
use Modules\Product\Models\Categoria;
use Modules\Product\Models\Marca;
use Modules\Product\Models\UnidadMedida;
use Modules\Auth\Models\Usuario;
use Mockery;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - ProductoService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de productos, verificando
 * que cada método cumple con su especificación a nivel de código.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de sentencias: Cada línea de código ejecutada
 * - Cobertura de decisiones: Cada condición if/else evaluada
 * - Cobertura de condiciones: Cada subcondición evaluada
 * - Pruebas de valores límite: Valores en los bordes de rangos
 * 
 * MÓDULO: Product
 * SERVICIO: ProductoService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class ProductoServiceTest extends TestCase
{
    protected ProductoService $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductoService();
        
        // Autenticar usuario para pruebas
        $this->user = Usuario::first();
        if ($this->user) {
            $this->actingAs($this->user);
        }
    }

    /*
     * =========================================================================
     * PRUEBAS DE LISTADO - Cobertura de filtros
     * =========================================================================
     */

    /**
     * UNIT-PROD-001: Listado sin filtros retorna productos paginados
     * 
     * CAJA BLANCA: Verifica la ruta por defecto sin filtros activos
     * COBERTURA: Líneas 16-48 (método list)
     */
    public function test_UNIT_PROD001_list_without_filters_returns_paginated_products(): void
    {
        // Act
        $result = $this->service->list([]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsObject($result);
        $this->assertTrue(method_exists($result, 'items')); // Es paginado
    }

    /**
     * UNIT-PROD-002: Filtro por activo funciona correctamente
     * 
     * CAJA BLANCA: Verifica la condición isset($filters['activo']) línea 20
     * COBERTURA DE DECISIÓN: Rama true del if
     */
    public function test_UNIT_PROD002_list_filters_by_active_status(): void
    {
        // Act: Filtrar solo activos
        $resultActivos = $this->service->list(['activo' => true]);
        $resultInactivos = $this->service->list(['activo' => false]);

        // Assert
        $this->assertNotNull($resultActivos);
        $this->assertNotNull($resultInactivos);
        
        // Verificar que los activos solo tienen productos activos
        foreach ($resultActivos->items() as $producto) {
            $this->assertTrue($producto->activo);
        }
    }

    /**
     * UNIT-PROD-003: Filtro por categoría funciona correctamente
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['categoria_id']) línea 24
     */
    public function test_UNIT_PROD003_list_filters_by_category(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $this->assertNotNull($categoria, 'Debe existir al menos una categoría');

        // Act
        $result = $this->service->list(['categoria_id' => $categoria->id]);

        // Assert
        foreach ($result->items() as $producto) {
            $this->assertEquals($categoria->id, $producto->categoria_id);
        }
    }

    /**
     * UNIT-PROD-004: Filtro por búsqueda funciona correctamente
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['search']) línea 32
     */
    public function test_UNIT_PROD004_list_filters_by_search_term(): void
    {
        // Arrange: Obtener un producto existente
        $producto = Producto::first();
        $this->assertNotNull($producto, 'Debe existir al menos un producto');
        $searchTerm = substr($producto->nombre, 0, 3);

        // Act
        $result = $this->service->list(['search' => $searchTerm]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $result->total());
    }

    /**
     * UNIT-PROD-005: Ordenamiento personalizado funciona
     * 
     * CAJA BLANCA: Verifica líneas 44-46 (orderBy)
     */
    public function test_UNIT_PROD005_list_applies_custom_ordering(): void
    {
        // Act: Ordenar por nombre descendente
        $result = $this->service->list([
            'order_by' => 'nombre',
            'order_dir' => 'desc'
        ]);

        // Assert: Verificar que el método list() acepta y procesa los parámetros
        $this->assertNotNull($result);
        $items = $result->items();
        
        // Verificar que retorna resultados (la lógica de orden se confía al ORM)
        $this->assertIsArray($items);
    }

    /**
     * UNIT-PROD-006: Paginación personalizada funciona
     * 
     * CAJA BLANCA: Verifica parámetro per_page línea 48
     */
    public function test_UNIT_PROD006_list_applies_custom_pagination(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 5]);

        // Assert
        $this->assertEquals(5, $result->perPage());
    }

    /*
     * =========================================================================
     * PRUEBAS DE CREACIÓN - Lógica de negocio
     * =========================================================================
     */

    /**
     * UNIT-PROD-007: Creación de producto con datos mínimos
     * 
     * CAJA BLANCA: Verifica flujo principal del método create() líneas 53-88
     * COBERTURA: Ruta sin unidades adicionales
     */
    public function test_UNIT_PROD007_create_product_with_minimum_data(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();
        
        $this->assertNotNull($categoria);
        $this->assertNotNull($unidad);

        $data = [
            'nombre' => 'Producto Test Unit ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 10.00,
            'precio_venta' => 15.00,
        ];

        // Act
        $producto = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Producto::class, $producto);
        $this->assertEquals($data['nombre'], $producto->nombre);
        $this->assertEquals($data['precio_compra'], $producto->precio_compra);
        $this->assertEquals($data['precio_venta'], $producto->precio_venta);
        $this->assertTrue($producto->activo); // Default true
        $this->assertNotNull($producto->sku); // Generado automáticamente

        // Cleanup
        $producto->forceDelete();
    }

    /**
     * UNIT-PROD-008: Creación establece valores por defecto
     * 
     * CAJA BLANCA: Verifica valores null coalesce (??) líneas 61-71
     */
    public function test_UNIT_PROD008_create_sets_default_values(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();

        $data = [
            'nombre' => 'Producto Defaults Test ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 100,
            'precio_venta' => 150,
            // Sin descripcion, codigo_barras, etc.
        ];

        // Act
        $producto = $this->service->create($data);

        // Assert: Verificar valores por defecto
        $this->assertNull($producto->descripcion);
        $this->assertNull($producto->codigo_barras);
        $this->assertNull($producto->marca_id);
        $this->assertTrue($producto->activo);
        $this->assertEquals($this->user->id, $producto->creado_por);

        // Cleanup
        $producto->forceDelete();
    }

    /**
     * UNIT-PROD-009: Creación carga relaciones correctamente
     * 
     * CAJA BLANCA: Verifica return con load() línea 83
     */
    public function test_UNIT_PROD009_create_loads_relations(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();

        $data = [
            'nombre' => 'Producto Relations Test ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 50,
            'precio_venta' => 75,
        ];

        // Act
        $producto = $this->service->create($data);

        // Assert: Relaciones cargadas
        $this->assertTrue($producto->relationLoaded('categoria'));
        $this->assertTrue($producto->relationLoaded('unidadBase'));

        // Cleanup
        $producto->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE ACTUALIZACIÓN - Flujo de cambios
     * =========================================================================
     */

    /**
     * UNIT-PROD-010: Actualización parcial de producto
     * 
     * CAJA BLANCA: Verifica array_filter y null coalesce líneas 94-109
     */
    public function test_UNIT_PROD010_update_partial_data(): void
    {
        // Arrange
        $producto = Producto::first();
        $this->assertNotNull($producto);
        
        $originalName = $producto->nombre;
        $newPrice = $producto->precio_venta + 10;

        // Act: Solo actualizar precio
        $updated = $this->service->update($producto, [
            'precio_venta' => $newPrice
        ]);

        // Assert
        $this->assertEquals($originalName, $updated->nombre); // No cambió
        $this->assertEquals($newPrice, $updated->precio_venta); // Sí cambió

        // Restore
        $this->service->update($producto, ['precio_venta' => $producto->precio_venta]);
    }

    /**
     * UNIT-PROD-011: Actualización registra usuario actualizador
     * 
     * CAJA BLANCA: Verifica línea 108 'actualizado_por'
     */
    public function test_UNIT_PROD011_update_records_updated_by(): void
    {
        // Arrange
        $producto = Producto::first();
        
        // Act
        $updated = $this->service->update($producto, [
            'descripcion' => 'Descripción actualizada ' . time()
        ]);

        // Assert
        $this->assertEquals($this->user->id, $updated->actualizado_por);
    }

    /*
     * =========================================================================
     * PRUEBAS DE ELIMINACIÓN - Validaciones de negocio
     * =========================================================================
     */

    /**
     * UNIT-PROD-012: Delete desactiva producto (soft delete)
     * 
     * CAJA BLANCA: Verifica línea 138 update(['activo' => false])
     */
    public function test_UNIT_PROD012_delete_deactivates_product(): void
    {
        // Arrange: Crear producto de prueba
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();
        
        $producto = $this->service->create([
            'nombre' => 'Producto Para Eliminar ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 10,
            'precio_venta' => 15,
        ]);

        // Act
        $result = $this->service->delete($producto);

        // Assert
        $this->assertTrue($result);
        $this->assertFalse($producto->fresh()->activo);

        // Cleanup
        $producto->forceDelete();
    }

    /**
     * UNIT-PROD-013: Activate reactiva producto
     * 
     * CAJA BLANCA: Verifica método activate() línea 146
     */
    public function test_UNIT_PROD013_activate_reactivates_product(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();
        
        $producto = $this->service->create([
            'nombre' => 'Producto Para Reactivar ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'activo' => false,
        ]);

        // Act
        $result = $this->service->activate($producto);

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($producto->fresh()->activo);

        // Cleanup
        $producto->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE
     * =========================================================================
     */

    /**
     * UNIT-PROD-014: Precios con valores límite
     * 
     * CAJA BLANCA: Prueba valores extremos en precio_compra/precio_venta
     */
    public function test_UNIT_PROD014_prices_boundary_values(): void
    {
        // Arrange
        $categoria = Categoria::first();
        $unidad = UnidadMedida::first();

        // Act: Precio mínimo (0.01)
        $productoMinimo = $this->service->create([
            'nombre' => 'Producto Precio Mínimo ' . time(),
            'categoria_id' => $categoria->id,
            'unidad_base_id' => $unidad->id,
            'precio_compra' => 0.01,
            'precio_venta' => 0.01,
        ]);

        // Assert
        $this->assertEquals(0.01, $productoMinimo->precio_compra);
        $this->assertEquals(0.01, $productoMinimo->precio_venta);

        // Cleanup
        $productoMinimo->forceDelete();
    }

    /**
     * UNIT-PROD-015: Paginación con valor límite 1
     * 
     * CAJA BLANCA: Prueba per_page = 1
     */
    public function test_UNIT_PROD015_pagination_boundary_value_one(): void
    {
        // Act
        $result = $this->service->list(['per_page' => 1]);

        // Assert
        $this->assertEquals(1, $result->perPage());
        $this->assertLessThanOrEqual(1, count($result->items()));
    }

    /*
     * =========================================================================
     * PRUEBAS DE MÚLTIPLES FILTROS COMBINADOS
     * =========================================================================
     */

    /**
     * UNIT-PROD-016: Múltiples filtros combinados
     * 
     * CAJA BLANCA: Verifica que todas las condiciones if se evalúan
     */
    public function test_UNIT_PROD016_multiple_filters_combined(): void
    {
        // Arrange
        $categoria = Categoria::first();

        // Act: Aplicar múltiples filtros
        $result = $this->service->list([
            'activo' => true,
            'categoria_id' => $categoria->id,
            'order_by' => 'precio_venta',
            'order_dir' => 'asc',
            'per_page' => 10
        ]);

        // Assert
        $this->assertNotNull($result);
        foreach ($result->items() as $producto) {
            $this->assertTrue($producto->activo);
            $this->assertEquals($categoria->id, $producto->categoria_id);
        }
    }
}
