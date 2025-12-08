<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Customer\Models\Cliente;
use Modules\Customer\Models\CreditoCliente;
use Modules\Customer\Models\PagoCredito;
use Modules\Sales\Models\Venta;
use Modules\Sales\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;

echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
echo '║        🧪 PRUEBA COMPLETA MÓDULO CUSTOMER + CRÉDITOS 🧪        ║' . PHP_EOL;
echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

DB::beginTransaction();
try {
    // ==========================================
    // PASO 1: CREAR CLIENTE
    // ==========================================
    echo '📝 PASO 1: Creando cliente...' . PHP_EOL;
    
    $cliente = Cliente::create([
        'nombre_completo' => 'Juan Carlos Pérez López',
        'tipo_documento' => 'CI',
        'numero_documento' => '7654321',
        'telefono' => '71234567',
        'email' => 'juanperez@email.com',
        'direccion' => 'Av. 6 de Agosto #1234, La Paz',
        'limite_credito' => 10000,
        'activo' => true,
        'creado_por' => 1,
    ]);
    
    echo '  ✓ Cliente creado: ' . $cliente->nombre_completo . ' (ID: ' . $cliente->id . ')' . PHP_EOL;
    echo '  📋 Documento: ' . $cliente->tipo_documento . ' ' . $cliente->numero_documento . PHP_EOL;
    echo '  💳 Límite de crédito: Bs. ' . number_format($cliente->limite_credito, 2) . PHP_EOL;
    echo PHP_EOL;
    
    // ==========================================
    // PASO 2: CREAR VENTA A CRÉDITO
    // ==========================================
    echo '💰 PASO 2: Creando venta a crédito...' . PHP_EOL;
    
    $venta = Venta::create([
        'cliente_id' => $cliente->id,
        'usuario_id' => 1,
        'tipo_venta' => 'credito',
        'tipo_documento' => 'factura',
        'metodo_pago' => 'efectivo',  // Método que se usará cuando se cobre el crédito
        'subtotal' => 2550,
        'descuento_porcentaje' => 0,
        'descuento_monto' => 0,
        'iva' => 0,
        'total' => 2550,
        'estado' => 'completada',
        'notas' => 'Venta a crédito - 30 días plazo',
        'creado_por' => 1,
    ]);
    
    // Agregar detalles (3 productos)
    $items = [
        ['producto_id' => 1, 'cant' => 3, 'precio' => 650],  // Taladros
        ['producto_id' => 2, 'cant' => 10, 'precio' => 85],  // Cemento
        ['producto_id' => 3, 'cant' => 10, 'precio' => 25],  // Destornilladores
    ];
    
    foreach ($items as $item) {
        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $item['producto_id'],
            'almacen_id' => 2, // Mostrador
            'cantidad' => $item['cant'],
            'unidad_id' => 2,
            'precio_unitario' => $item['precio'],
            'descuento_monto' => 0,
            'subtotal' => $item['cant'] * $item['precio'],
            'creado_por' => 1,
        ]);
        
        // Reducir stock
        DB::table('inventario')
            ->where('producto_id', $item['producto_id'])
            ->where('almacen_id', 2)
            ->decrement('cantidad_actual', $item['cant']);
    }
    
    echo '  ✓ Venta #' . $venta->numero_venta . ' creada' . PHP_EOL;
    echo '  💵 Total: Bs. ' . number_format($venta->total, 2) . PHP_EOL;
    echo '  📦 Productos: ' . $venta->detalles()->count() . ' items' . PHP_EOL;
    echo PHP_EOL;
    
    // ==========================================
    // PASO 3: CREAR REGISTRO DE CRÉDITO
    // ==========================================
    echo '📊 PASO 3: Registrando crédito...' . PHP_EOL;
    
    $credito = CreditoCliente::create([
        'cliente_id' => $cliente->id,
        'venta_id' => $venta->id,  // Referencia a la venta
        'monto_total' => $venta->total,
        'monto_pagado' => 0,
        'saldo_pendiente' => $venta->total,
        'fecha_vencimiento' => now()->addDays(30),
        'dias_credito' => 30,
        'estado' => 'pendiente',
        'creado_por' => 1,
    ]);
    
    echo '  ✓ Crédito creado (ID: ' . $credito->id . ')' . PHP_EOL;
    echo '  💰 Monto total: Bs. ' . number_format($credito->monto_total, 2) . PHP_EOL;
    echo '  📅 Vencimiento: ' . $credito->fecha_vencimiento->format('d/m/Y') . PHP_EOL;
    echo '  ⏱️  Días restantes: ' . $credito->fecha_vencimiento->diffInDays(now()) . ' días' . PHP_EOL;
    echo PHP_EOL;
    
    // ==========================================
    // PASO 4: REGISTRAR PRIMER PAGO
    // ==========================================
    echo '💳 PASO 4: Registrando primer pago (Bs. 1,000)...' . PHP_EOL;
    
    // Usar el método registrarPago() del modelo CreditoCliente
    $pago1 = $credito->registrarPago(1000, 'efectivo', 'Primer abono del cliente', 1);
    
    // Refrescar el crédito para obtener datos actualizados
    $credito->refresh();
    
    echo '  ✓ Pago registrado: Bs. ' . number_format($pago1->monto_pago, 2) . PHP_EOL;
    echo '  💵 Método: ' . strtoupper($pago1->metodo_pago) . PHP_EOL;
    echo '  📊 Estado del crédito:' . PHP_EOL;
    echo '     • Total: Bs. ' . number_format($credito->monto_total, 2) . PHP_EOL;
    echo '     • Pagado: Bs. ' . number_format($credito->monto_pagado, 2) . PHP_EOL;
    echo '     • Saldo: Bs. ' . number_format($credito->saldo_pendiente, 2) . PHP_EOL;
    echo '     • Estado: ' . strtoupper($credito->estado) . PHP_EOL;
    echo PHP_EOL;
    
    // ==========================================
    // PASO 5: REGISTRAR SEGUNDO PAGO
    // ==========================================
    echo '💳 PASO 5: Registrando segundo pago (Bs. 1,550 - Pago total)...' . PHP_EOL;
    
    // Refrescar el crédito antes del segundo pago
    $credito->refresh();
    
    // Usar el método registrarPago() del modelo CreditoCliente
    $pago2 = $credito->registrarPago(1550, 'qr', 'Pago final - Crédito saldado', 1);
    
    echo '  ✓ Pago registrado: Bs. ' . number_format($pago2->monto_pago, 2) . PHP_EOL;
    echo '  💵 Método: ' . strtoupper($pago2->metodo_pago) . PHP_EOL;
    echo '  📊 Estado del crédito:' . PHP_EOL;
    echo '     • Total: Bs. ' . number_format($credito->monto_total, 2) . PHP_EOL;
    echo '     • Pagado: Bs. ' . number_format($credito->monto_pagado, 2) . PHP_EOL;
    echo '     • Saldo: Bs. ' . number_format($credito->saldo_pendiente, 2) . PHP_EOL;
    echo '     • Estado: ' . strtoupper($credito->estado) . ' ✅' . PHP_EOL;
    echo PHP_EOL;
    
    // ==========================================
    // RESUMEN FINAL
    // ==========================================
    echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
    echo '║                     ✅ RESUMEN DE LA PRUEBA                     ║' . PHP_EOL;
    echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;
    
    $cliente->load('creditos.pagos');
    
    echo '👤 CLIENTE:' . PHP_EOL;
    echo '  Nombre: ' . $cliente->nombre_completo . PHP_EOL;
    echo '  Documento: ' . $cliente->tipo_documento . ' ' . $cliente->numero_documento . PHP_EOL;
    echo '  Créditos activos: ' . $cliente->creditos->count() . PHP_EOL;
    echo '  Total adeudado: Bs. ' . number_format($cliente->creditos()->sum('saldo_pendiente'), 2) . PHP_EOL;
    echo PHP_EOL;
    
    echo '💰 VENTA A CRÉDITO:' . PHP_EOL;
    echo '  Número: ' . $venta->numero_venta . PHP_EOL;
    echo '  Fecha: ' . $venta->fecha_venta->format('d/m/Y H:i') . PHP_EOL;
    echo '  Total: Bs. ' . number_format($venta->total, 2) . PHP_EOL;
    echo '  Items: ' . $venta->detalles()->count() . ' productos' . PHP_EOL;
    echo PHP_EOL;
    
    echo '📊 CRÉDITO:' . PHP_EOL;
    echo '  ID: ' . $credito->id . PHP_EOL;
    echo '  Monto total: Bs. ' . number_format($credito->monto_total, 2) . PHP_EOL;
    echo '  Monto pagado: Bs. ' . number_format($credito->monto_pagado, 2) . PHP_EOL;
    echo '  Saldo pendiente: Bs. ' . number_format($credito->saldo_pendiente, 2) . PHP_EOL;
    echo '  Estado: ' . strtoupper($credito->estado) . PHP_EOL;
    echo '  Vencimiento: ' . $credito->fecha_vencimiento->format('d/m/Y') . PHP_EOL;
    echo PHP_EOL;
    
    echo '💳 PAGOS REALIZADOS:' . PHP_EOL;
    $pagos = PagoCredito::where('credito_cliente_id', $credito->id)->get();
    foreach ($pagos as $i => $pago) {
        echo '  Pago #' . ($i + 1) . ':' . PHP_EOL;
        echo '    • Monto: Bs. ' . number_format($pago->monto_pago, 2) . PHP_EOL;
        echo '    • Método: ' . strtoupper($pago->metodo_pago) . PHP_EOL;
        echo '    • Fecha: ' . $pago->fecha_pago->format('d/m/Y H:i') . PHP_EOL;
        if ($pago->notas) {
            echo '    • Nota: ' . $pago->notas . PHP_EOL;
        }
        echo PHP_EOL;
    }
    
    DB::commit();
    
    echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
    echo '║                ✅ PRUEBA COMPLETADA CON ÉXITO ✅               ║' . PHP_EOL;
    echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo '❌ ERROR: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
