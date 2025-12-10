<?php

namespace Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sales\Models\Venta;
use Modules\Sales\Requests\StoreVentaRequest;
use Modules\Sales\Services\VentaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class VentaController extends Controller
{
    protected VentaService $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    /**
     * Listar ventas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'estado', 'tipo_venta', 'metodo_pago', 'usuario_id',
                'cliente_id', 'fecha_inicio', 'fecha_fin', 'hoy',
                'search', 'per_page'
            ]);

            $ventas = $this->ventaService->list($filters);
            return success_response($ventas);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear venta (POS)
     */
    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->create($request->validated());

            return success_response(
                $venta,
                'Venta registrada exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Mostrar venta
     */
    public function show(Venta $venta): JsonResponse
    {
        try {
            $venta->load(['detalles.producto', 'detalles.almacen', 'cliente', 'usuario']);
            return success_response($venta);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Anular venta
     */
    public function anular(Request $request, Venta $venta): JsonResponse
    {
        $request->validate([
            'razon' => 'required|string|max:500'
        ], [
            'razon.required' => 'Debe proporcionar una razón para anular la venta'
        ]);

        try {
            $ventaAnulada = $this->ventaService->anular($venta, $request->razon);

        return success_response(
            $ventaAnulada,
            'Venta anulada exitosamente'
        );

    } catch (Exception $e) {
        return error_response($e->getMessage(), null, $e->getCode() ?: 500);
    }
}

/**
 * Estadísticas de ventas
 */
public function statistics(Request $request): JsonResponse
{
    try {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);
        $stats = $this->ventaService->statistics($filters);

        return success_response($stats);

    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Ventas del día
 */
public function ventasHoy(): JsonResponse
{
    try {
        $ventas = $this->ventaService->ventasHoy();
        return success_response($ventas);

    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Ventas diarias (para gráfico)
 */
public function ventasDiarias(Request $request): JsonResponse
{
    try {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);
        $data = $this->ventaService->ventasDiarias($filters);
        return success_response($data);
    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Top productos más vendidos
 */
public function topProductos(Request $request): JsonResponse
{
    try {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'limit']);
        $data = $this->ventaService->topProductos($filters);
        return success_response($data);
    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}

/**
 * Ventas por método de pago
 */
public function porMetodoPago(Request $request): JsonResponse
{
    try {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);
        $data = $this->ventaService->porMetodoPago($filters);
        return success_response($data);
    } catch (Exception $e) {
        return error_response($e->getMessage());
    }
}
}
