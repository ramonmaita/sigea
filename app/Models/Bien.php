<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bien extends Model
{
    /** @use HasFactory<\Database\Factories\BienFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo_bien',
        'nombre',
        'serial_numero',
        'valor_adquisicion',
        'estado_bien', // 'Nuevo', 'Bueno', 'Regular', 'En Mantenimiento', 'Deteriorado', 'Para Desincorporar', 'Desincorporado'
        'ubicacion_actual',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valor_adquisicion' => 'decimal:2',
    ];

    public function ordenesSalida(): BelongsToMany
    {
        return $this->belongsToMany(OrdenSalida::class, 'bien_orden_salida') // Nombre de la tabla pivote
                    ->withPivot('observacion_item_salida', 'estado_item_retorno') // Campos adicionales de la tabla pivote
                    ->withTimestamps(); // Si tu tabla pivote tiene timestamps
    }
}
