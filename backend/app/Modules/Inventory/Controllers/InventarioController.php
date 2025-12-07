<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Modules\Inventory\Services\InventarioService;
use Modules\Inventory\Requests\TransferenciaRequest;
use Modules\Inventory\Requests\AjusteInventarioRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Listar inventario
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'almacen_id', 'stock_bajo', 'con_stock',
                'producto_id', 'search', 'per_page'
            ]);

            $inventario = $this->inventarioService->list($filters);
            return success_response($inventario);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Stock de un producto en todos los almacenes
     */
    public function stockProducto($productoId): JsonResponse
    {
        try {
            $stock = $this->inventarioService->stockProducto($productoId);
            return success_response($stock);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Transferir producto entre almacenes
     */
    public function transferir(TransferenciaRequest $request): JsonResponse
    {
        try {
        $movimiento = $this->inventarioService->transferir($request->validated());

        return success_response(
            $movimiento,
            'Transferencia realizada exitosamente',
            201
        );

    } catch (Exception $e) {
        return error_response($e->getMessage(), null, $e->getCode() ?: 500);
    }
}

/**
 * Ajustar inventario
 */
public function ajustar(AjusteInventarioRequest $request): JsonResponse
{
    try {
        $movimiento = $this->inventarioService->ajustar($request->validated());

        return success_response(
            $movimiento,
            'Ajuste de inventario realizado exitosamente',
            201
        );

    } catch (Exception $e) {
        return error_response($e->getMessage(), null, $e->getCode() ?: 500);
    }
}

/**
 * Actualizar stock mínimo
 */
public function actualizarStockMinimo(Request $request): JsonResponse
{
    $request->validate([
        'producto_id' => 'required|integer|exists:productos,id',
        'almacen_id' => 'required|integer|exists:almacenes,id',
        'stock_minimo' => 'required|numeric|min:0',
    ]);

    try {
        $inventario = $this->inventarioService->actualizarStockMinimo(
            $request->producto_id,
            $request->almacen_id,
            $request->stock_minimo
        );

        return success_response(
            $inventario,
            'Stock mínimo actualizado exitosamente'
        );

    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Productos con stock bajo
 */
public function stockBajo(Request $request): JsonResponse
{
    try {
        $almacenId = $request->query('almacen_id');
        $productos = $this->inventarioService->stockBajo($almacenId);

        return success_response($productos);

    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Estadísticas de inventario
 */
public function statistics(Request $request): JsonResponse
{
    try {
        $almacenId = $request->query('almacen_id');
        $stats = $this->inventarioService->statistics($almacenId);

        return success_response($stats);

    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}
}