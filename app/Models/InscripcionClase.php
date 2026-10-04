<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionClase extends Model
{
    use HasFactory;

    protected $table = 'inscripciones_clases';

    protected $fillable = [
        'socio_id',
        'clase_grupal_id',
        'fecha_clase',
        'monto_recargo',
        'monto_recargo_pagado',
        'estado_pago',
        'asistencia_confirmada',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_clase' => 'date',
            'monto_recargo' => 'decimal:2',
            'monto_recargo_pagado' => 'decimal:2',
            'asistencia_confirmada' => 'boolean',
        ];
    }

    /**
     * Socio inscrito en la clase.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Clase grupal correspondiente.
     *
     * @return BelongsTo<ClaseGrupal, $this>
     */
    public function claseGrupal(): BelongsTo
    {
        return $this->belongsTo(ClaseGrupal::class, 'clase_grupal_id');
    }
}
