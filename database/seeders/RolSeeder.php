<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'admin',
                'nombre_mostrar' => 'Administrador',
                'descripcion' => 'Control total del gimnasio, finanzas, usuarios y configuraciones.',
            ],
            [
                'nombre' => 'entrenador',
                'nombre_mostrar' => 'Entrenador / Coach',
                'descripcion' => 'Gestión de rutinas, seguimiento de socios y control de asistencias.',
            ],
            [
                'nombre' => 'socio',
                'nombre_mostrar' => 'Socio / Miembro',
                'descripcion' => 'Acceso a su perfil, rutinas asignadas y estado de cuenta.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
