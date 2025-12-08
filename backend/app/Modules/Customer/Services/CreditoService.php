<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\CreditoCliente;
use Modules\Customer\Models\Cliente;
use Exception;

class CreditoService
{
    /**
     * Listar créditos con filtros
     */
    public function list(array $filters = [])
    {
        $query = CreditoCliente::with(['cliente', 'venta']);

        // Filtros
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['pendientes'])) {
            $query->pendientes();
        }

        if (!empty($filters['vencidos'])) {
            $query->vencidos();
        }

        if (!empty($filters['por_vencer'])) {
            $dias = $filters['dias'] ?? 7;
            $query->porVencer($dias);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->whereBetween('creado_en', [$filters['fecha_inicio'], $filters['fecha_fin']]);
        }

        // Ordenar por fecha de vencimiento
        $query->orderBy('fecha_vencimiento', 'asc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Registrar pago de crédito
     */
    public function registrarPago(CreditoCliente $credito, array $data)
    {
        DB::beginTransaction();
        try {
            $monto = $data['monto_pago'];
            $metodoPago = $data['metodo_pago'];
            $notas = $data['notas'] ?? null;

            // Validar monto
            if ($monto <= 0) {
                throw new Exception('El monto del pago debe ser mayor a 0', 400);
            }

            if ($monto > $credito->saldo_pendiente) {
                throw new Exception(
                    "El monto del pago ({$monto}) excede el saldo pendiente ({$credito->saldo_pendiente})",
                    400
                );
            }

            // Registrar pago
            $pago = $credito->registrarPago($monto, $metodoPago, $notas);

            DB::commit();
            return $pago->load(['creditoCliente.cliente', 'creditoCliente.venta', 'usuario']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener créditos vencidos
     */
    public function creditosVencidos()
    {
        return CreditoCliente::vencidos()
            ->with(['cliente', 'venta'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Obtener créditos por vencer
     */
    public function creditosPorVencer($dias = 7)
    {
        return CreditoCliente::porVencer($dias)
            ->with(['cliente', 'venta'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Estadísticas de créditos
     */
    public function statistics(): array
    {
        $pendientes = CreditoCliente::pendientes();
        
        return [
            'total_pendiente' => (clone $pendientes)->sum('saldo_pendiente'),
            'cantidad_creditos_pendientes' => (clone $pendientes)->count(),
            'creditos_vencidos' => CreditoCliente::vencidos()->count(),
            'monto_vencido' => CreditoCliente::vencidos()->sum('saldo_pendiente'),
            'creditos_por_vencer' => CreditoCliente::porVencer(7)->count(),
            'por_estado' => DB::table('creditos_clientes')
                ->select('estado', DB::raw('count(*) as cantidad'), DB::raw('sum(saldo_pendiente) as monto'))
                ->groupBy('estado')
                ->get(),
        ];
    }
}