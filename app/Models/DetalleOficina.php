<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOficina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_oficina',
        'direccion',
        'telefonos',
        'email_contacto',
        'path_logo',
        'autoridades',
    ];

    protected $casts = [
        'autoridades' => 'array', // Para que Laravel maneje el campo 'autoridades' como un array/JSON automáticamente
    ];
}
