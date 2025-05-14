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
        Schema::create('requisicion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_id')->constrained('requisicions')->onDelete('cascade');
            $table->decimal('cantidad', 10, 2); // Ajusta precisión si es necesario
            $table->string('unidad_medida');
            $table->text('descripcion');
            $table->decimal('precio_unitario', 15, 2)->nullable(); // Precio por unidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisicion_items');
    }
};
