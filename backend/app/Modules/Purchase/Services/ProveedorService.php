<?php

namespace Modules\Purchase\Services;

use Modules\Purchase\Models\Proveedor;
use Exception;

class ProveedorService
{
    /**
     * Listar proveedores con filtros
     */
    public function list(array $filters = [])
    {
        $query = Proveedor::query();

        // Filtros
        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (!empty($filters['search'])) {
            $query->buscar($filters['search']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'razon_social';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear proveedor
     */
    public function create(array $data): Proveedor
    {
        return Proveedor::create([
            'razon_social' => $data['razon_social'],
            'nit' => $data['nit'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'email' => $data['email'] ?? null,
            'nombre_contacto' => $data['nombre_contacto'] ?? null,
            'activo' => $data['activo'] ?? true,
            'creado_por' => auth()->id(),
        ]);
    }

    /**
     * Actualizar proveedor
     */
    public function update(Proveedor $proveedor, array $data): Proveedor
    {
        $proveedor->update([
            'razon_social' => $data['razon_social'] ?? $proveedor->razon_social,
            'nit' => $data['nit'] ?? $proveedor->nit,
            'telefono' => $data['telefono'] ?? $proveedor->telefono,
            'direccion' => $data['direccion'] ?? $proveedor->direccion,
            'email' => $data['email'] ?? $proveedor->email,
            'nombre_contacto' => $data['nombre_contacto'] ?? $proveedor->nombre_contacto,
            'activo' => $data['activo'] ?? $proveedor->activo,
            'actualizado_por' => auth()->id(),
        ]);

        return $proveedor->fresh();
    }

    /**
     * Eliminar proveedor (desactivar)
     */
    public function delete(Proveedor $proveedor): bool
    {
        // Verificar que no tenga compras asociadas
        if ($proveedor->compras()->count() > 0) {
            throw new Exception('No se puede desactivar el proveedor porque tiene compras registradas', 400);
        }

        return $proveedor->update(['activo' => false]);
    }

    /**
     * Activar proveedor
     */
    public function activate(Proveedor $proveedor): bool
    {
        return $proveedor->update(['activo' => true]);
    }

    /**
     * Estadísticas de proveedores
     */
    public function statistics(): array
    {
        return [
            'total' => Proveedor::count(),
            'activos' => Proveedor::where('activo', true)->count(),
            'con_ordenes_pendientes' => Proveedor::whereHas('ordenesCompra', function ($q) {
                $q->where('estado', 'pendiente');
            })->count(),
        ];
    }
}