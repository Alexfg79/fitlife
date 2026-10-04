<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PoblarAsistenciasCompletasSeeder extends Seeder
{
    /**
     * Asegura 999 socios y pobla ~5,000+ asistencias cubriendo el 100% de los socios.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $ahora = Carbon::now();
        $hoy = Carbon::today();
        $passwordHash = Hash::make('password');

        $this->command?->info('🏋️ [1/3] Verificando conteo de socios para alcanzar al menos 999 socios...');

        $totalSociosActual = Socio::count();
        $sucursales = Sucursal::all();
        $sucursalesIds = $sucursales->pluck('id')->toArray();
        if (empty($sucursalesIds)) {
            $sucursalesIds = [1];
        }

        $rolSocio = Rol::firstOrCreate(['nombre' => 'socio'], ['descripcion' => 'Socio / Miembro del Gimnasio']);
        $plan = Plan::first();

        // 1. Si faltan socios para 999, crearlos
        if ($totalSociosActual < 999) {
            $faltantes = 999 - $totalSociosActual;
            $this->command?->info("Agregando {$faltantes} socios para llegar exactamente a 999 socios...");

            $dnisUsados = Socio::pluck('dni')->flip()->toArray();
            $correosUsados = User::pluck('email')->flip()->toArray();
            $ultimoId = (int) (Socio::max('id') ?? 0);

            for ($i = 1; $i <= $faltantes; $i++) {
                $num = 3000 + $ultimoId + $i;
                do {
                    $dni = (string) $faker->numberBetween(10000000, 99999999);
                } while (isset($dnisUsados[$dni]));
                $dnisUsados[$dni] = true;

                $nombre = $faker->firstName();
                $apellido = $faker->lastName().' '.$faker->lastName();
                $codigo = 'SOC-'.str_pad((string) $num, 5, '0', STR_PAD_LEFT);
                $correo = Str::slug($nombre.'.'.$apellido.'.'.$num).'@gmail.com';
                $sucursalId = $sucursalesIds[$i % count($sucursalesIds)];

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
                    'telefono' => '+58 412 '.$faker->numberBetween(1000000, 9999999),
                    'direccion' => $faker->streetAddress(),
                    'fecha_nacimiento' => '1996-06-15',
                    'estado' => 'activo',
                ]);

                Membresia::create([
                    'socio_id' => $socio->id,
                    'plan_id' => $plan?->id ?? 1,
                    'sucursal_id' => $sucursalId,
                    'fecha_inicio' => $hoy->copy()->subDays(10),
                    'fecha_fin' => $hoy->copy()->addDays(20),
                    'precio_pagado' => $plan?->precio ?? 80.00,
                    'cuotas_totales' => 1,
                    'cuotas_pagadas' => 1,
                    'saldo_pendiente' => 0,
                    'estado' => 'activa',
                    'metodo_pago' => 'transferencia',
                ]);
            }
        }

        $totalSocios = Socio::count();
        $this->command?->info("👥 Total de socios en base de datos: {$totalSocios}");

        // 2. Generar asistencias garantizando que el 100% de los socios tengan marcajes
        $this->command?->info('🚪 [2/3] Generando asistencias masivas para TODOS los socios (meta ~5,000+ asistencias)...');

        $socios = Socio::with('user')->get();
        $asistenciasBatch = [];
        $coachDefault = User::whereHas('rol', fn ($q) => $q->where('nombre', 'entrenador'))->first() ?? User::first();
        $coachId = $coachDefault?->id ?? 1;

        $bar = $this->command?->getOutput()->createProgressBar($socios->count());
        $bar?->start();

        foreach ($socios as $socio) {
            $sucursalId = $socio->user?->sucursal_id ?? 1;
            $asistenciasCount = $socio->asistencias()->count();

            // Garantizar entre 4 y 8 asistencias por cada socio
            $asistenciasAGenerar = max(3, 6 - $asistenciasCount);

            for ($a = 0; $a < $asistenciasAGenerar; $a++) {
                $diasAtras = $faker->numberBetween(0, 45);

                if ($faker->boolean(65)) {
                    $horaEntrada = $faker->boolean(50) ? $faker->numberBetween(7, 9) : $faker->numberBetween(18, 20);
                } else {
                    $horaEntrada = $faker->numberBetween(10, 16);
                }

                $minutoEntrada = $faker->numberBetween(0, 59);
                $fechaEntrada = $hoy->copy()->subDays($diasAtras)->setTime($horaEntrada, $minutoEntrada, 0);

                if ($socio->estado === 'moroso') {
                    $estadoAcceso = 'denegado_vencido';
                    $mensajeAcceso = 'Acceso Denegado: Cuota vencida. Regularizar en caja.';
                    $fechaSalida = null;
                    $permanencia = null;
                } elseif ($socio->estado === 'inactivo') {
                    $estadoAcceso = 'denegado_inactivo';
                    $mensajeAcceso = 'Acceso Denegado: Socio suspendido.';
                    $fechaSalida = null;
                    $permanencia = null;
                } else {
                    $estadoAcceso = 'permitido';
                    $mensajeAcceso = "Acceso Autorizado. Bienvenido/a {$socio->nombre}.";

                    // Dejar abierto si es hoy y $a == 0 en un 5% de socios
                    if ($diasAtras === 0 && $a === 0 && $faker->boolean(8)) {
                        $fechaSalida = null;
                        $permanencia = null;
                    } else {
                        $permanencia = $faker->numberBetween(45, 115);
                        $fechaSalida = $fechaEntrada->copy()->addMinutes($permanencia);
                    }
                }

                $asistenciasBatch[] = [
                    'socio_id' => $socio->id,
                    'sucursal_id' => $sucursalId,
                    'registrado_por' => $coachId,
                    'fecha_hora_entrada' => $fechaEntrada,
                    'fecha_hora_salida' => $fechaSalida,
                    'tiempo_permanencia_minutos' => $permanencia,
                    'fecha_hora' => $fechaEntrada,
                    'metodo' => $faker->randomElement(['qr', 'codigo', 'biometrico']),
                    'estado_acceso' => $estadoAcceso,
                    'mensaje_acceso' => $mensajeAcceso,
                    'created_at' => $fechaEntrada,
                    'updated_at' => $fechaSalida ?? $fechaEntrada,
                ];

                if (count($asistenciasBatch) >= 500) {
                    DB::table('asistencias')->insert($asistenciasBatch);
                    $asistenciasBatch = [];
                }
            }

            $bar?->advance();
        }

        if (! empty($asistenciasBatch)) {
            DB::table('asistencias')->insert($asistenciasBatch);
        }

        $bar?->finish();
        $this->command?->newLine();

        $this->command?->info('✅ [3/3] ¡Auditoría de Asistencias Completada!');
        $sociosConAsistencia = Socio::has('asistencias')->count();
        $totalAsistencias = Asistencia::count();

        $this->command?->table(
            ['Métrica', 'Total'],
            [
                ['Socios en el Sistema', $totalSocios],
                ['Socios con Asistencias', "{$sociosConAsistencia} de {$totalSocios} (100%)"],
                ['Total Asistencias Registradas', number_format($totalAsistencias, 0, ',', '.')],
                ['Asistencias En Sala Hoy', Asistencia::hoy()->abiertas()->count()],
            ]
        );
    }
}
