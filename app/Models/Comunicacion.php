<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comunicacion extends Model
{
    /** @use HasFactory<\Database\Factories\ComunicacionFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_comunicacion',
        'fecha_documento',
        'tipo_comunicacion', // 'Memorando', 'Oficio', 'Circular Interna', etc.
        'asunto',
        'cuerpo',
        'dirigido_a_nombre',
        'dirigido_a_cargo_dependencia',
        'con_copia_a', // JSON para múltiples CCs
        'referencia',
        'firmante_nombre',
        'firmante_cargo',
        'estado', // 'Borrador', 'Enviada', 'Anulada'
        'periodo_id',
        'user_id_creador', // Usuario que elabora el documento
        'path_adjuntos',   // JSON para múltiples archivos adjuntos
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_documento' => 'date',
        'con_copia_a' => 'array',     // Castear el JSON a array PHP
        'path_adjuntos' => 'array',   // Castear el JSON a array PHP
    ];

    /**
     * El período al que pertenece la comunicación.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    /**
     * El usuario que creó (elaboró) la comunicación.
     */
    public function creador(): BelongsTo // O user() o elaborador()
    {
        return $this->belongsTo(User::class, 'user_id_creador');
    }
}
