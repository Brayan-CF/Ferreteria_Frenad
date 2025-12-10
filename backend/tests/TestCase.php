<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\Usuario;
use Laravel\Sanctum\Sanctum;

/**
 * Clase base para todas las pruebas del sistema Ferretería FRENAD
 * 
 * Proporciona métodos helper para:
 * - Autenticación de usuarios en pruebas
 * - Verificación de respuestas JSON estándar
 * - Configuración común de pruebas
 * 
 * @package Tests
 * @version 1.0
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Usuario autenticado para las pruebas
     */
    protected ?Usuario $authenticatedUser = null;

    /**
     * Token de autenticación actual
     */
    protected ?string $authToken = null;

    /**
     * Configuración inicial antes de cada prueba
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar headers por defecto para API JSON
        $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Autenticar un usuario para las pruebas
     * 
     * @param Usuario|null $user Usuario específico o null para crear uno
     * @return $this
     */
    protected function authenticateUser(?Usuario $user = null): self
    {
        if ($user === null) {
            $user = Usuario::where('email', 'admin@frenad.com')->first();
        }

        if ($user) {
            $this->authenticatedUser = $user;
            Sanctum::actingAs($user, ['*']);
        }

        return $this;
    }

    /**
     * Autenticar usando credenciales y obtener token
     * 
     * @param string $email
     * @param string $password
     * @return string|null Token de autenticación
     */
    protected function loginAndGetToken(string $email = 'admin@frenad.com', string $password = 'password'): ?string
    {
        $response = $this->postJson('/api/auth/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->status() === 200) {
            $data = $response->json();
            $this->authToken = $data['data']['token'] ?? null;
            return $this->authToken;
        }

        return null;
    }

    /**
     * Realizar petición autenticada con token
     * 
     * @param string $method Método HTTP
     * @param string $uri URI del endpoint
     * @param array $data Datos a enviar
     * @return \Illuminate\Testing\TestResponse
     */
    protected function authenticatedRequest(string $method, string $uri, array $data = [])
    {
        $this->authenticateUser();
        return $this->{$method . 'Json'}($uri, $data);
    }

    /**
     * Verificar estructura de respuesta exitosa estándar
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $expectedStatus Código de estado esperado
     * @return void
     */
    protected function assertSuccessResponse($response, int $expectedStatus = 200): void
    {
        $response->assertStatus($expectedStatus);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
        $response->assertJson(['success' => true]);
    }

    /**
     * Verificar estructura de respuesta de error estándar
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $expectedStatus Código de estado esperado
     * @return void
     */
    protected function assertErrorResponse($response, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $response->assertJson(['success' => false]);
    }

    /**
     * Verificar estructura de respuesta paginada
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    protected function assertPaginatedResponse($response): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data',
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /**
     * Crear datos de prueba para un producto
     * 
     * @param array $override Datos a sobrescribir
     * @return array
     */
    protected function getProductData(array $override = []): array
    {
        return array_merge([
            'nombre' => 'Producto de Prueba ' . uniqid(),
            'descripcion' => 'Descripción del producto de prueba',
            'categoria_id' => 1,
            'unidad_base_id' => 1,
            'precio_compra' => 10.00,
            'precio_venta' => 15.00,
            'activo' => true,
        ], $override);
    }

    /**
     * Crear datos de prueba para un cliente
     * 
     * @param array $override Datos a sobrescribir
     * @return array
     */
    protected function getClienteData(array $override = []): array
    {
        return array_merge([
            'nombre_completo' => 'Cliente de Prueba ' . uniqid(),
            'nit' => '123456789',
            'telefono' => '70000000',
            'direccion' => 'Calle de prueba #123',
            'email' => 'cliente' . uniqid() . '@test.com',
            'es_frecuente' => false,
            'limite_credito' => 1000.00,
            'activo' => true,
        ], $override);
    }

    /**
     * Crear datos de prueba para una venta
     * 
     * @param array $override Datos a sobrescribir
     * @return array
     */
    protected function getVentaData(array $override = []): array
    {
        return array_merge([
            'tipo_venta' => 'contado',
            'metodo_pago' => 'efectivo',
            'tipo_documento' => 'nota_venta',
            'items' => [
                [
                    'producto_id' => 1,
                    'cantidad' => 1,
                    'precio_unitario' => 15.00,
                ]
            ],
        ], $override);
    }

    /**
     * Limpiar después de cada prueba
     */
    protected function tearDown(): void
    {
        $this->authenticatedUser = null;
        $this->authToken = null;
        parent::tearDown();
    }
}
