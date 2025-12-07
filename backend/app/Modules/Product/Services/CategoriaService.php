<?php

namespace Modules\Product\Services;

use Modules\Product\Models\Categoria;
use Exception;

class CategoriaService
{
    /**
     * Listar categorías
     */
    public function list(array $filters = [])
    {
        $query = Categoria::withCount('productos');

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
     * Crear categoría
     */
    public function create(array $data): Categoria
    {
        return Categoria::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => $data['activo'] ?? true,
            'creado_por' => auth()->id(),
        ]);
    }

    /**
     * Actualizar categoría
     */
    public function update(Categoria $categoria, array $data): Categoria
    {
        $categoria->update([
            'nombre' => $data['nombre'] ?? $categoria->nombre,
            'descripcion' => $data['descripcion'] ?? $categoria->descripcion,
            'activo' => $data['activo'] ?? $categoria->activo,
            'actualizado_por' => auth()->id(),
        ]);

        return $categoria->fresh();
    }

    /**
     * Eliminar categoría
     */
    public function delete(Categoria $categoria): bool
    {
        if ($categoria->productos()->count() > 0) {
            throw new Exception('No se puede eliminar la categoría porque tiene productos asociados', 400);
        }

        return $categoria->update(['activo' => false]);
    }
}