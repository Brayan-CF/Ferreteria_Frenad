<?php

namespace Modules\Purchase\Controllers;

use App\Http\Controllers\Controller;
use Modules\Purchase\Models\Compra;
use Modules\Purchase\Requests\StoreCompraRequest;
use Modules\Purchase\Services\CompraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CompraController extends Controller
{
    protected CompraService $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
    }

    /**
     * Listar compras
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'estado', 'proveedor_id', 'fecha_inicio',
                'fecha_fin', 'search', 'per_page'
            ]);

            $compras = $this->compraService->list($filters);
            return success_response($compras);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear compra (recepción de mercadería)
     */
    public function store(StoreCompraRequest $request): JsonResponse
    {
        try {
            $compra = $this->compraService->create($request->validated());

            return success_response(
                $compra,
                'Compra registrada exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Mostrar compra
     */
    public function show(Compra $compra): JsonResponse
    {
        try {
            $compra->load(['detalles.producto', 'detalles.almacenDestino', 'proveedor', 'usuario', 'ordenCompra']);
            return success_response($compra);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de compras
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fecha_inicio', 'fecha_fin']);
            $stats = $this->compraService->statistics($filters);

            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}