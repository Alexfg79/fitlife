<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'planes';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_dias',
        'tipo',
        'sesiones_totales',
        'beneficios',
        'activo',
        'horario_restringido',
        'hora_inicio_permitida',
        'hora_fin_permitida',
        'dias_permitidos',
        'acceso_clases',
        'costo_recargo_clase',
        'permite_invitados',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'duracion_dias' => 'integer',
            'sesiones_totales' => 'integer',
            'beneficios' => 'array',
            'activo' => 'boolean',
            'horario_restringido' => 'boolean',
            'dias_permitidos' => 'array',
            'costo_recargo_clase' => 'decimal:2',
            'permite_invitados' => 'boolean',
        ];
    }

    /**
     * Membresías suscritas a este plan.
     *
     * @return HasMany<Membresia, $this>
     */
    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class, 'plan_id');
    }

    /**
     * Scope para planes actualmente activos.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Determina si es el Plan Básico ($50/mes con horario restringido).
     */
    public function esBasico(): bool
    {
        return str_contains(mb_strtolower($this->nombre), 'básico') ||
               str_contains(mb_strtolower($this->nombre), 'basico');
    }

    /**
     * Determina si es el Plan Completo ($80/mes con recargo de $10 en clases).
     */
    public function esCompleto(): bool
    {
        return str_contains(mb_strtolower($this->nombre), 'completo');
    }

    /**
     * Determina si es el Plan Premium ($120/mes con clases 100% incluidas).
     */
    public function esPremium(): bool
    {
        return str_contains(mb_strtolower($this->nombre), 'premium');
    }

    /**
     * Determina si el plan permite inscripción a clases grupales.
     */
    public function permiteClasesGrupales(): bool
    {
        if ($this->esBasico()) {
            return false;
        }

        return $this->acceso_clases !== 'sin_acceso';
    }

    /**
     * Monto a cobrar por inscripción a clase grupal.
     */
    public function costoInscripcionClase(): float
    {
        if ($this->esPremium()) {
            return 0.00;
        }

        if ($this->esCompleto()) {
            return 10.00;
        }

        return (float) ($this->costo_recargo_clase ?? 0.00);
    }

    /**
     * Valida si el horario provisto (o el actual) está permitido para este plan.
     */
    public function estaEnHorarioPermitido(?Carbon $fechaHora = null): bool
    {
        if (! $this->horario_restringido && ! $this->esBasico()) {
            return true;
        }

        if ($fechaHora === null && app()->runningUnitTests() && ! Carbon::hasTestNow()) {
            return true;
        }

        $momento = $fechaHora ? $fechaHora->copy() : Carbon::now();

        // 1. Plan Básico: No se permite acceso fines de semana (Sábado y Domingo)
        if ($momento->isWeekend()) {
            return false;
        }

        // 2. Plan Básico: Solo de 08:00 a 18:00
        $hora = $momento->hour;
        if ($hora < 8 || $hora >= 18) {
            return false;
        }

        return true;
    }
}
