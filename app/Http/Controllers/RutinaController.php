<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRutinaRequest;
use App\Models\Ejercicio;
use App\Models\PlantillaRutina;
use App\Models\Rutina;
use App\Models\Socio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RutinaController extends Controller
{
    /**
     * Muestra el catálogo de rutinas con sus ejercicios y plantillas disponibles.
     */
    public function index(Request $solicitud): Response
    {
        $rutinas = Rutina::with(['socio:id,nombre,apellido,codigo', 'creador:id,name', 'ejercicios'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $ejercicios = Ejercicio::orderBy('grupo_muscular')->orderBy('nombre')->get();
        $socios = Socio::activos()->get(['id', 'nombre', 'apellido', 'codigo']);
        $plantillas = PlantillaRutina::where('activa', true)->with('ejercicios')->get();

        return Inertia::render('rutinas/index', [
            'rutinas' => $rutinas,
            'ejercicios' => $ejercicios,
            'socios' => $socios,
            'plantillas' => $plantillas,
        ]);
    }

    /**
     * Registra una nueva rutina y asocia sus ejercicios con el pivote de series y descansos.
     */
    public function store(StoreRutinaRequest $solicitud): RedirectResponse|JsonResponse
    {
        $datos = $solicitud->validated();

        $rutina = Rutina::create([
            'socio_id' => $datos['socio_id'] ?? null,
            'creada_por' => auth()->id(),
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'nivel' => $datos['nivel'],
            'dias_semana' => $datos['dias_semana'] ?? null,
            'activa' => true,
        ]);

        // Asociar ejercicios con datos pivote
        $ejerciciosPivote = [];
        foreach ($datos['ejercicios'] as $indice => $item) {
            $ejerciciosPivote[$item['ejercicio_id']] = [
                'orden' => $item['orden'] ?? ($indice + 1),
                'series' => $item['series'],
                'repeticiones' => (string) $item['repeticiones'],
                'tipo_serie' => $item['tipo_serie'] ?? 'estandar',
                'descanso_segundos' => $item['descanso_segundos'],
                'rpe' => $item['rpe'] ?? null,
                'tempo' => $item['tempo'] ?? null,
                'notas' => $item['notas'] ?? null,
            ];
        }

        $rutina->ejercicios()->sync($ejerciciosPivote);

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => "Rutina '{$rutina->nombre}' guardada con éxito.",
                'rutina' => $rutina->load('ejercicios'),
            ], 201);
        }

        return redirect()->route('rutinas.index')->with('success', "Rutina '{$rutina->nombre}' guardada con éxito.");
    }

    /**
     * Muestra el detalle de una rutina.
     */
    public function show(Rutina $rutina): Response
    {
        $rutina->load(['socio:id,nombre,apellido,codigo,dni,estado', 'creador:id,name', 'ejercicios']);
        $ejercicios = Ejercicio::orderBy('grupo_muscular')->orderBy('nombre')->get();
        $socios = Socio::activos()->get(['id', 'nombre', 'apellido', 'codigo']);

        return Inertia::render('rutinas/show', [
            'rutina' => $rutina,
            'ejercicios' => $ejercicios,
            'socios' => $socios,
        ]);
    }

    /**
     * Actualiza una rutina de entrenamiento y sincroniza sus ejercicios.
     */
    public function update(StoreRutinaRequest $solicitud, Rutina $rutina): RedirectResponse|JsonResponse
    {
        $datos = $solicitud->validated();

        $rutina->update([
            'socio_id' => $datos['socio_id'] ?? null,
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'nivel' => $datos['nivel'],
            'dias_semana' => $datos['dias_semana'] ?? null,
            'activa' => $solicitud->boolean('activa', true),
        ]);

        $ejerciciosPivote = [];
        foreach ($datos['ejercicios'] as $indice => $item) {
            $ejerciciosPivote[$item['ejercicio_id']] = [
                'orden' => $item['orden'] ?? ($indice + 1),
                'series' => $item['series'],
                'repeticiones' => (string) $item['repeticiones'],
                'tipo_serie' => $item['tipo_serie'] ?? 'estandar',
                'descanso_segundos' => $item['descanso_segundos'],
                'rpe' => $item['rpe'] ?? null,
                'tempo' => $item['tempo'] ?? null,
                'notas' => $item['notas'] ?? null,
            ];
        }

        $rutina->ejercicios()->sync($ejerciciosPivote);

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => "Rutina '{$rutina->nombre}' actualizada con éxito.",
                'rutina' => $rutina->load('ejercicios'),
            ]);
        }

        return redirect()->route('rutinas.index')->with('success', "Rutina '{$rutina->nombre}' actualizada con éxito.");
    }

    /**
     * Elimina una rutina de entrenamiento.
     */
    public function destroy(Request $solicitud, Rutina $rutina): RedirectResponse|JsonResponse
    {
        $nombre = $rutina->nombre;
        $rutina->ejercicios()->detach();
        $rutina->delete();

        $mensaje = "Rutina '{$nombre}' eliminada del sistema.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
            ]);
        }

        return redirect()->route('rutinas.index')->with('success', $mensaje);
    }

    /**
     * Asigna una rutina a un socio a partir de una plantilla predeterminada.
     */
    public function asignarDesdePlantilla(Request $solicitud, Socio $socio): RedirectResponse|JsonResponse
    {
        $solicitud->validate([
            'plantilla_id' => ['required', 'exists:plantillas_rutina,id'],
        ]);

        $plantilla = PlantillaRutina::findOrFail($solicitud->input('plantilla_id'));
        $rutina = $socio->asignarRutinaDesdePlantilla($plantilla, auth()->id());

        $mensaje = "Plantilla '{$plantilla->nombre}' asignada exitosamente a {$socio->nombre_completo}.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'rutina' => $rutina->load('ejercicios'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Motor de Generación Automática de Rutina según objetivo y nivel.
     */
    public function generarAutomatica(Request $solicitud, Socio $socio): RedirectResponse|JsonResponse
    {
        $solicitud->validate([
            'objetivo' => ['required', 'string', 'in:perder_peso,ganar_musculo,fuerza,acondicionamiento,resistencia'],
            'nivel' => ['required', 'string', 'in:principiante,intermedio,avanzado'],
        ]);

        $objetivo = $solicitud->input('objetivo');
        $nivel = $solicitud->input('nivel');

        $rutina = $socio->generarRutinaAutomatica($objetivo, $nivel, auth()->id());

        $mensaje = "Rutina automatizada '{$rutina->nombre}' generada exitosamente para {$socio->nombre_completo}.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'rutina' => $rutina->load('ejercicios'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Guarda una nueva plantilla reutilizable creada por un entrenador.
     */
    public function guardarPlantilla(Request $solicitud): RedirectResponse|JsonResponse
    {
        $solicitud->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'objetivo' => ['required', 'string', 'in:perder_peso,ganar_musculo,fuerza,acondicionamiento,resistencia'],
            'nivel' => ['required', 'string', 'in:principiante,intermedio,avanzado'],
            'dias_semana' => ['nullable', 'array'],
            'ejercicios' => ['required', 'array', 'min:1'],
            'ejercicios.*.ejercicio_id' => ['required', 'exists:ejercicios,id'],
            'ejercicios.*.series' => ['required', 'integer', 'min:1', 'max:10'],
            'ejercicios.*.repeticiones' => ['required', 'string'],
            'ejercicios.*.descanso_segundos' => ['required', 'integer', 'min:10', 'max:600'],
        ]);

        $plantilla = PlantillaRutina::create([
            'nombre' => $solicitud->input('nombre'),
            'descripcion' => $solicitud->input('descripcion'),
            'objetivo' => $solicitud->input('objetivo'),
            'nivel' => $solicitud->input('nivel'),
            'dias_semana' => $solicitud->input('dias_semana', ['Lunes', 'Miércoles', 'Viernes']),
            'creada_por' => auth()->id(),
            'activa' => true,
        ]);

        foreach ($solicitud->input('ejercicios') as $idx => $item) {
            DB::table('ejercicio_plantilla')->insert([
                'plantilla_id' => $plantilla->id,
                'ejercicio_id' => $item['ejercicio_id'],
                'orden' => $item['orden'] ?? ($idx + 1),
                'series' => $item['series'],
                'repeticiones' => (string) $item['repeticiones'],
                'tipo_serie' => $item['tipo_serie'] ?? 'estandar',
                'descanso_segundos' => $item['descanso_segundos'],
                'rpe' => $item['rpe'] ?? null,
                'tempo' => $item['tempo'] ?? null,
                'notas' => $item['notas'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $mensaje = "Plantilla reutilizable '{$plantilla->nombre}' guardada con éxito.";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'plantilla' => $plantilla->load('ejercicios'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Genera una rutina automática para un socio al azar o especificado.
     */
    public function generarAutomaticaGlobal(Request $solicitud): RedirectResponse|JsonResponse
    {
        $socioId = $solicitud->input('socio_id');
        $socio = $socioId
            ? Socio::findOrFail($socioId)
            : (Socio::where('estado', 'activo')->whereDoesntHave('rutinas', fn ($q) => $q->where('activa', true))->inRandomOrder()->first()
                ?? Socio::where('estado', 'activo')->inRandomOrder()->first()
                ?? Socio::first());

        if (! $socio) {
            $errorMsg = 'No hay socios registrados para generarles rutina.';
            if ($solicitud->wantsJson()) {
                return response()->json(['exito' => false, 'mensaje' => $errorMsg], 422);
            }

            return back()->withErrors(['rutina' => $errorMsg]);
        }

        $objetivos = ['ganar_musculo', 'fuerza', 'perder_peso', 'acondicionamiento'];
        $niveles = ['principiante', 'intermedio', 'avanzado'];

        $objetivo = $solicitud->input('objetivo', $objetivos[array_rand($objetivos)]);
        $nivel = $solicitud->input('nivel', $niveles[array_rand($niveles)]);

        $rutina = $socio->generarRutinaAutomatica($objetivo, $nivel, auth()->id());

        $mensaje = "¡Rutina '{$rutina->nombre}' ({$nivel}) generada automáticamente para {$socio->nombre_completo}!";

        if ($solicitud->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => $mensaje,
                'rutina' => $rutina->load('ejercicios'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }
}
