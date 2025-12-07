<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Usuario;
use Exception;

class AuthService
{
    /**
     * Autenticar usuario
     */
    public function login(array $credentials): array
    {
        $usuario = Usuario::where('email', $credentials['email'])
            ->with('roles')
            ->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password_hash)) {
            throw new Exception('Credenciales incorrectas', 401);
        }

        if (!$usuario->activo) {
            throw new Exception('Usuario inactivo. Contacte al administrador.', 403);
        }

        // Crear token de autenticación
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'roles' => $usuario->roles_nombres,
                'activo' => $usuario->activo,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout(Usuario $usuario): bool
    {
        // Revocar todos los tokens del usuario
        $usuario->tokens()->delete();
        return true;
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(Usuario $usuario): array
    {
        return [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'roles' => $usuario->roles_nombres,
            'activo' => $usuario->activo,
            'creado_en' => $usuario->creado_en->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Usuario $usuario, string $passwordActual, string $passwordNuevo): bool
    {
        if (!Hash::check($passwordActual, $usuario->password)) {
            throw new Exception('Contraseña actual incorrecta', 400);
        }

        $usuario->update(['password' => $passwordNuevo]);
        return true;
    }
}