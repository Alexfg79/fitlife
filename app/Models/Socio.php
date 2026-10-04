<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Socio extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'socios';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'codigo',
        'nombre',
        'apellido',
        'dni',
        'correo',
        'telefono',
        'direccion',
        'contacto_emergencia',
        'fecha_nacimiento',
        'foto',
        'estado',
        'observaciones_medicas',
    ];

    /**
     * Atributos adicionales anexados al JSON.
     *
     * @var list<string>
     */
    protected $appends = [
        'nombre_completo',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date:Y-m-d',
        ];
    }

    /**
     * Accesor para el nombre y apellido completos del socio.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Usuario de acceso al sistema asignado a este socio.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias de relación user().
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->usuario();
    }

    /**
     * Historial completo de membresías del socio.
     *
     * @return HasMany<Membresia, $this>
     */
    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class, 'socio_id');
    }

    /**
     * Membresía activa vigente del socio.
     *
     * @return HasOne<Membresia, $this>
     */
    public function membresiaActiva(): HasOne
    {
        return $this->hasOne(Membresia::class, 'socio_id')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>=', Carbon::today()->toDateString())
            ->latestOfMany('fecha_fin');
    }

    /**
     * Última membresía registrada (activa o vencida).
     *
     * @return HasOne<Membresia, $this>
     */
    public function ultimaMembresia(): HasOne
    {
        return $this->hasOne(Membresia::class, 'socio_id')->latestOfMany('fecha_fin');
    }

    /**
     * Registros de asistencia del socio.
     *
     * @return HasMany<Asistencia, $this>
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'socio_id');
    }

    /**
     * Rutinas asignadas al socio.
     *
     * @return HasMany<Rutina, $this>
     */
    public function rutinas(): HasMany
    {
        return $this->hasMany(Rutina::class, 'socio_id');
    }

    /**
     * Scope para socios con estado activo.
     *
     * @param  Builder<Socio>  $consulta
     * @return Builder<Socio>
     */
    public function scopeActivos(Builder $consulta): Builder
    {
        return $consulta->where('estado', 'activo');
    }

    /**
     * Scope para socios con estado inactivo.
     *
     * @param  Builder<Socio>  $consulta
     * @return Builder<Socio>
     */
    public function scopeInactivos(Builder $consulta): Builder
    {
        return $consulta->where('estado', 'inactivo');
    }

    /**
     * Scope para socios en estado moroso.
     *
     * @param  Builder<Socio>  $consulta
     * @return Builder<Socio>
     */
    public function scopeMorosos(Builder $consulta): Builder
    {
        return $consulta->where('estado', 'moroso');
    }

    /**
     * Scope para buscar socios por nombre, apellido, DNI, correo o código.
     *
     * @param  Builder<Socio>  $consulta
     * @return Builder<Socio>
     */
    public function scopeBuscar(Builder $consulta, ?string $termino): Builder
    {
        if (blank($termino)) {
            return $consulta;
        }

        return $consulta->where(function (Builder $sub) use ($termino) {
            $sub->where('nombre', 'like', "%{$termino}%")
                ->orWhere('apellido', 'like', "%{$termino}%")
                ->orWhere('dni', 'like', "%{$termino}%")
                ->orWhere('correo', 'like', "%{$termino}%")
                ->orWhere('codigo', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope para socios cuya última membresía esté vencida o no tengan membresía activa.
     *
     * @param  Builder<Socio>  $consulta
     * @return Builder<Socio>
     */
    public function scopeConMembresiaVencida(Builder $consulta): Builder
    {
        return $consulta->whereDoesntHave('membresias', function (Builder $query) {
            $query->where('estado', 'activa')
                ->where('fecha_fin', '>=', Carbon::today()->toDateString());
        });
    }

    /**
     * Congelamientos y pausas de membresías del socio.
     *
     * @return HasMany<CongelamientoMembresia, $this>
     */
    public function congelamientos(): HasMany
    {
        return $this->hasMany(CongelamientoMembresia::class, 'socio_id');
    }

    /**
     * Notificaciones y alertas emitidas para el socio.
     *
     * @return HasMany<NotificacionMembresia, $this>
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionMembresia::class, 'socio_id');
    }

    /**
     * Evalúa si el socio tiene una cuota activa y permiso de ingreso al gimnasio.
     */
    public function tieneAccesoValido(): bool
    {
        return $this->tieneMembresiaActiva();
    }

    /**
     * Determina si el socio posee una membresía activa, no congelada y con vigencia/sesiones válidas.
     */
    public function tieneMembresiaActiva(): bool
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        $membresia = $this->membresiaActiva;
        if (! $membresia) {
            return false;
        }

        if ($membresia->estaCongelada()) {
            return false;
        }

        if ($membresia->esPorSesiones() && ! $membresia->tieneSesionesDisponibles()) {
            return false;
        }

        return true;
    }

    /**
     * Congela la membresía activa del socio.
     */
    public function congelarMembresia(int $dias, string $motivo = 'vacaciones', ?string $soporte = null): ?CongelamientoMembresia
    {
        $membresia = $this->membresiaActiva;
        if (! $membresia) {
            return null;
        }

        $congelamiento = $membresia->congelar($dias, $motivo, $soporte);
        $this->unsetRelation('membresiaActiva');

        return $congelamiento;
    }

    /**
     * Reanuda la membresía pausada del socio.
     */
    public function reanudarMembresia(): bool
    {
        $membresia = $this->membresias()->where('estado', 'congelada')->latest('fecha_inicio')->first();
        if (! $membresia) {
            return false;
        }

        $exito = $membresia->reanudar();
        $this->unsetRelation('membresiaActiva');

        return $exito;
    }

    /**
     * Asigna una rutina al socio basándose en una plantilla prediseñada.
     */
    public function asignarRutinaDesdePlantilla(int|PlantillaRutina $plantilla, ?int $entrenadorId = null): Rutina
    {
        $plantillaModel = is_numeric($plantilla)
            ? PlantillaRutina::with('ejercicios')->findOrFail($plantilla)
            : $plantilla;

        if (! $plantillaModel->relationLoaded('ejercicios')) {
            $plantillaModel->load('ejercicios');
        }

        return $plantillaModel->asignarASocio($this, $entrenadorId);
    }

    /**
     * Generador automático de rutina según objetivo y nivel del socio.
     */
    public function generarRutinaAutomatica(string $objetivo, string $nivel = 'intermedio', ?int $entrenadorId = null): Rutina
    {
        // 1. Buscar plantilla coincidente por objetivo y nivel
        $plantilla = PlantillaRutina::where('objetivo', $objetivo)
            ->where('nivel', $nivel)
            ->where('activa', true)
            ->with('ejercicios')
            ->first();

        // 2. Si no hay plantilla exacta para ese nivel, buscar cualquiera del mismo objetivo
        if (! $plantilla) {
            $plantilla = PlantillaRutina::where('objetivo', $objetivo)
                ->where('activa', true)
                ->with('ejercicios')
                ->first();
        }

        if ($plantilla) {
            return $plantilla->asignarASocio($this, $entrenadorId);
        }

        // 3. Si no existen plantillas aún en BD, sintetizar una rutina dinámica basada en ejercicios del catálogo
        $ejercicios = Ejercicio::where('dificultad', $nivel)->inRandomOrder()->limit(5)->get();
        if ($ejercicios->count() < 4) {
            $ejercicios = Ejercicio::inRandomOrder()->limit(rand(4, 6))->get();
        }

        // Desactivar y archivar rutinas previas activas del socio
        Rutina::where('socio_id', $this->id)
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
        $nivelDificultad = $dificultadMap[strtolower($nivel)] ?? 'Intermedio';

        $titulosPorNivel = [
            'principiante' => [
                'Adaptación Anatómica y Core',
                'Iniciación en Máquinas Guiadas',
                'Fullbody Básico Funcional',
                'Acondicionamiento Físico General',
            ],
            'intermedio' => [
                'Hipertrofia Funcional Torso/Pierna',
                'Fuerza y Rendimiento A/B',
                'Push / Pull / Legs Volumen Intermedio',
                'Desarrollo Muscular Estructurado',
            ],
            'avanzado' => [
                'Especialización de Fuerza Máxima 5x5',
                'Sobrecarga Progresiva Alta Intensidad',
                'Potencia y Pliometría Atlética',
                'Periodización Ondulante Hipertrofia Pro',
            ],
        ];

        $titulosDisponibles = $titulosPorNivel[strtolower($nivel)] ?? $titulosPorNivel['intermedio'];
        $tituloBase = $titulosDisponibles[array_rand($titulosDisponibles)];

        $rutina = Rutina::create([
            'socio_id' => $this->id,
            'creada_por' => $entrenadorId,
            'nombre' => "{$tituloBase} ({$nivelDificultad})",
            'descripcion' => "Rutina automatizada generada para alcanzar objetivo de {$objetivo}.",
            'nivel' => strtolower($nivel),
            'nivel_dificultad' => $nivelDificultad,
            'dias_semana' => ['Lunes', 'Miércoles', 'Viernes'],
            'activa' => true,
            'estado' => 'Activa',
            'fecha_inicio' => Carbon::today(),
            'fecha_fin' => null,
        ]);

        $ahora = Carbon::now();
        foreach ($ejercicios as $idx => $ejercicio) {
            DB::table('ejercicio_rutina')->insert([
                'rutina_id' => $rutina->id,
                'ejercicio_id' => $ejercicio->id,
                'orden' => $idx + 1,
                'series' => $nivel === 'avanzado' ? 4 : 3,
                'repeticiones' => $objetivo === 'fuerza' ? '5-6' : ($objetivo === 'ganar_musculo' ? '8-12' : '12-15'),
                'tipo_serie' => 'estandar',
                'descanso_segundos' => $objetivo === 'fuerza' ? 120 : 60,
                'rpe' => $nivel === 'avanzado' ? 8 : 7,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        return $rutina;
    }

    /**
     * Calcula la cantidad de cuotas o mensualidades vencidas que adeuda el socio.
     */
    public function cuotasVencidasCount(): int
    {
        // 1. Cuotas impagas en membresías registradas
        $cuotasImpagas = (int) $this->membresias()
            ->selectRaw('COALESCE(SUM(cuotas_totales - cuotas_pagadas), 0) as impagas')
            ->value('impagas');

        if ($cuotasImpagas > 0) {
            return $cuotasImpagas;
        }

        // 2. Si no tiene cuotas fraccionadas pero su última membresía está vencida
        $ultima = $this->ultimaMembresia;
        if ($ultima && ($ultima->estado === 'vencida' || $ultima->fecha_fin < Carbon::today())) {
            $meses = (int) Carbon::today()->diffInMonths($ultima->fecha_fin);

            return max(1, $meses);
        }

        return 0;
    }

    /**
     * Determina si el socio debe ser bloqueado por morosidad (2 o más cuotas vencidas).
     */
    public function esMorosoBloqueado(): bool
    {
        return $this->cuotasVencidasCount() >= 2;
    }

    /**
     * Obtiene el registro de asistencia abierta (sin salida) de hoy para este socio.
     */
    public function asistenciaAbiertaHoy(): ?Asistencia
    {
        return $this->asistencias()
            ->hoy()
            ->permitidas()
            ->abiertas()
            ->latest('fecha_hora_entrada')
            ->first();
    }
}
