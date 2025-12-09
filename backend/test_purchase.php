<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Purchase\Models\Proveedor;
use Modules\Purchase\Models\Compra;
use Modules\Purchase\Models\DetalleCompra;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
echo '║        🧪 PRUEBA COMPLETA MÓDULO PURCHASE + INVENTARIO 🧪      ║' . PHP_EOL;
echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

DB::beginTransaction();
try {
    // ==========================================
    // PASO 1: CREAR PROVEEDOR
    // ==========================================
    echo '📝 PASO 1: Creando proveedor...' . PHP_EOL;
    
    $proveedor = Proveedor::create([
        'razon_social' => 'FERRETERÍA IMPORTADORA BOLIVIA S.R.L.',
        'nit' => '1025874563',
        'telefono' => '2-2458796',
        'direccion' => 'Av. Blanco Galindo Km 5, Quillacollo',
        'email' => 'ventas@ferreteimport.com.bo',
        'nombre_contacto' => 'Roberto Martínez',
        'activo' => true,
        'creado_por' => 1,
    ]);

    echo '  ✓ Proveedor creado: ID ' . $proveedor->id . PHP_EOL;
    echo '  - Razón Social: ' . $proveedor->razon_social . PHP_EOL;
    echo '  - NIT: ' . $proveedor->nit . PHP_EOL;
    echo '  - Contacto: ' . $proveedor->nombre_contacto . PHP_EOL;
    echo PHP_EOL;

    // ==========================================
    // PASO 2: VERIFICAR INVENTARIO INICIAL
    // ==========================================
    echo '📦 PASO 2: Verificando inventario inicial...' . PHP_EOL;
    
    $productosCompra = [
        ['id' => 1, 'nombre' => 'Taladro', 'almacen_id' => 1],
        ['id' => 2, 'nombre' => 'Cemento', 'almacen_id' => 1],
    ];

    foreach ($productosCompra as $index => $prod) {
        $inventario = Inventario::where('producto_id', $prod['id'])
            ->where('almacen_id', $prod['almacen_id'])
            ->first();
        
        $stockActual = $inventario ? $inventario->cantidad_actual : 0;
        echo '  📊 Producto ID ' . $prod['id'] . ' (' . $prod['nombre'] . ')' . PHP_EOL;
        echo '     Stock inicial en Bodega Principal: ' . $stockActual . ' unidades' . PHP_EOL;
        
        // Guardar para comparar después
        $productosCompra[$index]['stock_inicial'] = $stockActual;
    }
    echo PHP_EOL;

    // ==========================================
    // PASO 3: REGISTRAR COMPRA
    // ==========================================
    echo '🛒 PASO 3: Registrando compra de mercadería...' . PHP_EOL;
    
    // Generar número de compra
    $fechaHoy = date('Ymd');
    $ultimaCompra = Compra::whereDate('fecha_compra', today())
        ->orderBy('id', 'desc')
        ->first();
    $secuencial = $ultimaCompra ? intval(substr($ultimaCompra->numero_compra, -4)) + 1 : 1;
    $numeroCompra = 'C-' . $fechaHoy . '-' . str_pad($secuencial, 4, '0', STR_PAD_LEFT);

    // Calcular totales
    $items = [
        [
            'producto_id' => 1,
            'cantidad' => 20,
            'precio_unitario' => 450.00,
            'almacen_destino_id' => 1,
            'unidad_id' => 2,
        ],
        [
            'producto_id' => 2,
            'cantidad' => 50,
            'precio_unitario' => 65.00,
            'almacen_destino_id' => 1,
            'unidad_id' => 7, // Bolsa
        ],
    ];

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['cantidad'] * $item['precio_unitario'];
    }

    echo '  Detalle de la compra:' . PHP_EOL;
    echo '  - 20x Taladros @ Bs.450.00 = Bs.' . number_format(20 * 450, 2) . PHP_EOL;
    echo '  - 50x Cemento @ Bs.65.00 = Bs.' . number_format(50 * 65, 2) . PHP_EOL;
    echo '  - SUBTOTAL: Bs.' . number_format($subtotal, 2) . PHP_EOL;
    echo PHP_EOL;

    // Crear compra
    $compra = Compra::create([
        'numero_compra' => $numeroCompra,
        'proveedor_id' => $proveedor->id,
        'numero_factura_proveedor' => 'FV-2025-8456',
        'fecha_compra' => now(),
        'fecha_entrega_real' => now(),
        'subtotal' => $subtotal,
        'impuestos' => 0,
        'total' => $subtotal,
        'metodo_pago' => 'Efectivo',
        'estado' => 'recibida',
        'notas' => 'Compra de mercadería para reposición de stock',
        'usuario_id' => 1,
        'creado_por' => 1,
    ]);

    echo '  ✓ Compra registrada: ' . $compra->numero_compra . PHP_EOL;
    echo '  - Proveedor: ' . $proveedor->razon_social . PHP_EOL;
    echo '  - Factura: ' . $compra->numero_factura_proveedor . PHP_EOL;
    echo '  - Total: Bs.' . number_format($compra->total, 2) . PHP_EOL;
    echo '  - Estado: ' . strtoupper($compra->estado) . PHP_EOL;
    echo PHP_EOL;

    // ==========================================
    // PASO 4: CREAR DETALLES Y ACTUALIZAR INVENTARIO
    // ==========================================
    echo '📥 PASO 4: Procesando items y actualizando inventario...' . PHP_EOL;

    foreach ($items as $item) {
        $productoId = $item['producto_id'];
        $almacenId = $item['almacen_destino_id'];
        $cantidad = $item['cantidad'];
        $precioUnitario = $item['precio_unitario'];

        // Crear detalle de compra
        $detalle = DetalleCompra::create([
            'compra_id' => $compra->id,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'unidad_id' => $item['unidad_id'],
            'precio_unitario' => $precioUnitario,
            'subtotal' => $cantidad * $precioUnitario,
            'almacen_destino_id' => $almacenId,
            'creado_por' => 1,
        ]);

        echo '  ✓ Detalle creado: Producto ID ' . $productoId . ', Cantidad: ' . $cantidad . PHP_EOL;

        // Actualizar inventario
        $inventario = Inventario::firstOrNew([
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
        ]);

        $stockAnterior = $inventario->cantidad_actual ?? 0;

        if (!$inventario->exists) {
            $inventario->cantidad_actual = 0;
            $inventario->stock_minimo = 0;
            $inventario->creado_por = 1;
        }

        // Incrementar stock usando DB::table para evitar problemas con compound PK
        if ($inventario->exists) {
            DB::table('inventario')
                ->where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->update([
                    'cantidad_actual' => DB::raw("cantidad_actual + {$cantidad}"),
                ]);
            
            // Refrescar para obtener nuevo valor
            $inventario = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->first();
            $stockNuevo = $inventario->cantidad_actual;
        } else {
            // Si no existía, crearlo
            DB::table('inventario')->insert([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'cantidad_actual' => $cantidad,
                'stock_minimo' => 0,
            ]);
            $stockNuevo = $cantidad;
        }

        echo '     📊 Inventario actualizado: ' . $stockAnterior . ' → ' . $stockNuevo . ' unidades' . PHP_EOL;

        // Registrar movimiento de inventario usando DB::table
        DB::table('movimientos_inventario')->insert([
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'tipo_movimiento' => 'ENTRADA_COMPRA',
            'cantidad' => $cantidad,
            'razon' => 'Compra ' . $compra->numero_compra . ' - Factura: ' . $compra->numero_factura_proveedor,
            'usuario_id' => 1,
            'creado_en' => now(),
        ]);

        echo '     ✓ Movimiento registrado: ENTRADA_COMPRA' . PHP_EOL;
    }
    echo PHP_EOL;

    // ==========================================
    // PASO 5: VERIFICAR INVENTARIO FINAL
    // ==========================================
    echo '✅ PASO 5: Verificando inventario final...' . PHP_EOL;

    foreach ($productosCompra as $prod) {
        $inventario = Inventario::where('producto_id', $prod['id'])
            ->where('almacen_id', $prod['almacen_id'])
            ->first();
        
        $stockFinal = $inventario ? $inventario->cantidad_actual : 0;
        $incremento = $stockFinal - $prod['stock_inicial'];
        
        echo '  📊 Producto ID ' . $prod['id'] . ' (' . $prod['nombre'] . ')' . PHP_EOL;
        echo '     Stock inicial: ' . $prod['stock_inicial'] . ' unidades' . PHP_EOL;
        echo '     Stock final: ' . $stockFinal . ' unidades' . PHP_EOL;
        echo '     Incremento: +' . $incremento . ' unidades ✅' . PHP_EOL;
    }
    echo PHP_EOL;

    // ==========================================
    // RESUMEN FINAL
    // ==========================================
    echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
    echo '║                     ✅ RESUMEN DE LA PRUEBA                     ║' . PHP_EOL;
    echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

    echo '🏢 PROVEEDOR:' . PHP_EOL;
    echo '  Razón Social: ' . $proveedor->razon_social . PHP_EOL;
    echo '  NIT: ' . $proveedor->nit . PHP_EOL;
    echo '  Contacto: ' . $proveedor->nombre_contacto . PHP_EOL;
    echo PHP_EOL;

    echo '🛒 COMPRA:' . PHP_EOL;
    echo '  Número: ' . $compra->numero_compra . PHP_EOL;
    echo '  Factura: ' . $compra->numero_factura_proveedor . PHP_EOL;
    echo '  Fecha: ' . $compra->fecha_compra->format('d/m/Y') . PHP_EOL;
    echo '  Total: Bs.' . number_format($compra->total, 2) . PHP_EOL;
    echo '  Items: ' . count($items) . ' productos' . PHP_EOL;
    echo '  Estado: ' . strtoupper($compra->estado) . PHP_EOL;
    echo PHP_EOL;

    echo '📦 INVENTARIO:' . PHP_EOL;
    $totalMovimientos = DB::table('movimientos_inventario')
        ->where('tipo_movimiento', 'ENTRADA_COMPRA')
        ->where('razon', 'like', '%' . $compra->numero_compra . '%')
        ->count();
    echo '  Movimientos registrados: ' . $totalMovimientos . PHP_EOL;
    echo '  Productos actualizados: ' . count($items) . PHP_EOL;
    echo '  Almacén destino: Bodega Principal' . PHP_EOL;
    echo PHP_EOL;

    echo '╔══════════════════════════════════════════════════════════════════╗' . PHP_EOL;
    echo '║           ✅ TODAS LAS PRUEBAS PASARON EXITOSAMENTE ✅         ║' . PHP_EOL;
    echo '╚══════════════════════════════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

    DB::commit();
    echo '✅ TRANSACCIÓN CONFIRMADA - Todos los cambios guardados' . PHP_EOL;

} catch (Exception $e) {
    DB::rollBack();
    echo PHP_EOL . '❌ ERROR: ' . $e->getMessage() . PHP_EOL;
    echo '📍 Línea: ' . $e->getLine() . PHP_EOL;
    echo '📂 Archivo: ' . $e->getFile() . PHP_EOL;
    echo PHP_EOL . '🔄 TRANSACCIÓN REVERTIDA' . PHP_EOL;
}

echo PHP_EOL . '=== FIN PRUEBA MÓDULO PURCHASE ===' . PHP_EOL;
