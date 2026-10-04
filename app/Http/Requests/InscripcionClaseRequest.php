<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InscripcionClaseRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la inscripción a clases grupales.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'socio_id' => ['required', 'integer', 'exists:socios,id'],
            'clase_grupal_id' => ['nullable', 'integer', 'exists:clases_grupales,id'],
            'fecha_clase' => ['required', 'date'],
        ];
    }

    /**
     * Mensajes personalizados de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'socio_id.required' => 'Debe seleccionar un socio para realizar la inscripción.',
            'socio_id.exists' => 'El socio seleccionado no existe en el sistema.',
            'fecha_clase.required' => 'Debe especificar la fecha de la clase.',
            'fecha_clase.date' => 'La fecha de la clase no tiene un formato válido.',
            'clase_grupal_id.exists' => 'La clase grupal seleccionada no existe.',
        ];
    }
}
