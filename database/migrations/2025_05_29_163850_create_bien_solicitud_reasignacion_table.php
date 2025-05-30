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
        Schema::create('bien_solicitud_reasignacion', function (Blueprint $table) {
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->foreignId('solicitud_reasignacion_id')->constrained('solicitud_reasignacions')->onDelete('cascade');

            $table->text('observacion_especifica_bien')->nullable(); // Observación para este bien en esta reasignación específica
            // Podrías añadir más campos pivote si fueran necesarios, ej: estado_bien_al_reasignar

            $table->timestamps(); // Opcional, pero útil

            // Clave primaria compuesta
            $table->primary(['bien_id', 'solicitud_reasignacion_id'], 'bien_sol_reasig_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_solicitud_reasignacion');
    }
};
