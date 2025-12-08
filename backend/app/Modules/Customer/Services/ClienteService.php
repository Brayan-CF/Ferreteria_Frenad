<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Cliente;
use Exception;

class ClienteService
{
    /**
     * Listar clientes con filtros
     */
    public function list(array $filters = [])
    {
        $query = Cliente::query();

        // Filtros
        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (!empty($filters['es_frecuente'])) {
            $query->frecuentes();
        }

        if (!empty($filters['con_deuda'])) {
            $query->conDeuda();
        }

        if (!empty($filters['search'])) {
            $query->buscar($filters['search']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'nombre_completo';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear cliente
     */
    public function create(array $data): Cliente
    {
        return Cliente::create([
            'nombre_completo' => $data['nombre_completo'],
            'nit' => $data['nit'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'email' => $data['email'] ?? null,
            'es_frecuente' => $data['es_frecuente'] ?? false,
            'limite_credito' => $data['limite_credito'] ?? 0,
            'activo' => $data['activo'] ?? true,
            'creado_por' => auth()->id(),
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update(Cliente $cliente, array $data): Cliente
    {
        $cliente->update([
            'nombre_completo' => $data['nombre_completo'] ?? $cliente->nombre_completo,
            'nit' => $data['nit'] ?? $cliente->nit,
            'telefono' => $data['telefono'] ?? $cliente->telefono,
            'direccion' => $data['direccion'] ?? $cliente->direccion,
            'email' => $data['email'] ?? $cliente->email,
            'es_frecuente' => $data['es_frecuente'] ?? $cliente->es_frecuente,
            'limite_credito' => $data['limite_credito'] ?? $cliente->limite_credito,
            'activo' => $data['activo'] ?? $cliente->activo,
            'actualizado_por' => auth()->id(),
        ]);

        return $cliente->fresh();
    }

    /**
     * Eliminar cliente (desactivar)
     */
    public function delete(Cliente $cliente): bool
    {
        // Verificar que no tenga deuda pendiente
        if ($cliente->tiene_deuda) {
            throw new Exception('No se puede desactivar el cliente porque tiene deuda pendiente', 400);
        }

        return $cliente->update(['activo' => false]);
    }

    /**
     * Activar cliente
     */
    public function activate(Cliente $cliente): bool
    {
        return $cliente->update(['activo' => true]);
    }

    /**
     * Obtener estado de cuenta del cliente
     */
    public function estadoCuenta(Cliente $cliente): array
    {
        return [
            'cliente' => [
                'id' => $cliente->id,
                'nombre_completo' => $cliente->nombre_completo,
                'nit' => $cliente->nit,
                'telefono' => $cliente->telefono,
                'es_frecuente' => $cliente->es_frecuente,
            ],
            'resumen_financiero' => [
                'limite_credito' => $cliente->limite_credito,
                'deuda_total' => $cliente->deuda_total,
                'credito_disponible' => $cliente->credito_disponible,
                'estado_cuenta' => $cliente->estado_cuenta,
            ],
            'estadisticas_compras' => [
                'total_compras' => $cliente->total_compras,
                'cantidad_compras' => $cliente->cantidad_compras,
                'promedio_compra' => $cliente->cantidad_compras > 0 
                    ? round($cliente->total_compras / $cliente->cantidad_compras, 2) 
                    : 0,
            ],
            'creditos_pendientes' => $cliente->creditos()
                ->pendientes()
                ->with('venta')
                ->orderBy('fecha_vencimiento', 'asc')
                ->get(),
            'ultimos_pagos' => $cliente->pagos()
                ->with('creditoCliente.venta')
                ->orderBy('fecha_pago', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Historial de compras
     */
    public function historialCompras(Cliente $cliente, array $filters = [])
    {
        $query = $cliente->ventas()->with('detalles.producto');

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        return $query->orderBy('fecha_venta', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Estadísticas de clientes
     */
    public function statistics(): array
    {
        return [
            'total' => Cliente::count(),
            'activos' => Cliente::where('activo', true)->count(),
            'frecuentes' => Cliente::where('es_frecuente', true)->count(),
            'con_deuda' => Cliente::conDeuda()->count(),
            'deuda_total_sistema' => DB::table('creditos_clientes')
                ->whereIn('estado', ['pendiente', 'pagado_parcial', 'vencido'])
                ->sum('saldo_pendiente'),
        ];
    }
}