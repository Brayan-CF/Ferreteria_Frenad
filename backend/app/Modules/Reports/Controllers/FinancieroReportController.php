<?php

namespace Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Modules\Reports\Services\FinancieroReportService;
use Modules\Reports\Requests\ReportPeriodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancieroReportController extends Controller
{
    protected $financieroReportService;

    public function __construct(FinancieroReportService $financieroReportService)
    {
        $this->financieroReportService = $financieroReportService;
    }

    /**
     * Flujo de caja
     */
    public function flujoCaja(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->financieroReportService->flujoCaja($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Ingresos y egresos detallados
     */
    public function ingresosEgresos(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->financieroReportService->ingresosEgresos($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Cierre de caja
     */
    public function cierreCaja(Request $request): JsonResponse
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $data = $this->financieroReportService->cierreCaja($fecha);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Análisis de rentabilidad
     */
    public function rentabilidad(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->financieroReportService->rentabilidad($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
