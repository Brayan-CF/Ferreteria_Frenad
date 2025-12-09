<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;

class FinancieroReportService
{
    /**
     * Flujo de caja
     */
    public function flujoCaja(array $fechas): array
    {
        // Ingresos por ventas
        $ingresos = DB::selectOne("
            SELECT COALESCE(SUM(total), 0) as total_ingresos
            FROM ventas
            WHERE estado = 'completada'
                AND fecha_venta BETWEEN ? AND ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        // Egresos por compras
        $egresos = DB::selectOne("
            SELECT COALESCE(SUM(total), 0) as total_egresos
            FROM compras
            WHERE estado = 'recibida'
                AND fecha_compra BETWEEN ? AND ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        $flujoNeto = $ingresos->total_ingresos - $egresos->total_egresos;

        return [
            'ingresos' => round($ingresos->total_ingresos, 2),
            'egresos' => round($egresos->total_egresos, 2),
            'flujo_neto' => round($flujoNeto, 2),
            'periodo' => [
                'fecha_inicio' => $fechas['fecha_inicio']->format('Y-m-d'),
                'fecha_fin' => $fechas['fecha_fin']->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Ingresos y egresos detallados
     */
    public function ingresosEgresos(array $fechas): array
    {
        // Ingresos por método de pago
        $ingresosPorMetodo = DB::select("
            SELECT 
                metodo_pago,
                COUNT(*) as cantidad,
                SUM(total) as monto
            FROM ventas
            WHERE estado = 'completada'
                AND fecha_venta BETWEEN ? AND ?
            GROUP BY metodo_pago
            ORDER BY monto DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        // Egresos por proveedor
        $egresosPorProveedor = DB::select("
            SELECT 
                pr.razon_social as proveedor,
                COUNT(c.id) as cantidad_compras,
                SUM(c.total) as monto_total
            FROM compras c
            INNER JOIN proveedores pr ON c.proveedor_id = pr.id
            WHERE c.estado = 'recibida'
                AND c.fecha_compra BETWEEN ? AND ?
            GROUP BY pr.razon_social
            ORDER BY monto_total DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        return [
            'ingresos' => [
                'por_metodo' => $ingresosPorMetodo,
                'total' => array_sum(array_map(fn($i) => $i->monto, $ingresosPorMetodo)),
            ],
            'egresos' => [
                'por_proveedor' => $egresosPorProveedor,
                'total' => array_sum(array_map(fn($e) => $e->monto_total, $egresosPorProveedor)),
            ],
        ];
    }

    /**
     * Cierre de caja diario
     */
    public function cierreCaja(string $fecha): array
    {
        // Ventas del día
        $ventas = DB::select("
            SELECT 
                metodo_pago,
                COUNT(*) as cantidad,
                SUM(total) as monto
            FROM ventas
            WHERE DATE(fecha_venta) = ?
                AND estado = 'completada'
            GROUP BY metodo_pago
        ", [$fecha]);

        // Pagos de créditos del día
        $pagosCreditos = DB::selectOne("
            SELECT 
                COUNT(*) as cantidad_pagos,
                COALESCE(SUM(monto_pago), 0) as total_pagos
            FROM pagos_credito
            WHERE DATE(creado_en) = ?
        ", [$fecha]);

        $totalEfectivo = 0;
        $totalElectronico = 0;

        foreach ($ventas as $venta) {
            if ($venta->metodo_pago === 'efectivo') {
                $totalEfectivo += $venta->monto;
            } else {
                $totalElectronico += $venta->monto;
            }
        }

        return [
            'fecha' => $fecha,
            'ventas' => $ventas,
            'pagos_creditos' => [
                'cantidad' => $pagosCreditos->cantidad_pagos,
                'monto' => round($pagosCreditos->total_pagos, 2),
            ],
            'resumen' => [
                'total_efectivo' => round($totalEfectivo, 2),
                'total_electronico' => round($totalElectronico, 2),
                'total_general' => round($totalEfectivo + $totalElectronico + $pagosCreditos->total_pagos, 2),
            ],
        ];
    }

    /**
     * Análisis de rentabilidad
     */
    public function rentabilidad(array $fechas): array
    {
        // Margen de ganancia por producto vendido
        $rentabilidadProductos = DB::select("
            SELECT 
                p.nombre as producto,
                SUM(dv.cantidad) as unidades_vendidas,
                SUM(dv.subtotal) as ingresos,
                SUM(dv.cantidad * p.precio_compra) as costo,
                SUM(dv.subtotal - (dv.cantidad * p.precio_compra)) as ganancia,
                CASE 
                    WHEN SUM(dv.subtotal) > 0 THEN 
                        ((SUM(dv.subtotal - (dv.cantidad * p.precio_compra)) / SUM(dv.subtotal)) * 100)
                    ELSE 0
                END as margen_porcentaje
            FROM detalle_ventas dv
            INNER JOIN ventas v ON dv.venta_id = v.id
            INNER JOIN productos p ON dv.producto_id = p.id
            WHERE v.estado = 'completada'
                AND v.fecha_venta BETWEEN ? AND ?
            GROUP BY p.id, p.nombre
            ORDER BY ganancia DESC
            LIMIT 20
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);

        return [
            'productos' => $rentabilidadProductos,
            'resumen' => [
                'total_ingresos' => array_sum(array_map(fn($p) => $p->ingresos, $rentabilidadProductos)),
                'total_costos' => array_sum(array_map(fn($p) => $p->costo, $rentabilidadProductos)),
                'ganancia_total' => array_sum(array_map(fn($p) => $p->ganancia, $rentabilidadProductos)),
            ],
        ];
    }
}
