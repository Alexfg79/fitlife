<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\ClaseGrupal;
use App\Models\Ejercicio;
use App\Models\Membresia;
use App\Models\Plan;
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

class Cargar500SociosMasSeeder extends Seeder
{
    /**
     * Genera 500 nuevos socios adicionales con membresías, asistencias y rutinas.
     */
    public function run(int $cantidadSocios = 500): void
    {
        $faker = Faker::create('es_ES');
        $ahora = Carbon::now();
        $hoy = Carbon::today();
        $passwordHash = Hash::make('password');

        $this->command?->info("🚀 Iniciando generación de {$cantidadSocios} nuevos socios...");

        // 1. Obtener dependencias necesarias
        $rolSocio = Rol::firstOrCreate(['nombre' => 'socio'], ['descripcion' => 'Socio / Miembro del Gimnasio']);
        $entrenadores = User::whereHas('rol', fn ($q) => $q->where('nombre', 'entrenador'))->get();
        if ($entrenadores->isEmpty()) {
            $entrenadores = User::take(3)->get();
        }

        $sucursales = Sucursal::all();
        $sucursalesIds = $sucursales->pluck('id')->toArray();
        if (empty($sucursalesIds)) {
            $sucursalesIds = [1];
        }

        $planes = Plan::all();
        $planBasico = $planes->firstWhere('nombre', 'Plan Básico') ?? $planes->first();
        $planCompleto = $planes->firstWhere('nombre', 'Plan Completo') ?? $planes->first();
        $planPremium = $planes->firstWhere('nombre', 'Plan Premium') ?? $planes->last();

        $ejerciciosIds = Ejercicio::pluck('id')->toArray();
        $clasesGrupales = ClaseGrupal::all();

        // Evitar colisiones de DNI, correo y código
        $dnisUsados = Socio::pluck('dni')->flip()->toArray();
        $correosUsados = User::pluck('email')->flip()->toArray();

        $ultimoId = (int) (Socio::max('id') ?? 0);
        $offset = 2000 + $ultimoId;

        $sociosNuevos = [];
        $membresiasBatch = [];
        $metodosPago = ['efectivo', 'transferencia', 'tarjeta', 'pago_movil'];

        $this->command?->info("👥 [1/4] Creando {$cantidadSocios} Socios y Cuentas de Usuario...");
        $bar = $this->command?->getOutput()->createProgressBar($cantidadSocios);
        $bar?->start();

        for ($i = 1; $i <= $cantidadSocios; $i++) {
            $numCorrelativo = $offset + $i;

            do {
                $dni = (string) $faker->numberBetween(10000000, 99999999);
            } while (isset($dnisUsados[$dni]));
            $dnisUsados[$dni] = true;

            $nombre = $faker->firstName();
            $apellido = $faker->lastName().' '.$faker->lastName();
            $codigo = 'SOC-'.str_pad((string) $numCorrelativo, 5, '0', STR_PAD_LEFT);

            $baseEmail = Str::slug($nombre.'.'.$apellido.'.'.$numCorrelativo);
            $correo = $baseEmail.'@gmail.com';
            if (isset($correosUsados[$correo])) {
                $correo = $baseEmail.'_'.Str::random(4).'@gmail.com';
            }
            $correosUsados[$correo] = true;

            $sucursalId = $sucursalesIds[$i % count($sucursalesIds)];

            // 85% activos, 8% morosos, 7% inactivos
            $rnd = $faker->numberBetween(1, 100);
            $estadoSocio = $rnd <= 85 ? 'activo' : ($rnd <= 93 ? 'moroso' : 'inactivo');

            $usuario = User::create([
                'name' => "{$nombre} {$apellido}",
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
                'fecha_nacimiento' => $faker->dateTimeBetween('-55 years', '-16 years')->format('Y-m-d'),
                'estado' => $estadoSocio,
                'observaciones_medicas' => $faker->optional(0.12)->sentence(),
            ]);

            $sociosNuevos[] = $socio;

            // Asignar Membresía y Cuotas
            $rndPlan = $faker->numberBetween(1, 100);
            $plan = $rndPlan <= 35 ? $planBasico : ($rndPlan <= 75 ? $planCompleto : $planPremium);
            $duracion = $plan->duracion_dias ?: 30;

            if ($estadoSocio === 'moroso') {
                $estadoMembresia = 'vencida';
                $cuotasTotales = 2;
                $cuotasPagadas = 1;
                $saldoPendiente = $plan->precio;
                $fechaFin = $hoy->copy()->subDays($faker->numberBetween(5, 45));
                $fechaInicio = $fechaFin->copy()->subDays($duracion);
            } elseif ($estadoSocio === 'inactivo') {
                $estadoMembresia = 'vencida';
                $cuotasTotales = 1;
                $cuotasPagadas = 1;
                $saldoPendiente = 0;
                $fechaFin = $hoy->copy()->subDays($faker->numberBetween(30, 90));
                $fechaInicio = $fechaFin->copy()->subDays($duracion);
            } else {
                $estadoMembresia = 'activa';
                $cuotasTotales = 1;
                $cuotasPagadas = 1;
                $saldoPendiente = 0;
                $diasTranscurridos = $faker->numberBetween(1, 25);
                $fechaInicio = $hoy->copy()->subDays($diasTranscurridos);
                $fechaFin = $fechaInicio->copy()->addDays($duracion);
            }

            $membresia = Membresia::create([
                'socio_id' => $socio->id,
                'plan_id' => $plan->id,
                'sucursal_id' => $sucursalId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'precio_pagado' => $plan->precio,
                'cuotas_totales' => $cuotasTotales,
                'cuotas_pagadas' => $cuotasPagadas,
                'saldo_pendiente' => $saldoPendiente,
                'estado' => $estadoMembresia,
                'metodo_pago' => $faker->randomElement($metodosPago),
                'notas' => "Suscripción {$plan->nombre} sede {$sucursalId}",
            ]);

            // Registrar pago inicial
            DB::table('pagos')->insert([
                'membresia_id' => $membresia->id,
                'fecha_pago' => $fechaInicio->copy()->setTime($faker->numberBetween(8, 19), $faker->numberBetween(0, 59)),
                'monto' => $plan->precio,
                'metodo_pago' => ucfirst($membresia->metodo_pago),
                'referencia' => 'REF-'.strtoupper(Str::random(10)),
                'comprobante' => 'recibo_'.$membresia->id.'_'.$faker->numberBetween(1000, 9999).'.pdf',
                'notas' => 'Pago de cuota verificado.',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();

        // 2. Generación Masiva de Asistencias (~1,500 asistencias)
        $totalAsistencias = 1500;
        $this->command?->info("🚪 [2/4] Generando {$totalAsistencias} Marcajes de Asistencia adicionales...");
        $asistenciasBatch = [];
        $coachDefaultId = $entrenadores->first()?->id ?? 1;

        for ($k = 0; $k < $totalAsistencias; $k++) {
            $socio = $faker->randomElement($sociosNuevos);
            $diasAtras = $faker->numberBetween(0, 45);
            $sucursalId = $socio->user?->sucursal_id ?? 1;

            if ($faker->boolean(65)) {
                $horaEntrada = $faker->boolean(50) ? $faker->numberBetween(7, 9) : $faker->numberBetween(18, 20);
            } else {
                $horaEntrada = $faker->numberBetween(10, 16);
            }

            $minutoEntrada = $faker->numberBetween(0, 59);
            $fechaEntrada = $hoy->copy()->subDays($diasAtras)->setTime($horaEntrada, $minutoEntrada, 0);

            if ($socio->estado === 'moroso') {
                $estadoAcceso = 'denegado_vencido';
                $mensajeAcceso = 'Acceso Denegado: Cuota vencida. Pase por recepción para regularizar.';
                $fechaSalida = null;
                $permanenciaMinutos = null;
            } elseif ($socio->estado === 'inactivo') {
                $estadoAcceso = 'denegado_inactivo';
                $mensajeAcceso = 'Acceso Denegado: Socio inactivo en el sistema.';
                $fechaSalida = null;
                $permanenciaMinutos = null;
            } else {
                $estadoAcceso = 'permitido';
                $mensajeAcceso = "Acceso Autorizado. Bienvenido/a {$socio->nombre}.";

                // Dejar 10 socios dentro hoy
                if ($diasAtras === 0 && $k < 10) {
                    $fechaSalida = null;
                    $permanenciaMinutos = null;
                } else {
                    $permanenciaMinutos = $faker->numberBetween(45, 110);
                    $fechaSalida = $fechaEntrada->copy()->addMinutes($permanenciaMinutos);
                }
            }

            $asistenciasBatch[] = [
                'socio_id' => $socio->id,
                'sucursal_id' => $sucursalId,
                'registrado_por' => $coachDefaultId,
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

        // 3. Generación Masiva de Rutinas (~250 rutinas)
        $totalRutinas = 250;
        $this->command?->info("📋 [3/4] Diseñando {$totalRutinas} Rutinas adicionales con prescripción técnica...");

        $titulosRutinas = [
            'Principiante' => [
                'Adaptación Anatómica y Core',
                'Iniciación en Máquinas Guiadas',
                'Fullbody Básico Funcional',
                'Acondicionamiento Postural',
            ],
            'Intermedio' => [
                'Hipertrofia Torso / Pierna',
                'Fuerza y Rendimiento A/B',
                'Push / Pull / Legs Volumen',
                'Metabolic Conditioning & HIIT',
            ],
            'Avanzado' => [
                'Especialización de Fuerza 5x5',
                'Sobrecarga Progresiva Alta Intensidad',
                'Potencia y Pliometría Atlética',
                'German Body Composition Pro',
            ],
        ];

        for ($r = 0; $r < $totalRutinas; $r++) {
            $socio = $faker->randomElement($sociosNuevos);
            $entrenador = $entrenadores->isNotEmpty() ? $entrenadores->random() : User::first();
            $nivel = $faker->randomElement(['Principiante', 'Intermedio', 'Avanzado']);
            $titulo = $faker->randomElement($titulosRutinas[$nivel]);

            $esHistorica = $faker->boolean(20);
            $fechaInicioRutina = $esHistorica ? $hoy->copy()->subMonths(2) : $hoy->copy()->subDays($faker->numberBetween(3, 25));
            $fechaFinRutina = $esHistorica ? $hoy->copy()->subDays(20) : null;

            $rutina = Rutina::create([
                'socio_id' => $socio->id,
                'creada_por' => $entrenador->id,
                'nombre' => "{$titulo} ({$nivel}) - #".($offset + $r),
                'descripcion' => "Plan de entrenamiento enfocado en nivel {$nivel} prescrito por Coach {$entrenador->name}.",
                'nivel' => strtolower($nivel),
                'nivel_dificultad' => $nivel,
                'estado' => $esHistorica ? 'Histórica/Finalizada' : 'Activa',
                'fecha_inicio' => $fechaInicioRutina,
                'fecha_fin' => $fechaFinRutina,
                'dias_semana' => $faker->randomElement([
                    ['Lunes', 'Miércoles', 'Viernes'],
                    ['Martes', 'Jueves', 'Sábado'],
                    ['Lunes', 'Martes', 'Jueves', 'Viernes'],
                ]),
                'activa' => ! $esHistorica,
            ]);

            if (! empty($ejerciciosIds)) {
                $ejerciciosSeleccionados = $faker->randomElements($ejerciciosIds, min(count($ejerciciosIds), $faker->numberBetween(4, 6)));
                foreach ($ejerciciosSeleccionados as $pos => $ejId) {
                    DB::table('ejercicio_rutina')->insert([
                        'rutina_id' => $rutina->id,
                        'ejercicio_id' => $ejId,
                        'orden' => $pos + 1,
                        'series' => $faker->numberBetween(3, 4),
                        'repeticiones' => $faker->randomElement(['8-10', '10-12', '12-15']),
                        'descanso_segundos' => $faker->randomElement([60, 90, 120]),
                        'notas' => 'Cuidar tempo y respiración.',
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }
            }
        }

        $this->command?->info('✅ [4/4] ¡Carga masiva completada con éxito!');
        $this->command?->table(
            ['Métrica Total', 'Cantidad Actual'],
            [
                ['Socios Registrados', Socio::count()],
                ['Membresías Totales', Membresia::count()],
                ['Asistencias Registradas', Asistencia::count()],
                ['Asistencias En Sala Hoy', Asistencia::hoy()->abiertas()->count()],
                ['Rutinas Prescritas', Rutina::count()],
            ]
        );
    }
}
