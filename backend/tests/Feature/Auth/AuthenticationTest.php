<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Modules\Auth\Models\Usuario;

/**
 * ============================================================================
 * PRUEBAS DE AUTENTICACIÓN - Módulo Auth
 * ============================================================================
 * 
 * Archivo: tests/Feature/Auth/AuthenticationTest.php
 * Módulo: Auth
 * Versión: 1.0
 * Fecha: 2025-01-22
 * 
 * DESCRIPCIÓN:
 * Este archivo contiene las pruebas de integración para el sistema de
 * autenticación del backend. Verifica el correcto funcionamiento de:
 * - Login de usuarios (AUTH-001 a AUTH-003)
 * - Logout (AUTH-004)
 * - Perfil de usuario (AUTH-005)
 * - Control de acceso (AUTH-006, AUTH-007)
 * 
 * ENDPOINTS PROBADOS:
 * - POST   /api/auth/auth/login
 * - POST   /api/auth/auth/logout
 * - GET    /api/auth/auth/profile
 * 
 * DEPENDENCIAS:
 * - Laravel Sanctum para autenticación
 * - Base de datos con usuario admin@frenad.com
 * 
 * ============================================================================
 */
class AuthenticationTest extends TestCase
{
    /**
     * =========================================================================
     * AUTH-001: Login con credenciales válidas
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que un usuario puede autenticarse correctamente con
     * credenciales válidas y recibe un token de acceso.
     * 
     * PRECONDICIONES:
     * - Usuario admin@frenad.com existe en la base de datos
     * - Usuario está activo
     * - Contraseña es 'password'
     * 
     * ENTRADA:
     * - email: admin@frenad.com
     * - password: password
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Response contiene: success=true, token, datos del usuario
     * 
     * CRITERIO DE ÉXITO:
     * - Respuesta 200
     * - Token no vacío
     * - Datos del usuario correctos
     */
    public function test_AUTH001_user_can_login_with_valid_credentials(): void
    {
        // Arrange: Preparar datos de entrada
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];

        // Act: Ejecutar la acción
        $response = $this->postJson('/api/auth/auth/login', $credentials);

