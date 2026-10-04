<?php

namespace Database\Seeders;

use App\Models\ClaseGrupal;
use App\Models\Ejercicio;
use App\Models\Plan;
use App\Models\PlantillaRutina;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FitLifeExpansionSeeder extends Seeder
{
    /**
     * Run the database seeds for expansion features.
     */
    public function run(): void
    {
        $ahora = Carbon::now();
        $coach = User::where('email', 'coach@fitlife.test')->first() ?? User::where('rol_id', 2)->first();
        $coachId = $coach?->id;

        // 1. Clases Grupales
        $clases = [
            [
                'nombre' => 'Spinning RPM Alta Intensidad',
                'descripcion' => 'Sesión cardiovascular sobre bicicleta estática con intervalos de cadencia y potencia musical.',
                'entrenador_id' => $coachId,
                'horario' => 'Lunes a Viernes 07:00 - 08:00',
                'cupo_maximo' => 25,
                'sala' => 'Sala Cardio 1',
                'activa' => true,
            ],
            [
                'nombre' => 'CrossFit WOD & Power',
                'descripcion' => 'Entrenamiento de movimientos funcionales constantemente variados ejecutados a alta intensidad.',
                'entrenador_id' => $coachId,
                'horario' => 'Lunes a Viernes 18:00 - 19:30',
                'cupo_maximo' => 20,
                'sala' => 'Box Funcional',
                'activa' => true,
            ],
            [
                'nombre' => 'Power Yoga & Movilidad',
                'descripcion' => 'Fluidez de asanas dinámicas orientadas a la recuperación muscular, core y flexibilidad articular.',
                'entrenador_id' => $coachId,
                'horario' => 'Martes y Jueves 08:30 - 09:30',
                'cupo_maximo' => 15,
                'sala' => 'Sala Zen Studio',
                'activa' => true,
            ],
            [
                'nombre' => 'Boxeo & Acondicionamiento',
                'descripcion' => 'Fundamentos de golpeo al saco, sombra y trabajo de agilidad de pies combinados con saltos de comba.',
                'entrenador_id' => $coachId,
                'horario' => 'Lunes, Miércoles y Viernes 19:00 - 20:00',
                'cupo_maximo' => 18,
                'sala' => 'Ring & Bags Zone',
                'activa' => true,
            ],
        ];

        foreach ($clases as $c) {
            ClaseGrupal::firstOrCreate(['nombre' => $c['nombre']], $c);
        }

        // 2. Planes Especiales con Sesiones y Variedad
        $planesNuevos = [
            [
                'nombre' => 'Paquete 10 Pases / Sesiones',
                'descripcion' => 'Pases individuales con consumo por entrada, ideal para atletas con horarios rotativos.',
                'precio' => 45.00,
                'duracion_dias' => 60,
                'tipo' => 'sesiones',
                'sesiones_totales' => 10,
                'beneficios' => ['Acceso a sala de pesas', 'Sin matrícula fija', 'Vigencia extendida por 60 días'],
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Familiar Dúo Pro',
                'descripcion' => 'Membresía preferencial para dos miembros de la familia con acceso completo a sala y clases.',
                'precio' => 160.00,
                'duracion_dias' => 90,
                'tipo' => 'familiar',
                'sesiones_totales' => null,
                'beneficios' => ['2 carnets de acceso', 'Clases grupales incluidas', 'Descuento en tienda FitLife'],
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Corporativo Empresa',
                'descripcion' => 'Convenio corporativo para colaboradores empresariales con facturación mensual consolidada.',
                'precio' => 290.00,
                'duracion_dias' => 365,
                'tipo' => 'corporativo',
                'sesiones_totales' => null,
                'beneficios' => ['Acceso ilimitado 365 días', 'Lockers VIP', 'Pase de invitado 1 vez por mes'],
                'activo' => true,
            ],
        ];

        foreach ($planesNuevos as $p) {
            Plan::firstOrCreate(['nombre' => $p['nombre']], $p);
        }

        // 3. Plantillas de Rutinas Automatizadas
        $ejerciciosIds = Ejercicio::pluck('id')->toArray();
        if (count($ejerciciosIds) >= 4) {
            $plantillas = [
                [
                    'nombre' => 'Fuerza Máxima (5x5 Power)',
                    'descripcion' => 'Enfoque en ejercicios multiarticulares pesados para el desarrollo óptimo del sistema nervioso y fuerza pura.',
                    'objetivo' => 'fuerza',
                    'nivel' => 'avanzado',
                    'dias_semana' => ['Lunes', 'Miércoles', 'Viernes'],
                    'creada_por' => $coachId,
                    'activa' => true,
                ],
                [
                    'nombre' => 'Hipertrofia Funcional Torso / Pierna',
                    'descripcion' => 'Distribución moderna de 4 días enfocada en volumen de trabajo y tiempo bajo tensión.',
                    'objetivo' => 'ganar_musculo',
                    'nivel' => 'intermedio',
                    'dias_semana' => ['Lunes', 'Martes', 'Jueves', 'Viernes'],
                    'creada_por' => $coachId,
                    'activa' => true,
                ],
                [
                    'nombre' => 'Metabolic Burn & HIIT Quema Grasa',
                    'descripcion' => 'Circuitos con descansos cortos orientados al gasto calórico y preservación de masa magra.',
                    'objetivo' => 'perder_peso',
                    'nivel' => 'intermedio',
                    'dias_semana' => ['Lunes', 'Miércoles', 'Viernes'],
                    'creada_por' => $coachId,
                    'activa' => true,
                ],
                [
                    'nombre' => 'Iniciación Integral al Gimnasio (Fullbody)',
                    'descripcion' => 'Acondicionamiento general para aprendizaje de técnica de los patrones básicos de movimiento.',
                    'objetivo' => 'acondicionamiento',
                    'nivel' => 'principiante',
                    'dias_semana' => ['Lunes', 'Miércoles', 'Viernes'],
                    'creada_por' => $coachId,
                    'activa' => true,
                ],
            ];

            foreach ($plantillas as $plData) {
                $plantilla = PlantillaRutina::firstOrCreate(['nombre' => $plData['nombre']], $plData);

                // Asignar 4 ejercicios a la plantilla
                $ejSeleccionados = array_slice($ejerciciosIds, 0, 4);
                foreach ($ejSeleccionados as $i => $ejId) {
                    DB::table('ejercicio_plantilla')->updateOrInsert(
                        ['plantilla_id' => $plantilla->id, 'ejercicio_id' => $ejId],
                        [
                            'orden' => $i + 1,
                            'series' => 4,
                            'repeticiones' => $plData['objetivo'] === 'fuerza' ? '5' : '10-12',
                            'tipo_serie' => $plData['objetivo'] === 'fuerza' ? 'piramidal' : 'estandar',
                            'descanso_segundos' => $plData['objetivo'] === 'fuerza' ? 120 : 60,
                            'rpe' => 8,
                            'tempo' => '3-0-1-0',
                            'notas' => 'Control articular y fase excéntrica lenta.',
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ]
                    );
                }
            }
        }

        $this->command?->info('✅ Expansión sembrada con éxito: Clases grupales, Planes por sesiones y Plantillas de rutinas.');
    }
}
