<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\Almacen;
use Modules\Inventory\Models\MovimientoInventario;
use Modules\Product\Models\Producto;
use Exception;

class InventarioService
{
    /**
     * Listar inventario con filtros
     */
    public function list(array $filters = [])
    {
        $query = Inventario::with(['producto.categoria', 'producto.marca', 'almacen']);

        // Filtros
        if (!empty($filters['almacen_id'])) {
            $query->porAlmacen($filters['almacen_id']);
        }

        if (!empty($filters['stock_bajo'])) {
            $query->stockBajo();
        }

        if (!empty($filters['con_stock'])) {
            $query->conStock();
        }

        if (!empty($filters['producto_id'])) {
            $query->where('producto_id', $filters['producto_id']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('producto', function ($q) use ($filters) {
                $q->where('nombre', 'ilike', "%{$filters['search']}%")
                  ->orWhere('sku', 'ilike', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Obtener stock de un producto en todos los almacenes
     */
    public function stockProducto($productoId)
    {
        return Inventario::where('producto_id', $productoId)
            ->with('almacen')
            ->get();
    }

    /**
     * Transferir producto entre almacenes
     */
    public function transferir(array $data): MovimientoInventario
    {
        DB::beginTransaction();
        try {
            $productoId = $data['producto_id'];
            $almacenOrigenId = $data['almacen_origen_id'];
            $almacenDestinoId = $data['almacen_destino_id'];
            $cantidad = $data['cantidad'];
            $razon = $data['razon'] ?? null;

            // Validar que no sea el mismo almacén
            if ($almacenOrigenId == $almacenDestinoId) {
                throw new Exception('El almacén de origen y destino no pueden ser el mismo', 400);
            }

            // Obtener inventario origen
            $inventarioOrigen = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenOrigenId)
                ->first();

            if (!$inventarioOrigen) {
                throw new Exception('El producto no existe en el almacén de origen', 404);
            }

            if ($inventarioOrigen->cantidad_actual < $cantidad) {
                throw new Exception("Stock insuficiente en almacén origen. Disponible: {$inventarioOrigen->cantidad_actual}", 400);
            }

            // Reducir stock en origen
            $inventarioOrigen->reducirStock($cantidad);

            // Aumentar stock en destino (o crear si no existe)
            $inventarioDestino = Inventario::firstOrNew([
                'producto_id' => $productoId,
                'almacen_id' => $almacenDestinoId,
            ]);

            if (!$inventarioDestino->exists) {
                $inventarioDestino->cantidad_actual = 0;
                $inventarioDestino->stock_minimo = 0;
            }

            $inventarioDestino->aumentarStock($cantidad);

            // Registrar movimiento
            $movimiento = MovimientoInventario::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenOrigenId,
                'almacen_destino_id' => $almacenDestinoId,
                'tipo_movimiento' => MovimientoInventario::TRANSFERENCIA,
                'cantidad' => $cantidad,
                'razon' => $razon,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();
            return $movimiento->load(['producto', 'almacen', 'almacenDestino', 'usuario']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajustar inventario
     */
    public function ajustar(array $data): MovimientoInventario
    {
        DB::beginTransaction();
        try {
            $productoId = $data['producto_id'];
            $almacenId = $data['almacen_id'];
            $nuevaCantidad = $data['nueva_cantidad'];
            $razon = $data['razon'];

            // Obtener inventario actual
            $inventario = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->first();

            if (!$inventario) {
                throw new Exception('El producto no existe en el inventario del almacén especificado', 404);
            }

            $cantidadActual = $inventario->cantidad_actual;
            $diferencia = $nuevaCantidad - $cantidadActual;

            if ($diferencia == 0) {
                throw new Exception('La nueva cantidad es igual a la actual. No hay nada que ajustar.', 400);
            }

            // Determinar tipo de ajuste
            $tipoMovimiento = $diferencia > 0
                ? MovimientoInventario::AJUSTE_POSITIVO
                : MovimientoInventario::AJUSTE_NEGATIVO;

            // Actualizar inventario
            $inventario->cantidad_actual = $nuevaCantidad;
            $inventario->save();

            // Registrar movimiento
            $movimiento = MovimientoInventario::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => abs($diferencia),
                'razon' => $razon,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();
            return $movimiento->load(['producto', 'almacen', 'usuario']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar stock mínimo
     */
    public function actualizarStockMinimo($productoId, $almacenId, $stockMinimo): Inventario
    {
        $inventario = Inventario::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->firstOrFail();

        $inventario->stock_minimo = $stockMinimo;
        $inventario->save();

        return $inventario;
    }

    /**
     * Obtener productos con stock bajo
     */
    public function stockBajo($almacenId = null)
    {
        $query = Inventario::stockBajo()
            ->with(['producto.categoria', 'almacen']);

        if ($almacenId) {
            $query->porAlmacen($almacenId);
        }

        return $query->get();
    }

    /**
     * Estadísticas de inventario
     */
    public function statistics($almacenId = null): array
    {
        $query = Inventario::query();

        if ($almacenId) {
            $query->porAlmacen($almacenId);
        }

        $totalProductos = (clone $query)->distinct('producto_id')->count('producto_id');
        $stockBajo = (clone $query)->stockBajo()->count();
        $sinStock = (clone $query)->where('cantidad_actual', 0)->count();

        $valorTotal = DB::table('inventario')
            ->join('productos', 'inventario.producto_id', '=', 'productos.id')
            ->when($almacenId, function ($q) use ($almacenId) {
                return $q->where('inventario.almacen_id', $almacenId);
            })
            ->sum(DB::raw('inventario.cantidad_actual * productos.precio_compra'));

        return [
            'total_productos' => $totalProductos,
            'productos_stock_bajo' => $stockBajo,
            'productos_sin_stock' => $sinStock,
            'valor_total_inventario' => round($valorTotal, 2),
        ];
    }
}