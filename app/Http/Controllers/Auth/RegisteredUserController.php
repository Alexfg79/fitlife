<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la pantalla de registro unificada con planes disponibles.
     */
    public function create(Request $request): Response
    {
        $planes = Plan::activos()->get(['id', 'nombre', 'precio', 'duracion_dias', 'tipo']);

        return Inertia::render('auth/login', [
            'initialTab' => 'register',
            'canResetPassword' => true,
            'planes' => $planes,
            'planSeleccionado' => $request->input('plan'),
        ]);
    }

    /**
     * Procesa la solicitud de registro de un nuevo socio.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $user = DB::transaction(function () use ($datos) {
            // 1. Obtener el rol de Socio
            $rolSocio = Rol::where('nombre', 'socio')->first();

            // 2. Crear el usuario del sistema
            $user = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => Hash::make($datos['password']),
                'rol_id' => $rolSocio?->id,
            ]);

            // 3. Generar código único para carnet / QR (Ej: SOC-0001)
            $siguienteId = (Socio::withTrashed()->max('id') ?? 0) + 1;
            $codigoSocio = 'SOC-'.str_pad((string) $siguienteId, 4, '0', STR_PAD_LEFT);

            // 4. Dividir nombre y apellido
            $partes = explode(' ', trim($datos['name']), 2);
            $nombre = $partes[0];
            $apellido = $partes[1] ?? 'Socio';

            // 5. Crear la ficha de socio vinculada con estado inicial 'inactivo'
            $socio = Socio::create([
                'user_id' => $user->id,
                'codigo' => $codigoSocio,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $datos['dni'],
                'correo' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'estado' => 'inactivo',
                'observaciones_medicas' => 'Registro online autoservicio. Pendiente de activación.',
            ]);

            // 6. Si seleccionó un plan de membresía de interés
            if (! empty($datos['plan_id'])) {
                $plan = Plan::find($datos['plan_id']);
                if ($plan) {
                    $fechaInicio = Carbon::today();
                    $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias);

                    Membresia::create([
                        'socio_id' => $socio->id,
                        'plan_id' => $plan->id,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'precio_pagado' => $plan->precio,
                        'estado' => 'pendiente',
                        'metodo_pago' => 'pendiente',
                        'notas' => 'Pre-inscripción en línea con selección de plan. Pendiente de pago y activación.',
                    ]);
                }
            }

            return $user;
        });

        // 7. Disparar evento de verificación de correo
        event(new Registered($user));

        // 8. Iniciar sesión y redirigir al portal personal del socio
        Auth::login($user);

        return redirect()->route('mi-perfil');
    }
}
