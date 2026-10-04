<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'asistencias';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'socio_id',
        'sucursal_id',
        'registrado_por',
        'clase_id',
        'fecha_hora',
        'fecha_hora_entrada',
        'fecha_hora_salida',
        'tiempo_permanencia_minutos',
        'metodo',
        'tipo_acceso',
        'estado_acceso',
        'mensaje_acceso',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'fecha_hora_entrada' => 'datetime',
            'fecha_hora_salida' => 'datetime',
            'tiempo_permanencia_minutos' => 'integer',
        ];
    }

    /**
     * Boot model: Asegurar que fecha_hora y fecha_hora_entrada estén sincronizadas.
     */
    protected static function booted(): void
    {
        static::creating(function (Asistencia $asistencia) {
            if (empty($asistencia->fecha_hora_entrada) && ! empty($asistencia->fecha_hora)) {
                $asistencia->fecha_hora_entrada = $asistencia->fecha_hora;
            } elseif (empty($asistencia->fecha_hora) && ! empty($asistencia->fecha_hora_entrada)) {
                $asistencia->fecha_hora = $asistencia->fecha_hora_entrada;
            } elseif (empty($asistencia->fecha_hora_entrada)) {
                $asistencia->fecha_hora_entrada = Carbon::now();
                $asistencia->fecha_hora = $asistencia->fecha_hora_entrada;
            }
        });
    }

    /**
     * Socio que registra el acceso o asistencia.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Sucursal del gimnasio donde se realizó el marcaje.
     *
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Usuario (recepcionista, entrenador o admin) que procesó el acceso.
     *
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Clase grupal a la que asistió el socio si aplica.
     *
     * @return BelongsTo<ClaseGrupal, $this>
     */
    public function clase(): BelongsTo
    {
        return $this->belongsTo(ClaseGrupal::class, 'clase_id');
    }

    /**
     * Scope para asistencias de hoy.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHoy(Builder $query): Builder
    {
        $hoy = Carbon::today()->toDateString();

        return $query->where(function ($q) use ($hoy) {
            $q->whereDate('fecha_hora_entrada', $hoy)
                ->orWhereDate('fecha_hora', $hoy);
        });
    }

    /**
     * Scope para asistencias con entrada abierta (socio actualmente dentro de la sala).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAbiertas(Builder $query): Builder
    {
        return $query->whereNull('fecha_hora_salida');
    }

    /**
     * Scope para accesos autorizados.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePermitidas(Builder $query): Builder
    {
        return $query->where('estado_acceso', 'permitido');
    }

    /**
     * Scope para accesos denegados.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeDenegadas(Builder $query): Builder
    {
        return $query->where('estado_acceso', '!=', 'permitido');
    }

    /**
     * Registra la salida del socio y calcula los minutos de permanencia.
     */
    public function registrarSalida(?Carbon $horaSalida = null): self
    {
        $salida = $horaSalida ?? Carbon::now();
        $entrada = $this->fecha_hora_entrada ?? $this->fecha_hora ?? $salida;

        $minutos = (int) $entrada->diffInMinutes($salida);

        $this->update([
            'fecha_hora_salida' => $salida,
            'tiempo_permanencia_minutos' => max(1, $minutos),
        ]);

        return $this;
    }

    /**
     * Accessor para hora_salida (compatibilidad).
     */
    public function getHoraSalidaAttribute(): ?Carbon
    {
        return $this->fecha_hora_salida;
    }

    /**
     * Accessor para hora_entrada (compatibilidad).
     */
    public function getHoraEntradaAttribute(): ?Carbon
    {
        return $this->fecha_hora_entrada;
    }
}
