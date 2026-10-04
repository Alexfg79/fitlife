<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClaseGrupalRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para registrar o actualizar una clase grupal.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'profesor_id' => ['required', 'integer', 'exists:profesores,id'],
            'sucursal_id' => ['required', 'integer', 'exists:sucursales,id'],
            'dia_semana' => ['required', 'string', 'max:30'],
            'hora_inicio' => ['required', 'string', 'max:20'],
            'duracion_minutos' => ['required', 'integer', 'min:15', 'max:240'],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'sala' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'in:activa,cancelada,inactiva'],
            'activa' => ['nullable', 'boolean'],
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
            'nombre.required' => 'El nombre de la clase es obligatorio (Ej: Spinning, Yoga, Pilates, Zumba, Funcional).',
            'profesor_id.required' => 'Debe asignar un profesor a la clase.',
            'profesor_id.exists' => 'El profesor seleccionado no existe.',
            'sucursal_id.required' => 'Debe asignar una sucursal a la clase.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'dia_semana.required' => 'El día de la semana es obligatorio.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'duracion_minutos.required' => 'La duración en minutos es obligatoria.',
            'cupo_maximo.required' => 'El cupo máximo de alumnos es obligatorio.',
            'cupo_maximo.min' => 'El cupo mínimo debe ser al menos 1 alumno.',
        ];
    }
}
