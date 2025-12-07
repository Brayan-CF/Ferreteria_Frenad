<?php

namespace Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use Modules\Product\Models\Producto;
use Modules\Product\Requests\StoreProductoRequest;
use Modules\Product\Requests\UpdateProductoRequest;
use Modules\Product\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ProductoController extends Controller
{
    protected ProductoService $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    /**
     * Listar productos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'activo', 'categoria_id', 'marca_id', 'search',
                'stock_bajo', 'proximos_vencer', 'dias_vencer',
                'order_by', 'order_dir', 'per_page'
            ]);
            
            $productos = $this->productoService->list($filters);
            return success_response($productos);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear producto
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->create($request->validated());
            
            return success_response(
                $producto,
                'Producto creado exitosamente',
                201
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar producto
     */
    public function show(Producto $producto): JsonResponse
    {
        try {
            $producto->load([
                'categoria',
                'marca',
                'unidadBase',
                'unidades.unidad',
                'inventarios.almacen'
            ]);
            
            return success_response($producto);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar producto
     */
    public function update(UpdateProductoRequest $request, Producto $producto): JsonResponse
    {
        try {
            $productoActualizado = $this->productoService->update(
                $producto,
                $request->validated()
            );
            
            return success_response(
                $productoActualizado,
                'Producto actualizado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar producto (desactivar)
     */
    public function destroy(Producto $producto): JsonResponse
    {
        try {
            $this->productoService->delete($producto);
            
            return success_response(
                null,
                'Producto desactivado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Activar producto
     */
    public function activate(Producto $producto): JsonResponse
    {
        try {
            $this->productoService->activate($producto);
            
            return success_response(
                null,
                'Producto activado exitosamente'
            );

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Estadísticas de productos
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->productoService->statistics();
            return success_response($stats);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Buscar por código de barras
     */
    public function buscarPorCodigoBarras(Request $request): JsonResponse
    {
        $request->validate([
            'codigo_barras' => 'required|string'
        ]);

        try {
            $producto = $this->productoService->buscarPorCodigoBarras($request->codigo_barras);
            
            if (!$producto) {
                return error_response('Producto no encontrado', null, 404);
            }

            return success_response($producto);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Buscar por SKU
     */
    public function buscarPorSKU(Request $request): JsonResponse
    {
        $request->validate([
            'sku' => 'required|string'
        ]);

        try {
            $producto = $this->productoService->buscarPorSKU($request->sku);
            
            if (!$producto) {
                return error_response('Producto no encontrado', null, 404);
            }

            return success_response($producto);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}