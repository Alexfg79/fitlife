<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarAsistenciaRequest;
use App\Models\Asistencia;
use App\Models\ClaseGrupal;
use App\Models\Socio;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsistenciaController extends Controller
{
    /**
     * Muestra el registro histórico de asistencias con filtros.
     */
    public function index(Request $solicitud): Response
    {
        $socioId = $solicitud->input('socio_id');
        $fecha = $solicitud->input('fecha');
        $busqueda = $solicitud->input('buscar');
        $estado = $solicitud->input('estado');

        $asistencias = Asistencia::with([
            'socio:id,nombre,apellido,dni,codigo,foto',
            'registradoPor:id,name',
            'clase:id,nombre,sala',
        ])
            ->when($socioId, function ($consulta) use ($socioId) {
                $consulta->where('socio_id', $socioId);
            })
            ->when($fecha, function ($consulta) use ($fecha) {
                $consulta->whereDate('fecha_hora', $fecha);
            })
            ->when($estado, function ($consulta) use ($estado) {
                $consulta->where('estado_acceso', $estado);
            })
            ->when($busqueda, function ($consulta) use ($busqueda) {
                $consulta->whereHas('socio', function ($sub) use ($busqueda) {
                    $sub->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellido', 'like', "%{$busqueda}%")
                        ->orWhere('dni', 'like', "%{$busqueda}%")
                        ->orWhere('codigo', 'like', "%{$busqueda}%");
                });
            })
            ->latest('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        $clases = ClaseGrupal::where('activa', true)->get();
        $socioSeleccionado = $socioId ? Socio::find($socioId, ['id', 'nombre', 'apellido', 'codigo', 'dni']) : null;

        return Inertia::render('asistencias/index', [
            'asistencias' => $asistencias,
            'clases' => $clases,
            'socio_seleccionado' => $socioSeleccionado,
            'filtros' => [
                'fecha' => $fecha,
                'buscar' => $busqueda,
                'estado' => $estado,
                'socio_id' => $socioId,
            ],
            'total_hoy' => Asistencia::hoy()->permitidas()->count(),
            'denegadas_hoy' => Asistencia::hoy()->denegadas()->count(),
            'en_sala_ahora' => Asistencia::hoy()->permitidas()->abiertas()->count(),
        ]);
    }

    /**
     * Muestra la interfaz de autoservicio o Modo Kiosco para tablets de recepción.
     */
    public function kiosco(): Response
    {
        $clasesHoy = ClaseGrupal::where('activa', true)
            ->get()
            ->map(function (ClaseGrupal $clase) {
                return [
                    'id' => $clase->id,
                    'nombre' => $clase->nombre,
                    'horario' => $clase->horario,
                    'sala' => $clase->sala,
                    'cupo_maximo' => $clase->cupo_maximo,
                    'cupos_disponibles' => $clase->cuposDisponiblesHoy(),
                ];
            });

        return Inertia::render('asistencias/kiosco', [
            'clases' => $clasesHoy,
        ]);
    }

    /**
     * Endpoint API de alto rendimiento (< 200ms) para validación y registro automático de escaneo.
     * Soporta QR, código de barras, modo kiosco, check-in, check-out y clases grupales.
     */
    public function escanearAccesoRapido(Request $solicitud): JsonResponse
    {
        $inicio = microtime(true);

        $codigo = $solicitud->input('codigo');
        $dni = $solicitud->input('dni');
        $socioId = $solicitud->input('socio_id');
        $metodo = $solicitud->input('metodo', 'qr');
        $tipoAcceso = $solicitud->input('tipo_acceso', 'general');
        $claseId = $solicitud->input('clase_id');

        if (! $codigo && ! $dni && ! $socioId) {
            return response()->json([
                'exito' => false,
                'estado_acceso' => 'denegado_invalido',
                'mensaje' => 'Debe suministrar un código QR, DNI o identificador válido.',
            ], 422);
        }

        // Búsqueda ultra rápida con índices compuestos
        $socio = Socio::with(['membresiaActiva.plan', 'congelamientos' => fn ($q) => $q->where('estado', 'activo')])
            ->when($codigo, fn ($q) => $q->where('codigo', $codigo))
            ->when($dni, fn ($q) => $q->orWhere('dni', $dni))
            ->when($socioId, fn ($q) => $q->orWhere('id', $socioId))
            ->first();

        if (! $socio) {
            return response()->json([
                'exito' => false,
                'estado_acceso' => 'no_encontrado',
                'mensaje' => 'Socio no registrado en la base de datos.',
                'tiempo_ms' => round((microtime(true) - $inicio) * 1000, 2),
            ], 404);
        }

        // 1. Verificación automática de salida (Check-out): Si el socio ya tiene una entrada abierta hoy, registrar SALIDA
        $asistenciaAbierta = $socio->asistenciaAbiertaHoy();
        if ($asistenciaAbierta) {
            $asistenciaAbierta->registrarSalida();
            $tiempoMinutos = $asistenciaAbierta->tiempo_permanencia_minutos;

            $mensajeSalida = "Salida registrada exitosamente para {$socio->nombre_completo}. Tiempo de permanencia: {$tiempoMinutos} minutos.";
            $tiempoEjecucionMs = round((microtime(true) - $inicio) * 1000, 2);

            $respuestaSalida = [
                'exito' => true,
                'tipo_evento' => 'salida',
                'estado_acceso' => 'salida_registrada',
                'mensaje' => $mensajeSalida,
                'asistencia_id' => $asistenciaAbierta->id,
                'hora_salida' => $asistenciaAbierta->fecha_hora_salida->format('H:i:s'),
                'minutos_permanencia' => $tiempoMinutos,
                'tiempo_respuesta_ms' => $tiempoEjecucionMs,
                'socio' => [
                    'id' => $socio->id,
                    'nombre_completo' => $socio->nombre_completo,
                    'codigo' => $socio->codigo,
                    'dni' => $socio->dni,
                    'foto' => $socio->foto,
                    'plan' => $socio->membresiaActiva?->plan?->nombre ?? 'Sin plan activo',
                ],
            ];

            return response()->json($respuestaSalida, 200);
        }

        // 2. Si no tiene entrada abierta, procesar marcaje de ENTRADA (Check-in)
        $membresiaActiva = $socio->membresiaActiva;
        $estadoAcceso = 'permitido';
        $mensaje = '';

        // Regla estricta: Bloqueo de acceso por morosidad (2 o más cuotas vencidas)
        if ($socio->estado !== 'activo') {
            $estadoAcceso = 'denegado_inactivo';
            $mensaje = "Acceso Denegado: Socio en estado '{$socio->estado}'. Favor pasar por recepción.";
        } elseif ($socio->esMorosoBloqueado()) {
            $estadoAcceso = 'denegado_moroso';
            $cuotas = $socio->cuotasVencidasCount();
            $mensaje = "Acceso Denegado: El socio presenta {$cuotas} cuotas vencidas. Por favor pase por recepción para regularizar su deuda.";
        } elseif (! $membresiaActiva) {
            $ultima = $socio->ultimaMembresia;
            if ($ultima && $ultima->esPorSesiones() && ($ultima->sesiones_restantes ?? 0) === 0) {
                $estadoAcceso = 'denegado_sesiones_agotadas';
                $mensaje = 'Acceso Denegado: Has agotado las sesiones disponibles de tu paquete.';
            } else {
                $estadoAcceso = 'denegado_vencido';
                $mensaje = $ultima
                    ? "Acceso Denegado: Membresía vencida el {$ultima->fecha_fin->format('d/m/Y')}."
                    : 'Acceso Denegado: El socio no posee ninguna membresía registrada.';
            }
        } elseif ($membresiaActiva->plan && ! $membresiaActiva->plan->estaEnHorarioPermitido()) {
            $estadoAcceso = 'denegado_horario';
            $mensaje = 'Acceso Denegado: Su Plan Básico solo permite acceso de Lunes a Viernes entre 08:00 y 18:00 hrs.';
        } elseif ($membresiaActiva->estaCongelada()) {
            $estadoAcceso = 'denegado_congelada';
            $congelamiento = $membresiaActiva->congelamientoActivo;
            $mensaje = "Acceso Denegado: Tu membresía está pausada temporalmente por motivo de {$congelamiento?->motivo}.";
        } elseif ($membresiaActiva->esPorSesiones() && ! $membresiaActiva->tieneSesionesDisponibles()) {
            $estadoAcceso = 'denegado_sesiones_agotadas';
            $mensaje = 'Acceso Denegado: Has agotado las sesiones disponibles de tu paquete.';
        }

        // Validación de clase grupal si aplica
        $clase = null;
        if ($estadoAcceso === 'permitido' && $tipoAcceso === 'clase_grupal' && $claseId) {
            $clase = ClaseGrupal::find($claseId);
            if ($clase && ! $clase->tieneCupoDisponible()) {
                $estadoAcceso = 'denegado_cupo_lleno';
                $mensaje = "Acceso Denegado: La clase '{$clase->nombre}' alcanzó su cupo máximo ({$clase->cupo_maximo} atletas).";
            }
        }

        $descuentoSesion = false;
        if ($estadoAcceso === 'permitido') {
            if ($membresiaActiva->esPorSesiones()) {
                $membresiaActiva->descontarSesion();
                $descuentoSesion = true;
                $mensaje = "Acceso Autorizado: Sesión descontada. Te quedan {$membresiaActiva->sesiones_restantes} sesiones.";
            } else {
                $diasRestantes = $membresiaActiva->diasRestantes();
                $claseTxt = $clase ? " | Clase: {$clase->nombre}" : '';
                $mensaje = "Acceso Autorizado: ¡Bienvenido/a {$socio->nombre_completo}! ({$membresiaActiva->plan?->nombre}, {$diasRestantes} días vigentes{$claseTxt}).";
            }
        }

        // Registrar registro de asistencia (Check-in o Denegada)
        $ahora = Carbon::now();
        $asistencia = Asistencia::create([
            'socio_id' => $socio->id,
            'sucursal_id' => $solicitud->input('sucursal_id') ?? auth()->user()?->sucursal_id ?? Sucursal::value('id'),
            'registrado_por' => auth()->id(),
            'clase_id' => $clase?->id,
            'fecha_hora' => $ahora,
            'fecha_hora_entrada' => $ahora,
            'fecha_hora_salida' => $estadoAcceso === 'permitido' ? null : $ahora,
            'tiempo_permanencia_minutos' => $estadoAcceso === 'permitido' ? null : 0,
            'metodo' => $metodo,
            'tipo_acceso' => $tipoAcceso,
            'estado_acceso' => $estadoAcceso,
            'mensaje_acceso' => $mensaje,
        ]);

        $tiempoEjecucionMs = round((microtime(true) - $inicio) * 1000, 2);

        $respuesta = [
            'exito' => $estadoAcceso === 'permitido',
            'tipo_evento' => 'entrada',
            'estado_acceso' => $estadoAcceso,
            'mensaje' => $mensaje,
            'asistencia_id' => $asistencia->id,
            'hora_entrada' => $asistencia->fecha_hora_entrada->format('H:i:s'),
            'tiempo_respuesta_ms' => $tiempoEjecucionMs,
            'socio' => [
                'id' => $socio->id,
                'nombre_completo' => $socio->nombre_completo,
                'codigo' => $socio->codigo,
                'dni' => $socio->dni,
                'foto' => $socio->foto,
                'plan' => $membresiaActiva?->plan?->nombre ?? 'Sin plan activo',
                'vence' => $membresiaActiva?->fecha_fin->format('d/m/Y') ?? 'Vencida',
                'es_por_sesiones' => $membresiaActiva?->esPorSesiones() ?? false,
                'sesiones_restantes' => $membresiaActiva?->sesiones_restantes ?? null,
                'dias_restantes' => $membresiaActiva?->diasRestantes() ?? 0,
            ],
        ];

        return response()->json($respuesta, $estadoAcceso === 'permitido' ? 200 : 422);
    }

    /**
     * Procesa el registro rápido de acceso vía formulario web / Inertia.
     */
    public function registrarAcceso(RegistrarAsistenciaRequest $solicitud): JsonResponse|RedirectResponse
    {
        $codigo = $solicitud->input('codigo');
        $dni = $solicitud->input('dni');
        $socioId = $solicitud->input('socio_id');
        $metodo = $solicitud->input('metodo', 'codigo');
        $tipoAcceso = $solicitud->input('tipo_acceso', 'general');
        $claseId = $solicitud->input('clase_id');

        // Búsqueda del socio
        $socio = Socio::with(['membresiaActiva.plan', 'congelamientos' => fn ($q) => $q->where('estado', 'activo')])
            ->when($codigo, fn ($q) => $q->where('codigo', $codigo))
            ->when($dni, fn ($q) => $q->orWhere('dni', $dni))
            ->when($socioId, fn ($q) => $q->orWhere('id', $socioId))
            ->first();

        if (! $socio) {
            if ($solicitud->wantsJson()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Socio no encontrado con el código o documento provisto.',
                ], 404);
            }

            return back()->withErrors([
                'codigo' => 'No se encontró ningún socio con el código o DNI provisto.',
            ]);
        }

        // 1. Verificación automática de salida (Check-out)
        $asistenciaAbierta = $socio->asistenciaAbiertaHoy();
        if ($asistenciaAbierta) {
            $asistenciaAbierta->registrarSalida();
            $tiempoMinutos = $asistenciaAbierta->tiempo_permanencia_minutos;

            $mensajeSalida = "Salida registrada exitosamente para {$socio->nombre_completo}. Tiempo de permanencia: {$tiempoMinutos} minutos.";

            $respuestaSalida = [
                'exito' => true,
                'tipo_evento' => 'salida',
                'estado_acceso' => 'salida_registrada',
                'mensaje' => $mensajeSalida,
                'asistencia_id' => $asistenciaAbierta->id,
                'hora_salida' => $asistenciaAbierta->fecha_hora_salida->format('H:i:s'),
                'minutos_permanencia' => $tiempoMinutos,
                'socio' => [
                    'id' => $socio->id,
                    'nombre_completo' => $socio->nombre_completo,
                    'codigo' => $socio->codigo,
                    'dni' => $socio->dni,
                    'foto' => $socio->foto,
                    'plan' => $socio->membresiaActiva?->plan?->nombre ?? 'Sin plan activo',
                ],
            ];

            if ($solicitud->wantsJson()) {
                return response()->json($respuestaSalida, 200);
            }

            return back()->with('resultado_acceso', $respuestaSalida);
        }

        // 2. Registro de Entrada (Check-in)
        $membresiaActiva = $socio->membresiaActiva;
        $estadoAcceso = 'permitido';
        $mensaje = '';

        if ($socio->estado !== 'activo') {
            $estadoAcceso = 'denegado_inactivo';
            $mensaje = "Acceso Denegado: El socio se encuentra en estado '{$socio->estado}'.";
        } elseif ($socio->esMorosoBloqueado()) {
            $estadoAcceso = 'denegado_moroso';
            $cuotas = $socio->cuotasVencidasCount();
            $mensaje = "Acceso Denegado: El socio presenta {$cuotas} cuotas vencidas. Por favor pase por recepción para regularizar su deuda.";
        } elseif (! $membresiaActiva) {
            $estadoAcceso = 'denegado_vencido';
            $ultima = $socio->ultimaMembresia;
            $mensaje = $ultima
                ? "Acceso Denegado: Membresía vencida el {$ultima->fecha_fin->format('d/m/Y')}."
                : 'Acceso Denegado: El socio no posee ninguna membresía registrada.';
        } elseif ($membresiaActiva->plan && ! $membresiaActiva->plan->estaEnHorarioPermitido()) {
            $estadoAcceso = 'denegado_horario';
            $mensaje = 'Acceso Denegado: Su Plan Básico solo permite acceso de Lunes a Viernes entre 08:00 y 18:00 hrs.';
        } elseif ($membresiaActiva->estaCongelada()) {
            $estadoAcceso = 'denegado_congelada';
            $mensaje = 'Acceso Denegado: La membresía se encuentra pausada o congelada.';
        } elseif ($membresiaActiva->esPorSesiones() && ! $membresiaActiva->tieneSesionesDisponibles()) {
            $estadoAcceso = 'denegado_sesiones_agotadas';
            $mensaje = 'Acceso Denegado: Paquete de sesiones agotado.';
        }

        $clase = null;
        if ($estadoAcceso === 'permitido' && $tipoAcceso === 'clase_grupal' && $claseId) {
            $clase = ClaseGrupal::find($claseId);
            if ($clase && ! $clase->tieneCupoDisponible()) {
                $estadoAcceso = 'denegado_cupo_lleno';
                $mensaje = "Acceso Denegado: Cupo completo para la clase '{$clase->nombre}'.";
            }
        }

        if ($estadoAcceso === 'permitido' && $membresiaActiva->esPorSesiones()) {
            $membresiaActiva->descontarSesion();
            $mensaje = "Acceso Permitido: Sesión descontada. Restan: {$membresiaActiva->sesiones_restantes} sesiones.";
        } elseif ($estadoAcceso === 'permitido') {
            $diasRestantes = $membresiaActiva->diasRestantes();
            $mensaje = "Acceso Permitido: Bienvenido/a {$socio->nombre_completo}. Plan: {$membresiaActiva->plan?->nombre} ({$diasRestantes} días restantes).";
        }

        $ahora = Carbon::now();
        // Registrar asistencia
        $asistencia = Asistencia::create([
            'socio_id' => $socio->id,
            'sucursal_id' => $solicitud->input('sucursal_id') ?? auth()->user()?->sucursal_id ?? Sucursal::value('id'),
            'registrado_por' => auth()->id(),
            'clase_id' => $clase?->id,
            'fecha_hora' => $ahora,
            'fecha_hora_entrada' => $ahora,
            'fecha_hora_salida' => $estadoAcceso === 'permitido' ? null : $ahora,
            'tiempo_permanencia_minutos' => $estadoAcceso === 'permitido' ? null : 0,
            'metodo' => $metodo,
            'tipo_acceso' => $tipoAcceso,
            'estado_acceso' => $estadoAcceso,
            'mensaje_acceso' => $mensaje,
        ]);

        $respuesta = [
            'exito' => $estadoAcceso === 'permitido',
            'tipo_evento' => 'entrada',
            'estado_acceso' => $estadoAcceso,
            'mensaje' => $mensaje,
            'socio' => [
                'id' => $socio->id,
                'nombre_completo' => $socio->nombre_completo,
                'codigo' => $socio->codigo,
                'dni' => $socio->dni,
                'foto' => $socio->foto,
                'estado' => $socio->estado,
                'plan' => $membresiaActiva?->plan?->nombre ?? 'Sin plan activo',
                'vence' => $membresiaActiva?->fecha_fin->format('d/m/Y') ?? 'Vencida',
                'sesiones_restantes' => $membresiaActiva?->sesiones_restantes ?? null,
                'dias_restantes' => $membresiaActiva?->diasRestantes() ?? 0,
            ],
            'asistencia_id' => $asistencia->id,
            'fecha_hora' => $asistencia->fecha_hora_entrada->format('H:i:s'),
        ];

        if ($solicitud->wantsJson()) {
            return response()->json($respuesta, $estadoAcceso === 'permitido' ? 200 : 422);
        }

        return back()->with('resultado_acceso', $respuesta);
    }

    /**
     * Registra la salida (Check-out) de un socio y calcula su tiempo de permanencia.
     */
    public function registrarSalida(Request $solicitud): JsonResponse|RedirectResponse
    {
        $asistenciaId = $solicitud->input('asistencia_id');
        $socioId = $solicitud->input('socio_id');
        $codigo = $solicitud->input('codigo');
        $dni = $solicitud->input('dni');

        $asistencia = null;

        if ($asistenciaId) {
            $asistencia = Asistencia::find($asistenciaId);
        } elseif ($socioId) {
            $asistencia = Asistencia::where('socio_id', $socioId)->hoy()->abiertas()->latest('fecha_hora')->first();
        } elseif ($codigo || $dni) {
            $socio = Socio::when($codigo, fn ($q) => $q->where('codigo', $codigo))
                ->when($dni, fn ($q) => $q->orWhere('dni', $dni))
                ->first();

            if ($socio) {
                $asistencia = $socio->asistenciaAbiertaHoy();
            }
        }

        if (! $asistencia) {
            if ($solicitud->wantsJson()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'No se encontró un registro de ingreso abierto hoy para marcar salida.',
                ], 404);
            }

            return back()->withErrors([
                'salida' => 'No se encontró un registro de ingreso activo para marcar la salida.',
            ]);
        }

        $asistencia->registrarSalida();
        $minutos = $asistencia->tiempo_permanencia_minutos;

        $respuesta = [
            'exito' => true,
            'mensaje' => "Salida registrada con éxito. Tiempo de permanencia: {$minutos} minutos.",
            'asistencia_id' => $asistencia->id,
            'hora_salida' => $asistencia->fecha_hora_salida?->format('H:i:s') ?? Carbon::now()->format('H:i:s'),
            'tiempo_permanencia_minutos' => $minutos,
            'socio' => [
                'id' => $asistencia->socio_id,
                'nombre_completo' => $asistencia->socio?->nombre_completo,
            ],
        ];

        if ($solicitud->wantsJson()) {
            return response()->json($respuesta, 200);
        }

        return back()->with('resultado_salida', $respuesta);
    }

    /**
     * Registra automáticamente la asistencia de un socio al azar para simulación y pruebas rápidas.
     */
    public function generarAutomatica(Request $solicitud): RedirectResponse|JsonResponse
    {
        $socio = Socio::where('estado', 'activo')
            ->whereHas('membresiaActiva')
            ->inRandomOrder()
            ->first();

        if (! $socio) {
            $socio = Socio::where('estado', 'activo')->first() ?? Socio::first();
        }

        if (! $socio) {
            $errorMsg = 'No hay socios registrados para simular asistencias.';
            if ($solicitud->wantsJson()) {
                return response()->json(['exito' => false, 'mensaje' => $errorMsg], 422);
            }

            return back()->withErrors(['asistencia' => $errorMsg]);
        }

        $clase = ClaseGrupal::where('activa', true)->inRandomOrder()->first();
        $tipoAcceso = ($clase && rand(0, 1) === 1) ? 'clase_grupal' : 'general';

        $solicitudSimulada = new Request([
            'codigo' => $socio->codigo,
            'metodo' => 'qr',
            'tipo_acceso' => $tipoAcceso,
            'clase_id' => $tipoAcceso === 'clase_grupal' ? $clase?->id : null,
        ]);

        $res = $this->escanearAccesoRapido($solicitudSimulada);
        $data = $res->getData(true);

        if ($solicitud->wantsJson()) {
            return response()->json($data, $res->getStatusCode());
        }

        return back()->with('success', $data['mensaje'] ?? "Asistencia registrada automáticamente para {$socio->nombre_completo}.");
    }

    /**
     * Actualiza un registro de asistencia (hora de salida, estado o notas de auditoría).
     */
    public function update(Request $solicitud, Asistencia $asistencia): RedirectResponse|JsonResponse
    {
        $datos = $solicitud->validate([
            'fecha_hora' => ['nullable', 'date'],
            'fecha_hora_salida' => ['nullable', 'date'],
            'estado_acceso' => ['required', 'string', 'in:permitido,denegado_vencido,denegado_inactivo,denegado_cupo'],
            'mensaje_acceso' => ['nullable', 'string', 'max:255'],
            'tiempo_permanencia_minutos' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($datos['fecha_hora_salida']) && $datos['fecha_hora_salida']) {
            $salida = Carbon::parse($datos['fecha_hora_salida']);
            $entrada = isset($datos['fecha_hora']) ? Carbon::parse($datos['fecha_hora']) : $asistencia->fecha_hora;
            if (! isset($datos['tiempo_permanencia_minutos'])) {
                $datos['tiempo_permanencia_minutos'] = max(0, (int) $entrada->diffInMinutes($salida, false));
            }
        }

        $asistencia->update($datos);

        $mensaje = 'Registro de asistencia actualizado exitosamente.';

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'asistencia' => $asistencia->fresh(['socio', 'registradoPor']),
            ]);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Elimina un registro de asistencia erróneo o duplicado.
     */
    public function destroy(Request $solicitud, Asistencia $asistencia): RedirectResponse|JsonResponse
    {
        $asistencia->delete();

        $mensaje = 'Registro de asistencia eliminado del historial.';

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }
}
