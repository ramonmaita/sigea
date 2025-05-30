<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SolicitudDesincorporacion extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitudDesincorporacionFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_solicitud_des',
        'fecha_solicitud',
        'tipo_motivo_desincorporacion',
        'justificacion_detallada',
        'estado_solicitud',
        'fecha_ejecucion_des',
        'periodo_id',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_ejecucion_des' => 'date',
    ];

    /**
     * El período al que pertenece la solicitud.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }



    /**
     * Los bienes incluidos en esta solicitud de desincorporación.
     */
    public function biens(): BelongsToMany
    {
        return $this->belongsToMany(Bien::class, 'bien_solicitud_desincorporacion')
                    ->withPivot('observacion_especifica_bien') // Acceder a campos de la tabla pivote
                    ->withTimestamps(); // Si la tabla pivote tiene timestamps
    }
}
