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
        Schema::create('solicitud_desincorporacions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_solicitud_des')->unique(); // Se generará con lógica de correlativo
            $table->date('fecha_solicitud');
            $table->enum('tipo_motivo_desincorporacion', [
                'Inservible por Obsolescencia',
                'Inservible por Deterioro',
                'Extraviado',
                'Hurtado',
                'Donación',
                'Venta',
                'Otro'
            ]);
            $table->text('justificacion_detallada');
            $table->enum('estado_solicitud', [
                'Elaborada',
                'En Revisión Técnica',
                'Aprobada',
                'Rechazada',
                'Ejecutada', // Cuando los bienes se marcan como Desincorporados
                'Anulada'
            ])->default('Elaborada');
            $table->date('fecha_ejecucion_des')->nullable(); // Fecha en que se marcan los bienes como desincorporados

            $table->foreignId('periodo_id')->constrained('periodos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_desincorporacions');
    }
};
