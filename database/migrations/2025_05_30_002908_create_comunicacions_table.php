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
        Schema::create('comunicacions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_comunicacion')->unique(); // Se generará con lógica de correlativo
            $table->date('fecha_documento');
            $table->enum('tipo_comunicacion', ['Memorando', 'Oficio', 'Circular Interna', 'Notificación', 'Punto de Información']);
            $table->string('asunto');
            $table->longText('cuerpo'); // Para el editor de texto enriquecido

            $table->string('dirigido_a_nombre');
            $table->string('dirigido_a_cargo_dependencia')->nullable();
            $table->json('con_copia_a')->nullable(); // Para múltiples CCs estructurados

            $table->string('referencia')->nullable();

            $table->string('firmante_nombre'); // Se seleccionará/autocompletará de DetalleOficina
            $table->string('firmante_cargo');  // Se autocompletará

            $table->enum('estado', ['Borrador', 'Enviada', 'Anulada'])->default('Borrador');

            $table->foreignId('periodo_id')->constrained('periodos');
            $table->foreignId('user_id_creador')->nullable()->constrained('users')->comment('Usuario que elabora el documento'); // Recomendado

            $table->json('path_adjuntos')->nullable(); // Para múltiples archivos adjuntos

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicacions');
    }
};
