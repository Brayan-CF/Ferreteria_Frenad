<?php

namespace Modules\Customer\Controllers;

use App\Http\Controllers\Controller;
use Modules\Customer\Models\Cliente;
use Modules\Customer\Requests\StoreClienteRequest;
use Modules\Customer\Requests\UpdateClienteRequest;
use Modules\Customer\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ClienteController extends Controller
{
    protected ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Listar clientes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'activo', 'es_frecuente', 'con_deuda',
                'search', 'order_by', 'order_dir', 'per_page'
            ]);

            $clientes = $this->clienteService->list($filters);
            return success_response($clientes);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear cliente
     */
    public function store(StoreClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->create($request->validated());

            return success_response(
                $cliente,
                'Cliente creado exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar cliente
     */
    public function show(Cliente $cliente): JsonResponse
    {
        try {
            return success_response($cliente);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar cliente
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {
        try {
            $clienteActualizado = $this->clienteService->update($cliente, $request->validated());

            return success_response(
                $clienteActualizado,
                'Cliente actualizado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar cliente (desactivar)
     */
    public function destroy(Cliente $cliente): JsonResponse
    {
        try {
            $this->clienteService->delete($cliente);

            return success_response(
                null,
                'Cliente desactivado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Activar cliente
     */
    public function activate(Cliente $cliente): JsonResponse
    {
        try {
            $this->clienteService->activate($cliente);

            return success_response(
                null,
                'Cliente activado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estado de cuenta del cliente
     */
    public function estadoCuenta(Cliente $cliente): JsonResponse
    {
        try {
            $estadoCuenta = $this->clienteService->estadoCuenta($cliente);
            return success_response($estadoCuenta);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Historial de compras
     */
    public function historialCompras(Cliente $cliente, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fecha_inicio', 'fecha_fin', 'per_page']);
            $historial = $this->clienteService->historialCompras($cliente, $filters);

            return success_response($historial);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de clientes
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->clienteService->statistics();
            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}