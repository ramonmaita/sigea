<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisicion extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_requisicion',
        'fecha_solicitud',
        'dependencia_solicitante',
        'tipo_requisicion', // 'BIENES', 'SERVICIO', 'MATERIALES Y SUMINISTROS'
        'justificacion_uso',
        'observaciones',
        'estado', // 'Borrador', 'Enviada', 'Aprobada', 'Rechazada', 'Procesada', 'Anulada'
        'user_id',
        'periodo_id', // Clave foránea para el modelo Periodo
        'proyecto_accion_codigo',
        'fuente_financiamiento_nombre',
        'path_archivo_adjunto',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_solicitud' => 'date', // Laravel convertirá esto a un objeto Carbon
        // Los campos enum 'tipo_requisicion' y 'estado' se manejarán como strings por defecto,
        // lo cual está bien para Filament. Si usaras PHP 8.1+ Enums, podrías castearlos aquí.
    ];

    /**
     * Obtiene el usuario que creó la requisición.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el período al que pertenece la requisición.
     * Asegúrate de que el modelo App\Models\Periodo exista.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id'); // Usando 'periodo_id' como clave foránea
    }

    /**
     * Obtiene los ítems asociados a la requisición.
     */
    public function items(): HasMany
    {
        return $this->hasMany(RequisicionItem::class);
    }

    // Aquí podríamos añadir un accesor para el nombre del proyecto/acción y responsable
    // si necesitamos obtenerlos frecuentemente desde DetalleOficina usando proyecto_accion_codigo.
    // Por ejemplo:
    public function getProyectoAccionDetallesAttribute(): ?object
    {
        if ($this->proyecto_accion_codigo) {
            $detalleOficina = DetalleOficina::first();
            if ($detalleOficina && isset($detalleOficina->proyectos_acciones)) {
                foreach ($detalleOficina->proyectos_acciones as $proyecto) {
                    if (isset($proyecto['codigo']) && $proyecto['codigo'] === $this->proyecto_accion_codigo) {
                        return (object) ['nombre' => $proyecto['nombre'] ?? null, 'responsable' => $proyecto['responsable'] ?? null];
                    }
                }
            }
        }
        return null;
    }
}
