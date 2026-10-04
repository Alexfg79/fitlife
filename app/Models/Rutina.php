<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rutina extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'rutinas';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'socio_id',
        'creada_por',
        'nombre',
        'descripcion',
        'nivel',
        'nivel_dificultad',
        'dias_semana',
        'activa',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dias_semana' => 'array',
            'activa' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    /**
     * Socio a quien pertenece la rutina personalizada.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Entrenador que diseñó o asignó la rutina.
     *
     * @return BelongsTo<User, $this>
     */
    public function entrenador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    /**
     * Usuario o coach creador de la rutina (alias para vistas y controladores).
     *
     * @return BelongsTo<User, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    /**
     * Ejercicios que integran la rutina técnica con sus series y repeticiones.
     *
     * @return BelongsToMany<Ejercicio, $this>
     */
    public function ejercicios(): BelongsToMany
    {
        return $this->belongsToMany(Ejercicio::class, 'ejercicio_rutina')
            ->withPivot(['orden', 'series', 'repeticiones', 'descanso_segundos', 'notas'])
            ->withTimestamps()
            ->orderByPivot('orden');
    }

    /**
     * Scope para rutinas vigentes/activas.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', 'Activa')->where('activa', true);
    }

    /**
     * Scope para rutinas históricas finalizadas.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHistoricas(Builder $query): Builder
    {
        return $query->where('estado', 'Histórica/Finalizada');
    }

    /**
     * Archiva la rutina marcándola como finalizada e histórica.
     */
    public function archivar(?Carbon $fechaFin = null): self
    {
        $this->update([
            'estado' => 'Histórica/Finalizada',
            'activa' => false,
            'fecha_fin' => $fechaFin ?? Carbon::today(),
        ]);

        return $this;
    }
}
