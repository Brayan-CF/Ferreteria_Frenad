<?php

namespace Modules\Purchase\Controllers;

use App\Http\Controllers\Controller;
use Modules\Purchase\Models\Proveedor;
use Modules\Purchase\Requests\StoreProveedorRequest;
use Modules\Purchase\Services\ProveedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ProveedorController extends Controller
{
    protected ProveedorService $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    /**
     * Listar proveedores
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['activo', 'search', 'order_by', 'order_dir', 'per_page']);
            $proveedores = $this->proveedorService->list($filters);

            return success_response($proveedores);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear proveedor
     */
    public function store(StoreProveedorRequest $request): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->create($request->validated());

            return success_response(
                $proveedor,
                'Proveedor creado exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar proveedor
     */
    public function show(Proveedor $proveedor): JsonResponse
    {
        try {
            return success_response($proveedor);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar proveedor
     */
    public function update(Request $request, Proveedor $proveedor): JsonResponse
    {
        try {
            $proveedorActualizado = $this->proveedorService->update($proveedor, $request->all());

            return success_response(
                $proveedorActualizado,
                'Proveedor actualizado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar proveedor (desactivar)
     */
    public function destroy(Proveedor $proveedor): JsonResponse
    {
        try {
            $this->proveedorService->delete($proveedor);

            return success_response(
                null,
                'Proveedor desactivado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Activar proveedor
     */
    public function activate(Proveedor $proveedor): JsonResponse
    {
        try {
            $this->proveedorService->activate($proveedor);

            return success_response(
                null,
                'Proveedor activado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de proveedores
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->proveedorService->statistics();
            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}