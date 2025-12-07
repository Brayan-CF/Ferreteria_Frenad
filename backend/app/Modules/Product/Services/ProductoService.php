<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Producto;
use Modules\Product\Models\ProductoUnidad;
use Exception;

class ProductoService
{
    /**
     * Listar productos con filtros y paginación
     */
    public function list(array $filters = [])
    {
        $query = Producto::with(['categoria', 'marca', 'unidadBase', 'inventarios.almacen']);

        // Filtros
        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (!empty($filters['categoria_id'])) {
            $query->porCategoria($filters['categoria_id']);
        }

        if (!empty($filters['marca_id'])) {
            $query->porMarca($filters['marca_id']);
        }

        if (!empty($filters['search'])) {
            $query->buscar($filters['search']);
        }

        if (!empty($filters['stock_bajo'])) {
            $query->stockBajo();
        }

        if (!empty($filters['proximos_vencer'])) {
            $dias = $filters['dias_vencer'] ?? 30;
            $query->proximosVencer($dias);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'nombre';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear nuevo producto
     */
    public function create(array $data): Producto
    {
        DB::beginTransaction();
        try {
            // Crear producto
            $producto = Producto::create([
                'sku' => $data['sku'] ?? null, // Se genera automáticamente si no se envía
                'codigo_barras' => $data['codigo_barras'] ?? null,
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'categoria_id' => $data['categoria_id'],
                'marca_id' => $data['marca_id'] ?? null,
                'unidad_base_id' => $data['unidad_base_id'],
                'precio_compra' => $data['precio_compra'],
                'precio_venta' => $data['precio_venta'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'ubicacion_fisica' => $data['ubicacion_fisica'] ?? null,
                'activo' => $data['activo'] ?? true,
                'creado_por' => auth()->id(),
            ]);

            // Agregar unidades de conversión si se envían
            if (!empty($data['unidades'])) {
                $this->agregarUnidades($producto, $data['unidades']);
            }

            DB::commit();
            return $producto->load(['categoria', 'marca', 'unidadBase', 'unidades.unidad']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar producto
     */
    public function update(Producto $producto, array $data): Producto
    {
        DB::beginTransaction();
        try {
            $updateData = array_filter([
                'codigo_barras' => $data['codigo_barras'] ?? $producto->codigo_barras,
                'nombre' => $data['nombre'] ?? $producto->nombre,
                'descripcion' => $data['descripcion'] ?? $producto->descripcion,
                'categoria_id' => $data['categoria_id'] ?? $producto->categoria_id,
                'marca_id' => $data['marca_id'] ?? $producto->marca_id,
                'unidad_base_id' => $data['unidad_base_id'] ?? $producto->unidad_base_id,
                'precio_compra' => $data['precio_compra'] ?? $producto->precio_compra,
                'precio_venta' => $data['precio_venta'] ?? $producto->precio_venta,
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? $producto->fecha_vencimiento,
                'ubicacion_fisica' => $data['ubicacion_fisica'] ?? $producto->ubicacion_fisica,
                'activo' => $data['activo'] ?? $producto->activo,
                'actualizado_por' => auth()->id(),
            ], fn($value) => $value !== null);

            $producto->update($updateData);

            // Actualizar unidades de conversión si se envían
            if (isset($data['unidades'])) {
                // Eliminar unidades antiguas
                $producto->unidades()->delete();
                // Agregar nuevas unidades
                $this->agregarUnidades($producto, $data['unidades']);
            }

            DB::commit();
            return $producto->fresh(['categoria', 'marca', 'unidadBase', 'unidades.unidad']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar producto (desactivar)
     */
    public function delete(Producto $producto): bool
    {
        // Verificar que no tenga movimientos de inventario
        if ($producto->movimientos()->count() > 0) {
            throw new Exception('No se puede eliminar el producto porque tiene movimientos de inventario registrados', 400);
        }

        return $producto->update(['activo' => false]);
    }

    /**
     * Activar producto
     */
    public function activate(Producto $producto): bool
    {
        return $producto->update(['activo' => true]);
    }

    /**
     * Agregar unidades de conversión
     */
    protected function agregarUnidades(Producto $producto, array $unidades): void
    {
        foreach ($unidades as $unidad) {
            ProductoUnidad::create([
                'producto_id' => $producto->id,
                'unidad_id' => $unidad['unidad_id'],
                'factor_conversion' => $unidad['factor_conversion'],
                'es_unidad_compra' => $unidad['es_unidad_compra'] ?? false,
                'es_unidad_venta' => $unidad['es_unidad_venta'] ?? false,
            ]);
        }
    }

    /**
     * Obtener estadísticas de productos
     */
    public function statistics(): array
    {
        return [
            'total' => Producto::count(),
            'activos' => Producto::where('activo', true)->count(),
            'inactivos' => Producto::where('activo', false)->count(),
            'stock_bajo' => Producto::stockBajo()->count(),
            'proximos_vencer' => Producto::proximosVencer(30)->count(),
            'por_categoria' => DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->select('categorias.nombre', DB::raw('count(*) as total'))
                ->where('productos.activo', true)
                ->groupBy('categorias.nombre')
                ->get(),
            'valor_total_inventario' => $this->calcularValorTotalInventario(),
        ];
    }

    /**
     * Calcular valor total del inventario
     */
    protected function calcularValorTotalInventario(): float
    {
        return DB::table('productos')
            ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
            ->where('productos.activo', true)
            ->sum(DB::raw('inventario.cantidad_actual * productos.precio_compra'));
    }

    /**
     * Buscar producto por código de barras
     */
    public function buscarPorCodigoBarras(string $codigoBarras): ?Producto
    {
        return Producto::where('codigo_barras', $codigoBarras)
            ->with(['categoria', 'marca', 'unidadBase', 'inventarios.almacen'])
            ->first();
    }

    /**
     * Buscar producto por SKU
     */
    public function buscarPorSKU(string $sku): ?Producto
    {
        return Producto::where('sku', $sku)
            ->with(['categoria', 'marca', 'unidadBase', 'inventarios.almacen'])
            ->first();
    }
}