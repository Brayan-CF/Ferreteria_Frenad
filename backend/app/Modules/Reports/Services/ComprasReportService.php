<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;

class ComprasReportService
{
    /**
     * Resumen de compras
     */
    public function resumenGeneral(array $fechas): array
    {
        $compras = DB::selectOne("
            SELECT 
                COUNT(*) as cantidad_compras,
                COALESCE(SUM(total), 0) as total_compras,
                COALESCE(AVG(total), 0) as promedio_compra,
                MAX(total) as compra_maxima,
                MIN(total) as compra_minima
            FROM compras
            WHERE estado = 'recibida'
                AND fecha_compra BETWEEN ? AND ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        return [
            'resumen' => [
                'total_compras' => round($compras->total_compras, 2),
                'cantidad_compras' => $compras->cantidad_compras,
                'promedio_compra' => round($compras->promedio_compra, 2),
                'compra_maxima' => $compras->compra_maxima,
                'compra_minima' => $compras->compra_minima,
            ],
            'periodo' => [
                'fecha_inicio' => $fechas['fecha_inicio']->format('Y-m-d'),
                'fecha_fin' => $fechas['fecha_fin']->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Compras por proveedor
     */
    public function comprasPorProveedor(array $fechas): array
    {
        return DB::select("
            SELECT 
                pr.id,
                pr.razon_social as proveedor,
                COUNT(c.id) as cantidad_compras,
                COALESCE(SUM(c.total), 0) as total_compras,
                COALESCE(AVG(c.total), 0) as promedio_compra
            FROM proveedores pr
            LEFT JOIN compras c ON pr.id = c.proveedor_id 
                AND c.estado = 'recibida'
                AND c.fecha_compra BETWEEN ? AND ?
            WHERE pr.activo = true
            GROUP BY pr.id, pr.razon_social
            HAVING COUNT(c.id) > 0
            ORDER BY total_compras DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }

    /**
     * Productos más comprados
     */
    public function productosMasComprados(array $fechas, int $limit = 20): array
    {
        return DB::select("
            SELECT 
                p.id,
                p.nombre as producto,
                p.sku,
                c.nombre as categoria,
                SUM(dc.cantidad) as cantidad_comprada,
                SUM(dc.subtotal) as monto_total,
                COUNT(DISTINCT co.id) as numero_compras
            FROM productos p
            INNER JOIN detalle_compras dc ON p.id = dc.producto_id
            INNER JOIN compras co ON dc.compra_id = co.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE co.estado = 'recibida'
                AND co.fecha_compra BETWEEN ? AND ?
            GROUP BY p.id, p.nombre, p.sku, c.nombre
            ORDER BY cantidad_comprada DESC
            LIMIT ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin'], $limit]);
    }

    /**
     * Compras diarias
     */
    public function comprasDiarias(array $fechas): array
    {
        return DB::select("
            SELECT 
                DATE(fecha_compra) as fecha,
                COUNT(*) as cantidad_compras,
                SUM(total) as total_compras
            FROM compras
            WHERE estado = 'recibida'
                AND fecha_compra BETWEEN ? AND ?
            GROUP BY DATE(fecha_compra)
            ORDER BY fecha ASC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }
}