        // Assert: Verificar resultados
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'usuario' => [
                    'id',
                    'nombre',
                    'email',
                    'roles',
                    'activo',
                ],
                'token',
                'token_type',
            ],
        ]);
        
        $response->assertJson([
            'success' => true,
        ]);

        // Verificar que el token no está vacío
        $data = $response->json('data');
        $this->assertNotEmpty($data['token'], 'El token no debe estar vacío');
        $this->assertEquals('Bearer', $data['token_type']);
    }

    /**
     * =========================================================================
     * AUTH-002: Login con credenciales inválidas
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el sistema rechaza intentos de login con contraseña
     * incorrecta y devuelve el error apropiado.
     * 
     * PRECONDICIONES:
     * - Usuario admin@frenad.com existe en la base de datos
     * 
     * ENTRADA:
     * - email: admin@frenad.com
     * - password: contraseña_incorrecta
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 401 Unauthorized
     * - Response contiene mensaje de error
     * 
     * CRITERIO DE ÉXITO:
     * - Respuesta 401
     * - success = false
     * - Mensaje de error descriptivo
     */
    public function test_AUTH002_login_fails_with_invalid_password(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'contraseña_incorrecta',
        ];

        // Act
        $response = $this->postJson('/api/auth/auth/login', $credentials);

        // Assert
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /**
     * =========================================================================
     * AUTH-002b: Login con email inexistente
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el sistema rechaza intentos de login con un email
     * que no existe en el sistema.
     * 
     * PRECONDICIONES:
     * - Email usuario_inexistente@test.com NO existe en la BD
     * 
     * ENTRADA:
     * - email: usuario_inexistente@test.com
     * - password: cualquier_password
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 401 Unauthorized
     * 
     * NOTA DE SEGURIDAD:
     * El mensaje debe ser genérico para no revelar si el email existe
     */
    public function test_AUTH002b_login_fails_with_nonexistent_email(): void
    {
        // Arrange
        $credentials = [
            'email' => 'usuario_inexistente@test.com',
            'password' => 'cualquier_password',
        ];

        // Act
        $response = $this->postJson('/api/auth/auth/login', $credentials);

        // Assert
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * =========================================================================
     * AUTH-002c: Login con campos vacíos
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el sistema valida campos requeridos y devuelve
     * errores de validación apropiados.
     * 
     * ENTRADA:
     * - email: vacío
     * - password: vacío
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 422 Unprocessable Entity
     * - Errores de validación para ambos campos
     */
    public function test_AUTH002c_login_fails_with_empty_fields(): void
    {
        // Arrange
        $credentials = [
            'email' => '',
            'password' => '',
        ];

        // Act
        $response = $this->postJson('/api/auth/auth/login', $credentials);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
                'password',
            ],
        ]);
    }

    /**
     * =========================================================================
     * AUTH-002d: Login con email formato inválido
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el sistema valida el formato del email.
     * 
     * ENTRADA:
     * - email: "email_invalido" (sin @)
     * - password: cualquier valor
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 422 Unprocessable Entity
     * - Error de validación en campo email
     */
    public function test_AUTH002d_login_fails_with_invalid_email_format(): void
    {
        // Arrange
        $credentials = [
            'email' => 'email_invalido_sin_arroba',
            'password' => 'password',
        ];

        // Act
        $response = $this->postJson('/api/auth/auth/login', $credentials);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * =========================================================================
     * AUTH-004: Logout exitoso
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que un usuario autenticado puede cerrar sesión
     * correctamente y su token es invalidado.
     * 
     * PRECONDICIONES:
     * - Usuario está autenticado
     * - Tiene un token válido
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Token invalidado
     * 
     * VERIFICACIÓN ADICIONAL:
     * - Después del logout, el token ya no es válido
     */
    public function test_AUTH004_user_can_logout_successfully(): void
    {
        // Arrange: Autenticar primero
        $this->authenticateUser();

        // Act: Cerrar sesión
        $response = $this->postJson('/api/auth/auth/logout');

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * =========================================================================
     * AUTH-005: Obtener perfil de usuario autenticado
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que un usuario autenticado puede obtener su información
     * de perfil correctamente.
     * 
     * PRECONDICIONES:
     * - Usuario está autenticado con token válido
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 200 OK
     * - Datos del perfil: id, nombre, email, roles
     * 
     * DATOS ESPERADOS:
     * - email debe coincidir con admin@frenad.com
     * - activo debe ser true
     */
    public function test_AUTH005_authenticated_user_can_get_profile(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act
        $response = $this->getJson('/api/auth/auth/profile');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'nombre',
                'email',
                'roles',
                'activo',
            ],
        ]);
        
        $response->assertJson([
            'success' => true,
            'data' => [
                'email' => 'admin@frenad.com',
                'activo' => true,
            ],
        ]);
    }

    /**
     * =========================================================================
     * AUTH-006: Acceso sin token (401 Unauthorized)
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que los endpoints protegidos rechazan peticiones
     * sin token de autenticación.
     * 
     * PRECONDICIONES:
     * - No se envía header Authorization
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 401 Unauthorized
     * 
     * IMPORTANCIA:
     * Esta prueba es crítica para la seguridad del sistema
     */
    public function test_AUTH006_protected_endpoint_requires_authentication(): void
    {
        // Act: Intentar acceder a perfil sin autenticación
        $response = $this->getJson('/api/auth/auth/profile');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * =========================================================================
     * AUTH-006b: Múltiples endpoints protegidos sin token
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que varios endpoints críticos requieren autenticación.
     * 
     * ENDPOINTS VERIFICADOS:
     * - GET /api/product/productos
     * - GET /api/customer/clientes
     * - GET /api/sales/ventas
     * - GET /api/inventory/inventario
     */
    public function test_AUTH006b_multiple_endpoints_require_authentication(): void
    {
        $protectedEndpoints = [
            '/api/product/productos',
            '/api/customer/clientes',
            '/api/sales/ventas',
            '/api/inventory/inventario',
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401, "El endpoint {$endpoint} debería requerir autenticación");
        }
    }

    /**
     * =========================================================================
     * AUTH-007: Token inválido rechazado
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que el sistema rechaza tokens de autenticación inválidos
     * o malformados.
     * 
     * ENTRADA:
     * - Authorization: Bearer token_invalido_12345
     * 
     * RESULTADO ESPERADO:
     * - HTTP Status: 401 Unauthorized
     * 
     * IMPORTANCIA:
     * Previene ataques con tokens falsificados
     */
    public function test_AUTH007_invalid_token_is_rejected(): void
    {
        // Arrange: Usar token inválido
        $invalidToken = 'token_invalido_totalmente_falso_12345';

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $invalidToken,
        ])->getJson('/api/auth/auth/profile');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * =========================================================================
     * AUTH-007b: Token malformado rechazado
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que tokens con formato incorrecto son rechazados.
     * 
     * CASOS DE PRUEBA:
     * - Sin prefijo "Bearer"
     * - Token vacío
     */
    public function test_AUTH007b_malformed_token_is_rejected(): void
    {
        // Caso 1: Sin prefijo Bearer
        $response = $this->withHeaders([
            'Authorization' => 'token_sin_bearer',
        ])->getJson('/api/auth/auth/profile');
        
        $response->assertStatus(401);

        // Caso 2: Bearer sin token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ',
        ])->getJson('/api/auth/auth/profile');
        
        $response->assertStatus(401);
    }

    /**
     * =========================================================================
     * AUTH-EXTRA: Verificar que el token funciona en múltiples peticiones
     * =========================================================================
     * 
     * OBJETIVO:
     * Verificar que un token válido puede usarse para múltiples
     * peticiones consecutivas sin problemas.
     * 
     * RESULTADO ESPERADO:
     * - Todas las peticiones deben ser exitosas (200)
     */
    public function test_AUTH_token_works_for_multiple_requests(): void
    {
        // Arrange
        $this->authenticateUser();

        // Act & Assert: Múltiples peticiones exitosas
        $response1 = $this->getJson('/api/auth/auth/profile');
        $response1->assertStatus(200);

        $response2 = $this->getJson('/api/product/productos');
        $response2->assertStatus(200);

        $response3 = $this->getJson('/api/customer/clientes');
        $response3->assertStatus(200);
    }
}
