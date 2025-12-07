<?php

namespace Modules\Product\Services;

use Modules\Product\Models\Marca;
use Exception;

class MarcaService
{
    /**
     * Listar marcas
     */
    public function list(array $filters = [])
    {
        $query = Marca::withCount('productos');

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (!empty($filters['search'])) {
            $query->where('nombre', 'ilike', "%{$filters['search']}%");
        }

        $query->orderBy('nombre', 'asc');

        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Crear marca
     */
    public function create(array $data): Marca
    {
        return Marca::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'pais_origen' => $data['pais_origen'] ?? null,
            'activo' => $data['activo'] ?? true,
            'creado_por' => auth()->id(),
        ]);
    }

    /**
     * Actualizar marca
     */
    public function update(Marca $marca, array $data): Marca
    {
        $marca->update([
            'nombre' => $data['nombre'] ?? $marca->nombre,
            'descripcion' => $data['descripcion'] ?? $marca->descripcion,
            'pais_origen' => $data['pais_origen'] ?? $marca->pais_origen,
            'activo' => $data['activo'] ?? $marca->activo,
        ]);

        return $marca->fresh();
    }

    /**
     * Eliminar marca
     */
    public function delete(Marca $marca): bool
    {
        if ($marca->productos()->count() > 0) {
            throw new Exception('No se puede eliminar la marca porque tiene productos asociados', 400);
        }

        return $marca->update(['activo' => false]);
    }
}