<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\Usuario;
use Modules\Auth\Models\Rol;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@frenad.com'],
            [
                'nombre' => 'Administrador Sistema',
                'password' => 'admin123', // Se hasheará automáticamente
                'activo' => true,
            ]
        );

        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        if ($rolAdmin && !$admin->roles->contains($rolAdmin->id)) {
            $admin->roles()->attach($rolAdmin->id);
        }

        // Usuario Vendedor
        $vendedor = Usuario::firstOrCreate(
            ['email' => 'vendedor@frenad.com'],
            [
                'nombre' => 'Juan Pérez',
                'password' => 'vendedor123',
                'activo' => true,
            ]
        );

        $rolVendedor = Rol::where('nombre', 'Vendedor')->first();
        if ($rolVendedor && !$vendedor->roles->contains($rolVendedor->id)) {
            $vendedor->roles()->attach($rolVendedor->id);
        }

        $this->command->info('✓ Usuarios creados exitosamente');
    }
}