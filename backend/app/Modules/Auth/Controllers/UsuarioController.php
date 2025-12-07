<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Requests\StoreUsuarioRequest;
use Modules\Auth\Requests\UpdateUsuarioRequest;
use Modules\Auth\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class UsuarioController extends Controller
{
    protected UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Listar usuarios
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['activo', 'rol', 'search', 'per_page']);
            $usuarios = $this->usuarioService->list($filters);
            
            return success_response($usuarios);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear usuario
     */
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        try {
            $usuario = $this->usuarioService->create($request->validated());
            
            return success_response(
                $usuario,
                'Usuario creado exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar usuario
     */
    public function show(Usuario $usuario): JsonResponse
    {
        try {
            $usuario->load('roles');
            return success_response($usuario);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        try {
            $usuarioActualizado = $this->usuarioService->update(
                $usuario,
                $request->validated()
            );
            
            return success_response(
                $usuarioActualizado,
                'Usuario actualizado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar usuario (desactivar)
     */
    public function destroy(Usuario $usuario): JsonResponse
    {
        try {
            $this->usuarioService->delete($usuario);
            
            return success_response(
                null,
                'Usuario desactivado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Activar usuario
     */
    public function activate(Usuario $usuario): JsonResponse
    {
        try {
            $this->usuarioService->activate($usuario);
            
            return success_response(
                null,
                'Usuario activado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de usuarios
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->usuarioService->statistics();
            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}