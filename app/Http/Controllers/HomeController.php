<?php

namespace App\Http\Controllers;

use App\Models\ClaseGrupal;
use App\Models\Plan;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Muestra la Home Page principal de FitLife Gimnasio.
     * Consulta planes activos, clases grupales destacadas y sucursales desde PostgreSQL.
     */
    public function index(Request $request): Response
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location(route('home'));
        }

        // 1. Consultar los 3 planes de membresía activos ordenados por precio ascendente
        $planes = Plan::activos()
            ->orderBy('precio', 'asc')
            ->get();

        // 2. Consultar las clases grupales activas con su profesor asignado y sucursal
        $clasesGrupales = ClaseGrupal::with(['profesor', 'sucursal'])
            ->where('activa', true)
            ->orderBy('nombre', 'asc')
            ->get();

        // 3. Consultar las 3 sucursales oficiales de FitLife
        $sucursales = Sucursal::orderBy('id', 'asc')->get();

        return response()->view('home', compact('planes', 'clasesGrupales', 'sucursales'));
    }
}
