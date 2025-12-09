<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Venta;

class VentasReportService
{
    /**
     * Resumen general de ventas
     */
    public function resumenGeneral(array $fechas, array $filtros = []): array
    {
        $query = Venta::query()
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        if (!empty($filtros['vendedor_id'])) {
            $query->where('usuario_id', $filtros['vendedor_id']);
        }

        if (!empty($filtros['tipo_venta'])) {
            $query->where('tipo_venta', $filtros['tipo_venta']);
        }

        $totalVentas = (clone $query)->sum('total');
        $cantidadVentas = (clone $query)->count();
        $promedioVenta = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        // Ventas por tipo
        $ventasPorTipo = (clone $query)
            ->select('tipo_venta', DB::raw('count(*) as cantidad'), DB::raw('sum(total) as monto'))
            ->groupBy('tipo_venta')
            ->get();

        // Ventas por método de pago
        $ventasPorMetodoPago = (clone $query)
            ->select('metodo_pago', DB::raw('count(*) as cantidad'), DB::raw('sum(total) as monto'))
            ->groupBy('metodo_pago')
            ->get();

        return [
            'resumen' => [
                'total_ventas' => round($totalVentas, 2),
                'cantidad_ventas' => $cantidadVentas,
                'promedio_venta' => round($promedioVenta, 2),
                'ticket_maximo' => (clone $query)->max('total'),
                'ticket_minimo' => (clone $query)->min('total'),
            ],
            'por_tipo' => $ventasPorTipo,
            'por_metodo_pago' => $ventasPorMetodoPago,
            'periodo' => [
                'fecha_inicio' => $fechas['fecha_inicio']->format('Y-m-d'),
                'fecha_fin' => $fechas['fecha_fin']->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Ventas por vendedor
     */
    public function ventasPorVendedor(array $fechas): array
    {
        return DB::select("
            SELECT 
                u.id,
                u.nombre as vendedor,
                COUNT(v.id) as cantidad_ventas,
                COALESCE(SUM(v.total), 0) as total_ventas,
                COALESCE(AVG(v.total), 0) as promedio_venta
            FROM usuarios u
            LEFT JOIN ventas v ON u.id = v.usuario_id 
                AND v.estado = 'completada'
                AND v.fecha_venta BETWEEN ? AND ?
            WHERE u.activo = true
            GROUP BY u.id, u.nombre
            HAVING COUNT(v.id) > 0
            ORDER BY total_ventas DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }

    /**
     * Productos más vendidos
     */
    public function productosMasVendidos(array $fechas, int $limit = 20): array
    {
        return DB::select("
            SELECT 
                p.id,
                p.nombre as producto,
                p.sku,
                c.nombre as categoria,
                m.nombre as marca,
                SUM(dv.cantidad) as cantidad_vendida,
                SUM(dv.subtotal) as ingresos_totales,
                COUNT(DISTINCT v.id) as numero_ventas
            FROM productos p
            INNER JOIN detalle_ventas dv ON p.id = dv.producto_id
            INNER JOIN ventas v ON dv.venta_id = v.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN marcas m ON p.marca_id = m.id
            WHERE v.estado = 'completada'
                AND v.fecha_venta BETWEEN ? AND ?
            GROUP BY p.id, p.nombre, p.sku, c.nombre, m.nombre
            ORDER BY cantidad_vendida DESC
            LIMIT ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin'], $limit]);
    }

    /**
     * Ventas por categoría
     */
    public function ventasPorCategoria(array $fechas): array
    {
        return DB::select("
            SELECT 
                c.id,
                c.nombre as categoria,
                COUNT(DISTINCT dv.producto_id) as productos_diferentes,
                SUM(dv.cantidad) as unidades_vendidas,
                SUM(dv.subtotal) as total_ventas
            FROM categorias c
            INNER JOIN productos p ON c.id = p.categoria_id
            INNER JOIN detalle_ventas dv ON p.id = dv.producto_id
            INNER JOIN ventas v ON dv.venta_id = v.id
            WHERE v.estado = 'completada'
                AND v.fecha_venta BETWEEN ? AND ?
            GROUP BY c.id, c.nombre
            ORDER BY total_ventas DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }

    /**
     * Ventas diarias (para gráfico de tendencia)
     */
    public function ventasDiarias(array $fechas): array
    {
        return DB::select("
            SELECT 
                DATE(fecha_venta) as fecha,
                COUNT(*) as cantidad_ventas,
                SUM(total) as total_ventas
            FROM ventas
            WHERE estado = 'completada'
                AND fecha_venta BETWEEN ? AND ?
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha ASC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }

    /**
     * Análisis de descuentos aplicados
     */
    public function analisisDescuentos(array $fechas): array
    {
        $query = Venta::query()
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$fechas['fecha_inicio'], $fechas['fecha_fin']])
            ->where('descuento_monto', '>', 0);

        return [
            'total_descuentos' => round($query->sum('descuento_monto'), 2),
            'ventas_con_descuento' => $query->count(),
            'promedio_descuento' => round($query->avg('descuento_monto'), 2),
            'descuento_maximo' => $query->max('descuento_monto'),
        ];
    }
}
