<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\ClaseGrupal;
use App\Models\Ejercicio;
use App\Models\InscripcionClase;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FitLifeMassiveSeeder extends Seeder
{
    /**
     * Run the database seeds with batching and high performance.
     */
    public function run(int $totalSocios = 494): void
    {
        $faker = Faker::create('es_ES');
        $ahora = Carbon::now();
        $hoy = Carbon::today();
        $passwordHash = Hash::make('password');

        $this->command?->info('🏋️ [1/7] Verificando roles, sucursales, profesores, planes estrictos y ejercicios...');

        $rolAdmin = Rol::firstOrCreate(['nombre' => 'admin'], ['descripcion' => 'Administrador Total']);
        $rolEntrenador = Rol::firstOrCreate(['nombre' => 'entrenador'], ['descripcion' => 'Coach y Entrenador']);
        $rolSocio = Rol::firstOrCreate(['nombre' => 'socio'], ['descripcion' => 'Socio / Miembro del Gimnasio']);

        $sucursales = Sucursal::all();
        if ($sucursales->count() < 3) {
            $this->call(SucursalSeeder::class);
            $sucursales = Sucursal::all();
        }
        $sucursalesIds = $sucursales->pluck('id')->toArray();

        // Planes estrictos: Básico ($50), Completo ($80), Premium ($120)
        $planes = Plan::all();
        if ($planes->count() < 3) {
            $this->call(PlanSeeder::class);
            $planes = Plan::all();
        }
        $planBasico = $planes->firstWhere('nombre', 'Plan Básico');
        $planCompleto = $planes->firstWhere('nombre', 'Plan Completo');
        $planPremium = $planes->firstWhere('nombre', 'Plan Premium');

        // Profesores y clases
        $clasesGrupales = ClaseGrupal::all();
        if ($clasesGrupales->count() < 5) {
            $this->call(ProfesorSeeder::class);
            $clasesGrupales = ClaseGrupal::all();
        }

        // Catálogo de ejercicios
        if (Ejercicio::count() < 30) {
            $this->call(EjercicioSeeder::class);
        }
        $ejerciciosIds = Ejercicio::pluck('id')->toArray();

        // 1. Administrador y Entrenadores con asignación de sucursal
        $this->command?->info('👤 [2/7] Asegurando Administrador y Coaches con sedes multi-sucursal...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@fitlife.test'],
            [
                'name' => 'Alexander Director',
                'password' => $passwordHash,
                'rol_id' => $rolAdmin->id,
                'sucursal_id' => 1,
            ]
        );

        $entrenadores = [];
        $coachPrincipal = User::firstOrCreate(
            ['email' => 'coach@fitlife.test'],
            [
                'name' => 'Marcos Silva (Head Coach)',
                'password' => $passwordHash,
                'rol_id' => $rolEntrenador->id,
                'sucursal_id' => 1,
            ]
        );
        $entrenadores[] = $coachPrincipal;

        $nombresEntrenadores = [
            'Laura Hernández (Coach Funcional)',
            'Roberto Vargas (Coach Hipertrofia)',
            'Sofia Navarro (Coach Cross & HIIT)',
            'Javier Morales (Coach Acondicionamiento)',
        ];

        foreach ($nombresEntrenadores as $i => $nombreCoach) {
            $emailCoach = 'coach'.($i + 1).'@fitlife.test';
            $sucursalCoach = $sucursalesIds[($i + 1) % count($sucursalesIds)];
            $coach = User::firstOrCreate(
                ['email' => $emailCoach],
                [
                    'name' => $nombreCoach,
                    'password' => $passwordHash,
                    'rol_id' => $rolEntrenador->id,
                    'sucursal_id' => $sucursalCoach,
                ]
            );
            $entrenadores[] = $coach;
        }

        // 2. Creación Masiva de Socios (500 socios distribuidos equitativamente en 3 sucursales)
        $this->command?->info("👥 [3/7] Generando {$totalSocios} Socios distribuidos en las 3 sucursales...");

        $sociosCreados = [];
        $dnisUsados = Socio::pluck('dni')->flip()->toArray();

        // Socio demo garantizado
        $usuarioSocioDemo = User::firstOrCreate(
            ['email' => 'socio@fitlife.test'],
            [
                'name' => 'Carlos Mendoza (Socio FitLife)',
                'password' => $passwordHash,
                'rol_id' => $rolSocio->id,
                'sucursal_id' => 1,
            ]
        );

        $socioDemo = Socio::firstOrCreate(
            ['correo' => 'socio@fitlife.test'],
            [
                'user_id' => $usuarioSocioDemo->id,
                'codigo' => 'SOC-00001',
                'nombre' => 'Carlos',
                'apellido' => 'Mendoza',
                'dni' => '10000001',
                'telefono' => '+58 412 1234567',
                'direccion' => 'Av. Francisco de Miranda, Edif. FitLife Pro, Caracas',
                'fecha_nacimiento' => '1995-05-15',
                'estado' => 'activo',
                'observaciones_medicas' => 'Apto para todo tipo de entrenamiento deportivo.',
            ]
        );
        $sociosCreados[] = $socioDemo;
        $dnisUsados['10000001'] = true;

        $ultimoId = (int) (Socio::max('id') ?? 0);
        $offset = 1000 + $ultimoId;

        $bar = $this->command?->getOutput()->createProgressBar($totalSocios);
        $bar?->start();

        for ($i = 1; $i <= $totalSocios; $i++) {
            $numCorrelativo = $offset + $i;

            do {
                $dni = (string) $faker->numberBetween(10000000, 99999999);
            } while (isset($dnisUsados[$dni]));
            $dnisUsados[$dni] = true;

            $nombre = $faker->firstName();
            $apellido = $faker->lastName().' '.$faker->lastName();
            $correo = Str::slug($nombre.'.'.$apellido.'.'.$numCorrelativo).'@gmail.com';
            $codigo = 'SOC-'.str_pad((string) $numCorrelativo, 5, '0', STR_PAD_LEFT);

            // Distribución entre las 3 sucursales
            $sucursalId = $sucursalesIds[$i % count($sucursalesIds)];

            // Estado de negocio
            // 80% activo, 10% moroso, 10% inactivo
            $rndEstado = $faker->numberBetween(1, 100);
            $estadoSocio = $rndEstado <= 80 ? 'activo' : ($rndEstado <= 90 ? 'moroso' : 'inactivo');

            $usuario = User::create([
                'name' => $nombre.' '.$apellido,
                'email' => $correo,
                'password' => $passwordHash,
                'rol_id' => $rolSocio->id,
                'sucursal_id' => $sucursalId,
            ]);

            $socio = Socio::create([
                'user_id' => $usuario->id,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'correo' => $correo,
                'telefono' => '+58 4'.$faker->randomElement(['12', '14', '24', '16']).' '.$faker->numberBetween(1000000, 9999999),
                'direccion' => $faker->streetAddress().', '.$faker->city(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-16 years')->format('Y-m-d'),
                'estado' => $estadoSocio,
                'observaciones_medicas' => $faker->optional(0.15)->sentence(),
            ]);

            $sociosCreados[] = $socio;
            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();

        // 3. Asignación de Membresías y Cuotas (Planes Estrictos: Básico $50, Completo $80, Premium $120)
        $this->command?->info('💳 [4/7] Asignando 500 Membresías con los 3 planes estrictos y cuotas morosas controladas...');

        $membresiasCreadas = [];
        $metodosPago = ['Efectivo', 'Transferencia', 'Tarjeta', 'Pago_Movil'];

        // Repartición de planes: 35% Básico ($50), 45% Completo ($80), 20% Premium ($120)
        foreach ($sociosCreados as $index => $socio) {
            $rndPlan = $faker->numberBetween(1, 100);
            if ($rndPlan <= 35) {
                $plan = $planBasico ?? $planes->first();
            } elseif ($rndPlan <= 80) {
                $plan = $planCompleto ?? $planes->first();
            } else {
                $plan = $planPremium ?? $planes->last();
            }

            $duracion = $plan->duracion_dias ?: 30;
            $sucursalSocio = $socio->user?->sucursal_id ?? 1;

            // Configurar cuotas y estados para auditoría de morosidad
            // Socio #2 a #25: Morosos con 1 cuota pendiente (alerta)
            // Socio #26 a #50: Morosos bloqueados con 2 o más cuotas pendientes
            $cuotasTotales = 1;
            $cuotasPagadas = 1;
            $saldoPendiente = 0.00;

            if ($index >= 2 && $index <= 25) {
                // 1 cuota vencida
                $estado = 'activa';
                $cuotasTotales = 2;
                $cuotasPagadas = 1;
                $saldoPendiente = $plan->precio;
                $fechaInicio = $hoy->copy()->subDays(25);
                $fechaFin = $fechaInicio->copy()->addDays($duracion);
            } elseif ($index >= 26 && $index <= 50) {
                // 2+ cuotas vencidas (BLOQUEADO en torniquete)
                $estado = 'vencida';
                $cuotasTotales = 3;
                $cuotasPagadas = 1;
                $saldoPendiente = $plan->precio * 2;
                $fechaFin = $hoy->copy()->subDays(65);
                $fechaInicio = $fechaFin->copy()->subDays($duracion);
                $socio->update(['estado' => 'moroso']);
            } elseif ($socio->estado === 'inactivo') {
                $estado = 'vencida';
                $fechaFin = $hoy->copy()->subDays(15);
                $fechaInicio = $fechaFin->copy()->subDays($duracion);
            } else {
                $estado = 'activa';
                $diasTranscurridos = $faker->numberBetween(1, 20);
                $fechaInicio = $hoy->copy()->subDays($diasTranscurridos);
                $fechaFin = $fechaInicio->copy()->addDays($duracion);
            }

            $membresia = Membresia::create([
                'socio_id' => $socio->id,
                'plan_id' => $plan->id,
                'sucursal_id' => $sucursalSocio,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'precio_pagado' => $plan->precio,
                'cuotas_totales' => $cuotasTotales,
                'cuotas_pagadas' => $cuotasPagadas,
                'saldo_pendiente' => $saldoPendiente,
                'estado' => $estado,
                'metodo_pago' => strtolower($faker->randomElement($metodosPago)),
                'notas' => 'Suscripción '.$plan->nombre.' en FitLife Sucursal #'.$sucursalSocio,
            ]);

            $membresiasCreadas[] = $membresia;
        }

        // 4. Pagos Transaccionales
        $this->command?->info('💰 [5/7] Generando ~600 Pagos transaccionales vinculados...');
        $pagosBatch = [];
        foreach ($membresiasCreadas as $membresia) {
            if ($membresia->cuotas_pagadas > 0) {
                $pagosBatch[] = [
                    'membresia_id' => $membresia->id,
                    'fecha_pago' => $membresia->fecha_inicio->copy()->setTime($faker->numberBetween(8, 19), $faker->numberBetween(0, 59)),
                    'monto' => $membresia->precio_pagado,
                    'metodo_pago' => ucfirst($membresia->metodo_pago),
                    'referencia' => 'REF-'.strtoupper(Str::random(10)),
                    'comprobante' => 'recibo_'.$membresia->id.'_'.$faker->numberBetween(1000, 9999).'.pdf',
                    'notas' => 'Pago de cuota validado en sucursal.',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        foreach (array_chunk($pagosBatch, 200) as $chunk) {
            DB::table('pagos')->insert($chunk);
        }

        // 5. Asistencias con Marcas de Entrada, Salida y Tiempos de Permanencia
        $this->command?->info('🚪 [6/7] Generando ~2000 Marcajes de Asistencia con marcas de entrada/salida y permanencia...');

        $asistenciasBatch = [];
        $totalAsistencias = 2000;

        for ($k = 0; $k < $totalAsistencias; $k++) {
            $socio = $faker->randomElement($sociosCreados);
            $diasAtras = $faker->numberBetween(0, 60);
            $sucursalId = $socio->user?->sucursal_id ?? 1;

            // Hora de entrada
            if ($faker->boolean(60)) {
                // Horas pico
                $horaEntrada = $faker->boolean(50) ? $faker->numberBetween(7, 9) : $faker->numberBetween(18, 20);
            } else {
                // Horas valle
                $horaEntrada = $faker->numberBetween(10, 16);
            }

            $minutoEntrada = $faker->numberBetween(0, 59);
            $fechaEntrada = $hoy->copy()->subDays($diasAtras)->setTime($horaEntrada, $minutoEntrada, 0);

            // Verificar si el socio es moroso bloqueado (>2 cuotas)
            $esMorosoBloqueado = $socio->cuotasVencidasCount() >= 2 || $socio->estado === 'moroso';

            if ($esMorosoBloqueado) {
                $estadoAcceso = 'denegado_moroso';
                $mensajeAcceso = 'Acceso Denegado: Su cuenta presenta 2 o más cuotas vencidas. Por favor acérquese a recepción para regularizar su pago.';
                $fechaSalida = null;
                $permanenciaMinutos = null;
            } elseif ($socio->estado === 'inactivo') {
                $estadoAcceso = 'denegado_inactivo';
                $mensajeAcceso = 'Acceso Denegado. Socio inactivo o suspendido.';
                $fechaSalida = null;
                $permanenciaMinutos = null;
            } else {
                $estadoAcceso = 'permitido';
                $mensajeAcceso = "Acceso Autorizado. Bienvenido/a {$socio->nombre}. Torniquete {$sucursalId}A.";

                // Si es hoy y es uno de los primeros 15 marcajes de hoy, dejar asistencia ABIERTA (socio aún dentro)
                if ($diasAtras === 0 && $k < 15) {
                    $fechaSalida = null;
                    $permanenciaMinutos = null;
                } else {
                    $permanenciaMinutos = $faker->numberBetween(45, 115);
                    $fechaSalida = $fechaEntrada->copy()->addMinutes($permanenciaMinutos);
                }
            }

            $asistenciasBatch[] = [
                'socio_id' => $socio->id,
                'sucursal_id' => $sucursalId,
                'registrado_por' => $entrenadores[0]->id,
                'fecha_hora_entrada' => $fechaEntrada,
                'fecha_hora_salida' => $fechaSalida,
                'tiempo_permanencia_minutos' => $permanenciaMinutos,
                'fecha_hora' => $fechaEntrada,
                'metodo' => $faker->randomElement(['qr', 'codigo', 'biometrico']),
                'estado_acceso' => $estadoAcceso,
                'mensaje_acceso' => $mensajeAcceso,
                'created_at' => $fechaEntrada,
                'updated_at' => $fechaSalida ?? $fechaEntrada,
            ];
        }

        foreach (array_chunk($asistenciasBatch, 300) as $chunk) {
            DB::table('asistencias')->insert($chunk);
        }

        // 6. Rutinas con Historial Acumulativo y Niveles de Dificultad
        $this->command?->info('📋 [7/7] Creando Rutinas con historial y niveles de dificultad (Principiante, Intermedio, Avanzado)...');

        $titulosRutinas = [
            'Principiante' => [
                'Adaptación Anatómica y Core',
                'Iniciación en Máquinas Guiadas',
                'Fullbody Básico Funcional',
            ],
            'Intermedio' => [
                'Hipertrofia Funcional Torso/Pierna',
                'Fuerza y Rendimiento A/B',
                'Push / Pull / Legs Volumen Intermedio',
            ],
            'Avanzado' => [
                'Especialización de Fuerza Máxima 5x5',
                'Sobrecarga Progresiva Alta Intensidad',
                'Potencia y Pliometría Atlética',
            ],
        ];

        for ($r = 0; $r < 180; $r++) {
            $socio = $faker->randomElement($sociosCreados);
            $entrenador = $faker->randomElement($entrenadores);
            $nivel = $faker->randomElement(['Principiante', 'Intermedio', 'Avanzado']);
            $titulo = $faker->randomElement($titulosRutinas[$nivel]);

            // Un 30% de socios tienen una rutina histórica y una activa
            $esHistorica = $faker->boolean(30);
            $estadoRutina = $esHistorica ? 'Histórica/Finalizada' : 'Activa';
            $fechaInicioRutina = $esHistorica ? $hoy->copy()->subMonths(3) : $hoy->copy()->subDays($faker->numberBetween(5, 30));
            $fechaFinRutina = $esHistorica ? $hoy->copy()->subDays(31) : null;

            $rutina = Rutina::create([
                'socio_id' => $socio->id,
                'creada_por' => $entrenador->id,
                'nombre' => $titulo.' ('.$nivel.') #'.($r + 1),
                'descripcion' => "Plan de entrenamiento enfocado en nivel {$nivel} con progresión planificada.",
                'nivel' => strtolower($nivel),
                'nivel_dificultad' => $nivel,
                'estado' => $estadoRutina,
                'fecha_inicio' => $fechaInicioRutina,
                'fecha_fin' => $fechaFinRutina,
                'dias_semana' => ['Lunes', 'Miércoles', 'Viernes'],
                'activa' => ! $esHistorica,
            ]);

            $ejerciciosSeleccionados = $faker->randomElements($ejerciciosIds, $faker->numberBetween(4, 6));
            foreach ($ejerciciosSeleccionados as $pos => $ejId) {
                DB::table('ejercicio_rutina')->insert([
                    'rutina_id' => $rutina->id,
                    'ejercicio_id' => $ejId,
                    'orden' => $pos + 1,
                    'series' => $faker->numberBetween(3, 4),
                    'repeticiones' => $faker->randomElement(['8-10', '10-12', '12-15']),
                    'descanso_segundos' => $faker->randomElement([60, 90, 120]),
                    'notas' => 'Control excéntrico en fase concéntrica.',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }

        // 7. Inscripciones a Clases Grupales con Políticas de Recargo
        $this->command?->info('🧘 [EXTRA] Generando inscripciones a clases grupales respetando reglas de planes...');
        $inscripcionesBatch = [];
        $fechasClases = [
            $hoy->toDateString(),
            $hoy->copy()->addDays(1)->toDateString(),
            $hoy->copy()->addDays(2)->toDateString(),
            $hoy->copy()->subDays(1)->toDateString(),
            $hoy->copy()->subDays(2)->toDateString(),
        ];

        // Tomar socios activos con membresía
        $sociosConMembresia = Socio::with('membresiaActiva.plan')->where('estado', 'activo')->get();
        $inscripcionesUnicas = [];

        foreach ($sociosConMembresia->take(120) as $socioItem) {
            $planSocio = $socioItem->membresiaActiva?->plan;
            if (! $planSocio || $planSocio->nombre === 'Plan Básico') {
                // Plan Básico NO puede inscribirse a clases grupales
                continue;
            }

            $clase = $clasesGrupales->random();
            $fechaClase = $faker->randomElement($fechasClases);
            $key = $socioItem->id.'-'.$clase->id.'-'.$fechaClase;

            if (isset($inscripcionesUnicas[$key])) {
                continue;
            }
            $inscripcionesUnicas[$key] = true;

            // Recargo: Completo = $10.00 | Premium = $0.00
            $recargo = ($planSocio->nombre === 'Plan Completo') ? 10.00 : 0.00;

            $inscripcionesBatch[] = [
                'socio_id' => $socioItem->id,
                'clase_grupal_id' => $clase->id,
                'fecha_clase' => $fechaClase,
                'monto_recargo_pagado' => $recargo,
                'estado' => 'confirmada',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        foreach (array_chunk($inscripcionesBatch, 100) as $chunk) {
            DB::table('inscripciones_clases')->insert($chunk);
        }

        $this->command?->info('✅ ¡Poblamiento y refacciones obligatorias completadas con éxito!');
        $this->command?->table(
            ['Métrica Auditada', 'Cantidad'],
            [
                ['Sucursales Activas', Sucursal::count()],
                ['Profesores Titulares', Profesor::count()],
                ['Clases Grupales', ClaseGrupal::count()],
                ['Planes Estrictos ($50, $80, $120)', Plan::count()],
                ['Socios Registrados', Socio::count()],
                ['Membresías Multi-sucursal', Membresia::count()],
                ['Marcajes de Asistencia (con Entrada/Salida)', Asistencia::count()],
                ['Asistencias Abiertas Hoy', Asistencia::hoy()->abiertas()->count()],
                ['Inscripciones a Clases Grupales', InscripcionClase::count()],
                ['Recargos Cobrados por Clases ($10/clase)', '$'.number_format(InscripcionClase::sum('monto_recargo_pagado'), 2)],
                ['Rutinas Diseñadas', Rutina::count()],
            ]
        );
    }
}
