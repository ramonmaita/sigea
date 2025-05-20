<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_salidas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden_salida')->unique(); // Lo haremos único globalmente por ahora, o ajustamos con periodo
            $table->date('fecha_salida');
            $table->date('fecha_retorno_prevista')->nullable();
            $table->date('fecha_retorno_real')->nullable();

            $table->enum('tipo_salida', [
                'Préstamo Interno',
                'Préstamo Externo',
                'Reparación',
                'Traslado Definitivo',
                'Evento Especial',
                'Para Desincorporar' // Añadido
            ]); // Puedes añadir más tipos según necesites

            $table->string('destino_o_unidad_receptora');
            $table->string('persona_responsable_retiro');
            $table->string('cedula_responsable_retiro')->nullable();
            $table->text('justificacion');
            $table->text('observaciones')->nullable();

            $table->enum('estado_orden', [
                'Solicitada',
                'Aprobada',
                'Ejecutada (Bienes Entregados)',
                'Retornada Parcialmente', // Si aplica para múltiples items
                'Retornada Completamente', // Si aplica para múltiples items
                'Cerrada', // Para salidas definitivas o préstamos completados
                'Anulada'
            ])->default('Solicitada');

            $table->foreignId('periodo_id')->constrained('periodos'); // Usando tu tabla 'periodos'

            // Campos para Proveedor (si tipo_salida == 'Reparación')
            $table->string('proveedor_nombre')->nullable();
            $table->text('proveedor_direccion')->nullable();
            $table->string('proveedor_telefono')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_salidas');
    }
};
