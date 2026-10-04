<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Membresia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'membresias';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'socio_id',
        'sucursal_id',
        'plan_id',
        'tipo',
        'sesiones_totales',
        'sesiones_restantes',
        'fecha_inicio',
        'fecha_fin',
        'precio_pagado',
        'cuotas_totales',
        'cuotas_pagadas',
        'saldo_pendiente',
        'estado',
        'metodo_pago',
        'notas',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'precio_pagado' => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'sesiones_totales' => 'integer',
            'sesiones_restantes' => 'integer',
            'cuotas_totales' => 'integer',
            'cuotas_pagadas' => 'integer',
        ];
    }

    /**
     * Socio titular de la membresía.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Plan suscrito en la membresía.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Sucursal donde se contrató la membresía.
     *
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Pagos y comprobantes de la membresía.
     *
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'membresia_id');
    }

    /**
     * Scope para membresías con estado activa y fecha fin mayor o igual a hoy.
     *
     * @param  Builder<Membresia>  $consulta
     * @return Builder<Membresia>
     */
    public function scopeActivas(Builder $consulta): Builder
    {
        return $consulta->where('estado', 'activa')
            ->where('fecha_fin', '>=', Carbon::today()->toDateString());
    }

    /**
     * Scope para membresías vencidas (estado vencida o fecha fin menor a hoy).
     *
     * @param  Builder<Membresia>  $consulta
     * @return Builder<Membresia>
     */
    public function scopeVencidas(Builder $consulta): Builder
    {
        return $consulta->where(function (Builder $sub) {
            $sub->where('estado', 'vencida')
                ->orWhere('fecha_fin', '<', Carbon::today()->toDateString());
        });
    }

    /**
     * Scope para membresías próximas a vencer en los siguientes N días.
     *
     * @param  Builder<Membresia>  $consulta
     * @return Builder<Membresia>
     */
    public function scopePorVencer(Builder $consulta, int $dias = 5): Builder
    {
        $hoy = Carbon::today()->toDateString();
        $limite = Carbon::today()->addDays($dias)->toDateString();

        return $consulta->where('estado', 'activa')
            ->whereBetween('fecha_fin', [$hoy, $limite]);
    }

    /**
     * Determina si la membresía se encuentra activa y vigente.
     */
    public function estaActiva(): bool
    {
        return $this->estado === 'activa' && ! $this->estaVencida() && ! $this->estaCongelada();
    }

    /**
     * Determina si la membresía se encuentra vencida.
     */
    public function estaVencida(): bool
    {
        return $this->estado === 'vencida' || Carbon::parse($this->fecha_fin)->isPast();
    }

    /**
     * Calcula los días restantes de vigencia de la membresía.
     */
    public function diasRestantes(): int
    {
        $hoy = Carbon::today();
        $fin = Carbon::parse($this->fecha_fin);

        if ($fin->isPast()) {
            return 0;
        }

        return (int) $hoy->diffInDays($fin, false);
    }

    /**
     * Congelamientos o pausas registradas para esta membresía.
     *
     * @return HasMany<CongelamientoMembresia, $this>
     */
    public function congelamientos(): HasMany
    {
        return $this->hasMany(CongelamientoMembresia::class, 'membresia_id');
    }

    /**
     * Pausa o congelamiento actualmente activo.
     *
     * @return HasOne<CongelamientoMembresia, $this>
     */
    public function congelamientoActivo(): HasOne
    {
        return $this->hasOne(CongelamientoMembresia::class, 'membresia_id')
            ->where('estado', 'activo')
            ->latestOfMany();
    }

    /**
     * Notificaciones y recordatorios emitidos para esta membresía.
     *
     * @return HasMany<NotificacionMembresia, $this>
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionMembresia::class, 'membresia_id');
    }

    /**
     * Determina si la membresía se encuentra pausada o congelada.
     */
    public function estaCongelada(): bool
    {
        return $this->estado === 'congelada';
    }

    /**
     * Indica si la membresía opera bajo modalidad de sesiones/pases.
     */
    public function esPorSesiones(): bool
    {
        return $this->tipo === 'sesiones' || ($this->sesiones_totales !== null && $this->sesiones_totales > 0);
    }

    /**
     * Verifica si quedan sesiones disponibles para ingresar.
     */
    public function tieneSesionesDisponibles(): bool
    {
        if (! $this->esPorSesiones()) {
            return true;
        }

        return ($this->sesiones_restantes ?? 0) > 0;
    }

    /**
     * Descuenta una sesión de la cuota activa y la actualiza a completada si llega a cero.
     */
    public function descontarSesion(): bool
    {
        if (! $this->esPorSesiones() || ! $this->tieneSesionesDisponibles()) {
            return false;
        }

        $restantes = max(0, ($this->sesiones_restantes ?? 1) - 1);
        $nuevoEstado = $restantes === 0 ? 'vencida' : $this->estado;

        $this->update([
            'sesiones_restantes' => $restantes,
            'estado' => $nuevoEstado,
        ]);

        return true;
    }

    /**
     * Congela la membresía temporalmente por vacaciones o razones médicas.
     */
    public function congelar(int $dias, string $motivo = 'vacaciones', ?string $soporte = null, ?string $observaciones = null): CongelamientoMembresia
    {
        $hoy = Carbon::today();
        $fechaFinPlanificada = $hoy->copy()->addDays($dias);

        $congelamiento = CongelamientoMembresia::create([
            'membresia_id' => $this->id,
            'socio_id' => $this->socio_id,
            'fecha_inicio' => $hoy,
            'fecha_fin_planificada' => $fechaFinPlanificada,
            'dias_congelados' => $dias,
            'motivo' => $motivo,
            'documento_soporte' => $soporte,
            'estado' => 'activo',
            'observaciones' => $observaciones,
        ]);

        $this->update(['estado' => 'congelada']);

        NotificacionMembresia::create([
            'socio_id' => $this->socio_id,
            'membresia_id' => $this->id,
            'tipo' => 'congelada',
            'titulo' => 'Membresía Pausada',
            'mensaje' => "Tu membresía ha sido pausada temporalmente por {$dias} días por motivo de {$motivo}.",
            'canal' => 'sistema',
            'enviada_el' => Carbon::now(),
        ]);

        return $congelamiento;
    }

    /**
     * Reanuda la membresía congelada y prorroga la fecha de vencimiento.
     */
    public function reanudar(): bool
    {
        $congelamiento = $this->congelamientoActivo;
        if (! $congelamiento) {
            if ($this->estado === 'congelada') {
                $this->update(['estado' => 'activa']);

                return true;
            }

            return false;
        }

        $hoy = Carbon::today();
        $diasPausados = max(1, $congelamiento->fecha_inicio->diffInDays($hoy));

        $congelamiento->finalizar($hoy);

        // Prorrogar la fecha de fin por los días que estuvo congelada
        $nuevaFechaFin = Carbon::parse($this->fecha_fin)->addDays($diasPausados);

        $this->update([
            'estado' => 'activa',
            'fecha_fin' => $nuevaFechaFin,
        ]);

        NotificacionMembresia::create([
            'socio_id' => $this->socio_id,
            'membresia_id' => $this->id,
            'tipo' => 'reanudada',
            'titulo' => 'Membresía Reanudada',
            'mensaje' => "Tu membresía ha sido reactivada. Se añadieron {$diasPausados} días a tu vigencia (Nuevo vencimiento: {$nuevaFechaFin->format('d/m/Y')}).",
            'canal' => 'sistema',
            'enviada_el' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Registra el pago de una cuota en pagos fraccionados.
     */
    public function registrarPagoCuota(float $monto, string $metodoPago = 'efectivo', ?string $referencia = null): Pago
    {
        $ahora = Carbon::now();
        $cuotasPagadas = min($this->cuotas_totales, $this->cuotas_pagadas + 1);
        $nuevoSaldo = max(0, (float) $this->saldo_pendiente - $monto);

        $pago = Pago::create([
            'membresia_id' => $this->id,
            'fecha_pago' => $ahora,
            'monto' => $monto,
            'metodo_pago' => ucfirst($metodoPago),
            'referencia' => $referencia ?? 'CUOTA-'.strtoupper(Str::random(8)),
            'comprobante' => 'recibo_cuota_'.$this->id.'_'.time().'.pdf',
            'notas' => "Abono cuota {$cuotasPagadas} de {$this->cuotas_totales}.",
        ]);

        $this->update([
            'cuotas_pagadas' => $cuotasPagadas,
            'saldo_pendiente' => $nuevoSaldo,
        ]);

        return $pago;
    }
}
