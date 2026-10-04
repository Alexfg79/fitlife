<?php

namespace Database\Factories;

use App\Models\Rutina;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rutina>
 */
class RutinaFactory extends Factory
{
    protected $model = Rutina::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = [
            'Hipertrofia Tren Superior',
            'Fuerza y Potencia Piernas',
            'Full Body Acondicionamiento',
            'Torso - Pierna Avanzado',
            'Cardio HIIT y Quema Grasa',
            'Glúteos y Core Élite',
            'Push / Pull / Legs Volumen',
            'Movilidad y Resistencia Funcional',
        ];

        return [
            'socio_id' => Socio::factory(),
            'creada_por' => User::factory(),
            'nombre' => fake()->randomElement($nombres),
            'descripcion' => fake()->paragraph(2),
            'nivel' => fake()->randomElement(['principiante', 'intermedio', 'avanzado']),
            'dias_semana' => fake()->randomElements(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'], fake()->numberBetween(3, 5)),
            'activa' => fake()->boolean(85),
        ];
    }
}
