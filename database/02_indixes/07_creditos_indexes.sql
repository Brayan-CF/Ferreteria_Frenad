-- ============================================================================
-- ÍNDICES PARA CRÉDITOS
-- ============================================================================

CREATE INDEX idx_creditos_cliente ON creditos_clientes(cliente_id);
CREATE INDEX idx_creditos_venta ON creditos_clientes(venta_id);
CREATE INDEX idx_creditos_estado ON creditos_clientes(estado);
CREATE INDEX idx_creditos_vencimiento ON creditos_clientes(fecha_vencimiento);
CREATE INDEX idx_pagos_credito_credito ON pagos_credito(credito_cliente_id);