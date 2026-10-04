<?php

namespace App\Console\Commands;

use App\Models\CongelamientoMembresia;
use App\Models\Membresia;
use App\Models\NotificacionMembresia;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcesarVencimientosCommand extends Command
{
    /**
     * Nombre y firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'fitlife:procesar-vencimientos
                            {--dias-alerta=5 : Días previos para la alerta preventiva principal}
                            {--forzar : Ejecuta sin confirmación}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Automatiza el cambio de estado de membresías vencidas, reanuda congelamientos expirados y genera notificaciones preventivas a 5 y 1 día.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $hoy = Carbon::today();
        $ahora = Carbon::now();
        $this->info("⚡ [FITLIFE ENGINE] Iniciando procesamiento diario de membresías ({$hoy->format('d/m/Y')})...");

        $estadisticas = [
            'congelamientos_reanudados' => 0,
            'membresias_vencidas' => 0,
            'alertas_5_dias' => 0,
            'alertas_1_dia' => 0,
            'alertas_cuotas_pendientes' => 0,
        ];

        // 1. Reanudar automáticamente congelamientos cumplidos
        $congelamientosExpirados = CongelamientoMembresia::with('membresia')
            ->where('estado', 'activo')
            ->whereDate('fecha_fin_planificada', '<=', $hoy)
            ->get();

        foreach ($congelamientosExpirados as $congelamiento) {
            if ($congelamiento->membresia) {
                $congelamiento->membresia->reanudar();
                $estadisticas['congelamientos_reanudados']++;
            }
        }

        // 2. Procesar membresías vencidas
        $membresiasVencidas = Membresia::with(['socio', 'plan'])
            ->where('estado', 'activa')
            ->whereDate('fecha_fin', '<', $hoy)
            ->get();

        foreach ($membresiasVencidas as $membresia) {
            $membresia->update(['estado' => 'vencida']);

            // Notificación al socio
            NotificacionMembresia::create([
                'socio_id' => $membresia->socio_id,
                'membresia_id' => $membresia->id,
                'tipo' => 'vencida',
                'titulo' => 'Membresía Expirada',
                'mensaje' => "Tu suscripción al plan {$membresia->plan?->nombre} finalizó el {$membresia->fecha_fin->format('d/m/Y')}. Renueva ahora para continuar entrenando.",
                'canal' => 'sistema',
                'enviada_el' => $ahora,
            ]);

            // Si el socio no tiene otra membresía activa, marcar en mora
            if ($membresia->socio && ! $membresia->socio->tieneAccesoValido()) {
                $membresia->socio->update(['estado' => 'moroso']);
            }

            $estadisticas['membresias_vencidas']++;
        }

        // 3. Alertas preventivas a 5 Días
        $fecha5Dias = $hoy->copy()->addDays(5)->toDateString();
        $membresias5Dias = Membresia::with(['socio', 'plan'])
            ->where('estado', 'activa')
            ->whereDate('fecha_fin', $fecha5Dias)
            ->get();

        foreach ($membresias5Dias as $m5) {
            // Evitar duplicar notificación en el mismo día
            $existeAlerta = NotificacionMembresia::where('membresia_id', $m5->id)
                ->where('tipo', 'vencimiento_5_dias')
                ->whereDate('enviada_el', $hoy)
                ->exists();

            if (! $existeAlerta) {
                NotificacionMembresia::create([
                    'socio_id' => $m5->socio_id,
                    'membresia_id' => $m5->id,
                    'tipo' => 'vencimiento_5_dias',
                    'titulo' => 'Recordatorio: Tu membresía vence en 5 días',
                    'mensaje' => "Hola {$m5->socio?->nombre}, tu plan {$m5->plan?->nombre} vencerá el {$m5->fecha_fin->format('d/m/Y')}. Pasa por recepción para renovar con beneficios anticipados.",
                    'canal' => 'sistema',
                    'enviada_el' => $ahora,
                ]);
                $estadisticas['alertas_5_dias']++;
            }
        }

        // 4. Alertas preventivas críticas a 1 Día
        $fecha1Dia = $hoy->copy()->addDays(1)->toDateString();
        $membresias1Dia = Membresia::with(['socio', 'plan'])
            ->where('estado', 'activa')
            ->whereDate('fecha_fin', $fecha1Dia)
            ->get();

        foreach ($membresias1Dia as $m1) {
            $existeAlerta = NotificacionMembresia::where('membresia_id', $m1->id)
                ->where('tipo', 'vencimiento_1_dia')
                ->whereDate('enviada_el', $hoy)
                ->exists();

            if (! $existeAlerta) {
                NotificacionMembresia::create([
                    'socio_id' => $m1->socio_id,
                    'membresia_id' => $m1->id,
                    'tipo' => 'vencimiento_1_dia',
                    'titulo' => '⚠️ Urgente: Tu plan FitLife vence mañana',
                    'mensaje' => "Atención {$m1->socio?->nombre}, tu membresía finaliza mañana ({$m1->fecha_fin->format('d/m/Y')}). Renueva hoy para evitar interrupciones en torniquetes.",
                    'canal' => 'sistema',
                    'enviada_el' => $ahora,
                ]);
                $estadisticas['alertas_1_dia']++;
            }
        }

        // 5. Alertas de Cuotas Pendientes de Pago Fraccionado
        $cuotasPendientes = Membresia::with(['socio', 'plan'])
            ->where('estado', 'activa')
            ->where('saldo_pendiente', '>', 0)
            ->get();

        foreach ($cuotasPendientes as $cp) {
            $existeAlerta = NotificacionMembresia::where('membresia_id', $cp->id)
                ->where('tipo', 'cuota_pendiente')
                ->whereDate('enviada_el', $hoy)
                ->exists();

            if (! $existeAlerta) {
                NotificacionMembresia::create([
                    'socio_id' => $cp->socio_id,
                    'membresia_id' => $cp->id,
                    'tipo' => 'cuota_pendiente',
                    'titulo' => 'Saldo o Cuota Pendiente',
                    'mensaje' => "Posees un saldo pendiente de \${$cp->saldo_pendiente} para el plan {$cp->plan?->nombre}. Cuota {$cp->cuotas_pagadas}/{$cp->cuotas_totales}.",
                    'canal' => 'sistema',
                    'enviada_el' => $ahora,
                ]);
                $estadisticas['alertas_cuotas_pendientes']++;
            }
        }

        // Resumen de ejecución
        $this->newLine();
        $this->info('📊 Resumen del Procesamiento Automático:');
        $this->table(
            ['Métrica de Automatización', 'Cantidad'],
            [
                ['Congelamientos expirados reanudados', $estadisticas['congelamientos_reanudados']],
                ['Membresías vencidas actualizadas', $estadisticas['membresias_vencidas']],
                ['Alertas emitidas (Recordatorio 5 Días)', $estadisticas['alertas_5_dias']],
                ['Alertas críticas emitidas (Recordatorio 1 Día)', $estadisticas['alertas_1_dia']],
                ['Alertas de pagos fraccionados pendientes', $estadisticas['alertas_cuotas_pendientes']],
            ]
        );

        $this->info('✅ Proceso de auditoría y automatización finalizado con éxito.');

        return Command::SUCCESS;
    }
}
