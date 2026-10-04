<?php

namespace Database\Factories;

use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membresia>
 */
class MembresiaFactory extends Factory
{
    protected $model = Membresia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = Carbon::today()->subDays(fake()->numberBetween(1, 20));
        $fechaFin = $fechaInicio->copy()->addDays(30);

        return [
            'socio_id' => Socio::factory(),
            'plan_id' => Plan::factory(),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'precio_pagado' => 45.00,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
            'notas' => 'Membresía generada por Factory',
        ];
    }

    /**
     * Estado: membresía activa vigente.
     */
    public function activa(int $diasRestantes = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => Carbon::today()->subDays(10),
            'fecha_fin' => Carbon::today()->addDays($diasRestantes),
            'estado' => 'activa',
        ]);
    }

    /**
     * Estado: membresía vencida.
     */
    public function vencida(int $diasVencida = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => Carbon::today()->subDays(30 + $diasVencida),
            'fecha_fin' => Carbon::today()->subDays($diasVencida),
            'estado' => 'vencida',
        ]);
    }

    /**
     * Estado: membresía próxima a vencer (por vencer).
     */
    public function porVencer(int $diasRestantes = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => Carbon::today()->subDays(27),
            'fecha_fin' => Carbon::today()->addDays($diasRestantes),
            'estado' => 'activa',
        ]);
    }
}
