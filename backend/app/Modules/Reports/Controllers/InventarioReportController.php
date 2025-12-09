<?php

namespace Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Modules\Reports\Services\InventarioReportService;
use Modules\Reports\Requests\ReportPeriodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioReportController extends Controller
{
    protected $inventarioReportService;

    public function __construct(InventarioReportService $inventarioReportService)
    {
        $this->inventarioReportService = $inventarioReportService;
    }

    /**
     * Productos con stock bajo
     */
    public function stockBajo(): JsonResponse
    {
        $data = $this->inventarioReportService->stockBajo();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Movimientos de inventario
     */
    public function movimientos(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        
        $filtros = array_filter([
            'producto_id' => $request->producto_id,
            'almacen_id' => $request->almacen_id,
            'tipo_movimiento' => $request->tipo_movimiento,
        ]);

        $data = $this->inventarioReportService->movimientos($fechas, $filtros);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Inventario valorizado
     */
    public function valorizado(Request $request): JsonResponse
    {
        $almacenId = $request->input('almacen_id');
        $data = $this->inventarioReportService->inventarioValorizado($almacenId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Productos sin movimiento
     */
    public function sinMovimiento(Request $request): JsonResponse
    {
        $dias = $request->input('dias', 30);
        $data = $this->inventarioReportService->productosSinMovimiento($dias);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
