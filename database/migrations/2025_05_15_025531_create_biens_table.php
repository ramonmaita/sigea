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
        Schema::create('biens', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_bien')->unique(); // Código único del bien, se generará
            $table->string('nombre'); // Nombre descriptivo
            $table->string('serial_numero')->nullable()->unique(); // Serial, puede ser único si existe
            $table->decimal('valor_adquisicion', 15, 2)->nullable(); // Valor con 2 decimales

            // Usamos enum para los estados del bien
            $table->enum('estado_bien', [
                'Nuevo',
                'Bueno',
                'Regular',
                'En Mantenimiento', // Cambiado de 'En Reparación'
                'Deteriorado',
                'Para Desincorporar',
                'Desincorporado'
            ])->default('Bueno'); // Un estado por defecto

            $table->string('ubicacion_actual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biens');
    }
};
