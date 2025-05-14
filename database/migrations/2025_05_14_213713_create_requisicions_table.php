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
        Schema::create('requisicions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_requisicion'); // Lógica de unicidad con periodo_id se manejará al generar el número
            $table->date('fecha_solicitud');
            $table->string('dependencia_solicitante'); // Texto libre por ahora
            $table->enum('tipo_requisicion',['BIENES','SERVICIO','MATERIALES Y SUMINISTROS']); // Ej: 'bienes', 'servicio', 'materiales_suministros'
            $table->text('justificacion_uso'); // Detalle del uso o destino
            $table->text('observaciones')->nullable();
            $table->enum('estado',['Borrador', 'Enviada', 'Aprobada', 'Rechazada', 'Procesada', 'Anulada'])->default('Borrador'); // Ej: Borrador, Enviada, Aprobada, etc.

            $table->foreignId('user_id')->constrained('users')->comment('Usuario que crea la requisición');
            $table->foreignId('periodo_id')->constrained('periodos');

            // Código del Proyecto/Acción seleccionado de la lista en DetalleOficina
            $table->string('proyecto_accion_codigo')->nullable();
            // Nombre de la Fuente de Financiamiento seleccionada de la lista en DetalleOficina
            $table->string('fuente_financiamiento_nombre')->nullable();

            $table->string('path_archivo_adjunto')->nullable(); // Para el archivo adjunto opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisicions');
    }
};
