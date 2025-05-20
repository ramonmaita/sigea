<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOficina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_oficina',
        'acronimo_oficina',
        'direccion',
        'telefonos',
        'email_contacto',
        'path_logo',
        'autoridades',
        'proyectos_acciones',
        'fuentes_financiamiento',
    ];

    protected $casts = [
        'autoridades' => 'array', // Para que Laravel maneje el campo 'autoridades' como un array/JSON automáticamente
        'proyectos_acciones' => 'array',
        'fuentes_financiamiento' => 'array',
    ];
}
