<?php

namespace Database\Factories;

use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Socio>
 */
class SocioFactory extends Factory
{
    protected $model = Socio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $contador = 1000;
        $contador++;

        return [
            'codigo' => 'SOC-'.$contador,
            'nombre' => fake('es_ES')->firstName(),
            'apellido' => fake('es_ES')->lastName(),
            'dni' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'foto' => null,
            'estado' => 'activo',
            'observaciones_medicas' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Estado: socio inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'inactivo',
        ]);
    }

    /**
     * Estado: socio moroso.
     */
    public function moroso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'moroso',
        ]);
    }
}
