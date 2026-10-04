<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocioRequest;
use App\Http\Requests\UpdateSocioRequest;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SocioController extends Controller
{
    /**
     * Muestra el listado de socios con filtros y paginación.
     */
    public function index(Request $solicitud): Response
    {
        $termino = $solicitud->input('buscar');
        $filtroEstado = $solicitud->input('estado');

        $socios = Socio::with(['membresiaActiva.plan', 'ultimaMembresia.plan'])
            ->buscar($termino)
            ->when($filtroEstado, function ($consulta) use ($filtroEstado) {
                $consulta->where('estado', $filtroEstado);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $planes = Plan::activos()->get(['id', 'nombre', 'precio', 'duracion_dias']);

        return Inertia::render('socios/index', [
            'socios' => $socios,
            'planes' => $planes,
            'filtros' => [
                'buscar' => $termino,
                'estado' => $filtroEstado,
            ],
        ]);
    }

    /**
     * Formulario de creación de socio.
     */
    public function create(): Response
    {
        $planes = Plan::activos()->get(['id', 'nombre', 'precio', 'duracion_dias']);

        return Inertia::render('socios/create', [
            'planes' => $planes,
        ]);
    }

    /**
     * Registra un nuevo socio en el sistema y opcionalmente su primera membresía.
     */
    public function store(StoreSocioRequest $solicitud): RedirectResponse
    {
        $datos = $solicitud->validated();

        // Generación de código único para carnet / QR (Ej: SOC-0001)
        $siguienteId = (Socio::withTrashed()->max('id') ?? 0) + 1;
        $codigoSocio = 'SOC-'.str_pad((string) $siguienteId, 4, '0', STR_PAD_LEFT);

        $socio = Socio::create([
            'codigo' => $codigoSocio,
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'dni' => $datos['dni'],
            'correo' => $datos['correo'],
            'telefono' => $datos['telefono'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'foto' => $datos['foto'] ?? null,
            'estado' => 'activo',
            'observaciones_medicas' => $datos['observaciones_medicas'] ?? null,
        ]);

        // Si se seleccionó un plan inicial, crear la membresía de bienvenida
        if (! empty($datos['plan_id'])) {
            $plan = Plan::findOrFail($datos['plan_id']);
            $fechaInicio = Carbon::today();
            $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias);

            Membresia::create([
                'socio_id' => $socio->id,
                'plan_id' => $plan->id,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'precio_pagado' => $plan->precio,
                'estado' => 'activa',
                'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
                'notas' => 'Membresía inicial al dar de alta al socio.',
            ]);
        }

        return redirect()->route('socios.index')->with('success', "Socio {$socio->nombre_completo} registrado con éxito con el código {$socio->codigo}.");
    }

    /**
     * Muestra la ficha técnica completa del socio con indagación rápida entre socios.
     */
    public function show(Socio $socio): Response
    {
        $socio->load([
            'membresias.plan',
            'membresiaActiva.plan',
            'rutinas.ejercicios',
            'asistencias' => fn ($q) => $q->latest('fecha_hora_entrada')->take(15),
            'user.sucursal',
        ]);

        $planes = Plan::activos()->get(['id', 'nombre', 'precio', 'duracion_dias']);

        $prevSocio = Socio::where('id', '<', $socio->id)->orderBy('id', 'desc')->first(['id', 'nombre', 'apellido', 'codigo']);
        $nextSocio = Socio::where('id', '>', $socio->id)->orderBy('id', 'asc')->first(['id', 'nombre', 'apellido', 'codigo']);

        return Inertia::render('socios/show', [
            'socio' => $socio,
            'planes' => $planes,
            'navegacion' => [
                'anterior' => $prevSocio,
                'siguiente' => $nextSocio,
            ],
        ]);
    }

    /**
     * Formulario de edición de socio.
     */
    public function edit(Socio $socio): Response
    {
        $socio->load(['membresiaActiva.plan', 'user.sucursal']);
        $sucursales = Sucursal::all(['id', 'nombre', 'ciudad']);

        return Inertia::render('socios/edit', [
            'socio' => $socio,
            'sucursales' => $sucursales,
        ]);
    }

    /**
     * Actualiza la información del socio.
     */
    public function update(UpdateSocioRequest $solicitud, Socio $socio): RedirectResponse|JsonResponse
    {
        $datos = $solicitud->validated();
        $socio->update($datos);

        if ($solicitud->has('sucursal_id') && $socio->user) {
            $socio->user->update(['sucursal_id' => $solicitud->input('sucursal_id')]);
        }

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => "Datos de {$socio->nombre_completo} actualizados correctamente.",
                'socio' => $socio->fresh(),
            ]);
        }

        return redirect()->route('socios.show', $socio->id)->with('success', "Datos de {$socio->nombre_completo} actualizados correctamente.");
    }

    /**
     * Elimina (soft delete) un socio.
     */
    public function destroy(Socio $socio): RedirectResponse
    {
        $nombre = $socio->nombre_completo;
        $socio->delete();

        return redirect()->route('socios.index')->with('success', "El socio {$nombre} ha sido dado de baja.");
    }

    /**
     * Genera y registra automáticamente un socio con datos realistas y membresía activa.
     */
    public function generarAutomatico(Request $solicitud): RedirectResponse|JsonResponse
    {
        $faker = Factory::create('es_ES');
        $siguienteId = (Socio::withTrashed()->max('id') ?? 0) + 1;
        $codigoSocio = 'SOC-'.str_pad((string) $siguienteId, 4, '0', STR_PAD_LEFT);

        $nombre = $faker->firstName();
        $apellido = $faker->lastName().' '.$faker->lastName();
        $dni = (string) $faker->unique()->numberBetween(10000000, 99999999);
        $correo = Str::slug($nombre.'.'.$apellido.'.'.$siguienteId).'@gmail.com';

        // Crear usuario vinculado
        $passwordHash = Hash::make('password');
        $rolSocio = Rol::where('nombre', 'socio')->first();
        $usuario = User::create([
            'name' => "{$nombre} {$apellido}",
            'email' => $correo,
            'password' => $passwordHash,
            'rol_id' => $rolSocio?->id ?? 3,
        ]);

        $socio = Socio::create([
            'user_id' => $usuario->id,
            'codigo' => $codigoSocio,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'dni' => $dni,
            'correo' => $correo,
            'telefono' => '+58 4'.$faker->randomElement(['12', '14', '24', '16']).' '.$faker->numberBetween(1000000, 9999999),
            'direccion' => $faker->streetAddress().', Caracas',
            'fecha_nacimiento' => $faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'foto' => 'https://images.unsplash.com/photo-'.$faker->randomElement([
                '1534528741775-53994a69daeb',
                '1507003211169-0a1dd7228f2d',
                '1500648767791-00dcc994a43e',
                '1494790108377-be9c29b29330',
                '1517841905240-472988babdf9',
            ]).'?w=200&fit=crop&crop=faces',
            'estado' => 'activo',
            'observaciones_medicas' => $faker->optional(0.3)->sentence() ?? 'Apto para actividad física de alta intensidad.',
        ]);

        // Asignar un plan activo automáticamente
        $plan = Plan::inRandomOrder()->first() ?? Plan::first();
        if ($plan) {
            $fechaInicio = Carbon::today();
            $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias ?: 30);
            $tipo = $plan->tipo ?? 'individual';
            $sesiones = $plan->sesiones_totales;

            $membresia = Membresia::create([
                'socio_id' => $socio->id,
                'plan_id' => $plan->id,
                'tipo' => $tipo,
                'sesiones_totales' => $sesiones,
                'sesiones_restantes' => $sesiones,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'precio_pagado' => $plan->precio,
                'estado' => 'activa',
                'metodo_pago' => $faker->randomElement(['efectivo', 'tarjeta', 'transferencia', 'pago_movil']),
                'notas' => 'Membresía asignada automáticamente con registro de 1-clic.',
            ]);

            // Pago registrado
            $membresia->pagos()->create([
                'fecha_pago' => Carbon::now(),
                'monto' => $plan->precio,
                'metodo_pago' => ucfirst($membresia->metodo_pago),
                'referencia' => 'AUTO-'.strtoupper(Str::random(8)),
                'comprobante' => 'recibo_auto_'.$socio->id.'.pdf',
                'notas' => 'Pago automático de alta.',
            ]);
        }

        // Asignar rutina de entrenamiento activa automáticamente
        $entrenador = User::where('rol_id', 2)->inRandomOrder()->first();
        $objetivos = ['ganar_musculo', 'fuerza', 'perder_peso', 'acondicionamiento'];
        $niveles = ['principiante', 'intermedio', 'avanzado'];
        $rutina = $socio->generarRutinaAutomatica(
            $faker->randomElement($objetivos),
            $faker->randomElement($niveles),
            $entrenador?->id
        );

        $mensaje = "¡Socio {$socio->nombre_completo} ({$socio->codigo}) generado automáticamente con plan {$plan?->nombre} y rutina {$rutina->nombre}!";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'socio' => $socio->load(['membresiaActiva.plan', 'rutinas.ejercicios']),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }
}
