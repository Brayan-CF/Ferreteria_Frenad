-- ============================================================================
-- VISTA: CLIENTES CON DEUDA
-- ============================================================================

CREATE OR REPLACE VIEW vista_clientes_deuda AS
SELECT 
    c.id,
    c.nombre_completo,
    c.telefono,
    c.nit,
    COUNT(cc.id) AS ventas_credito,
    SUM(cc.monto_total) AS total_credito,
    SUM(cc.monto_pagado) AS total_pagado,
    SUM(cc.saldo_pendiente) AS saldo_pendiente,
    MAX(cc.fecha_vencimiento) AS fecha_vencimiento_proxima,
    CASE 
        WHEN MAX(cc.fecha_vencimiento) < CURRENT_DATE THEN 'VENCIDO'
        WHEN MAX(cc.fecha_vencimiento) <= CURRENT_DATE + INTERVAL '7 days' THEN 'POR VENCER'
        ELSE 'AL DÍA'
    END AS estado_deuda
FROM clientes c
INNER JOIN creditos_clientes cc ON c.id = cc.cliente_id
WHERE cc.estado IN ('pendiente', 'pagado_parcial', 'vencido')
GROUP BY c.id, c.nombre_completo, c.telefono, c.nit
HAVING SUM(cc.saldo_pendiente) > 0
ORDER BY saldo_pendiente DESC;

COMMENT ON VIEW vista_clientes_deuda IS 'Clientes con deuda pendiente ordenados por monto';