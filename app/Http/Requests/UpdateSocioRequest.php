<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocioRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $socioId = $this->route('socio')?->id ?? $this->route('socio');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', Rule::unique('socios', 'dni')->ignore($socioId)],
            'correo' => ['required', 'email', 'max:150', Rule::unique('socios', 'correo')->ignore($socioId)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'estado' => ['required', 'in:activo,inactivo,moroso'],
            'observaciones_medicas' => ['nullable', 'string', 'max:1000'],
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
            'nombre.required' => 'El nombre del socio es obligatorio.',
            'apellido.required' => 'El apellido del socio es obligatorio.',
            'dni.required' => 'El número de DNI es obligatorio.',
            'dni.unique' => 'Ya existe un socio registrado con este DNI.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El formato del correo electrónico no es válido.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
