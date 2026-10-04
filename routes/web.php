<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ClaseGrupalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InscripcionClaseController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\PortalSocioController;
use App\Http\Controllers\RutinaController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    // Portal Personal del Socio
    Route::get('mi-perfil', [PortalSocioController::class, 'miPerfil'])->name('mi-perfil');

    // Panel de Control Principal (Dashboard con KPIs y Accesos Rápidos)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Socios
    Route::post('socios/generar-automatico', [SocioController::class, 'generarAutomatico'])->name('socios.generar-automatico');
    Route::resource('socios', SocioController::class);

    // Módulo de Asistencias y Registro Biométrico / Código / Kiosco
    Route::get('asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
    Route::get('asistencias/kiosco', [AsistenciaController::class, 'kiosco'])->name('asistencias.kiosco');
    Route::post('asistencias/acceso', [AsistenciaController::class, 'registrarAcceso'])->name('asistencias.acceso');
    Route::post('asistencias/escanear', [AsistenciaController::class, 'escanearAccesoRapido'])->name('asistencias.escanear');
    Route::post('asistencias/check-out', [AsistenciaController::class, 'registrarSalida'])->name('asistencias.checkout');
    Route::post('asistencias/generar-automatica', [AsistenciaController::class, 'generarAutomatica'])->name('asistencias.generar-automatica');
    Route::put('asistencias/{asistencia}', [AsistenciaController::class, 'update'])->name('asistencias.update');
    Route::delete('asistencias/{asistencia}', [AsistenciaController::class, 'destroy'])->name('asistencias.destroy');

    // Módulo de Membresías (Suscripción, Renovación, Congelamiento y Pagos de Cuotas)
    Route::post('membresias', [MembresiaController::class, 'store'])->name('membresias.store');
    Route::post('membresias/{membresia}/congelar', [MembresiaController::class, 'congelar'])->name('membresias.congelar');
    Route::post('membresias/{membresia}/reanudar', [MembresiaController::class, 'reanudar'])->name('membresias.reanudar');
    Route::post('membresias/{membresia}/cuotas', [MembresiaController::class, 'registrarCuota'])->name('membresias.cuotas');

    // Clases Grupales e Inscripciones
    Route::resource('clases', ClaseGrupalController::class);
    Route::post('clases/{clase}/inscribir', [InscripcionClaseController::class, 'inscribir'])->name('clases.inscribir');
    Route::patch('inscripciones-clases/{inscripcion}/confirmar', [InscripcionClaseController::class, 'confirmarAsistencia'])->name('clases.inscripciones.confirmar');
    Route::delete('inscripciones-clases/{inscripcion}', [InscripcionClaseController::class, 'destroy'])->name('clases.inscripciones.destroy');
    Route::get('clases/{clase}/inscripciones', [InscripcionClaseController::class, 'index'])->name('clases.inscripciones.index');

    // Módulo de Rutinas y Ejercicios (Plantillas y Generador Automático)
    Route::resource('rutinas', RutinaController::class);
    Route::post('rutinas/generar-automatica', [RutinaController::class, 'generarAutomaticaGlobal'])->name('rutinas.generar-automatica');
    Route::post('socios/{socio}/rutinas/plantilla', [RutinaController::class, 'asignarDesdePlantilla'])->name('socios.rutinas.plantilla');
    Route::post('socios/{socio}/rutinas/generar', [RutinaController::class, 'generarAutomatica'])->name('socios.rutinas.generar');
    Route::post('rutinas/plantillas', [RutinaController::class, 'guardarPlantilla'])->name('rutinas.plantillas.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
