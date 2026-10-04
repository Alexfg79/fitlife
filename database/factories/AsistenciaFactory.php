<?php

namespace Database\Factories;

use App\Models\Asistencia;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    protected $model = Asistencia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Fecha en los últimos 90 días con hora realista de gimnasio (06:00 a 21:00)
        $diasAtras = fake()->numberBetween(0, 90);
        $hora = fake()->numberBetween(6, 21);
        $minuto = fake()->numberBetween(0, 59);
        $segundo = fake()->numberBetween(0, 59);

        $fechaHora = Carbon::today()->subDays($diasAtras)->setTime($hora, $minuto, $segundo);

        $permitido = fake()->boolean(85); // 85% permitido
        $estadoAcceso = $permitido ? 'permitido' : fake()->randomElement(['denegado_vencido', 'denegado_inactivo']);
        $mensaje = match ($estadoAcceso) {
            'permitido' => 'Acceso autorizado. Membresía activa.',
            'denegado_vencido' => 'Acceso denegado. Membresía vencida.',
            'denegado_inactivo' => 'Acceso denegado. Socio inactivo.',
            default => 'Validación de acceso.',
        };

        return [
            'socio_id' => Socio::factory(),
            'registrado_por' => null,
            'fecha_hora' => $fechaHora,
            'metodo' => fake()->randomElement(['qr', 'codigo', 'biometrico']),
            'estado_acceso' => $estadoAcceso,
            'mensaje_acceso' => $mensaje,
        ];
    }

    /**
     * Estado: Acceso permitido.
     */
    public function permitido(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_acceso' => 'permitido',
            'mensaje_acceso' => 'Acceso autorizado. Membresía activa.',
        ]);
    }

    /**
     * Estado: Acceso denegado por cuota vencida.
     */
    public function denegadoCuotaVencida(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_acceso' => 'denegado_vencido',
            'mensaje_acceso' => 'Acceso denegado. Membresía vencida.',
        ]);
    }
}
