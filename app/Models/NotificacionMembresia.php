<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionMembresia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'notificaciones_membresia';

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'socio_id',
        'membresia_id',
        'tipo',
        'titulo',
        'mensaje',
        'canal',
        'leida',
        'enviada_el',
    ];

    /**
     * Atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'enviada_el' => 'datetime',
        ];
    }

    /**
     * Socio destinatario de la alerta.
     *
     * @return BelongsTo<Socio, $this>
     */
    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    /**
     * Membresía relacionada.
     *
     * @return BelongsTo<Membresia, $this>
     */
    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class, 'membresia_id');
    }
}
