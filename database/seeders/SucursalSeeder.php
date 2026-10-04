<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sucursales = [
            [
                'id' => 1,
                'nombre' => 'FitLife Las Mercedes',
                'direccion' => 'Av. Principal de Las Mercedes, Torre Financiera, Piso 1',
                'ciudad' => 'Caracas',
                'estado' => 'Miranda',
            ],
            [
                'id' => 2,
                'nombre' => 'FitLife Chacao',
                'direccion' => 'Calle El Parque con Av. Francisco de Miranda, Edif. Centro Plaza',
                'ciudad' => 'Caracas',
                'estado' => 'Miranda',
            ],
            [
                'id' => 3,
                'nombre' => 'FitLife El Viñedo',
                'direccion' => 'Av. Monseñor Adams, C.C. El Viñedo, Nivel C1',
                'ciudad' => 'Valencia',
                'estado' => 'Carabobo',
            ],
        ];

        foreach ($sucursales as $s) {
            Sucursal::updateOrCreate(['id' => $s['id']], $s);
        }
    }
}
