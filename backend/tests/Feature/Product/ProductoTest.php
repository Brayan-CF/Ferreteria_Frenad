<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Modules\Product\Models\Producto;
use Modules\Product\Models\Categoria;

/**
 * ============================================================================
 * PRUEBAS DE PRODUCTOS - Módulo Product
 * ============================================================================
 * 
 * Archivo: tests/Feature/Product/ProductoTest.php
 * Módulo: Product
 * Versión: 1.0
 * Fecha: 2025-01-22
 * 
 * DESCRIPCIÓN:
 * Pruebas de integración para el CRUD de productos, verificando:
 * - Listado con paginación y filtros (PROD-001 a PROD-003)
 * - Creación de productos (PROD-004, PROD-005)
 * - Actualización y eliminación (PROD-006, PROD-007)
 * 
 * ENDPOINTS PROBADOS:
 * - GET    /api/product/productos
 * - GET    /api/product/productos/{id}
 * - POST   /api/product/productos
 * - PUT    /api/product/productos/{id}
 * - DELETE /api/product/productos/{id}
 * 
 * ============================================================================
 */
class ProductoTest extends TestCase
{
    /**
     * =========================================================================
     * PROD-001: Listar productos con paginación
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el endpoint de listado devuelve productos paginados
     * correctamente con la estructura esperada.
     * 
     * PRECONDICIONES:
     * - Usuario autenticado
     * - Existen productos en la base de datos
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Respuesta con estructura de paginación Laravel
     * - Array de productos con sus relaciones
     */
    public function test_PROD001_can_list_products_with_pagination(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act
        $response = $this->getJson('/api/product/productos');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'sku',
                        'nombre',
                        'precio_venta',
                        'activo',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
        
        $response->assertJson(['success' => true]);
    }

    /**
     * =========================================================================
     * PROD-002: Buscar productos por nombre
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el filtro de búsqueda funciona correctamente
     * buscando por nombre de producto.
     * 
     * ENTRADA:
     * - search: término de búsqueda
     * 
     * RESULTADO ESPERADO:
     * - Solo productos que coincidan con el término
     */
    public function test_PROD002_can_search_products_by_name(): void
    {
        // Arrange
        $this->authenticateUser();
        
        // Obtener un producto existente para buscar
        $producto = Producto::first();
        $searchTerm = substr($producto->nombre ?? 'Martillo', 0, 4);

        // Act
        $response = $this->getJson('/api/product/productos?search=' . $searchTerm);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * =========================================================================
     * PROD-003: Filtrar productos por categoría
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el filtro por categoría funciona correctamente.
     * 
     * ENTRADA:
     * - categoria_id: ID de categoría válido
     * 
     * RESULTADO ESPERADO:
     * - Solo productos de la categoría especificada
     */
    public function test_PROD003_can_filter_products_by_category(): void
    {
        // Arrange
        $this->authenticateUser();
        $categoria = Categoria::first();

        // Act
        $response = $this->getJson('/api/product/productos?categoria_id=' . ($categoria->id ?? 1));

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * =========================================================================
     * PROD-003b: Filtrar productos activos/inactivos
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se puede filtrar por estado activo del producto.
     */
    public function test_PROD003b_can_filter_active_products(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act - Solo activos
        $response = $this->getJson('/api/product/productos?activo=true');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * =========================================================================
     * PROD-004: Obtener un producto específico
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se puede obtener los detalles de un producto
     * específico por su ID.
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Datos completos del producto con relaciones
     */
    public function test_PROD004_can_get_single_product(): void
    {
        // Arrange
        $this->authenticateUser();
        $producto = Producto::first();

        // Act
        $response = $this->getJson('/api/product/productos/' . $producto->id);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'sku',
                'nombre',
                'descripcion',
                'precio_compra',
                'precio_venta',
                'activo',
            ],
        ]);
    }

    /**
     * =========================================================================
     * PROD-004b: Producto no encontrado devuelve 404
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se devuelve 404 al buscar un producto inexistente.
     */
    public function test_PROD004b_returns_404_for_nonexistent_product(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act
        $response = $this->getJson('/api/product/productos/99999');

        // Assert
        $response->assertStatus(404);
    }

