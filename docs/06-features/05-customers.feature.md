# Módulo de Clientes

## Descripción
Gestión de clientes con control de créditos, límites de crédito, seguimiento de pagos y análisis de morosidad.

## Modelos

### Cliente
- **Ubicación:** `Modules/Customer/Models/Cliente.php`
- **Tabla:** `clientes`
- **Campos:** nombre_completo, nit, telefono, email, direccion, limite_credito

### CreditoCliente
- **Ubicación:** `Modules/Customer/Models/CreditoCliente.php`
- **Tabla:** `creditos_clientes`
- **Campos:** cliente_id, venta_id, monto_total, monto_pagado, saldo_pendiente, fecha_vencimiento, estado

### PagoCredito
- **Ubicación:** `Modules/Customer/Models/PagoCredito.php`
- **Tabla:** `pagos_credito`
- **Campos:** credito_cliente_id, monto_pago, metodo_pago, fecha_pago

## Endpoints

### Clientes
- `GET /api/customers` - Listar clientes
- `GET /api/customers/{id}` - Ver cliente con estado de cuenta
- `POST /api/customers` - Registrar cliente
- `PUT /api/customers/{id}` - Actualizar cliente
- `DELETE /api/customers/{id}` - Eliminar cliente
- `POST /api/customers/{id}/activate` - Activar/desactivar cliente
- `GET /api/customers/{id}/account-status` - Estado de cuenta detallado
- `GET /api/customers/{id}/purchase-history` - Historial de compras
- `GET /api/customers/stats` - Estadísticas generales

### Créditos
- `GET /api/credits` - Listar créditos
- `GET /api/credits/{id}` - Ver detalle de crédito
- `POST /api/credits/{id}/payment` - Registrar pago
- `GET /api/credits/overdue` - Créditos vencidos
- `GET /api/credits/due-soon` - Créditos por vencer (7 días)
- `GET /api/credits/stats` - Estadísticas de créditos

## Características

### Estados de Crédito
```
pendiente         → Sin pagos realizados
pagado_parcial    → Pagos parciales aplicados
pagado_completo   → Deuda liquidada totalmente
vencido           → Fecha de vencimiento superada sin pago completo
cancelado         → Crédito anulado
```

### Actualización Automática de Estado
El sistema actualiza automáticamente el estado del crédito:
- Al registrar un pago
- Al verificar fechas de vencimiento
- Al cancelar una venta a crédito

### Control de Límite de Crédito
```php
// Validación automática
credito_disponible = limite_credito - deuda_total

// No permite ventas a crédito si:
- deuda_total >= limite_credito
- tiene créditos vencidos
```

## Validaciones

### Crear Cliente
- **nombre_completo:** requerido, max 150 caracteres
- **nit:** opcional, único si se proporciona
- **telefono:** requerido
- **email:** opcional, formato email válido
- **limite_credito:** opcional, numérico, mínimo 0

### Registrar Pago
- **credito_cliente_id:** requerido, debe existir
- **monto_pago:** requerido, mayor a 0, no exceder saldo pendiente
- **metodo_pago:** requerido (efectivo o qr)

## Ejemplos de Uso

### Registrar Cliente
```bash
POST /api/customers
{
  "nombre_completo": "Juan Carlos Pérez López",
  "nit": "1234567019",
  "telefono": "77123456",
  "email": "juan.perez@email.com",
  "direccion": "Av. América #123",
  "limite_credito": 5000.00
}
```

### Consultar Estado de Cuenta
```bash
GET /api/customers/5/account-status

# Respuesta incluye:
- total_compras
- deuda_total
- credito_disponible
- creditos_activos (lista)
- ultimo_pago
```

### Registrar Pago de Crédito
```bash
POST /api/credits/12/payment
{
  "monto_pago": 500.00,
  "metodo_pago": "efectivo",
  "notas": "Abono a cuenta"
}
```

### Consultar Créditos Vencidos
```bash
GET /api/credits/overdue
```

## Scopes Disponibles

### Cliente
- `Activos()` - Solo clientes activos
- `Frecuentes()` - Clientes con más de 5 compras
- `ConDeuda()` - Clientes con saldo pendiente
- `Buscar($termino)` - Por nombre, NIT o teléfono

### CreditoCliente
- `Pendientes()` - Créditos sin pagar
- `Vencidos()` - Con fecha de vencimiento pasada
- `PorVencer($dias)` - Próximos a vencer

### PagoCredito
- `EntreFechas($inicio, $fin)` - Pagos en rango
- `PorMetodo($metodo)` - Por método de pago
- `Hoy()` - Pagos del día

## Accessors Útiles

### Cliente
```php
$cliente->total_compras         // Suma de todas las compras
$cliente->deuda_total           // Saldo pendiente total
$cliente->credito_disponible    // Límite - deuda
$cliente->estado_cuenta         // Array completo
```

## Integración con Otros Módulos

- **Ventas:** Crea créditos automáticamente en ventas a crédito
- **Reportes:** Proporciona datos para análisis de clientes y morosidad

## Notas Técnicas
- Al eliminar un cliente, debe tener deuda = 0
- Los pagos parciales se registran en `pagos_credito`
- El estado del crédito se actualiza automáticamente con cada pago
- La fecha de vencimiento se calcula: fecha_venta + dias_credito (default 30)
- Los clientes pueden tener múltiples créditos activos simultáneamente
