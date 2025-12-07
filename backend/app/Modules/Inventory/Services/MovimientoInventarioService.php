<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioService
{
    /**
     * Listar movimientos con filtros (Kardex)
     */
    public function list(array $filters = [])
    {
        $query = MovimientoInventario::with([
            'producto',
            'almacen',
            'almacenDestino',
            'usuario'
        ]);

        // Filtros
        if (!empty($filters['producto_id'])) {
            $query->porProducto($filters['producto_id']);
        }

        if (!empty($filters['almacen_id'])) {
            $query->porAlmacen($filters['almacen_id']);
        }

        if (!empty($filters['tipo_movimiento'])) {
            $query->porTipo($filters['tipo_movimiento']);
        }

        if (!empty($filters['usuario_id'])) {
            $query->porUsuario($filters['usuario_id']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        // Ordenar por fecha descendente (más recientes primero)
        $query->orderBy('creado_en', 'desc');

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Obtener kardex de un producto específico
     */
    public function kardexProducto($productoId, array $filters = [])
    {
        $query = MovimientoInventario::porProducto($productoId)
            ->with(['almacen', 'almacenDestino', 'usuario']);

        if (!empty($filters['almacen_id'])) {
            $query->porAlmacen($filters['almacen_id']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        return $query->orderBy('creado_en', 'asc')->get();
    }

    /**
     * Estadísticas de movimientos
     */
    public function statistics(array $filters = []): array
    {
        $query = MovimientoInventario::query();

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        $porTipo = (clone $query)
            ->select('tipo_movimiento', DB::raw('count(*) as total'), DB::raw('sum(cantidad) as cantidad_total'))
            ->groupBy('tipo_movimiento')
            ->get();

        $totalMovimientos = (clone $query)->count();

        return [
            'total_movimientos' => $totalMovimientos,
            'por_tipo' => $porTipo,
        ];
    }
}