<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
        'sucursal_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Atributos ocultos en serialización.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Rol asignado al usuario.
     *
     * @return BelongsTo<Rol, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Sucursal a la cual está adscrito el usuario.
     *
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Perfil de socio si el usuario es un cliente del gimnasio.
     *
     * @return HasOne<Socio, $this>
     */
    public function socio(): HasOne
    {
        return $this->hasOne(Socio::class, 'user_id');
    }

    /**
     * Rutinas creadas por este usuario (entrenador / admin).
     *
     * @return HasMany<Rutina, $this>
     */
    public function rutinasCreadas(): HasMany
    {
        return $this->hasMany(Rutina::class, 'creada_por');
    }

    /**
     * Asistencias registradas por este usuario en recepción.
     *
     * @return HasMany<Asistencia, $this>
     */
    public function asistenciasRegistradas(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'registrado_por');
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     */
    public function tieneRol(string $nombreRol): bool
    {
        return $this->rol?->nombre === $nombreRol;
    }

    /**
     * Verifica si el usuario es Administrador.
     */
    public function esAdmin(): bool
    {
        return $this->tieneRol('admin');
    }

    /**
     * Verifica si el usuario es Entrenador.
     */
    public function esEntrenador(): bool
    {
        return $this->tieneRol('entrenador');
    }

    /**
     * Verifica si el usuario es Socio.
     */
    public function esSocio(): bool
    {
        return $this->tieneRol('socio');
    }
}
