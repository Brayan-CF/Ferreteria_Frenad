<?php

namespace Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Modules\Reports\Services\VentasReportService;
use Modules\Reports\Requests\ReportPeriodRequest;
use Illuminate\Http\JsonResponse;

class VentasReportController extends Controller
{
    protected $ventasReportService;

    public function __construct(VentasReportService $ventasReportService)
    {
        $this->ventasReportService = $ventasReportService;
    }

    /**
     * Resumen general de ventas
     */
    public function resumen(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        
        $filtros = array_filter([
            'vendedor_id' => $request->vendedor_id,
            'tipo_venta' => $request->tipo_venta,
        ]);

        $data = $this->ventasReportService->resumenGeneral($fechas, $filtros);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Ventas por vendedor
     */
    public function porVendedor(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->ventasReportService->ventasPorVendedor($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Productos más vendidos
     */
    public function porProducto(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $limit = $request->input('limit', 20);
        
        $data = $this->ventasReportService->productosMasVendidos($fechas, $limit);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Ventas por categoría
     */
    public function porCategoria(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->ventasReportService->ventasPorCategoria($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Tendencia de ventas diarias
     */
    public function tendenciaDiaria(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->ventasReportService->ventasDiarias($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Análisis de descuentos
     */
    public function descuentos(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->ventasReportService->analisisDescuentos($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
