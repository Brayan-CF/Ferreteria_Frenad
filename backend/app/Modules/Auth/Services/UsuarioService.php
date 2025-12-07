<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;
use Exception;

class UsuarioService
{
    /**
     * Listar usuarios con paginación
     */
    public function list(array $filters = [])
    {
        $query = Usuario::with('roles');

        // Filtros
        if (!empty($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (!empty($filters['rol'])) {
            $query->conRol($filters['rol']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nombre', 'ilike', "%{$filters['search']}%")
                  ->orWhere('email', 'ilike', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear nuevo usuario
     */
    public function create(array $data): Usuario
    {
        DB::beginTransaction();
        try {
            // Crear usuario
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => $data['password'],
                'activo' => $data['activo'] ?? true,
            ]);

            // Asignar roles
            if (!empty($data['roles'])) {
                $rolesIds = Rol::whereIn('nombre', $data['roles'])->pluck('id');
                $usuario->roles()->attach($rolesIds);
            }

            DB::commit();
            return $usuario->load('roles');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(Usuario $usuario, array $data): Usuario
    {
        DB::beginTransaction();
        try {
            // Actualizar datos básicos
            $updateData = [];
            
            if (isset($data['nombre'])) {
                $updateData['nombre'] = $data['nombre'];
            }
            
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }
            
            if (isset($data['activo'])) {
                $updateData['activo'] = $data['activo'];
            }

            $usuario->update($updateData);

            // Actualizar roles si se envían
            if (isset($data['roles'])) {
                $rolesIds = Rol::whereIn('nombre', $data['roles'])->pluck('id');
                $usuario->roles()->sync($rolesIds);
            }

            DB::commit();
            return $usuario->fresh('roles');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar usuario (soft delete - marcar como inactivo)
     */
    public function delete(Usuario $usuario): bool
    {
        // En lugar de eliminar, desactivamos el usuario
        return $usuario->update(['activo' => false]);
    }

    /**
     * Activar usuario
     */
    public function activate(Usuario $usuario): bool
    {
        return $usuario->update(['activo' => true]);
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function statistics(): array
    {
        return [
            'total' => Usuario::count(),
            'activos' => Usuario::where('activo', true)->count(),
            'inactivos' => Usuario::where('activo', false)->count(),
            'por_rol' => DB::table('usuario_roles')
                ->join('roles', 'usuario_roles.rol_id', '=', 'roles.id')
                ->select('roles.nombre', DB::raw('count(*) as total'))
                ->groupBy('roles.nombre')
                ->get(),
        ];
    }
}