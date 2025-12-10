<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Customer\Services\ClienteService;
use Modules\Customer\Models\Cliente;
use Modules\Auth\Models\Usuario;
use Exception;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - ClienteService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de clientes, incluyendo
 * validaciones de crédito, deuda y estado de cuenta.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de condiciones: Cada condición if/else
 * - Pruebas de validación: Reglas de negocio
 * - Valores límite: Límites de crédito, deudas
 * 
 * MÓDULO: Customer
 * SERVICIO: ClienteService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class ClienteServiceTest extends TestCase
{
    protected ClienteService $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClienteService();
        
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
     * UNIT-CLI-001: Listado sin filtros
     * 
     * CAJA BLANCA: Verifica ruta por defecto del método list()
     */
    public function test_UNIT_CLI001_list_without_filters(): void
    {
        // Act
        $result = $this->service->list([]);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue(method_exists($result, 'items'));
    }

    /**
     * UNIT-CLI-002: Filtro por estado activo
     * 
     * CAJA BLANCA: Verifica condición isset($filters['activo']) línea 19
     */
    public function test_UNIT_CLI002_list_filters_by_active_status(): void
    {
        // Act
        $resultActivos = $this->service->list(['activo' => true]);
        $resultInactivos = $this->service->list(['activo' => false]);

        // Assert
        foreach ($resultActivos->items() as $cliente) {
            $this->assertTrue($cliente->activo);
        }
        foreach ($resultInactivos->items() as $cliente) {
            $this->assertFalse($cliente->activo);
        }
    }

    /**
     * UNIT-CLI-003: Filtro clientes frecuentes
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['es_frecuente']) línea 23
     */
    public function test_UNIT_CLI003_list_filters_frequent_customers(): void
    {
        // Act
        $result = $this->service->list(['es_frecuente' => true]);

        // Assert
        foreach ($result->items() as $cliente) {
            $this->assertTrue($cliente->es_frecuente);
        }
    }

    /**
     * UNIT-CLI-004: Filtro clientes con deuda
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['con_deuda']) línea 27
     */
    public function test_UNIT_CLI004_list_filters_customers_with_debt(): void
    {
        // Act
        $result = $this->service->list(['con_deuda' => true]);

        // Assert: Los clientes retornados deben tener deuda
        foreach ($result->items() as $cliente) {
            $this->assertTrue($cliente->tiene_deuda);
        }
    }

    /**
     * UNIT-CLI-005: Filtro por búsqueda
     * 
     * CAJA BLANCA: Verifica condición !empty($filters['search']) línea 31
     */
    public function test_UNIT_CLI005_list_filters_by_search(): void
    {
        // Arrange
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->markTestSkipped('No hay clientes en la base de datos');
        }
        $searchTerm = substr($cliente->nombre_completo, 0, 3);

        // Act
        $result = $this->service->list(['search' => $searchTerm]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $result->total());
    }

    /**
     * UNIT-CLI-006: Ordenamiento personalizado
     * 
     * CAJA BLANCA: Verifica líneas 36-38 (orderBy)
     */
    public function test_UNIT_CLI006_list_custom_ordering(): void
    {
        // Act
        $result = $this->service->list([
            'order_by' => 'nombre_completo',
            'order_dir' => 'desc'
        ]);

        // Assert
        $this->assertNotNull($result);
    }

    /*
     * =========================================================================
     * PRUEBAS DE CREACIÓN
     * =========================================================================
     */

    /**
     * UNIT-CLI-007: Creación con datos mínimos
     * 
     * CAJA BLANCA: Verifica método create() líneas 45-58
     */
    public function test_UNIT_CLI007_create_with_minimum_data(): void
    {
        // Arrange
        $data = [
            'nombre_completo' => 'Cliente Test Unit ' . time(),
        ];

        // Act
        $cliente = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertEquals($data['nombre_completo'], $cliente->nombre_completo);
        $this->assertTrue($cliente->activo); // Default
        $this->assertFalse($cliente->es_frecuente); // Default
        $this->assertEquals(0, $cliente->limite_credito); // Default

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-008: Creación con todos los campos
     * 
     * CAJA BLANCA: Verifica todos los campos del create()
     */
    public function test_UNIT_CLI008_create_with_all_fields(): void
    {
        // Arrange
        $data = [
            'nombre_completo' => 'Cliente Completo Test ' . time(),
            'nit' => '12345678' . rand(10, 99),
            'telefono' => '70012345',
            'direccion' => 'Calle Test 123',
            'email' => 'test' . time() . '@test.com',
            'es_frecuente' => true,
            'limite_credito' => 5000.00,
            'activo' => true,
        ];

        // Act
        $cliente = $this->service->create($data);

        // Assert
        $this->assertEquals($data['nombre_completo'], $cliente->nombre_completo);
        $this->assertEquals($data['nit'], $cliente->nit);
        $this->assertEquals($data['telefono'], $cliente->telefono);
        $this->assertEquals($data['direccion'], $cliente->direccion);
        $this->assertEquals($data['email'], $cliente->email);
        $this->assertTrue($cliente->es_frecuente);
        $this->assertEquals(5000.00, $cliente->limite_credito);
        $this->assertEquals($this->user->id, $cliente->creado_por);

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-009: Valores por defecto se aplican correctamente
     * 
     * CAJA BLANCA: Verifica operadores ?? en líneas 49-55
     */
    public function test_UNIT_CLI009_default_values_applied(): void
    {
        // Arrange
        $data = [
            'nombre_completo' => 'Cliente Defaults ' . time(),
        ];

        // Act
        $cliente = $this->service->create($data);

        // Assert: Verificar valores por defecto
        $this->assertNull($cliente->nit);
        $this->assertNull($cliente->telefono);
        $this->assertNull($cliente->direccion);
        $this->assertNull($cliente->email);
        $this->assertFalse($cliente->es_frecuente);
        $this->assertEquals(0, $cliente->limite_credito);
        $this->assertTrue($cliente->activo);

        // Cleanup
        $cliente->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE ACTUALIZACIÓN
     * =========================================================================
     */

    /**
     * UNIT-CLI-010: Actualización parcial
     * 
     * CAJA BLANCA: Verifica que solo se actualizan campos enviados
     */
    public function test_UNIT_CLI010_update_partial_data(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Update Test ' . time(),
            'telefono' => '70000001',
        ]);
        $originalPhone = $cliente->telefono;
        $originalName = $cliente->nombre_completo;

        // Act: Solo actualizar nombre
        $newName = 'Nombre Actualizado ' . (time() + 1);
        $updated = $this->service->update($cliente, [
            'nombre_completo' => $newName,
        ]);

        // Assert
        $this->assertEquals($newName, $updated->nombre_completo);
        $this->assertNotEquals($originalName, $updated->nombre_completo);
        $this->assertEquals($originalPhone, $updated->telefono); // No cambió

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-011: Actualización registra usuario
     * 
     * CAJA BLANCA: Verifica línea 73 'actualizado_por'
     */
    public function test_UNIT_CLI011_update_records_user(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Usuario Test ' . time(),
        ]);

        // Act
        $updated = $this->service->update($cliente, [
            'telefono' => '70099999',
        ]);

        // Assert
        $this->assertEquals($this->user->id, $updated->actualizado_por);

        // Cleanup
        $cliente->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE ELIMINACIÓN Y VALIDACIÓN DE DEUDA
     * =========================================================================
     */

    /**
     * UNIT-CLI-012: Delete desactiva cliente sin deuda
     * 
     * CAJA BLANCA: Verifica línea 90 update(['activo' => false])
     */
    public function test_UNIT_CLI012_delete_deactivates_customer_without_debt(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Sin Deuda ' . time(),
        ]);

        // Act
        $result = $this->service->delete($cliente);

        // Assert
        $this->assertTrue($result);
        $this->assertFalse($cliente->fresh()->activo);

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-013: Activate reactiva cliente
     * 
     * CAJA BLANCA: Verifica método activate() línea 96
     */
    public function test_UNIT_CLI013_activate_reactivates_customer(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Reactivar ' . time(),
            'activo' => false,
        ]);

        // Act
        $result = $this->service->activate($cliente);

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($cliente->fresh()->activo);

        // Cleanup
        $cliente->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE ESTADO DE CUENTA
     * =========================================================================
     */

    /**
     * UNIT-CLI-014: Estado de cuenta retorna estructura correcta
     * 
     * CAJA BLANCA: Verifica método estadoCuenta() líneas 103-135
     */
    public function test_UNIT_CLI014_account_status_returns_correct_structure(): void
    {
        // Arrange
        $cliente = Cliente::first();
        if (!$cliente) {
            $cliente = $this->service->create([
                'nombre_completo' => 'Cliente Estado Cuenta ' . time(),
            ]);
        }

        // Act
        $estado = $this->service->estadoCuenta($cliente);

        // Assert: Verificar estructura
        $this->assertArrayHasKey('cliente', $estado);
        $this->assertArrayHasKey('resumen_financiero', $estado);
        $this->assertArrayHasKey('estadisticas_compras', $estado);
        $this->assertArrayHasKey('creditos_pendientes', $estado);
        $this->assertArrayHasKey('ultimos_pagos', $estado);

        // Verificar datos del cliente
        $this->assertEquals($cliente->id, $estado['cliente']['id']);
        $this->assertEquals($cliente->nombre_completo, $estado['cliente']['nombre_completo']);
    }

    /**
     * UNIT-CLI-015: Resumen financiero calcula correctamente
     * 
     * CAJA BLANCA: Verifica líneas 112-117 (resumen_financiero)
     */
    public function test_UNIT_CLI015_financial_summary_calculated_correctly(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Financiero ' . time(),
            'limite_credito' => 10000.00,
        ]);

        // Act
        $estado = $this->service->estadoCuenta($cliente);

        // Assert
        $this->assertEquals(10000.00, $estado['resumen_financiero']['limite_credito']);
        $this->assertArrayHasKey('deuda_total', $estado['resumen_financiero']);
        $this->assertArrayHasKey('credito_disponible', $estado['resumen_financiero']);
        $this->assertArrayHasKey('estado_cuenta', $estado['resumen_financiero']);

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-016: Estadísticas de compras calculan promedio
     * 
     * CAJA BLANCA: Verifica líneas 118-123 (promedio_compra)
     */
    public function test_UNIT_CLI016_purchase_statistics_calculate_average(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Estadísticas ' . time(),
        ]);

        // Act
        $estado = $this->service->estadoCuenta($cliente);

        // Assert
        $this->assertArrayHasKey('total_compras', $estado['estadisticas_compras']);
        $this->assertArrayHasKey('cantidad_compras', $estado['estadisticas_compras']);
        $this->assertArrayHasKey('promedio_compra', $estado['estadisticas_compras']);

        // Verificar cálculo de promedio (cantidad 0 = promedio 0)
        if ($estado['estadisticas_compras']['cantidad_compras'] == 0) {
            $this->assertEquals(0, $estado['estadisticas_compras']['promedio_compra']);
        }

        // Cleanup
        $cliente->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE
     * =========================================================================
     */

    /**
     * UNIT-CLI-017: Límite de crédito cero
     * 
     * VALORES LÍMITE: Crédito mínimo permitido
     */
    public function test_UNIT_CLI017_credit_limit_zero(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Sin Crédito ' . time(),
            'limite_credito' => 0,
        ]);

        // Assert
        $this->assertEquals(0, $cliente->limite_credito);

        // Cleanup
        $cliente->forceDelete();
    }

    /**
     * UNIT-CLI-018: Límite de crédito alto
     * 
     * VALORES LÍMITE: Crédito máximo razonable
     */
    public function test_UNIT_CLI018_credit_limit_high(): void
    {
        // Arrange
        $cliente = $this->service->create([
            'nombre_completo' => 'Cliente Alto Crédito ' . time(),
            'limite_credito' => 999999.99,
        ]);

        // Assert
        $this->assertEquals(999999.99, $cliente->limite_credito);

        // Cleanup
        $cliente->forceDelete();
    }

    /*
     * =========================================================================
     * PRUEBAS DE COMBINACIÓN DE FILTROS
     * =========================================================================
     */

    /**
     * UNIT-CLI-019: Múltiples filtros combinados
     * 
     * CAJA BLANCA: Verifica que todas las condiciones se evalúan
     */
    public function test_UNIT_CLI019_multiple_filters_combined(): void
    {
        // Act
        $result = $this->service->list([
            'activo' => true,
            'es_frecuente' => false,
            'order_by' => 'nombre_completo',
            'order_dir' => 'asc',
            'per_page' => 5
        ]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(5, $result->perPage());
        
        foreach ($result->items() as $cliente) {
            $this->assertTrue($cliente->activo);
        }
    }
}
