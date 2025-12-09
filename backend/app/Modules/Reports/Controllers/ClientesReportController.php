<?php

namespace Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Modules\Reports\Services\ClientesReportService;
use Modules\Reports\Requests\ReportPeriodRequest;
use Illuminate\Http\JsonResponse;

class ClientesReportController extends Controller
{
    protected $clientesReportService;

    public function __construct(ClientesReportService $clientesReportService)
    {
        $this->clientesReportService = $clientesReportService;
    }

    /**
     * Créditos pendientes
     */
    public function creditosPendientes(): JsonResponse
    {
        $data = $this->clientesReportService->creditosPendientes();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Top clientes
     */
    public function topClientes(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $limit = $request->input('limit', 20);
        
        $data = $this->clientesReportService->topClientes($fechas, $limit);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Análisis de morosidad
     */
    public function morosidad(): JsonResponse
    {
        $data = $this->clientesReportService->analisisMorosidad();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Clientes nuevos
     */
    public function clientesNuevos(ReportPeriodRequest $request): JsonResponse
    {
        $fechas = $request->getFechas();
        $data = $this->clientesReportService->clientesNuevos($fechas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
