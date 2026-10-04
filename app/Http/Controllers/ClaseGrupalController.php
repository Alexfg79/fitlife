<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaseGrupalRequest;
use App\Models\ClaseGrupal;
use App\Models\Profesor;
use App\Models\Socio;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClaseGrupalController extends Controller
{
    /**
     * Catálogo y calendario de clases grupales para administradores y socios.
     */
    public function index(Request $request): Response
    {
        $fechaConsulta = $request->input('fecha', Carbon::today()->toDateString());
        $disciplina = $request->input('disciplina');
        $sucursalId = $request->input('sucursal_id');
        $diaSemana = $request->input('dia_semana');

        $query = ClaseGrupal::with([
            'profesor',
            'sucursal',
            'inscripciones' => function ($q) use ($fechaConsulta) {
                $q->whereDate('fecha_clase', $fechaConsulta)
                    ->where('estado', '!=', 'cancelada')
                    ->with('socio:id,nombre,apellido,dni,codigo,estado');
            },
        ])->where('activa', true);

        if ($disciplina) {
            $query->where('nombre', 'ilike', "%{$disciplina}%");
        }

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($diaSemana) {
            $query->where('dia_semana', $diaSemana);
        }

        $clasesCollection = $query->orderBy('hora_inicio')->get()->map(function (ClaseGrupal $clase) {
            $inscritosActivos = $clase->inscripciones->count();
            $cuposRestantes = max(0, $clase->cupo_maximo - $inscritosActivos);

            return [
                'id' => $clase->id,
                'nombre' => $clase->nombre,
                'descripcion' => $clase->descripcion,
                'dia_semana' => $clase->dia_semana ?? 'Lunes',
                'hora_inicio' => $clase->hora_inicio ?? '08:00',
                'horario' => $clase->horario,
                'duracion_minutos' => $clase->duracion_minutos,
                'cupo_maximo' => $clase->cupo_maximo,
                'cupos_restantes' => $cuposRestantes,
                'esta_llena' => $cuposRestantes <= 0,
                'sala' => $clase->sala,
                'activa' => $clase->activa,
                'estado' => $clase->estado ?? 'activa',
                'sucursal' => $clase->sucursal ? [
                    'id' => $clase->sucursal->id,
                    'nombre' => $clase->sucursal->nombre,
                    'ciudad' => $clase->sucursal->ciudad,
                ] : null,
                'profesor' => $clase->profesor ? [
                    'id' => $clase->profesor->id,
                    'nombre_completo' => $clase->profesor->nombre_completo,
                    'especialidad' => $clase->profesor->especialidad,
                    'telefono' => $clase->profesor->telefono,
                    'correo' => $clase->profesor->email,
                ] : null,
                'inscripciones' => $clase->inscripciones->map(function ($inscripcion) {
                    return [
                        'id' => $inscripcion->id,
                        'socio_id' => $inscripcion->socio_id,
                        'socio_nombre' => $inscripcion->socio?->nombre_completo,
                        'socio_dni' => $inscripcion->socio?->dni,
                        'socio_codigo' => $inscripcion->socio?->codigo,
                        'fecha_clase' => $inscripcion->fecha_clase?->toDateString(),
                        'monto_recargo' => (float) ($inscripcion->monto_recargo ?? $inscripcion->monto_recargo_pagado ?? 0),
                        'estado_pago' => $inscripcion->estado_pago ?? 'Exento',
                        'asistencia_confirmada' => (bool) $inscripcion->asistencia_confirmada,
                        'estado' => $inscripcion->estado,
                    ];
                }),
            ];
        });

        // Catálogos para filtros y formularios
        $sucursales = Sucursal::select('id', 'nombre', 'ciudad')->orderBy('nombre')->get();
        $profesores = Profesor::where('activo', true)
            ->select('id', 'nombre', 'apellido', 'especialidad', 'telefono', 'correo')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre_completo' => $p->nombre_completo,
                'especialidad' => $p->especialidad,
            ]);

        // Lista de socios para inscripción rápida (incluyendo información de plan)
        $socios = Socio::with('membresiaActiva.plan')
            ->where('estado', 'activo')
            ->orderBy('apellido')
            ->limit(100)
            ->get()
            ->map(function (Socio $s) {
                $plan = $s->membresiaActiva?->plan;

                return [
                    'id' => $s->id,
                    'nombre_completo' => $s->nombre_completo,
                    'dni' => $s->dni,
                    'codigo' => $s->codigo,
                    'plan_nombre' => $plan?->nombre ?? 'Sin Membresía',
                    'es_basico' => $plan?->esBasico() ?? false,
                    'es_completo' => $plan?->esCompleto() ?? false,
                    'es_premium' => $plan?->esPremium() ?? false,
                ];
            });

        $disciplinas = ['Spinning', 'Yoga', 'Pilates', 'Zumba', 'Funcional'];
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        return Inertia::render('clases/index', [
            'clases' => $clasesCollection,
            'sucursales' => $sucursales,
            'profesores' => $profesores,
            'socios' => $socios,
            'disciplinas' => $disciplinas,
            'diasSemana' => $diasSemana,
            'fechaSeleccionada' => $fechaConsulta,
            'filtros' => [
                'disciplina' => $disciplina,
                'sucursal_id' => $sucursalId ? (int) $sucursalId : null,
                'dia_semana' => $diaSemana,
                'fecha' => $fechaConsulta,
            ],
        ]);
    }

    /**
     * Guarda una nueva clase grupal.
     */
    public function store(StoreClaseGrupalRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        if (empty($datos['horario'])) {
            $datos['horario'] = "{$datos['dia_semana']} {$datos['hora_inicio']}";
        }

        ClaseGrupal::create($datos);

        return redirect()->route('clases.index')->with('success', 'Clase grupal registrada exitosamente.');
    }

    /**
     * Actualiza una clase grupal existente.
     */
    public function update(StoreClaseGrupalRequest $request, ClaseGrupal $clase): RedirectResponse
    {
        $datos = $request->validated();
        if (empty($datos['horario'])) {
            $datos['horario'] = "{$datos['dia_semana']} {$datos['hora_inicio']}";
        }

        $clase->update($datos);

        return redirect()->route('clases.index')->with('success', 'Clase grupal actualizada exitosamente.');
    }

    /**
     * Elimina o cancela una clase grupal.
     */
    public function destroy(ClaseGrupal $clase): RedirectResponse
    {
        $clase->update([
            'activa' => false,
            'estado' => 'cancelada',
        ]);

        return redirect()->route('clases.index')->with('success', 'Clase grupal cancelada exitosamente.');
    }
}
