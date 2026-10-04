<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la pantalla de inicio de sesión y registro unificada.
     */
    public function create(Request $request): Response
    {
        $planes = Plan::activos()->get(['id', 'nombre', 'precio', 'duracion_dias', 'tipo']);

        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'planes' => $planes,
            'initialTab' => $request->input('tab', 'login'),
            'planSeleccionado' => $request->input('plan'),
        ]);
    }

    /**
     * Procesa la solicitud de autenticación con redirección inteligente por rol.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Redirección inteligente: socios van a su portal, admins/entrenadores al dashboard
        if ($user && $user->esSocio()) {
            return redirect()->intended(route('mi-perfil', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cierra la sesión activa y redirige al Home Page.
     * Utiliza Inertia::location para forzar una redirección limpia a nivel de navegador
     * y evitar que la página de inicio se abra dentro de un modal/ventana de Inertia.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('home'));
    }
}
