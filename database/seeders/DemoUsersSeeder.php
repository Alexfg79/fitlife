<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Ejercicio;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Garantizar que existan los usuarios de demostración para el acceso rápido por rol:
     * 1. Administrador: admin@fitlife.test / password
     * 2. Entrenador:    coach@fitlife.test / password
     * 3. Socio:         socio@fitlife.test / password
     */
    public function run(): void
    {
        $ahora = Carbon::now();
        $hoy = Carbon::today();
        $passwordHash = Hash::make('password');
        $sucursalId = Sucursal::value('id');

        // 1. Roles
        $rolAdmin = Rol::firstOrCreate(['nombre' => 'admin'], [
            'nombre_mostrar' => 'Administrador',
            'descripcion' => 'Control total del gimnasio, finanzas, usuarios y configuraciones.',
        ]);

        $rolEntrenador = Rol::firstOrCreate(['nombre' => 'entrenador'], [
            'nombre_mostrar' => 'Entrenador / Coach',
            'descripcion' => 'Gestión de rutinas, seguimiento de socios y control de asistencias.',
        ]);

        $rolSocio = Rol::firstOrCreate(['nombre' => 'socio'], [
            'nombre_mostrar' => 'Socio / Miembro',
            'descripcion' => 'Acceso a su perfil, rutinas asignadas y estado de cuenta.',
        ]);

        // 2. Usuario Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@fitlife.test'],
            [
                'name' => 'Alexander Director (Admin)',
                'password' => $passwordHash,
                'rol_id' => $rolAdmin->id,
                'sucursal_id' => $sucursalId,
            ]
        );
        if ($admin->rol_id !== $rolAdmin->id || ! Hash::check('password', $admin->password)) {
            $admin->update([
                'password' => $passwordHash,
                'rol_id' => $rolAdmin->id,
                'sucursal_id' => $sucursalId,
            ]);
        }

        // 3. Usuario Entrenador (Coach)
        $coach = User::firstOrCreate(
            ['email' => 'coach@fitlife.test'],
            [
                'name' => 'Marcos Silva (Head Coach)',
                'password' => $passwordHash,
                'rol_id' => $rolEntrenador->id,
                'sucursal_id' => $sucursalId,
            ]
        );
        if ($coach->rol_id !== $rolEntrenador->id || ! Hash::check('password', $coach->password)) {
            $coach->update([
                'name' => 'Marcos Silva (Head Coach)',
                'password' => $passwordHash,
                'rol_id' => $rolEntrenador->id,
                'sucursal_id' => $sucursalId,
            ]);
        }

        // 4. Usuario Socio
        $userSocio = User::firstOrCreate(
            ['email' => 'socio@fitlife.test'],
            [
                'name' => 'Carlos Mendoza (Socio FitLife)',
                'password' => $passwordHash,
                'rol_id' => $rolSocio->id,
                'sucursal_id' => $sucursalId,
            ]
        );
        if ($userSocio->rol_id !== $rolSocio->id || ! Hash::check('password', $userSocio->password)) {
            $userSocio->update([
                'name' => 'Carlos Mendoza (Socio FitLife)',
                'password' => $passwordHash,
                'rol_id' => $rolSocio->id,
                'sucursal_id' => $sucursalId,
            ]);
        }

        // 5. Perfil de Socio Vinculado
        $socio = Socio::firstOrCreate(
            ['correo' => 'socio@fitlife.test'],
            [
                'user_id' => $userSocio->id,
                'codigo' => 'SOC-00001',
                'nombre' => 'Carlos',
                'apellido' => 'Mendoza',
                'dni' => '10000001',
                'telefono' => '+58 412 1234567',
                'direccion' => 'Av. Francisco de Miranda, Edif. FitLife Pro, Caracas',
                'fecha_nacimiento' => '1995-05-15',
                'estado' => 'activo',
                'observaciones_medicas' => 'Apto para entrenamiento de alta intensidad. Sin restricciones.',
            ]
        );
        if ($socio->user_id !== $userSocio->id) {
            $socio->update(['user_id' => $userSocio->id, 'estado' => 'activo']);
        }

        // 6. Plan y Membresía Activa para el Socio (Plan Premium $120)
        $plan = Plan::where('nombre', 'Plan Premium')->first() ?? Plan::first();
        if ($plan) {
            $membresia = Membresia::firstOrCreate(
                ['socio_id' => $socio->id],
                [
                    'plan_id' => $plan->id,
                    'sucursal_id' => $sucursalId,
                    'fecha_inicio' => $hoy->copy()->subDays(20),
                    'fecha_fin' => $hoy->copy()->addDays($plan->duracion_dias ?: 30),
                    'precio_pagado' => $plan->precio,
                    'estado' => 'activa',
                    'metodo_pago' => 'tarjeta',
                    'notas' => 'Membresía activa de demostración para evaluación de acceso rápido.',
                ]
            );

            // 7. Pago Asociado a la Membresía
            Pago::firstOrCreate(
                ['membresia_id' => $membresia->id],
                [
                    'fecha_pago' => $membresia->fecha_inicio->setTime(10, 30),
                    'monto' => $membresia->precio_pagado,
                    'metodo_pago' => 'Tarjeta',
                    'referencia' => 'DEMO-PAGO-001',
                    'comprobante' => 'comprobante_socio_demo.pdf',
                    'notas' => 'Pago validado en línea vía pasarela deportiva.',
                ]
            );
        }

        // 8. Asistencias Recientes para el Socio con entrada y salida
        $asistenciasCount = Asistencia::where('socio_id', $socio->id)->count();
        if ($asistenciasCount === 0) {
            $entradaHoy = $hoy->copy()->setTime(8, 15);
            $salidaHoy = $hoy->copy()->setTime(9, 35);
            Asistencia::create([
                'socio_id' => $socio->id,
                'sucursal_id' => $sucursalId,
                'fecha_hora_entrada' => $entradaHoy,
                'fecha_hora_salida' => $salidaHoy,
                'tiempo_permanencia_minutos' => 80,
                'fecha_hora' => $entradaHoy,
                'metodo' => 'qr',
                'estado_acceso' => 'permitido',
                'mensaje_acceso' => 'Acceso autorizado - Membresía Activa',
            ]);

            $entradaAyer = $hoy->copy()->subDays(1)->setTime(18, 45);
            $salidaAyer = $hoy->copy()->subDays(1)->setTime(20, 00);
            Asistencia::create([
                'socio_id' => $socio->id,
                'sucursal_id' => $sucursalId,
                'fecha_hora_entrada' => $entradaAyer,
                'fecha_hora_salida' => $salidaAyer,
                'tiempo_permanencia_minutos' => 75,
                'fecha_hora' => $entradaAyer,
                'metodo' => 'codigo',
                'estado_acceso' => 'permitido',
                'mensaje_acceso' => 'Acceso autorizado - Membresía Activa',
            ]);
        }

        // 9. Rutina Asignada al Socio por el Coach
        $rutinaExistente = Rutina::where('socio_id', $socio->id)->first();
        if (! $rutinaExistente) {
            $rutina = Rutina::create([
                'socio_id' => $socio->id,
                'creada_por' => $coach->id,
                'nombre' => 'Fuerza e Hipertrofia Elite (4 Días)',
                'descripcion' => 'Programa personalizado enfocado en progresión de sobrecarga para pecho, espalda y piernas.',
                'nivel' => 'intermedio',
                'nivel_dificultad' => 'Intermedio',
                'estado' => 'Activa',
                'fecha_inicio' => $hoy->copy()->subDays(15),
                'dias_semana' => ['Lunes', 'Martes', 'Jueves', 'Viernes'],
                'activa' => true,
            ]);

            // Vincular 5 ejercicios si existen en el catálogo
            $ejercicios = Ejercicio::limit(5)->get();
            foreach ($ejercicios as $idx => $ej) {
                DB::table('ejercicio_rutina')->insert([
                    'rutina_id' => $rutina->id,
                    'ejercicio_id' => $ej->id,
                    'orden' => $idx + 1,
                    'series' => 4,
                    'repeticiones' => '10-12',
                    'descanso_segundos' => 90,
                    'notas' => 'Enfoque en tempo excéntrico 3-0-1. Carga progresiva.',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        }

        $this->command?->info('✅ Usuarios demo verificados: admin@fitlife.test, coach@fitlife.test y socio@fitlife.test listos!');
    }
}
