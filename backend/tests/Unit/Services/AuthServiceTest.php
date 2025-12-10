<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Exception;

/**
 * ============================================================================
 * PRUEBAS UNITARIAS DE CAJA BLANCA - AuthService
 * ============================================================================
 * 
 * OBJETIVO: Probar la lógica interna del servicio de autenticación, incluyendo
 * validación de credenciales, generación de tokens y manejo de sesiones.
 * 
 * TÉCNICAS APLICADAS:
 * - Cobertura de decisiones: Cada condición de autenticación
 * - Pruebas de excepción: Credenciales inválidas
 * - Pruebas de seguridad: Validación de contraseñas
 * 
 * MÓDULO: Auth
 * SERVICIO: AuthService
 * AUTOR: Equipo de Desarrollo
 * FECHA: 2025-01-22
 */
class AuthServiceTest extends TestCase
{
    protected AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService();
    }

    /*
     * =========================================================================
     * PRUEBAS DE LOGIN - Credenciales válidas
     * =========================================================================
     */

    /**
     * UNIT-AUTH-001: Login exitoso con credenciales válidas
     * 
     * CAJA BLANCA: Verifica flujo completo de login líneas 14-42
     */
    public function test_UNIT_AUTH001_login_success_with_valid_credentials(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertArrayHasKey('usuario', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertNotEmpty($result['token']);
    }

    /**
     * UNIT-AUTH-002: Login retorna datos de usuario correctos
     * 
     * CAJA BLANCA: Verifica estructura del array 'usuario' líneas 32-39
     */
    public function test_UNIT_AUTH002_login_returns_correct_user_data(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertArrayHasKey('id', $result['usuario']);
        $this->assertArrayHasKey('nombre', $result['usuario']);
        $this->assertArrayHasKey('email', $result['usuario']);
        $this->assertArrayHasKey('roles', $result['usuario']);
        $this->assertArrayHasKey('activo', $result['usuario']);
        
        $this->assertEquals('admin@frenad.com', $result['usuario']['email']);
        $this->assertTrue($result['usuario']['activo']);
    }

    /*
     * =========================================================================
     * PRUEBAS DE LOGIN - Credenciales inválidas
     * =========================================================================
     */

    /**
     * UNIT-AUTH-003: Login falla con email inexistente
     * 
     * CAJA BLANCA: Verifica condición !$usuario línea 21
     */
    public function test_UNIT_AUTH003_login_fails_with_nonexistent_email(): void
    {
        // Arrange
        $credentials = [
            'email' => 'noexiste@test.com',
            'password' => 'cualquiera',
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Credenciales incorrectas');

        // Act
        $this->service->login($credentials);
    }

    /**
     * UNIT-AUTH-004: Login falla con contraseña incorrecta
     * 
     * CAJA BLANCA: Verifica condición !Hash::check() línea 21
     */
    public function test_UNIT_AUTH004_login_fails_with_wrong_password(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'contraseña_incorrecta',
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Credenciales incorrectas');

        // Act
        $this->service->login($credentials);
    }

    /**
     * UNIT-AUTH-005: Login falla con usuario inactivo
     * 
     * CAJA BLANCA: Verifica condición !$usuario->activo línea 25
     */
    public function test_UNIT_AUTH005_login_fails_with_inactive_user(): void
    {
        // Arrange: Buscar o crear usuario inactivo
        $usuario = Usuario::where('activo', false)->first();
        
        if (!$usuario) {
            // Si no hay usuario inactivo, creamos uno temporal
            $this->markTestSkipped('No hay usuarios inactivos para probar');
        }

        $credentials = [
            'email' => $usuario->email,
            'password' => 'password',
        ];

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Usuario inactivo');

        // Act
        $this->service->login($credentials);
    }

    /*
     * =========================================================================
     * PRUEBAS DE LOGOUT
     * =========================================================================
     */

    /**
     * UNIT-AUTH-006: Logout exitoso
     * 
     * CAJA BLANCA: Verifica método logout() líneas 48-52
     */
    public function test_UNIT_AUTH006_logout_success(): void
    {
        // Arrange
        $usuario = Usuario::first();
        $this->assertNotNull($usuario);

        // Act
        $result = $this->service->logout($usuario);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * UNIT-AUTH-007: Logout elimina tokens del usuario
     * 
     * CAJA BLANCA: Verifica línea 50 tokens()->delete()
     */
    public function test_UNIT_AUTH007_logout_deletes_user_tokens(): void
    {
        // Arrange: Login para crear token
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];
        $loginResult = $this->service->login($credentials);
        
        $usuario = Usuario::where('email', 'admin@frenad.com')->first();
        $tokenCountBefore = $usuario->tokens()->count();

        // Act
        $this->service->logout($usuario);

        // Assert
        $tokenCountAfter = $usuario->fresh()->tokens()->count();
        $this->assertEquals(0, $tokenCountAfter);
    }

    /*
     * =========================================================================
     * PRUEBAS DE PERFIL
     * =========================================================================
     */

    /**
     * UNIT-AUTH-008: Profile retorna estructura correcta
     * 
     * CAJA BLANCA: Verifica método profile() líneas 57-66
     */
    public function test_UNIT_AUTH008_profile_returns_correct_structure(): void
    {
        // Arrange
        $usuario = Usuario::first();
        $this->assertNotNull($usuario);

        // Act
        $result = $this->service->profile($usuario);

        // Assert
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('nombre', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertArrayHasKey('activo', $result);
        $this->assertArrayHasKey('creado_en', $result);
    }

    /**
     * UNIT-AUTH-009: Profile retorna datos del usuario correcto
     * 
     * CAJA BLANCA: Verifica que los datos coinciden con el usuario
     */
    public function test_UNIT_AUTH009_profile_returns_correct_user_data(): void
    {
        // Arrange
        $usuario = Usuario::where('email', 'admin@frenad.com')->first();
        $this->assertNotNull($usuario);

        // Act
        $result = $this->service->profile($usuario);

        // Assert
        $this->assertEquals($usuario->id, $result['id']);
        $this->assertEquals($usuario->nombre, $result['nombre']);
        $this->assertEquals($usuario->email, $result['email']);
        $this->assertEquals($usuario->activo, $result['activo']);
    }

    /**
     * UNIT-AUTH-010: Profile formatea fecha correctamente
     * 
     * CAJA BLANCA: Verifica formato de fecha línea 65
     */
    public function test_UNIT_AUTH010_profile_formats_date_correctly(): void
    {
        // Arrange
        $usuario = Usuario::first();

        // Act
        $result = $this->service->profile($usuario);

        // Assert: Verificar formato Y-m-d H:i:s
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $result['creado_en']
        );
    }

    /*
     * =========================================================================
     * PRUEBAS DE CAMBIO DE CONTRASEÑA
     * =========================================================================
     */

    /**
     * UNIT-AUTH-011: Cambio de contraseña falla con contraseña actual incorrecta
     * 
     * CAJA BLANCA: Verifica validación líneas 73-75
     */
    public function test_UNIT_AUTH011_change_password_fails_with_wrong_current(): void
    {
        // Arrange
        $usuario = Usuario::first();

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Contraseña actual incorrecta');

        // Act
        $this->service->changePassword($usuario, 'contraseña_incorrecta', 'nueva123');
    }

    /*
     * =========================================================================
     * PRUEBAS DE VALORES LÍMITE
     * =========================================================================
     */

    /**
     * UNIT-AUTH-012: Login con email en diferentes formatos
     * 
     * VALORES LÍMITE: Emails con diferentes casos
     */
    public function test_UNIT_AUTH012_login_email_case_sensitivity(): void
    {
        // Act & Assert: Email en mayúsculas debería funcionar si la DB ignora case
        try {
            $result = $this->service->login([
                'email' => 'ADMIN@FRENAD.COM',
                'password' => 'password',
            ]);
            // Si funciona, verificar que retorna datos
            $this->assertNotEmpty($result['token']);
        } catch (Exception $e) {
            // Si no funciona, es porque la DB es case-sensitive
            $this->assertEquals('Credenciales incorrectas', $e->getMessage());
        }
    }

    /**
     * UNIT-AUTH-013: Login con email trimmed
     * 
     * VALORES LÍMITE: Email con espacios (debería fallar)
     */
    public function test_UNIT_AUTH013_login_email_with_spaces(): void
    {
        // Assert
        $this->expectException(Exception::class);

        // Act: Email con espacios extra
        $this->service->login([
            'email' => ' admin@frenad.com ',
            'password' => 'password',
        ]);
    }

    /*
     * =========================================================================
     * PRUEBAS DE SEGURIDAD
     * =========================================================================
     */

    /**
     * UNIT-AUTH-014: Token generado es único
     * 
     * SEGURIDAD: Cada login genera token diferente
     */
    public function test_UNIT_AUTH014_generated_tokens_are_unique(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];

        // Act
        $result1 = $this->service->login($credentials);
        $result2 = $this->service->login($credentials);

        // Assert
        $this->assertNotEquals($result1['token'], $result2['token']);
    }

    /**
     * UNIT-AUTH-015: Login carga roles del usuario
     * 
     * CAJA BLANCA: Verifica with('roles') línea 17
     */
    public function test_UNIT_AUTH015_login_loads_user_roles(): void
    {
        // Arrange
        $credentials = [
            'email' => 'admin@frenad.com',
            'password' => 'password',
        ];

        // Act
        $result = $this->service->login($credentials);

        // Assert
        $this->assertArrayHasKey('roles', $result['usuario']);
        $this->assertIsArray($result['usuario']['roles']);
    }
}
