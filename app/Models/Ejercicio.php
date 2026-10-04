<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ejercicio extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'ejercicios';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'grupo_muscular',
        'dificultad',
        'equipamiento',
        'descripcion',
        'equipo_necesario',
    ];

    /**
     * Rutinas en las cuales se incluye este ejercicio.
     *
     * @return BelongsToMany<Rutina, $this>
     */
    public function rutinas(): BelongsToMany
    {
        return $this->belongsToMany(Rutina::class, 'ejercicio_rutina')
            ->withPivot(['orden', 'series', 'repeticiones', 'tipo_serie', 'descanso_segundos', 'rpe', 'tempo', 'notas'])
            ->withTimestamps();
    }

    /**
     * Plantillas predeterminadas que incluyen este ejercicio.
     *
     * @return BelongsToMany<PlantillaRutina, $this>
     */
    public function plantillas(): BelongsToMany
    {
        return $this->belongsToMany(PlantillaRutina::class, 'ejercicio_plantilla', 'ejercicio_id', 'plantilla_id')
            ->withPivot(['orden', 'series', 'repeticiones', 'tipo_serie', 'descanso_segundos', 'rpe', 'tempo', 'notas'])
            ->withTimestamps();
    }

    /**
     * Scope para filtrar ejercicios por grupo muscular.
     *
     * @param  Builder<Ejercicio>  $consulta
     * @return Builder<Ejercicio>
     */
    public function scopePorGrupoMuscular(Builder $consulta, string $grupo): Builder
    {
        return $consulta->where('grupo_muscular', $grupo);
    }

    /**
     * Scope para filtrar ejercicios por nivel de dificultad.
     *
     * @param  Builder<Ejercicio>  $consulta
     * @return Builder<Ejercicio>
     */
    public function scopePorDificultad(Builder $consulta, string $dificultad): Builder
    {
        return $consulta->where('dificultad', $dificultad);
    }

    /**
     * Scope para filtrar ejercicios por tipo de equipamiento.
     *
     * @param  Builder<Ejercicio>  $consulta
     * @return Builder<Ejercicio>
     */
    public function scopePorEquipamiento(Builder $consulta, string $equipamiento): Builder
    {
        return $consulta->where('equipamiento', $equipamiento);
    }
}
