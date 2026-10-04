<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'estado',
    ];

    /**
     * Usuarios asignados o adscritos a esta sucursal.
     *
     * @return HasMany<User, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    /**
     * Membresías contratadas en esta sucursal.
     *
     * @return HasMany<Membresia, $this>
     */
    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class, 'sucursal_id');
    }

    /**
     * Asistencias y accesos registrados en esta sucursal.
     *
     * @return HasMany<Asistencia, $this>
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'sucursal_id');
    }

    /**
     * Clases grupales impartidas en esta sucursal.
     *
     * @return HasMany<ClaseGrupal, $this>
     */
    public function clasesGrupales(): HasMany
    {
        return $this->hasMany(ClaseGrupal::class, 'sucursal_id');
    }
}
