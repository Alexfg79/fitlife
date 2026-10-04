<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaseGrupal extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'clases_grupales';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'profesor_id',
        'entrenador_id',
        'dia_semana',
        'hora_inicio',
        'horario',
        'duracion_minutos',
        'cupo_maximo',
        'sala',
        'activa',
        'estado',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cupo_maximo' => 'integer',
            'duracion_minutos' => 'integer',
            'activa' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ClaseGrupal $clase) {
            if (empty($clase->horario)) {
                $clase->horario = trim(($clase->dia_semana ?? 'Lunes').' '.($clase->hora_inicio ?? '08:00'));
            }
        });
    }

    /**
     * Sucursal donde se imparte la clase grupal.
     *
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Profesor que imparte la clase grupal.
     *
     * @return BelongsTo<Profesor, $this>
     */
    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    /**
     * Entrenador responsable (compatibilidad).
     *
     * @return BelongsTo<User, $this>
     */
    public function entrenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrenador_id');
    }

    /**
     * Inscripciones formales a esta clase.
     *
     * @return HasMany<InscripcionClase, $this>
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionClase::class, 'clase_grupal_id');
    }

    /**
     * Asistencias registradas para esta clase.
     *
     * @return HasMany<Asistencia, $this>
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'clase_id');
    }

    /**
     * Retorna la cantidad de cupos disponibles para una fecha determinada.
     */
    public function cuposDisponiblesParaFecha(string|Carbon $fecha): int
    {
        $fechaStr = $fecha instanceof Carbon ? $fecha->toDateString() : $fecha;

        $inscritos = $this->inscripciones()
            ->whereDate('fecha_clase', $fechaStr)
            ->where('estado', '!=', 'cancelada')
            ->count();

        $asistentes = $this->asistencias()
            ->where(function ($q) use ($fechaStr) {
                $q->whereDate('fecha_hora_entrada', $fechaStr)
                    ->orWhereDate('fecha_hora', $fechaStr);
            })
            ->where('estado_acceso', 'permitido')
            ->count();

        $ocupados = max($inscritos, $asistentes);

        return max(0, $this->cupo_maximo - $ocupados);
    }

    /**
     * Retorna la cantidad de cupos disponibles en el día de hoy.
     */
    public function cuposDisponiblesHoy(): int
    {
        return $this->cuposDisponiblesParaFecha(Carbon::today());
    }

    /**
     * Indica si aún hay aforo para inscribirse en una fecha.
     */
    public function tieneCupoDisponibleParaFecha(string|Carbon $fecha): bool
    {
        return $this->cuposDisponiblesParaFecha($fecha) > 0;
    }

    /**
     * Indica si aún hay aforo hoy.
     */
    public function tieneCupoDisponible(): bool
    {
        return $this->tieneCupoDisponibleParaFecha(Carbon::today());
    }
}
