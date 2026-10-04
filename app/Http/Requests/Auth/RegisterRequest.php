<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación que aplican a la solicitud de registro.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:30', 'unique:socios,dni'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'plan_id' => ['nullable', 'integer', 'exists:planes,id'],
            'terminos' => ['accepted'],
        ];
    }

    /**
     * Nombres de atributos personalizados para mensajes de validación.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre completo',
            'dni' => 'cédula o DNI',
            'email' => 'correo electrónico',
            'telefono' => 'número telefónico',
            'password' => 'contraseña',
            'plan_id' => 'plan de membresía',
            'terminos' => 'términos y condiciones',
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre completo es obligatorio.',
            'dni.required' => 'La cédula o DNI es obligatoria para tu ficha de socio.',
            'dni.unique' => 'Esta cédula o DNI ya se encuentra registrada en el sistema.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en FitLife.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'plan_id.exists' => 'El plan seleccionado no es válido o ya no está disponible.',
            'terminos.accepted' => 'Debes aceptar los Términos y Condiciones del gimnasio para crear tu cuenta.',
        ];
    }
}
