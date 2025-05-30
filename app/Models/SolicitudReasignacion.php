<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SolicitudReasignacion extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitudReasignacionFactory> */
    use HasFactory;

     protected $fillable = [
        'numero_solicitud_rea',
        'fecha_solicitud',
        'motivo_reasignacion',
        'unidad_administrativa_origen',
        'responsable_actual_origen', // Nombre del responsable en origen (snapshot)
        'cedula_responsable_origen',   // Cédula del responsable en origen
        'unidad_administrativa_destino',
        'responsable_destino',         // Nombre del nuevo responsable (texto libre)
        'cedula_responsable_destino',  // Cédula del nuevo responsable
        'estado_solicitud',
        'periodo_id',        // Asumiendo que lo incluiste
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        // 'fecha_aprobacion' => 'date',     // Si lo incluiste
        'fecha_ejecucion_des' => 'date',  // Si lo incluiste
        // Los campos enum ('estado_solicitud') se manejarán como strings.
    ];

    /**
     * El período al que pertenece la solicitud.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }


    /**
     * Los bienes incluidos en esta solicitud de reasignación.
     */
    public function biens(): BelongsToMany
    {
        return $this->belongsToMany(Bien::class, 'bien_solicitud_reasignacion')
                    ->withPivot('observacion_especifica_bien') // Acceder a campos de la tabla pivote
                    ->withTimestamps(); // Si la tabla pivote tiene timestamps
    }
}
