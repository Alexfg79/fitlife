<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CongelamientoMembresia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'congelamientos_membresia';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'membresia_id',
        'socio_id',
        'fecha_inicio',
        'fecha_fin_planificada',
        'fecha_reanudacion',
        'dias_congelados',
        'motivo',
        'documento_soporte',
        'estado',
        'observaciones',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_planificada' => 'date',
            'fecha_reanudacion' => 'date',
            'dias_congelados' => 'integer',
        ];
    }

    /**
     * Membresía sujeta a la pausa o congelamiento.
     *
     * @return BelongsTo<Membresia, $this>
     */
    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class, 'membresia_id');
    }

    /**
     * Socio titular de la membresía pausada.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Finaliza el congelamiento y calcula los días efectivamente pausados.
     */
    public function finalizar(?Carbon $fechaReanudacion = null): void
    {
        $fecha = $fechaReanudacion ?? Carbon::today();
        $diasEfectivos = max(1, $this->fecha_inicio->diffInDays($fecha));

        $this->update([
            'estado' => 'finalizado',
            'fecha_reanudacion' => $fecha,
            'dias_congelados' => $diasEfectivos,
        ]);
    }
}