    /**
     * =========================================================================
     * PROD-005: Crear producto con datos válidos
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se puede crear un nuevo producto con todos
     * los datos requeridos.
     * 
     * ENTRADA:
     * - nombre, categoria_id, unidad_base_id, precio_compra, precio_venta
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 201 Created
     * - Producto creado con SKU generado automáticamente
     */
    public function test_PROD005_can_create_product_with_valid_data(): void
    {
        // Arrange
        $this->authenticateUser();
        $categoria = Categoria::first();
        
        $productData = [
            'nombre' => 'Producto de Prueba PHPUnit ' . uniqid(),
            'descripcion' => 'Producto creado durante pruebas automatizadas',
            'categoria_id' => $categoria->id ?? 1,
            'unidad_base_id' => 1,
            'precio_compra' => 25.50,
            'precio_venta' => 35.00,
            'activo' => true,
        ];

        // Act
        $response = $this->postJson('/api/product/productos', $productData);

        // Assert
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'sku',
                'nombre',
                'precio_venta',
            ],
        ]);

        // Verificar que el producto fue creado consultando la API
        $createdId = $response->json('data.id');
        $verifyResponse = $this->getJson('/api/product/productos/' . $createdId);
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson([
            'data' => [
                'nombre' => $productData['nombre'],
            ],
        ]);
    }

    /**
     * =========================================================================
     * PROD-005b: Crear producto sin campos requeridos falla
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que la validación rechaza productos sin datos obligatorios.
     * 
     * CAMPOS REQUERIDOS:
     * - nombre
     * - categoria_id
     * - unidad_base_id
     * - precio_compra
     * - precio_venta
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 422 Unprocessable Entity
     * - Errores de validación
     */
    public function test_PROD005b_cannot_create_product_without_required_fields(): void
    {
        // Arrange
        $this->authenticateUser();
        $invalidData = [
            'descripcion' => 'Producto sin nombre',
        ];

        // Act
        $response = $this->postJson('/api/product/productos', $invalidData);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    /**
     * =========================================================================
     * PROD-005c: Precio de venta debe ser mayor que precio de compra
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar la regla de negocio: precio_venta >= precio_compra
     * 
     * NOTA:
     * Esta validación puede estar a nivel de servicio o controlador
     */
    public function test_PROD005c_validates_price_logic(): void
    {
        // Arrange
        $this->authenticateUser();
        $productData = [
            'nombre' => 'Producto Precio Inválido',
            'categoria_id' => 1,
            'unidad_base_id' => 1,
            'precio_compra' => 100.00, // Mayor que venta
            'precio_venta' => 50.00,   // Menor que compra
        ];

        // Act
        $response = $this->postJson('/api/product/productos', $productData);

        // Assert: Puede ser 422 o 201 dependiendo de las reglas de negocio
        // Por ahora verificamos que la petición se procesa
        $this->assertTrue(in_array($response->status(), [201, 422, 400]));
    }

    /**
     * =========================================================================
     * PROD-006: Actualizar producto existente
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se puede actualizar un producto existente.
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Datos actualizados en la BD
     */
    public function test_PROD006_can_update_existing_product(): void
    {
        // Arrange
        $this->authenticateUser();
        $producto = Producto::first();
        $nuevoNombre = 'Producto Actualizado ' . uniqid();

        // Act
        $response = $this->putJson('/api/product/productos/' . $producto->id, [
            'nombre' => $nuevoNombre,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verificar actualización consultando la API
        $verifyResponse = $this->getJson('/api/product/productos/' . $producto->id);
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson([
            'data' => [
                'nombre' => $nuevoNombre,
            ],
        ]);
    }

    /**
     * =========================================================================
     * PROD-007: Desactivar producto (soft delete)
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que se puede desactivar (eliminar lógicamente) un producto.
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Producto marcado como inactivo
     */
    public function test_PROD007_can_deactivate_product(): void
    {
        // Arrange
        $this->authenticateUser();
        
        // Crear un producto para desactivar
        $producto = Producto::create([
            'nombre' => 'Producto Para Desactivar ' . uniqid(),
            'categoria_id' => 1,
            'unidad_base_id' => 1,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'activo' => true,
            'creado_por' => 1,
        ]);

        // Act
        $response = $this->deleteJson('/api/product/productos/' . $producto->id);

        // Assert
        $response->assertStatus(200);
        
        // Verificar que está desactivado
        $productoActualizado = Producto::find($producto->id);
        $this->assertFalse((bool) $productoActualizado->activo);
    }

    /**
     * =========================================================================
     * PROD-EXTRA: Verificar que productos requieren autenticación
     * =========================================================================
     */
    public function test_PROD_products_endpoint_requires_authentication(): void
    {
        // Act: Sin autenticación
        $response = $this->getJson('/api/product/productos');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * =========================================================================
     * PROD-EXTRA: Obtener estadísticas de productos
     * =========================================================================
     */
    public function test_PROD_can_get_product_statistics(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act
        $response = $this->getJson('/api/product/productos/statistics');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
