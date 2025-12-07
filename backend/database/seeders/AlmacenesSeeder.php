<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Almacen;

class AlmacenesSeeder extends Seeder
{
    public function run(): void
    {
        $almacenes = [
            [
                'nombre' => 'Bodega Principal',
                'tipo' => 'bodega',
                'ubicacion' => 'Área trasera - Materiales pesados (planchas, fierros, tubos soldadura)',
                'activo' => true,
            ],
            [
                'nombre' => 'Mostrador Venta',
                'tipo' => 'mostrador',
                'ubicacion' => 'Área frontal - Estantes con productos de alta rotación',
                'activo' => true,
            ],
        ];

        foreach ($almacenes as $almacen) {
            Almacen::firstOrCreate(
                ['nombre' => $almacen['nombre']],
                $almacen
            );
        }

        $this->command->info('✓ Almacenes creados exitosamente');
    }
}