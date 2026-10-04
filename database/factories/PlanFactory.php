<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Plan Mensual Fitness',
            'descripcion' => 'Acceso ilimitado a sala de musculación y cardio.',
            'precio' => 35.00,
            'duracion_dias' => 30,
            'activo' => true,
        ];
    }
}
