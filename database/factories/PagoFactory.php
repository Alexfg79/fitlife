<?php

namespace Database\Factories;

use App\Models\Membresia;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metodos = ['Efectivo', 'Transferencia', 'Tarjeta', 'Pago_Movil'];
        $fechaPago = Carbon::now()->subDays(fake()->numberBetween(0, 90));

        return [
            'membresia_id' => Membresia::factory(),
            'fecha_pago' => $fechaPago,
            'monto' => fake()->randomElement([25.00, 35.00, 50.00, 90.00, 140.00, 480.00]),
            'metodo_pago' => fake()->randomElement($metodos),
            'referencia' => 'REF-'.strtoupper(fake()->bothify('??###?##')),
            'comprobante' => 'comp_'.fake()->numberBetween(100000, 999999).'.pdf',
            'notas' => fake()->optional(0.4)->sentence(),
        ];
    }
}
