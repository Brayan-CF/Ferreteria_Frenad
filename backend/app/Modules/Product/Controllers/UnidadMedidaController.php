<?php

namespace Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use Modules\Product\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class UnidadMedidaController extends Controller
{
    /**
     * Listar unidades de medida
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = UnidadMedida::query();

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('abreviatura', 'ilike', "%{$search}%");
                });
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            $query->orderBy('nombre');

            $perPage = $request->get('per_page', 15);
            $unidades = $query->paginate($perPage);

            return success_response($unidades);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Mostrar unidad de medida
     */
    public function show(UnidadMedida $unidade): JsonResponse
    {
        try {
            return success_response($unidade);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Crear unidad de medida
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:unidades_medida,nombre',
            'abreviatura' => 'required|string|max:10|unique:unidades_medida,abreviatura',
            'activo' => 'boolean',
        ]);

        try {
            $unidad = UnidadMedida::create([
                'nombre' => $request->nombre,
                'abreviatura' => $request->abreviatura,
                'activo' => $request->activo ?? true,
            ]);

            return success_response($unidad, 'Unidad de medida creada exitosamente', 201);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Actualizar unidad de medida
     */
    public function update(Request $request, UnidadMedida $unidade): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:50|unique:unidades_medida,nombre,' . $unidade->id,
            'abreviatura' => 'sometimes|string|max:10|unique:unidades_medida,abreviatura,' . $unidade->id,
            'activo' => 'boolean',
        ]);

        try {
            $unidade->update($request->only(['nombre', 'abreviatura', 'activo']));

            return success_response($unidade, 'Unidad de medida actualizada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    /**
     * Eliminar unidad de medida
     */
    public function destroy(UnidadMedida $unidade): JsonResponse
    {
        try {
            // Verificar si está en uso
            if ($unidade->productos()->exists()) {
                throw new Exception('No se puede eliminar: hay productos usando esta unidad');
            }

            $unidade->delete();

            return success_response(null, 'Unidad de medida eliminada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}
