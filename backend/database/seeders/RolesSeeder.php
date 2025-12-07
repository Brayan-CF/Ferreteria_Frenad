<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\Rol;

class RolesSeeder extends Seeder
{
    /**
     * Seed de roles del sistema
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total al sistema, gestión de usuarios, cierre de caja',
            ],
            [
                'nombre' => 'Vendedor',
                'descripcion' => 'Realiza ventas, consulta inventario, genera reportes básicos',
            ],
            [
                'nombre' => 'Bodeguero',
                'descripcion' => 'Gestiona compras, recibe mercadería, hace transferencias de inventario',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }

        $this->command->info('✓ Roles creados exitosamente');
    }
}
