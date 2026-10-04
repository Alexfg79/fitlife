<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalSocioController extends Controller
{
    /**
     * Muestra el portal personal del socio autenticado (credencial digital, membresía, rutinas y asistencias).
     */
    public function miPerfil(Request $request): Response|RedirectResponse
    {
        $usuario = $request->user();
        $socio = $usuario->socio;

        // Si el usuario no tiene ficha de socio (ej. es Admin o Entrenador puro), redirigir al dashboard
        if (! $socio) {
            return redirect()->route('dashboard')->with('info', 'Acceso exclusivo para cuentas de socio.');
        }

        $socio->load([
            'membresiaActiva.plan',
            'ultimaMembresia.plan',
            'rutinas.ejercicios',
            'asistencias' => fn ($q) => $q->latest('fecha_hora')->take(10),
        ]);

        return Inertia::render('socios/mi-perfil', [
            'socio' => $socio,
        ]);
    }
}
