<?php

namespace App\Console\Commands;

use App\Models\Membresia;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckVencimientosMembresias extends Command
{
    /**
     * Nombre y firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'fitlife:check-vencimientos {--dias-aviso=3 : Días previos para alertar cuotas por vencer}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Verifica y actualiza automáticamente las membresías vencidas de los socios del gimnasio.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $hoy = Carbon::today();
        $this->info("Iniciando auditoría de vencimientos FitLife para la fecha: {$hoy->format('d/m/Y')}");

        // 1. Obtener membresías marcadas como activas pero cuya fecha de fin ya expiró
        $membresiasVencidas = Membresia::with(['socio', 'plan'])
            ->where('estado', 'activa')
            ->where('fecha_fin', '<', $hoy->toDateString())
            ->get();

        $totalActualizadas = 0;
        $filasActualizadas = [];

        foreach ($membresiasVencidas as $membresia) {
            $membresia->update([
                'estado' => 'vencida',
            ]);

            $socio = $membresia->socio;

            // Si el socio no tiene ninguna otra membresía activa, marcarlo en mora
            if ($socio && ! $socio->tieneAccesoValido()) {
                $socio->update([
                    'estado' => 'moroso',
                ]);
            }

            $filasActualizadas[] = [
                $membresia->id,
                $socio?->codigo ?? 'N/A',
                $socio?->nombre_completo ?? 'Desconocido',
                $membresia->plan?->nombre ?? 'Plan',
                $membresia->fecha_fin->format('d/m/Y'),
                'Vencida y Socio en Mora',
            ];

            $totalActualizadas++;
        }

        if ($totalActualizadas > 0) {
            $this->table(
                ['ID Membresía', 'Código Socio', 'Socio', 'Plan', 'Fecha Vencimiento', 'Acción Aplicada'],
                $filasActualizadas
            );
            $this->warn("Se actualizaron {$totalActualizadas} membresía(s) al estado 'vencida'.");
        } else {
            $this->info('No se encontraron membresías pendientes de vencimiento.');
        }

        // 2. Reporte de membresías próximas a vencer
        $diasAviso = (int) $this->option('dias-aviso');
        $proximasAVencer = Membresia::with(['socio', 'plan'])
            ->porVencer($diasAviso)
            ->get();

        if ($proximasAVencer->isNotEmpty()) {
            $this->newLine();
            $this->info("Alertas: {$proximasAVencer->count()} membresía(s) vencerán en los próximos {$diasAviso} días:");

            $filasAvisos = $proximasAVencer->map(function ($m) {
                return [
                    $m->socio?->codigo ?? 'N/A',
                    $m->socio?->nombre_completo ?? 'N/A',
                    $m->socio?->telefono ?? 'Sin teléfono',
                    $m->plan?->nombre ?? 'Plan',
                    $m->fecha_fin->format('d/m/Y'),
                    $m->diasRestantes().' día(s)',
                ];
            })->toArray();

            $this->table(
                ['Código', 'Socio', 'Teléfono', 'Plan', 'Fecha Fin', 'Días Restantes'],
                $filasAvisos
            );
        }

        $this->info('Auditoría de cuotas FitLife finalizada con éxito.');

        return Command::SUCCESS;
    }
}
