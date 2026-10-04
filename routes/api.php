<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\InscripcionClaseController;
use App\Http\Controllers\RutinaController;
use App\Http\Controllers\SocioController;
use App\Models\ClaseGrupal;
use App\Models\Socio;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API para Terminales Biométricos y Lectores de Acceso FitLife
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Endpoint para lector QR / Biométrico / Torniquete
    Route::post('/acceso', [AsistenciaController::class, 'registrarAcceso'])
        ->name('api.asistencia.acceso');

    // Endpoint de consulta rápida de socio por código para el terminal
    Route::get('/socios/verificar/{codigo}', function (string $codigo) {
        $socio = Socio::with(['membresiaActiva.plan', 'ultimaMembresia.plan'])
            ->where('codigo', $codigo)
            ->orWhere('dni', $codigo)
            ->first();

        if (! $socio) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'Socio no registrado.',
            ], 404);
        }

        return response()->json([
            'encontrado' => true,
            'socio' => [
                'id' => $socio->id,
                'nombre_completo' => $socio->nombre_completo,
                'codigo' => $socio->codigo,
                'dni' => $socio->dni,
                'estado' => $socio->estado,
                'tiene_acceso' => $socio->tieneAccesoValido(),
                'plan' => $socio->membresiaActiva?->plan?->nombre,
                'dias_restantes' => $socio->membresiaActiva?->diasRestantes() ?? 0,
            ],
        ]);
    })->name('api.socios.verificar');
});

// Endpoint rápido de validación y registro automático (<200ms)
Route::post('/asistencias/escanear', [AsistenciaController::class, 'escanearAccesoRapido'])
    ->name('api.asistencias.escanear');

// Endpoint de marcaje de salida (Check-out)
Route::post('/asistencias/check-out', [AsistenciaController::class, 'registrarSalida'])
    ->name('api.asistencias.checkout');

// Listado de clases grupales activas con aforo en tiempo real
Route::get('/clases-grupales', function () {
    $clases = ClaseGrupal::where('activa', true)->get()->map(function ($c) {
        return [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'horario' => $c->horario,
            'sala' => $c->sala,
            'cupo_maximo' => $c->cupo_maximo,
            'cupos_disponibles' => $c->cuposDisponiblesHoy(),
        ];
    });

    return response()->json(['clases' => $clases]);
})->name('api.clases.index');

// Inscripción a clases grupales
Route::post('/clases/{clase}/inscribir', [InscripcionClaseController::class, 'store'])->name('api.clases.inscribir');
Route::get('/clases/{clase}/inscripciones', [InscripcionClaseController::class, 'index'])->name('api.clases.inscripciones');

// Endpoints de generación rápida automatizada en los 3 módulos
Route::post('/socios/generar-automatico', [SocioController::class, 'generarAutomatico']);
Route::post('/asistencias/generar-automatica', [AsistenciaController::class, 'generarAutomatica']);
Route::post('/rutinas/generar-automatica', [RutinaController::class, 'generarAutomaticaGlobal']);
