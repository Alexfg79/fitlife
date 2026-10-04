<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMembresiaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para suscripción o renovación de membresía.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'socio_id' => ['required', 'exists:socios,id'],
            'plan_id' => ['required', 'exists:planes,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'precio_pagado' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'string', 'in:efectivo,tarjeta,transferencia'],
            'notas' => ['nullable', 'string', 'max:500'],
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
            'socio_id.required' => 'El socio es obligatorio.',
            'socio_id.exists' => 'El socio seleccionado no existe.',
            'plan_id.required' => 'Debe seleccionar un plan de membresía.',
            'plan_id.exists' => 'El plan seleccionado no es válido.',
            'metodo_pago.required' => 'Debe especificar el método de pago.',
        ];
    }
}
