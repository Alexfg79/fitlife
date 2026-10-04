<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'nombre',
        'apellido',
        'especialidad',
        'telefono',
        'correo',
        'correo_electronico',
        'activo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Nombre completo del profesor.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombre ?? '').' '.($this->apellido ?? ''));
    }

    /**
     * Correo electrónico consolidado.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->correo_electronico ?: $this->correo;
    }

    /**
     * Cuenta de usuario vinculada al profesor.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Clases grupales impartidas por el profesor.
     *
     * @return HasMany<ClaseGrupal, $this>
     */
    public function clasesGrupales(): HasMany
    {
        return $this->hasMany(ClaseGrupal::class, 'profesor_id');
    }

    /**
     * Alumnos inscritos a través de sus clases grupales.
     *
     * @return HasManyThrough<InscripcionClase, ClaseGrupal, $this>
     */
    public function inscripciones(): HasManyThrough
    {
        return $this->hasManyThrough(InscripcionClase::class, ClaseGrupal::class, 'profesor_id', 'clase_grupal_id');
    }

    /**
     * Conteo total de alumnos o inscripciones históricas.
     */
    public function totalAlumnosInscritos(): int
    {
        return $this->inscripciones()->count();
    }
}
