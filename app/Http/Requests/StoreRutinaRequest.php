<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRutinaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para rutinas de entrenamiento.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'socio_id' => ['nullable', 'exists:socios,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'nivel' => ['required', 'in:principiante,intermedio,avanzado'],
            'dias_semana' => ['nullable', 'array'],
            'dias_semana.*' => ['string'],
            'ejercicios' => ['required', 'array', 'min:1'],
            'ejercicios.*.ejercicio_id' => ['required', 'exists:ejercicios,id'],
            'ejercicios.*.orden' => ['nullable', 'integer', 'min:1'],
            'ejercicios.*.series' => ['required', 'integer', 'min:1', 'max:20'],
            'ejercicios.*.repeticiones' => ['required', 'string', 'max:20'],
            'ejercicios.*.descanso_segundos' => ['required', 'integer', 'min:0', 'max:600'],
            'ejercicios.*.notas' => ['nullable', 'string', 'max:200'],
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
            'nombre.required' => 'El nombre de la rutina es obligatorio.',
            'nivel.required' => 'El nivel de dificultad es obligatorio.',
            'ejercicios.required' => 'Debe agregar al menos un ejercicio a la rutina.',
            'ejercicios.min' => 'Debe agregar al menos un ejercicio a la rutina.',
        ];
    }
}
