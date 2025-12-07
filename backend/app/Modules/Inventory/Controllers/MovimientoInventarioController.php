<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Modules\Inventory\Services\MovimientoInventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MovimientoInventarioController extends Controller
{
    protected MovimientoInventarioService $movimientoService;

    public function __construct(MovimientoInventarioService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    /**
     * Listar movimientos (Kardex general)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'producto_id', 'almacen_id', 'tipo_movimiento',
                'usuario_id', 'fecha_inicio', 'fecha_fin', 'per_page'
            ]);

            $movimientos = $this->movimientoService->list($filters);
            return success_response($movimientos);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Kardex de un producto específico
     */
    public function kardexProducto($productoId, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['almacen_id', 'fecha_inicio', 'fecha_fin']);
            $kardex = $this->movimientoService->kardexProducto($productoId, $filters);

            return success_response($kardex);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de movimientos
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fecha_inicio', 'fecha_fin']);
            $stats = $this->movimientoService->statistics($filters);

            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}