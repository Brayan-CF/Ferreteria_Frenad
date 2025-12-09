<?php

namespace Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Modules\Reports\Services\ComprasReportService;
use Modules\Reports\Requests\ReportPeriodRequest;
use Illuminate\Http\JsonResponse;

class ComprasReportController extends Controller
{
    protected $comprasReportService;

    public function __construct(ComprasReportService $comprasReportService)
    {
        $this->comprasReportService = $comprasReportService;
    }

    /**
     * Resumen de compras
     */
    public function resumen(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->comprasReportService->resumenGeneral($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Compras por proveedor
     */
    public function porProveedor(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->comprasReportService->comprasPorProveedor($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Productos más comprados
     */
    public function porProducto(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $limit = $request->input('limit', 20);
        
        $data = $this->comprasReportService->productosMasComprados($fechas, $limit);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Tendencia de compras diarias
     */
    public function tendenciaDiaria(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->comprasReportService->comprasDiarias($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
