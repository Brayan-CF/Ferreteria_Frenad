<?php

namespace Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Inventory\Models\Almacen;
use Exception;

class AlmacenController extends Controller
{
    /**
     * Listar todos los almacenes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Almacen::query();

            // Filtro por activo
            if ($request->has('activo')) {
                $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
            }

            // Filtro por tipo
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Búsqueda por nombre
            if ($request->has('search')) {
                $query->where('nombre', 'ilike', '%' . $request->search . '%');
            }

            $almacenes = $query->orderBy('nombre')
                              ->paginate($request->get('per_page', 15));

            return success_response($almacenes);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar un almacén específico
     */
    public function show($id): JsonResponse
    {
        try {
            $almacen = Almacen::with(['inventarios.producto'])->find($id);

            if (!$almacen) {
                return error_response('Almacén no encontrado', 404);
            }

            return success_response($almacen);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear nuevo almacén
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:almacenes,nombre',
                'tipo' => 'required|in:bodega,mostrador,otro',
                'ubicacion' => 'nullable|string|max:255',
                'activo' => 'nullable|boolean',
            ], [
                'nombre.required' => 'El nombre es requerido',
                'nombre.unique' => 'Ya existe un almacén con ese nombre',
                'tipo.required' => 'El tipo es requerido',
                'tipo.in' => 'El tipo debe ser: bodega, mostrador u otro',
            ]);

            $almacen = Almacen::create([
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'ubicacion' => $validated['ubicacion'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return success_response($almacen, 'Almacén creado exitosamente', 201);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar almacén
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $almacen = Almacen::find($id);

            if (!$almacen) {
                return error_response('Almacén no encontrado', 404);
            }

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:100|unique:almacenes,nombre,' . $id,
                'tipo' => 'sometimes|required|in:bodega,mostrador,otro',
                'ubicacion' => 'nullable|string|max:255',
                'activo' => 'nullable|boolean',
            ]);

            $almacen->update($validated);

            return success_response($almacen, 'Almacén actualizado exitosamente');
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar almacén (soft delete via activo=false)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $almacen = Almacen::find($id);

            if (!$almacen) {
                return error_response('Almacén no encontrado', 404);
            }

            // Verificar si tiene inventario
            if ($almacen->inventarios()->exists()) {
                return error_response('No se puede eliminar el almacén porque tiene productos en inventario', 400);
            }

            $almacen->delete();

            return success_response(null, 'Almacén eliminado exitosamente');
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Obtener lista simplificada de almacenes (para selects)
     */
    public function list(): JsonResponse
    {
        try {
            $almacenes = Almacen::where('activo', true)
                               ->select('id', 'nombre', 'tipo')
                               ->orderBy('nombre')
                               ->get();

            return success_response($almacenes);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}
