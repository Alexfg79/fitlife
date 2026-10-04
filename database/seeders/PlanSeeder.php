<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Acceso a sala de máquinas y musculación de Lunes a Viernes de 08:00 a 18:00 hrs. No incluye clases grupales.',
                'precio' => 50.00,
                'duracion_dias' => 30,
                'tipo' => 'individual',
                'horario_restringido' => true,
                'hora_inicio_permitida' => '08:00:00',
                'hora_fin_permitida' => '18:00:00',
                'dias_permitidos' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'acceso_clases' => 'sin_acceso',
                'costo_recargo_clase' => 0.00,
                'permite_invitados' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Completo',
                'descripcion' => 'Acceso libre e ilimitado 24/7 a instalaciones. Clases grupales con recargo obligatorio de $10 por clase.',
                'precio' => 80.00,
                'duracion_dias' => 30,
                'tipo' => 'individual',
                'horario_restringido' => false,
                'hora_inicio_permitida' => null,
                'hora_fin_permitida' => null,
                'dias_permitidos' => null,
                'acceso_clases' => 'con_recargo',
                'costo_recargo_clase' => 10.00,
                'permite_invitados' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Premium',
                'descripcion' => 'Acceso total 24/7 a todas las áreas. Clases grupales 100% incluidas sin costo extra e invitados ilimitados.',
                'precio' => 120.00,
                'duracion_dias' => 30,
                'tipo' => 'individual',
                'horario_restringido' => false,
                'hora_inicio_permitida' => null,
                'hora_fin_permitida' => null,
                'dias_permitidos' => null,
                'acceso_clases' => 'incluido',
                'costo_recargo_clase' => 0.00,
                'permite_invitados' => true,
                'activo' => true,
            ],
        ];

        foreach ($planes as $plan) {
            Plan::updateOrCreate(['nombre' => $plan['nombre']], $plan);
        }
    }
}
