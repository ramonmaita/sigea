<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
     use HasFactory;

    protected $fillable = [
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Podrías añadir constantes para los estados para usarlas más fácilmente
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_INACTIVO = 'inactivo';

    public static function getEstados(): array
    {
        return [
            self::ESTADO_ACTIVO => 'activo',
            self::ESTADO_INACTIVO => 'inactivo',
        ];
    }
}
