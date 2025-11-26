-- ============================================================================
-- VISTA: REPORTE DE CAJA DEL DÍA
-- ============================================================================

CREATE OR REPLACE VIEW vista_caja_hoy AS
SELECT 
    ac.id AS arqueo_id,
    ac.fecha_apertura,
    ac.fecha_cierre,
    u_apertura.nombre AS usuario_apertura,
    u_cierre.nombre AS usuario_cierre,
    ac.monto_inicial,
    ac.total_ventas_efectivo,
    ac.total_ventas_qr,
    ac.monto_final,
    ac.total_esperado,
    ac.diferencia,
    ac.estado,
    CASE 
        WHEN ac.diferencia > 0 THEN 'SOBRANTE'
        WHEN ac.diferencia < 0 THEN 'FALTANTE'
        ELSE 'CUADRADO'
    END AS estado_diferencia
FROM arqueos_caja ac
INNER JOIN usuarios u_apertura ON ac.usuario_apertura_id = u_apertura.id
LEFT JOIN usuarios u_cierre ON ac.usuario_cierre_id = u_cierre.id
WHERE DATE(ac.fecha_apertura) = CURRENT_DATE
ORDER BY ac.fecha_apertura DESC;

COMMENT ON VIEW vista_caja_hoy IS 'Estado actual de caja del día';