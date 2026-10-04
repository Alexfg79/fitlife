<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembresiaRequest;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Socio;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembresiaController extends Controller
{
    /**
     * Registra o renueva la membresía de un socio.
     */
    public function store(StoreMembresiaRequest $solicitud): RedirectResponse|JsonResponse
    {
        $datos = $solicitud->validated();
        $socio = Socio::findOrFail($datos['socio_id']);
        $plan = Plan::findOrFail($datos['plan_id']);

        // Determinar fecha de inicio: si tiene membresía activa, inicia al terminar la anterior, si no, inicia hoy
        $membresiaActiva = $socio->membresiaActiva;
        if ($membresiaActiva && Carbon::parse($membresiaActiva->fecha_fin)->isFuture()) {
            $fechaInicio = Carbon::parse($membresiaActiva->fecha_fin)->addDay();
        } else {
            $fechaInicio = ! empty($datos['fecha_inicio']) ? Carbon::parse($datos['fecha_inicio']) : Carbon::today();
        }

        $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias);
        $precio = ! empty($datos['precio_pagado']) ? (float) $datos['precio_pagado'] : (float) $plan->precio;

        $tipo = $plan->tipo ?? 'individual';
        $sesionesTotales = $plan->sesiones_totales;
        $sesionesRestantes = $sesionesTotales;

        $cuotasTotales = (int) ($datos['cuotas_totales'] ?? 1);
        $montoInicial = ! empty($datos['monto_inicial']) ? (float) $datos['monto_inicial'] : $precio;
        $saldoPendiente = max(0, $precio - $montoInicial);
        $cuotasPagadas = $saldoPendiente > 0 ? 1 : $cuotasTotales;

        $membresia = Membresia::create([
            'socio_id' => $socio->id,
            'sucursal_id' => $datos['sucursal_id'] ?? $socio->user?->sucursal_id ?? auth()->user()?->sucursal_id ?? Sucursal::value('id'),
            'plan_id' => $plan->id,
            'tipo' => $tipo,
            'sesiones_totales' => $sesionesTotales,
            'sesiones_restantes' => $sesionesRestantes,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'precio_pagado' => $precio,
            'cuotas_totales' => $cuotasTotales,
            'cuotas_pagadas' => $cuotasPagadas,
            'saldo_pendiente' => $saldoPendiente,
            'estado' => 'activa',
            'metodo_pago' => $datos['metodo_pago'],
            'notas' => $datos['notas'] ?? "Suscripción {$plan->nombre}",
        ]);

        // Registrar pago inicial en la tabla de pagos
        $membresia->pagos()->create([
            'fecha_pago' => Carbon::now(),
            'monto' => $montoInicial,
            'metodo_pago' => ucfirst($datos['metodo_pago']),
            'referencia' => 'PAG-'.strtoupper(Str::random(8)),
            'comprobante' => 'comprobante_'.$membresia->id.'_'.time().'.pdf',
            'notas' => $saldoPendiente > 0 ? "Cuota inicial 1/{$cuotasTotales}" : 'Pago total completado.',
        ]);

        // Si el socio estaba en estado moroso o inactivo, reactivarlo
        if ($socio->estado !== 'activo') {
            $socio->update(['estado' => 'activo']);
        }

        $mensaje = "Membresía '{$plan->nombre}' asignada con éxito a {$socio->nombre_completo} hasta el {$fechaFin->format('d/m/Y')}.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'membresia' => $membresia->load('plan'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Pausa o congela temporalmente una membresía activa.
     */
    public function congelar(Request $solicitud, Membresia $membresia): RedirectResponse|JsonResponse
    {
        $solicitud->validate([
            'dias' => ['required', 'integer', 'min:1', 'max:90'],
            'motivo' => ['required', 'string', 'in:vacaciones,medico,laboral,otro'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $dias = (int) $solicitud->input('dias');
        $motivo = $solicitud->input('motivo');
        $observaciones = $solicitud->input('observaciones');

        $congelamiento = $membresia->congelar($dias, $motivo, null, $observaciones);

        $mensaje = "La membresía ha sido congelada exitosamente por {$dias} días por motivo de {$motivo}.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'congelamiento' => $congelamiento,
                'membresia' => $membresia->fresh(),
            ]);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Reanuda una membresía previamente congelada o en pausa.
     */
    public function reanudar(Membresia $membresia): RedirectResponse|JsonResponse
    {
        $exito = $membresia->reanudar();

        if (! $exito) {
            $errorMsg = 'La membresía no se encuentra en estado congelada o no tiene pausas activas.';
            if (request()->wantsJson()) {
                return response()->json(['exito' => false, 'mensaje' => $errorMsg], 422);
            }

            return back()->withErrors(['membresia' => $errorMsg]);
        }

        $mensaje = "Membresía reactivada con éxito. Vencimiento actualizado al {$membresia->fecha_fin->format('d/m/Y')}.";

        if (request()->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'membresia' => $membresia->fresh(),
            ]);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Registra el abono o cuota de un pago fraccionado.
     */
    public function registrarCuota(Request $solicitud, Membresia $membresia): RedirectResponse|JsonResponse
    {
        $solicitud->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'string', 'in:efectivo,tarjeta,transferencia,pago_movil'],
            'referencia' => ['nullable', 'string', 'max:50'],
        ]);

        $monto = (float) $solicitud->input('monto');
        $metodo = $solicitud->input('metodo_pago');
        $ref = $solicitud->input('referencia');

        $pago = $membresia->registrarPagoCuota($monto, $metodo, $ref);

        $mensaje = "Abono de \${$monto} registrado exitosamente. Saldo restante: \${$membresia->fresh()->saldo_pendiente}.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'pago' => $pago,
                'membresia' => $membresia->fresh(),
            ]);
        }

        return back()->with('success', $mensaje);
    }
}
