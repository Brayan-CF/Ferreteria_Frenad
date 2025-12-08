<?php

namespace Modules\Customer\Controllers;

use App\Http\Controllers\Controller;
use Modules\Customer\Models\CreditoCliente;
use Modules\Customer\Requests\RegistrarPagoRequest;
use Modules\Customer\Services\CreditoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CreditoController extends Controller
{
    protected CreditoService $creditoService;

    public function __construct(CreditoService $creditoService)
    {
        $this->creditoService = $creditoService;
    }

    /**
     * Listar créditos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'cliente_id', 'estado', 'pendientes', 'vencidos',
                'por_vencer', 'dias', 'fecha_inicio', 'fecha_fin', 'per_page'
            ]);

            $creditos = $this->creditoService->list($filters);
            return success_response($creditos);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar crédito
     */
    public function show(CreditoCliente $credito): JsonResponse
    {
        try {
            $credito->load(['cliente', 'venta.detalles.producto', 'pagos.usuario']);
            return success_response($credito);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Registrar pago de crédito
     */
    public function registrarPago(RegistrarPagoRequest $request, CreditoCliente $credito): JsonResponse
    {
        try {
            $pago = $this->creditoService->registrarPago($credito, $request->validated());

            return success_response(
                $pago,
                'Pago registrado exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Créditos vencidos
     */
    public function vencidos(): JsonResponse
    {
        try {
            $creditos = $this->creditoService->creditosVencidos();
            return success_response($creditos);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Créditos por vencer
     */
    public function porVencer(Request $request): JsonResponse
    {
        try {
            $dias = $request->query('dias', 7);
            $creditos = $this->creditoService->creditosPorVencer($dias);

            return success_response($creditos);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de créditos
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->creditoService->statistics();
            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}