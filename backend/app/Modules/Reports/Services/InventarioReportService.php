<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;

class InventarioReportService
{
    /**
     * Productos con stock bajo
     */
    public function stockBajo(): array
    {
        return DB::select("
            SELECT 
                p.id,
                p.nombre as producto,
                p.sku,
                c.nombre as categoria,
                a.nombre as almacen,
                i.cantidad_actual,
                i.stock_minimo,
                (i.stock_minimo - i.cantidad_actual) as cantidad_necesaria
            FROM inventario i
            INNER JOIN productos p ON i.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            INNER JOIN almacenes a ON i.almacen_id = a.id
            WHERE i.cantidad_actual < i.stock_minimo
                AND p.activo = true
            ORDER BY cantidad_necesaria DESC
        ");
    }

    /**
     * Movimientos de inventario (Kardex)
     */
    public function movimientos(array $fechas, array $filtros = []): array
    {
        $sql = "
            SELECT 
                m.id,
                m.creado_en as fecha,
                p.nombre as producto,
                a.nombre as almacen_origen,
                CASE 
                    WHEN m.almacen_destino_id IS NOT NULL THEN ad.nombre
                    ELSE NULL
                END as almacen_destino,
                m.tipo_movimiento,
                m.cantidad,
                m.razon,
                u.nombre as usuario
            FROM movimientos_inventario m
            INNER JOIN productos p ON m.producto_id = p.id
            INNER JOIN almacenes a ON m.almacen_id = a.id
            LEFT JOIN almacenes ad ON m.almacen_destino_id = ad.id
            INNER JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.creado_en BETWEEN ? AND ?
        ";

        $params = [$fechas['fecha_inicio'], $fechas['fecha_fin']];

        if (!empty($filtros['producto_id'])) {
            $sql .= " AND m.producto_id = ?";
            $params[] = $filtros['producto_id'];
        }

        if (!empty($filtros['almacen_id'])) {
            $sql .= " AND m.almacen_id = ?";
            $params[] = $filtros['almacen_id'];
        }

        if (!empty($filtros['tipo_movimiento'])) {
            $sql .= " AND m.tipo_movimiento = ?";
            $params[] = $filtros['tipo_movimiento'];
        }

        $sql .= " ORDER BY m.creado_en DESC LIMIT 500";

        return DB::select($sql, $params);
    }

    /**
     * Inventario valorizado
     */
    public function inventarioValorizado(int $almacenId = null): array
    {
        $sql = "
            SELECT 
                p.id,
                p.nombre as producto,
                p.sku,
                c.nombre as categoria,
                a.nombre as almacen,
                i.cantidad_actual,
                p.precio_compra,
                (i.cantidad_actual * p.precio_compra) as valor_total
            FROM inventario i
            INNER JOIN productos p ON i.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            INNER JOIN almacenes a ON i.almacen_id = a.id
            WHERE i.cantidad_actual > 0
        ";

        $params = [];

        if ($almacenId) {
            $sql .= " AND i.almacen_id = ?";
            $params[] = $almacenId;
        }

        $sql .= " ORDER BY valor_total DESC";

        $items = DB::select($sql, $params);

        $valorTotal = array_sum(array_map(fn($item) => $item->valor_total ?? 0, $items));

        return [
            'items' => $items,
            'resumen' => [
                'valor_total' => round($valorTotal, 2),
                'cantidad_productos' => count($items),
            ],
        ];
    }

    /**
     * Productos sin movimiento
     */
    public function productosSinMovimiento(int $dias = 30): array
    {
        return DB::select("
            SELECT 
                p.id,
                p.nombre as producto,
                p.sku,
                c.nombre as categoria,
                i.cantidad_actual,
                MAX(m.creado_en) as ultimo_movimiento,
                CURRENT_DATE - MAX(DATE(m.creado_en)) as dias_sin_movimiento
            FROM productos p
            LEFT JOIN movimientos_inventario m ON p.id = m.producto_id
            LEFT JOIN inventario i ON p.id = i.producto_id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = true
            GROUP BY p.id, p.nombre, p.sku, c.nombre, i.cantidad_actual
            HAVING MAX(m.creado_en) < CURRENT_DATE - INTERVAL '{$dias} days' 
                OR MAX(m.creado_en) IS NULL
            ORDER BY dias_sin_movimiento DESC NULLS FIRST
        ");
    }
}
