<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;

class ClientesReportService
{
    /**
     * Créditos pendientes
     */
    public function creditosPendientes(): array
    {
        $creditos = DB::select("
            SELECT 
                cl.id as cliente_id,
                cl.nombre_completo as cliente,
                cl.telefono,
                cc.id as credito_id,
                v.numero_venta,
                cc.monto_total,
                cc.monto_pagado,
                cc.saldo_pendiente,
                cc.fecha_vencimiento,
                cc.estado,
                CASE 
                    WHEN cc.fecha_vencimiento < CURRENT_DATE THEN 'Vencido'
                    WHEN cc.fecha_vencimiento BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '7 days' THEN 'Por vencer'
                    ELSE 'Vigente'
                END as situacion
            FROM creditos_clientes cc
            INNER JOIN clientes cl ON cc.cliente_id = cl.id
            INNER JOIN ventas v ON cc.venta_id = v.id
            WHERE cc.estado IN ('pendiente', 'pagado_parcial')
            ORDER BY cc.fecha_vencimiento ASC
        ");

        $totalPendiente = array_sum(array_map(fn($c) => $c->saldo_pendiente, $creditos));

        return [
            'creditos' => $creditos,
            'resumen' => [
                'cantidad_creditos' => count($creditos),
                'total_pendiente' => round($totalPendiente, 2),
            ],
        ];
    }

    /**
     * Top clientes
     */
    public function topClientes(array $fechas, int $limit = 20): array
    {
        return DB::select("
            SELECT 
                c.id,
                c.nombre_completo as cliente,
                c.telefono,
                c.email,
                COUNT(DISTINCT v.id) as cantidad_compras,
                COALESCE(SUM(v.total), 0) as total_comprado,
                COALESCE(AVG(v.total), 0) as promedio_compra,
                MAX(v.fecha_venta) as ultima_compra
            FROM clientes c
            LEFT JOIN ventas v ON c.id = v.cliente_id 
                AND v.estado = 'completada'
                AND v.fecha_venta BETWEEN ? AND ?
            WHERE c.activo = true
            GROUP BY c.id, c.nombre_completo, c.telefono, c.email
            HAVING COUNT(DISTINCT v.id) > 0
            ORDER BY total_comprado DESC
            LIMIT ?
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin'], $limit]);
    }

    /**
     * Análisis de morosidad
     */
    public function analisisMorosidad(): array
    {
        $morosidad = DB::selectOne("
            SELECT 
                COUNT(*) as cantidad_clientes_morosos,
                COALESCE(SUM(saldo_pendiente), 0) as monto_total_moroso,
                COALESCE(AVG(saldo_pendiente), 0) as promedio_deuda
            FROM creditos_clientes
            WHERE estado IN ('pendiente', 'pagado_parcial', 'vencido')
                AND fecha_vencimiento < CURRENT_DATE
        ");

        $clientesMorosos = DB::select("
            SELECT 
                cl.id,
                cl.nombre_completo as cliente,
                cl.telefono,
                COUNT(cc.id) as creditos_vencidos,
                COALESCE(SUM(cc.saldo_pendiente), 0) as deuda_total,
                MIN(cc.fecha_vencimiento) as credito_mas_antiguo,
                CURRENT_DATE - MIN(cc.fecha_vencimiento) as dias_mora_maxima
            FROM clientes cl
            INNER JOIN creditos_clientes cc ON cl.id = cc.cliente_id
            WHERE cc.estado IN ('pendiente', 'pagado_parcial', 'vencido')
                AND cc.fecha_vencimiento < CURRENT_DATE
            GROUP BY cl.id, cl.nombre_completo, cl.telefono
            ORDER BY deuda_total DESC
        ");

        return [
            'resumen' => [
                'cantidad_clientes_morosos' => $morosidad->cantidad_clientes_morosos,
                'monto_total_moroso' => round($morosidad->monto_total_moroso, 2),
                'promedio_deuda' => round($morosidad->promedio_deuda, 2),
            ],
            'clientes_morosos' => $clientesMorosos,
        ];
    }

    /**
     * Clientes nuevos en el período
     */
    public function clientesNuevos(array $fechas): array
    {
        return DB::select("
            SELECT 
                c.id,
                c.nombre_completo as cliente,
                c.telefono,
                c.email,
                c.creado_en as fecha_registro,
                COUNT(v.id) as compras_realizadas,
                COALESCE(SUM(v.total), 0) as total_comprado
            FROM clientes c
            LEFT JOIN ventas v ON c.id = v.cliente_id AND v.estado = 'completada'
            WHERE c.creado_en BETWEEN ? AND ?
            GROUP BY c.id, c.nombre_completo, c.telefono, c.email, c.creado_en
            ORDER BY c.creado_en DESC
        ", [$fechas['fecha_inicio'], $fechas['fecha_fin']]);
    }
}
