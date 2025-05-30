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
        Schema::create('solicitud_reasignacions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_solicitud_rea')->unique(); // Se generará con lógica de correlativo
            $table->date('fecha_solicitud');
            $table->text('motivo_reasignacion');

            $table->string('unidad_administrativa_origen')->nullable(); // Puede ser nullable si el bien no tenía una unidad formal
            $table->string('responsable_actual_origen')->nullable(); // Nombre del responsable en origen (snapshot)
            $table->string('cedula_responsable_origen')->nullable(); // Cédula del responsable en origen

            $table->string('unidad_administrativa_destino'); // Nueva unidad/dependencia destino
            $table->string('responsable_destino'); // Nombre del nuevo responsable (texto libre)
            $table->string('cedula_responsable_destino')->nullable(); // Cédula del nuevo responsable

            $table->enum('estado_solicitud', [
                'Elaborada',
                'Aprobada',
                'Rechazada',
                'Ejecutada', // Cuando el bien se actualiza con la nueva ubicación/responsable
                'Anulada'
            ])->default('Elaborada');

            $table->foreignId('periodo_id')->constrained('periodos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_reasignacions');
    }
};
