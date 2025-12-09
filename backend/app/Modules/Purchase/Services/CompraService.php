<?php

namespace Modules\Purchase\Services;

use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\Compra;
use Modules\Purchase\Models\DetalleCompra;
use Modules\Purchase\Models\OrdenCompra;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\MovimientoInventario;
use Exception;

class CompraService
{
    /**
     * Listar compras con filtros
     */
    public function list(array $filters = [])
    {
        $query = Compra::with(['proveedor', 'usuario', 'ordenCompra']);

        // Filtros
        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['proveedor_id'])) {
            $query->porProveedor($filters['proveedor_id']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('numero_compra', 'ilike', "%{$filters['search']}%")
                  ->orWhere('numero_factura_proveedor', 'ilike', "%{$filters['search']}%");
            });
        }

        // Ordenar por fecha descendente
        $query->orderBy('fecha_compra', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear nueva compra (recepción de mercadería)
     */
    public function create(array $data): Compra
    {
        DB::beginTransaction();
        try {
            // Validar que hay items
            if (empty($data['items']) || count($data['items']) == 0) {
                throw new Exception('Debe agregar al menos un producto a la compra', 400);
            }

            // Calcular totales
            $totales = $this->calcularTotales($data['items']);

            // Crear compra
            $compra = Compra::create([
                'orden_compra_id' => $data['orden_compra_id'] ?? null,
                'proveedor_id' => $data['proveedor_id'],
                'numero_factura_proveedor' => $data['numero_factura_proveedor'] ?? null,
                'fecha_entrega_esperada' => $data['fecha_entrega_esperada'] ?? null,
                'fecha_entrega_real' => now(),
                'subtotal' => $totales['subtotal'],
                'impuestos' => $totales['impuestos'],
                'total' => $totales['total'],
                'metodo_pago' => $data['metodo_pago'] ?? 'Efectivo',
                'estado' => Compra::ESTADO_RECIBIDA,
                'notas' => $data['notas'] ?? null,
                'usuario_id' => auth()->id(),
                'creado_por' => auth()->id(),
            ]);

            // Procesar cada item
            foreach ($data['items'] as $item) {
                $this->procesarItem($compra, $item);
            }

            // Si está vinculada a una orden, actualizar estado de la orden
            if ($compra->orden_compra_id) {
                $this->actualizarEstadoOrden($compra->orden_compra_id);
            }

            DB::commit();
            return $compra->load(['detalles.producto', 'proveedor', 'usuario']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcular totales de la compra
     */
    protected function calcularTotales(array $items): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['cantidad'] * $item['precio_unitario'];
        }

        $impuestos = 0; // Agregar lógica de impuestos si es necesario
        $total = $subtotal + $impuestos;

        return [
            'subtotal' => round($subtotal, 2),
            'impuestos' => round($impuestos, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Procesar un item de la compra
     */
    protected function procesarItem(Compra $compra, array $item): void
    {
        $productoId = $item['producto_id'];
        $almacenDestinoId = $item['almacen_destino_id'];
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
            'almacen_destino_id' => $almacenDestinoId,
            'creado_por' => auth()->id(),
        ]);

        // Aumentar stock en inventario
        $inventario = Inventario::firstOrNew([
            'producto_id' => $productoId,
            'almacen_id' => $almacenDestinoId,
        ]);

        if (!$inventario->exists) {
            $inventario->cantidad_actual = 0;
            $inventario->stock_minimo = 0;
        }

        $inventario->aumentarStock($cantidad);

        // Registrar movimiento de inventario
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'almacen_id' => $almacenDestinoId,
            'tipo_movimiento' => MovimientoInventario::ENTRADA_COMPRA,
            'cantidad' => $cantidad,
            'razon' => 'Compra ' . $compra->numero_compra . ' - Detalle ID: ' . $detalle->id,
            'usuario_id' => auth()->id(),
        ]);
    }

    /**
     * Actualizar estado de orden de compra
     */
    protected function actualizarEstadoOrden($ordenId): void
    {
        $orden = OrdenCompra::find($ordenId);
        if ($orden) {
            // Lógica simple: marcar como recibida completa
            // En un sistema más complejo, verificarías si se recibió todo
            $orden->update([
                'estado' => OrdenCompra::ESTADO_RECIBIDA_COMPLETA,
                'actualizado_por' => auth()->id(),
            ]);
        }
    }

    /**
     * Estadísticas de compras
     */
    public function statistics(array $filters = []): array
    {
        $query = Compra::recibidas();

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->entreFechas($filters['fecha_inicio'], $filters['fecha_fin']);
        } else {
            // Por defecto, compras del mes actual
            $query->whereMonth('fecha_compra', now()->month)
                ->whereYear('fecha_compra', now()->year);
        }

        $totalCompras = (clone $query)->sum('total');
        $cantidadCompras = (clone $query)->count();

        $porProveedor = (clone $query)
            ->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->select('proveedores.razon_social', DB::raw('count(*) as total'), DB::raw('sum(compras.total) as monto'))
            ->groupBy('proveedores.razon_social')
            ->get();

        return [
            'total_compras' => round($totalCompras, 2),
            'cantidad_compras' => $cantidadCompras,
            'promedio_compra' => $cantidadCompras > 0 ? round($totalCompras / $cantidadCompras, 2) : 0,
            'por_proveedor' => $porProveedor,
        ];
    }
}