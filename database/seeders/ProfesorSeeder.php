<?php

namespace Database\Seeders;

use App\Models\ClaseGrupal;
use App\Models\Profesor;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class ProfesorSeeder extends Seeder
{
    /**
     * Run the database seeds for Profesores and Clases Grupales.
     */
    public function run(): void
    {
        $sucursales = Sucursal::all();
        $sucursalPrincipal = $sucursales->first();
        $sucursalNorte = $sucursales->skip(1)->first() ?? $sucursalPrincipal;
        $sucursalValencia = $sucursales->skip(2)->first() ?? $sucursalPrincipal;

        $profesoresData = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'Mendoza',
                'especialidad' => 'Spinning & Ciclismo Indoor',
                'telefono' => '+58 412-1002001',
                'correo' => 'carlos.mendoza@fitlife.test',
                'correo_electronico' => 'carlos.mendoza@fitlife.test',
                'activo' => true,
                'estado' => 'activo',
                'clase' => [
                    'nombre' => 'Spinning',
                    'descripcion' => 'Sesión cardiovascular de alta intensidad en bicicleta fija con ritmos dinámicos.',
                    'horario' => 'Lunes a Viernes 07:00 - 07:45',
                    'dia_semana' => 'Lunes',
                    'hora_inicio' => '07:00',
                    'duracion_minutos' => 45,
                    'cupo_maximo' => 20,
                    'sala' => 'Sala de Spinning',
                    'sucursal_id' => $sucursalPrincipal?->id,
                    'activa' => true,
                    'estado' => 'activa',
                ],
            ],
            [
                'nombre' => 'Elena',
                'apellido' => 'Rostova',
                'especialidad' => 'Yoga & Flexibilidad',
                'telefono' => '+58 414-2003002',
                'correo' => 'elena.rostova@fitlife.test',
                'correo_electronico' => 'elena.rostova@fitlife.test',
                'activo' => true,
                'estado' => 'activo',
                'clase' => [
                    'nombre' => 'Yoga',
                    'descripcion' => 'Sesión de vinyasa yoga, equilibrio postural, respiración y elongación profunda.',
                    'horario' => 'Martes y Jueves 08:30 - 09:30',
                    'dia_semana' => 'Martes',
                    'hora_inicio' => '08:30',
                    'duracion_minutos' => 60,
                    'cupo_maximo' => 15,
                    'sala' => 'Sala Zen & Armonía',
                    'sucursal_id' => $sucursalNorte?->id,
                    'activa' => true,
                    'estado' => 'activa',
                ],
            ],
            [
                'nombre' => 'Mariana',
                'apellido' => 'Silva',
                'especialidad' => 'Pilates & Fortalecimiento Core',
                'telefono' => '+58 424-3004003',
                'correo' => 'mariana.silva@fitlife.test',
                'correo_electronico' => 'mariana.silva@fitlife.test',
                'activo' => true,
                'estado' => 'activo',
                'clase' => [
                    'nombre' => 'Pilates',
                    'descripcion' => 'Control corporal, alineación de columna, estabilidad y tonicidad de la zona media.',
                    'horario' => 'Lunes, Miércoles y Viernes 10:00 - 10:50',
                    'dia_semana' => 'Miércoles',
                    'hora_inicio' => '10:00',
                    'duracion_minutos' => 50,
                    'cupo_maximo' => 15,
                    'sala' => 'Studio Pilates Mat',
                    'sucursal_id' => $sucursalValencia?->id,
                    'activa' => true,
                    'estado' => 'activa',
                ],
            ],
            [
                'nombre' => 'Valentina',
                'apellido' => 'Gómez',
                'especialidad' => 'Zumba & Ritmos Latinos',
                'telefono' => '+58 416-4005004',
                'correo' => 'valentina.gomez@fitlife.test',
                'correo_electronico' => 'valentina.gomez@fitlife.test',
                'activo' => true,
                'estado' => 'activo',
                'clase' => [
                    'nombre' => 'Zumba',
                    'descripcion' => 'Acondicionamiento aeróbico al ritmo de música caribeña y pop urbano de alta energía.',
                    'horario' => 'Lunes, Miércoles y Viernes 18:00 - 19:00',
                    'dia_semana' => 'Jueves',
                    'hora_inicio' => '18:00',
                    'duracion_minutos' => 60,
                    'cupo_maximo' => 30,
                    'sala' => 'Sala Principal Fitness',
                    'sucursal_id' => $sucursalPrincipal?->id,
                    'activa' => true,
                    'estado' => 'activa',
                ],
            ],
            [
                'nombre' => 'Rodrigo',
                'apellido' => 'Valero',
                'especialidad' => 'Entrenamiento Funcional & HIIT',
                'telefono' => '+58 412-5006005',
                'correo' => 'rodrigo.valero@fitlife.test',
                'correo_electronico' => 'rodrigo.valero@fitlife.test',
                'activo' => true,
                'estado' => 'activo',
                'clase' => [
                    'nombre' => 'Funcional',
                    'descripcion' => 'Circuitos de fuerza metabólica, kettlebells, cajones pliométricos y battleropes.',
                    'horario' => 'Lunes a Viernes 19:00 - 19:55',
                    'dia_semana' => 'Viernes',
                    'hora_inicio' => '19:00',
                    'duracion_minutos' => 55,
                    'cupo_maximo' => 25,
                    'sala' => 'Box Funcional',
                    'sucursal_id' => $sucursalNorte?->id,
                    'activa' => true,
                    'estado' => 'activa',
                ],
            ],
        ];

        foreach ($profesoresData as $item) {
            $claseInfo = $item['clase'];
            unset($item['clase']);

            $profesor = Profesor::where('correo', $item['correo'])->first();
            if ($profesor) {
                $profesor->update($item);
            } else {
                $profesor = Profesor::create($item);
            }

            ClaseGrupal::updateOrCreate(
                ['nombre' => $claseInfo['nombre']],
                array_merge($claseInfo, ['profesor_id' => $profesor->id])
            );
        }
    }
}
