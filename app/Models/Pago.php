<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'membresia_id',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'referencia',
        'comprobante',
        'notas',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_pago' => 'datetime',
            'monto' => 'decimal:2',
        ];
    }

    /**
     * Relación: Un pago pertenece a una membresía.
     */
    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class, 'membresia_id');
    }
}
