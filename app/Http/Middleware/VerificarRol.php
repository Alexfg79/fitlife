<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Maneja una solicitud entrante y verifica si el usuario autenticado tiene alguno de los roles permitidos.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $solicitud, Closure $next, string ...$rolesPermitidos): Response
    {
        $usuario = $solicitud->user();

        if (! $usuario) {
            abort(Response::HTTP_UNAUTHORIZED, 'No autenticado.');
        }

        // Si el usuario es administrador, tiene acceso completo
        if ($usuario->esAdmin()) {
            return $next($solicitud);
        }

        $rolUsuario = $usuario->rol?->nombre;

        if (! $rolUsuario || ! in_array($rolUsuario, $rolesPermitidos, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Acceso denegado: No cuentas con los permisos necesarios.');
        }

        return $next($solicitud);
    }
}
