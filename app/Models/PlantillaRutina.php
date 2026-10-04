<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class PlantillaRutina extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'plantillas_rutina';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'objetivo',
        'nivel',
        'dias_semana',
        'creada_por',
        'activa',
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
        ];
    }

    /**
     * Entrenador o usuario que diseñó la plantilla.
     *
     * @return BelongsTo<User, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    /**
     * Catálogo de ejercicios vinculados a esta plantilla.
     *
     * @return BelongsToMany<Ejercicio, $this>
     */
    public function ejercicios(): BelongsToMany
    {
        return $this->belongsToMany(Ejercicio::class, 'ejercicio_plantilla', 'plantilla_id', 'ejercicio_id')
            ->withPivot([
                'orden',
                'series',
                'repeticiones',
                'tipo_serie',
                'descanso_segundos',
                'rpe',
                'tempo',
                'notas',
            ])
            ->withTimestamps()
            ->orderByPivot('orden', 'asc');
    }

    /**
     * Scope para filtrar plantillas por objetivo.
     *
     * @param  Builder<PlantillaRutina>  $consulta
     * @return Builder<PlantillaRutina>
     */
    public function scopePorObjetivo(Builder $consulta, string $objetivo): Builder
    {
        return $consulta->where('objetivo', $objetivo);
    }

    /**
     * Scope para filtrar plantillas por nivel de dificultad.
     *
     * @param  Builder<PlantillaRutina>  $consulta
     * @return Builder<PlantillaRutina>
     */
    public function scopePorNivel(Builder $consulta, string $nivel): Builder
    {
        return $consulta->where('nivel', $nivel);
    }

    /**
     * Clona e importa esta plantilla directamente a la ficha de un socio.
     */
    public function asignarASocio(Socio $socio, ?int $entrenadorId = null): Rutina
    {
        $ahora = Carbon::now();

        // Desactivar y archivar rutinas previas activas del socio
        Rutina::where('socio_id', $socio->id)
            ->where('estado', 'Activa')
            ->update([
                'activa' => false,
                'estado' => 'Histórica/Finalizada',
                'fecha_fin' => Carbon::today(),
            ]);

        $dificultadMap = [
            'principiante' => 'Principiante',
            'intermedio' => 'Intermedio',
            'avanzado' => 'Avanzado',
        ];
        $nivelDificultad = $dificultadMap[strtolower($this->nivel)] ?? 'Intermedio';

        $rutina = Rutina::create([
            'socio_id' => $socio->id,
            'creada_por' => $entrenadorId ?? $this->creada_por,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?? "Rutina generada a partir de plantilla '{$this->nombre}'.",
            'nivel' => $this->nivel,
            'nivel_dificultad' => $nivelDificultad,
            'dias_semana' => $this->dias_semana,
            'activa' => true,
            'estado' => 'Activa',
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => null,
        ]);

        // Clonar ejercicios y parámetros biomecánicos
        foreach ($this->ejercicios as $ejercicio) {
            DB::table('ejercicio_rutina')->insert([
                'rutina_id' => $rutina->id,
                'ejercicio_id' => $ejercicio->id,
                'orden' => $ejercicio->pivot->orden ?? 1,
                'series' => $ejercicio->pivot->series ?? 3,
                'repeticiones' => $ejercicio->pivot->repeticiones ?? '10-12',
                'tipo_serie' => $ejercicio->pivot->tipo_serie ?? 'estandar',
                'descanso_segundos' => $ejercicio->pivot->descanso_segundos ?? 60,
                'rpe' => $ejercicio->pivot->rpe ?? null,
                'tempo' => $ejercicio->pivot->tempo ?? null,
                'notas' => $ejercicio->pivot->notas ?? null,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        return $rutina;
    }
}
