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
        Schema::create('bien_orden_salida', function (Blueprint $table) {
            // $table->id();
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->foreignId('orden_salida_id')->constrained('orden_salidas')->onDelete('cascade');
            $table->text('observacion_item_salida')->nullable(); // Observación específica para este bien en esta orden
            $table->string('estado_item_retorno')->nullable(); // Podría ser útil para rastrear el estado de retorno de cada item

            // Primary key compuesta para evitar duplicados
            $table->primary(['bien_id', 'orden_salida_id']); // Si quieres evitar que un mismo bien esté dos veces en la misma orden
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_orden_salida');
    }
};
