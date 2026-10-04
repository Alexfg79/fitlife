<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignarRutinasTodosSociosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $hoy = Carbon::today();
        $ahora = Carbon::now();

        // 1. Obtener entrenadores disponibles
        $entrenadoresIds = User::where('rol_id', 2)->pluck('id')->toArray();
        if (empty($entrenadoresIds)) {
            $primerUsuario = User::first();
            $entrenadoresIds = $primerUsuario ? [$primerUsuario->id] : [null];
        }

        // 2. Obtener catálogo de ejercicios
        $ejercicios = Ejercicio::all(['id', 'grupo_muscular', 'nombre']);
        if ($ejercicios->isEmpty()) {
            $this->command?->error('No hay ejercicios registrados en el sistema.');

            return;
        }

        $ejerciciosPorGrupo = $ejercicios->groupBy('grupo_muscular');
        $todosEjerciciosIds = $ejercicios->pluck('id')->toArray();

        // 3. Catálogo de títulos realistas por nivel
        $titulosRutinas = [
            'Principiante' => [
                'Adaptación Anatómica y Core',
                'Iniciación en Máquinas Guiadas',
                'Fullbody Básico Funcional',
                'Acondicionamiento Físico General',
                'Tonificación y Movilidad Articular',
                'Circuito Metabólico Principiante',
                'Fundamentos de Fuerza y Postura',
            ],
            'Intermedio' => [
                'Hipertrofia Funcional Torso/Pierna',
                'Fuerza y Rendimiento A/B',
                'Push / Pull / Legs Volumen Intermedio',
                'Desarrollo Muscular Estructurado',
                'Definición y Resistencia Muscular',
                'Especialización Miembro Superior e Inferior',
                'Full Body de Alto Volumen',
            ],
            'Avanzado' => [
                'Especialización de Fuerza Máxima 5x5',
                'Sobrecarga Progresiva Alta Intensidad',
                'Potencia y Pliometría Atlética',
                'Periodización Ondulante Hipertrofia Pro',
                'Atleta Élite Rendimiento Total',
                'Acondicionamiento Táctico y Fuerza Estricta',
                'Heavy Duty y Tensión Mecánica',
            ],
        ];

        $diasOpciones = [
            ['Lunes', 'Miércoles', 'Viernes'],
            ['Martes', 'Jueves', 'Sábado'],
            ['Lunes', 'Martes', 'Jueves', 'Viernes'],
            ['Lunes', 'Miércoles', 'Viernes', 'Sábado'],
            ['Lunes', 'Martes', 'Miércoles', 'Viernes'],
        ];

        // 4. Buscar todos los socios que NO tienen una rutina activa
        $sociosSinRutinaActiva = Socio::whereDoesntHave('rutinas', function ($q) {
            $q->where('activa', true);
        })->get(['id', 'nombre', 'apellido']);

        $totalSocios = $sociosSinRutinaActiva->count();
        $this->command?->info("Asignando rutinas activas a {$totalSocios} socios...");

        $ejerciciosBatch = [];

        foreach ($sociosSinRutinaActiva as $socio) {
            $randNivel = rand(1, 100);
            if ($randNivel <= 35) {
                $nivel = 'Principiante';
            } elseif ($randNivel <= 75) {
                $nivel = 'Intermedio';
            } else {
                $nivel = 'Avanzado';
            }

            $objetivos = ['ganar_musculo', 'fuerza', 'perder_peso', 'acondicionamiento'];
            $objetivo = $faker->randomElement($objetivos);
            $titulo = $faker->randomElement($titulosRutinas[$nivel]);
            $entrenadorId = $faker->randomElement($entrenadoresIds);
            $dias = $faker->randomElement($diasOpciones);
            $fechaInicio = $hoy->copy()->subDays(rand(2, 45));

            $rutina = Rutina::create([
                'socio_id' => $socio->id,
                'creada_por' => $entrenadorId,
                'nombre' => "{$titulo} ({$nivel})",
                'descripcion' => "Plan de entrenamiento personalizado enfocado en {$objetivo} con progresión gradual para {$socio->nombre}.",
                'nivel' => strtolower($nivel),
                'nivel_dificultad' => $nivel,
                'estado' => 'Activa',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
                'dias_semana' => $dias,
                'activa' => true,
            ]);

            // Seleccionar 4 a 6 ejercicios balanceados de distintos grupos musculares
            $gruposDisponibles = array_keys($ejerciciosPorGrupo->toArray());
            $cantidadEjercicios = rand(4, 6);
            $gruposElegidos = $faker->randomElements($gruposDisponibles, min(count($gruposDisponibles), $cantidadEjercicios));

            $ejerciciosSeleccionados = [];
            foreach ($gruposElegidos as $grupo) {
                $ej = $ejerciciosPorGrupo[$grupo]->random();
                if (! in_array($ej->id, $ejerciciosSeleccionados)) {
                    $ejerciciosSeleccionados[] = $ej->id;
                }
            }

            while (count($ejerciciosSeleccionados) < $cantidadEjercicios) {
                $randomId = $faker->randomElement($todosEjerciciosIds);
                if (! in_array($randomId, $ejerciciosSeleccionados)) {
                    $ejerciciosSeleccionados[] = $randomId;
                }
            }

            foreach ($ejerciciosSeleccionados as $pos => $ejId) {
                $ejerciciosBatch[] = [
                    'rutina_id' => $rutina->id,
                    'ejercicio_id' => $ejId,
                    'orden' => $pos + 1,
                    'series' => $nivel === 'Avanzado' ? rand(4, 5) : rand(3, 4),
                    'repeticiones' => $objetivo === 'fuerza' ? '5-6' : ($objetivo === 'ganar_musculo' ? '8-12' : ($objetivo === 'perder_peso' ? '12-15' : '10-12')),
                    'tipo_serie' => 'estandar',
                    'descanso_segundos' => $objetivo === 'fuerza' ? 120 : ($nivel === 'Principiante' ? 90 : 60),
                    'rpe' => $nivel === 'Avanzado' ? rand(8, 9) : rand(7, 8),
                    'tempo' => $faker->randomElement(['3-0-1-0', '2-0-2-0', '4-0-1-0', null]),
                    'notas' => $faker->randomElement([
                        'Control excéntrico en fase concéntrica.',
                        'Mantener abdomen activo y columna neutra.',
                        'Enfoque en rango completo de movimiento sin balanceos.',
                        'Pausa isométrica de 1 segundo al final del recorrido.',
                        'Calentar previamente con series de aproximación.',
                    ]),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            if (count($ejerciciosBatch) >= 500) {
                DB::table('ejercicio_rutina')->insert($ejerciciosBatch);
                $ejerciciosBatch = [];
            }
        }

        if (! empty($ejerciciosBatch)) {
            DB::table('ejercicio_rutina')->insert($ejerciciosBatch);
        }

        $this->command?->info('¡Rutinas asignadas con éxito! Ahora todos los socios cuentan con al menos una rutina activa.');
    }
}
