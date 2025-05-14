<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionItem extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requisicion_id',
        'cantidad',
        'unidad_medida',
        'descripcion', // Anteriormente 'descripcion_articulo'
        'precio_unitario', // Anteriormente 'precio_unitario_referencial'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'decimal:2',        // Castear a decimal con 2 posiciones
        'precio_unitario' => 'decimal:2', // Castear a decimal con 2 posiciones
    ];

    /**
     * Obtiene la requisición a la que pertenece este ítem.
     */
    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(Requisicion::class);
    }

    /**
     * Accesor para calcular el total del ítem (opcional).
     * No se guarda en la base de datos, se calcula al vuelo.
     */
    public function getTotalItemAttribute(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
