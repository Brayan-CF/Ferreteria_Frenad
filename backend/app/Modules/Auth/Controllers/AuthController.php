<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Requests\LoginRequest;
use Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login de usuario
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());
            
            return success_response(
                $data,
                'Inicio de sesión exitoso',
                200
            );

        } catch (Exception $e) {
            return error_response(
                $e->getMessage(),
                null,
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * Logout de usuario
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());
            
            return success_response(
                null,
                'Sesión cerrada exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $profile = $this->authService->profile($request->user());
            
            return success_response($profile);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nuevo' => 'required|string|min:6|confirmed',
        ]);

        try {
            $this->authService->changePassword(
                $request->user(),
                $request->password_actual,
                $request->password_nuevo
            );
            
            return success_response(
                null,
                'Contraseña actualizada exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}