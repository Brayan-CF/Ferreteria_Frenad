<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Models\UnidadMedida;

class UnidadesMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Metro', 'abreviatura' => 'm', 'tipo' => 'longitud'],
            ['nombre' => 'Pieza', 'abreviatura' => 'pza', 'tipo' => 'unidad'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'tipo' => 'peso'],
            ['nombre' => 'Caja', 'abreviatura' => 'cja', 'tipo' => 'unidad'],
            ['nombre' => 'Rollo', 'abreviatura' => 'rollo', 'tipo' => 'unidad'],
            ['nombre' => 'Paquete', 'abreviatura' => 'pqt', 'tipo' => 'unidad'],
            ['nombre' => 'Litro', 'abreviatura' => 'l', 'tipo' => 'volumen'],
            ['nombre' => 'Metro cuadrado', 'abreviatura' => 'm²', 'tipo' => 'area'],
            ['nombre' => 'Bolsa', 'abreviatura' => 'bolsa', 'tipo' => 'unidad'],
            ['nombre' => 'Galón', 'abreviatura' => 'gal', 'tipo' => 'volumen'],
            ['nombre' => 'Tubo', 'abreviatura' => 'tubo', 'tipo' => 'unidad'],
            ['nombre' => 'Plancha', 'abreviatura' => 'plancha', 'tipo' => 'unidad'],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(
                ['nombre' => $unidad['nombre']],
                $unidad
            );
        }

        $this->command->info(' Unidades de medida creadas');
    }
}