<?php

namespace Modules\Sales\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Venta;
use Modules\Sales\Models\DetalleVenta;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\MovimientoInventario;
use Modules\Customer\Models\CreditoCliente;
use Exception;

class VentaService
{
    /**
     * Listar ventas con filtros
     */
    public function list(array $filters = [])
    {
        $query = Venta::with(['cliente', 'usuario', 'detalles.producto']);

        // Filtros
        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_venta'])) {
            $query->where('tipo_venta', $filters['tipo_venta']);
        }

        if (!empty($filters['metodo_pago'])) {
            $query->where('metodo_pago', $filters['metodo_pago']);
        }

        if (!empty($filters['usuario_id'])) {
            $query->porVendedor($filters['usuario_id']);
        }

        if (!empty($filters['cliente_id'])) {
            $query->porCliente($filters['cliente_id']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        if (!empty($filters['hoy'])) {
            $query->hoy();
        }

        if (!empty($filters['search'])) {
            $query->where('numero_venta', 'ilike', "%{$filters['search']}%");
        }

        // Ordenar por fecha descendente
        $query->orderBy('fecha_venta', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear nueva venta (Proceso completo POS)
     */
    public function create(array $data): Venta
    {
        DB::beginTransaction();
        try {
            // Validar que hay items
            if (empty($data['items']) || count($data['items']) == 0) {
                throw new Exception('Debe agregar al menos un producto a la venta', 400);
            }

            // Calcular totales
            $totales = $this->calcularTotales($data['items'], $data['descuento_porcentaje'] ?? 0);

            // Crear venta
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'usuario_id' => auth()->id(),
                'tipo_venta' => $data['tipo_venta'],
                'tipo_documento' => $data['tipo_documento'] ?? Venta::DOC_NOTA_VENTA,
                'metodo_pago' => $data['metodo_pago'],
                'subtotal' => $totales['subtotal'],
                'descuento_porcentaje' => $data['descuento_porcentaje'] ?? 0,
                'descuento_monto' => $totales['descuento_total'],
                'iva' => $totales['iva'],
                'total' => $totales['total'],
                'estado' => Venta::ESTADO_COMPLETADA,
                'notas' => $data['notas'] ?? null,
                'creado_por' => auth()->id(),
            ]);

            // Procesar cada item
            foreach ($data['items'] as $item) {
                $this->procesarItem($venta, $item);
            }

            // Si es venta a crédito, crear registro de crédito
            if ($data['tipo_venta'] === Venta::TIPO_CREDITO) {
                $this->crearCreditoCliente($venta);
            }

            DB::commit();
            return $venta->load(['detalles.producto', 'cliente', 'usuario']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Anular venta
     */
    public function anular(Venta $venta, string $razon): Venta
    {
        DB::beginTransaction();
        try {
            if (!$venta->puedeSerAnulada()) {
                throw new Exception('Esta venta no puede ser anulada', 400);
            }

            // Devolver stock al inventario
            foreach ($venta->detalles as $detalle) {
                $this->devolverStock($detalle);
            }

            // Marcar venta como anulada
            $venta->update([
                'estado' => Venta::ESTADO_ANULADA,
                'notas' => ($venta->notas ?? '') . "\n[ANULADA] Razón: {$razon}",
                'actualizado_por' => auth()->id(),
            ]);

            // Si era venta a crédito, marcar crédito como cancelado
            if ($venta->tipo_venta === Venta::TIPO_CREDITO) {
                CreditoCliente::where('venta_id', $venta->id)
                    ->update(['estado' => 'cancelado']);
            }

            DB::commit();
            return $venta->fresh(['detalles.producto']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcular totales de la venta
     */
    protected function calcularTotales(array $items, float $descuentoPorcentaje = 0): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $precioItem = $item['cantidad'] * $item['precio_unitario'];
            $descuentoItem = $item['descuento'] ?? 0;
            $subtotal += ($precioItem - $descuentoItem);
        }

        // Calcular descuento global
        $descuentoGlobal = ($subtotal * $descuentoPorcentaje) / 100;
        $subtotalConDescuento = $subtotal - $descuentoGlobal;

        // Calcular IVA (13% en Bolivia) solo si es factura
        $iva = 0; // Por ahora sin IVA, agregar lógica si tipo_documento === 'factura'

        $total = $subtotalConDescuento + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento_total' => round($descuentoGlobal, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Procesar un item de la venta
     */
    protected function procesarItem(Venta $venta, array $item): void
    {
        $productoId = $item['producto_id'];
        $almacenId = $item['almacen_id'];
        $cantidad = $item['cantidad'];
        $precioUnitario = $item['precio_unitario'];
        $descuento = $item['descuento'] ?? 0;

        // Validar stock disponible
        $inventario = Inventario::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->first();

        if (!$inventario || $inventario->cantidad_actual < $cantidad) {
            throw new Exception("Stock insuficiente para producto ID {$productoId} en almacén ID {$almacenId}", 400);
        }

        // Crear detalle de venta
        $detalle = DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'cantidad' => $cantidad,
            'unidad_id' => $item['unidad_id'],
            'precio_unitario' => $precioUnitario,
            'descuento_monto' => $descuento,
            'subtotal' => ($cantidad * $precioUnitario) - $descuento,
            'creado_por' => auth()->id(),
        ]);

        // Reducir stock
        $inventario->reducirStock($cantidad);

        // Registrar movimiento de inventario
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'tipo_movimiento' => MovimientoInventario::SALIDA_VENTA,
            'cantidad' => $cantidad,
            'venta_id' => $venta->id,
            'detalle_venta_id' => $detalle->id,
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Devolver stock al anular venta
     */
    protected function devolverStock(DetalleVenta $detalle): void
    {
        $inventario = Inventario::where('producto_id', $detalle->producto_id)
            ->where('almacen_id', $detalle->almacen_id)
            ->first();

        if ($inventario) {
            $inventario->aumentarStock($detalle->cantidad);

            // Registrar movimiento de devolución
            MovimientoInventario::create([
                'producto_id' => $detalle->producto_id,
                'almacen_id' => $detalle->almacen_id,
                'tipo_movimiento' => MovimientoInventario::DEVOLUCION_VENTA,
                'cantidad' => $detalle->cantidad,
                'venta_id' => $detalle->venta_id,
                'razon' => 'Venta anulada',
                'usuario_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Crear registro de crédito al cliente
     */
    protected function crearCreditoCliente(Venta $venta): void
    {
        if (!$venta->cliente_id) {
            throw new Exception('Debe seleccionar un cliente para ventas a crédito', 400);
        }

        CreditoCliente::create([
            'cliente_id' => $venta->cliente_id,
            'venta_id' => $venta->id,
            'monto_total' => $venta->total,
            'monto_pagado' => 0,
            'saldo_pendiente' => $venta->total,
            'fecha_vencimiento' => now()->addDays(30), // 30 días de plazo
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Estadísticas de ventas
     */
    public function statistics(array $filters = []): array
    {
        $query = Venta::completadas();

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        } else {
            // Por defecto, ventas del mes actual
            $query->whereMonth('fecha_venta', now()->month)
                ->whereYear('fecha_venta', now()->year);
        }

        $totalVentas = (clone $query)->sum('total');
        $cantidadVentas = (clone $query)->count();
        $promedioVenta = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        // Ventas por tipo
        $porTipo = (clone $query)
            ->select('tipo_venta', DB::raw('count(*) as total'), DB::raw('sum(total) as monto'))
            ->groupBy('tipo_venta')
            ->get();

        // Ventas por método de pago
        $porMetodoPago = (clone $query)
            ->select('metodo_pago', DB::raw('count(*) as total'), DB::raw('sum(total) as monto'))
            ->groupBy('metodo_pago')
            ->get();

        // Ventas por vendedor
        $porVendedor = (clone $query)
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->select('usuarios.nombre', DB::raw('count(*) as total'), DB::raw('sum(ventas.total) as monto'))
            ->groupBy('usuarios.nombre')
            ->get();

        return [
            'total_ventas' => round($totalVentas, 2),
            'cantidad_ventas' => $cantidadVentas,
            'promedio_venta' => round($promedioVenta, 2),
            'por_tipo' => $porTipo,
            'por_metodo_pago' => $porMetodoPago,
            'por_vendedor' => $porVendedor,
        ];
    }

    /**
     * Ventas del día
     */
    public function ventasHoy()
    {
        return Venta::hoy()
            ->with(['detalles.producto', 'usuario'])
            ->orderBy('fecha_venta', 'desc')
            ->get();
    }

    /**
     * Ventas diarias agrupadas por fecha
     */
    public function ventasDiarias(array $filters = []): array
    {
        $query = Venta::completadas();

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        } else {
            $query->whereDate('fecha_venta', '>=', now()->subDays(7));
        }

        return $query->select(
                DB::raw("DATE(fecha_venta) as fecha"),
                DB::raw("SUM(total) as total"),
                DB::raw("COUNT(*) as cantidad")
            )
            ->groupBy(DB::raw("DATE(fecha_venta)"))
            ->orderBy('fecha')
            ->get()
            ->toArray();
    }

    /**
     * Top productos más vendidos
     */
    public function topProductos(array $filters = []): array
    {
        $limit = $filters['limit'] ?? 10;

        $query = DetalleVenta::join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada');

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->whereBetween('ventas.fecha_venta', [$filters['fecha_inicio'], $filters['fecha_fin']]);
        }

        return $query->select(
                'productos.nombre',
                DB::raw("SUM(detalle_ventas.cantidad) as cantidad"),
                DB::raw("SUM(detalle_ventas.subtotal) as total")
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Ventas por método de pago
     */
    public function porMetodoPago(array $filters = []): array
    {
        $query = Venta::completadas();

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        return $query->select(
                'metodo_pago',
                DB::raw("COUNT(*) as cantidad"),
                DB::raw("SUM(total) as total")
            )
            ->groupBy('metodo_pago')
            ->get()
            ->toArray();
    }
}