<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarAsistenciaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para registro de accesos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo' => ['required_without_all:socio_id,dni', 'nullable', 'string', 'max:50'],
            'dni' => ['required_without_all:codigo,socio_id', 'nullable', 'string', 'max:20'],
            'socio_id' => ['required_without_all:codigo,dni', 'nullable', 'exists:socios,id'],
            'metodo' => ['nullable', 'string', 'in:qr,codigo,biometrico,manual,kiosco'],
            'tipo_acceso' => ['nullable', 'string', 'in:general,clase_grupal'],
            'clase_id' => ['nullable', 'exists:clases_grupales,id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.required_without_all' => 'Debe ingresar el código QR, DNI o ID del socio para registrar la asistencia.',
            'metodo.in' => 'El método de acceso seleccionado no es válido.',
        ];
    }
}
