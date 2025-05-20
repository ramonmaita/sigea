<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrdenSalida extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenSalidaFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_orden_salida',
        'fecha_salida',
        'fecha_retorno_prevista',
        'fecha_retorno_real',
        'tipo_salida',
        'destino_o_unidad_receptora',
        'persona_responsable_retiro',
        'cedula_responsable_retiro',
        'justificacion',
        'observaciones',
        'estado_orden',
        'periodo_id',
        'proveedor_nombre',
        'proveedor_direccion',
        'proveedor_telefono',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_retorno_prevista' => 'date',
        'fecha_retorno_real' => 'date',
    ];

    /**
     * El período al que pertenece la orden de salida.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    /**
     * Los bienes incluidos en esta orden de salida.
     */
    public function biens(): BelongsToMany
    {
        return $this->belongsToMany(Bien::class, 'bien_orden_salida') // Nombre de la tabla pivote
                    ->withPivot('observacion_item_salida', 'estado_item_retorno') // Campos adicionales de la tabla pivote
                    ->withTimestamps(); // Si tu tabla pivote tiene timestamps
    }
}
