<?php

namespace App\Http\Controllers;

use App\Http\Requests\InscripcionClaseRequest;
use App\Models\ClaseGrupal;
use App\Models\InscripcionClase;
use App\Models\Pago;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class InscripcionClaseController extends Controller
{
    /**
     * Lista las inscripciones para una clase grupal.
     */
    public function index(ClaseGrupal $clase): JsonResponse
    {
        $inscripciones = $clase->inscripciones()
            ->with(['socio:id,nombre,apellido,dni,codigo', 'socio.membresiaActiva.plan'])
            ->orderBy('fecha_clase', 'desc')
            ->get();

        return response()->json([
            'clase' => $clase->load(['profesor', 'sucursal']),
            'inscripciones' => $inscripciones,
            'cupos_disponibles_hoy' => $clase->cuposDisponiblesHoy(),
        ]);
    }

    /**
     * Inscribe a un socio en una clase grupal validando reglas de negocio, membresía y aforo.
     */
    public function inscribir(InscripcionClaseRequest $request, ?ClaseGrupal $clase = null): JsonResponse|RedirectResponse
    {
        $claseId = $clase ? $clase->id : $request->input('clase_grupal_id');
        $claseModel = $clase ?? ClaseGrupal::findOrFail($claseId);

        $fechaClase = Carbon::parse($request->input('fecha_clase', Carbon::today()->toDateString()))->toDateString();
        $socioId = (int) $request->input('socio_id');

        $socio = Socio::with('membresiaActiva.plan')->findOrFail($socioId);

        // 1. Validar membresía activa
        $membresia = $socio->membresiaActiva;
        if (! $membresia || ! $membresia->estaActiva()) {
            $mensaje = 'El socio no posee una membresía activa para inscribirse a clases.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $mensaje, 'mensaje' => $mensaje], 422);
            }

            return back()->withErrors(['socio_id' => $mensaje])->with('error', $mensaje);
        }

        $plan = $membresia->plan;

        // 2. Validar Plan Básico vs Completo / Premium
        if ($plan && ($plan->esBasico() || ! $plan->permiteClasesGrupales())) {
            $mensaje = 'El Plan Básico no incluye acceso a clases grupales. Actualiza a Plan Completo o Premium. (El Plan Básico no permite inscripción a clases grupales.)';
            if ($request->wantsJson()) {
                return response()->json(['error' => $mensaje, 'mensaje' => $mensaje, 'message' => $mensaje], 422);
            }

            return back()->withErrors(['plan' => $mensaje])->with('error', $mensaje);
        }

        // 3. Validar cupos y disponibilidad en tiempo real
        if (! $claseModel->tieneCupoDisponibleParaFecha($fechaClase)) {
            $mensaje = 'No hay cupos disponibles para esta clase en la fecha seleccionada. Clase llena.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $mensaje, 'mensaje' => $mensaje], 422);
            }

            return back()->withErrors(['cupo' => $mensaje])->with('error', $mensaje);
        }

        // 4. Validar que no esté inscrito ya para esa misma fecha
        $yaInscrito = InscripcionClase::where('socio_id', $socio->id)
            ->where('clase_grupal_id', $claseModel->id)
            ->whereDate('fecha_clase', $fechaClase)
            ->where('estado', '!=', 'cancelada')
            ->exists();

        if ($yaInscrito) {
            $mensaje = 'El socio ya se encuentra inscrito en esta clase para la fecha seleccionada.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $mensaje, 'mensaje' => $mensaje], 422);
            }

            return back()->withErrors(['socio_id' => $mensaje])->with('error', $mensaje);
        }

        // 5. Determinar recargo y estado de pago según el Plan
        $recargo = 0.00;
        $estadoPago = 'Exento';

        if ($plan) {
            if ($plan->esCompleto()) {
                $recargo = 10.00;
                $estadoPago = 'Pagado';
            } elseif ($plan->esPremium()) {
                $recargo = 0.00;
                $estadoPago = 'Exento';
            } else {
                $recargo = (float) ($plan->costo_recargo_clase ?? 0.00);
                $estadoPago = $recargo > 0 ? 'Pagado' : 'Exento';
            }
        }

        // 6. Ejecutar inscripción y registro de recargo dentro de una transacción DB
        $inscripcion = DB::transaction(function () use ($socio, $claseModel, $fechaClase, $recargo, $estadoPago, $membresia) {
            // Si aplica recargo, registrar transacción en pagos
            if ($recargo > 0) {
                Pago::create([
                    'membresia_id' => $membresia->id,
                    'fecha_pago' => Carbon::now(),
                    'monto' => $recargo,
                    'metodo_pago' => 'Tarjeta',
                    'referencia' => 'REC-CLASE-'.$claseModel->id.'-'.time(),
                    'notas' => "Recargo obligatorio por clase grupal: {$claseModel->nombre} ({$fechaClase})",
                ]);
            }

            return InscripcionClase::create([
                'socio_id' => $socio->id,
                'clase_grupal_id' => $claseModel->id,
                'fecha_clase' => $fechaClase,
                'monto_recargo' => $recargo,
                'monto_recargo_pagado' => $recargo,
                'estado_pago' => $estadoPago,
                'asistencia_confirmada' => false,
                'estado' => 'confirmada',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => 'Inscripción realizada con éxito.',
                'inscripcion' => $inscripcion,
                'recargo_pagado' => $recargo,
            ], 201);
        }

        $mensajeExito = $recargo > 0
            ? 'Inscripción exitosa. Se aplicó recargo de $'.number_format($recargo, 2).' al Plan Completo.'
            : 'Inscripción exitosa. Clase incluida en el Plan Premium ($0.00).';

        return back()->with('success', $mensajeExito);
    }

    /**
     * Alias de store para compatibilidad RESTful.
     */
    public function store(InscripcionClaseRequest $request, ?ClaseGrupal $clase = null): JsonResponse|RedirectResponse
    {
        return $this->inscribir($request, $clase);
    }

    /**
     * Confirmación de asistencia del socio a la clase grupal.
     */
    public function confirmarAsistencia(InscripcionClase $inscripcion): JsonResponse|RedirectResponse
    {
        $inscripcion->update([
            'asistencia_confirmada' => true,
            'estado' => 'asistio',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => 'Asistencia confirmada para la clase.',
            ]);
        }

        return back()->with('success', 'Asistencia confirmada con éxito.');
    }

    /**
     * Cancela una inscripción a clase grupal.
     */
    public function destroy(InscripcionClase $inscripcion): JsonResponse|RedirectResponse
    {
        $inscripcion->update(['estado' => 'cancelada']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => 'Inscripción cancelada exitosamente.',
            ]);
        }

        return back()->with('success', 'Inscripción cancelada correctamente.');
    }
}
