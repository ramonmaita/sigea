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
        Schema::create('bien_solicitud_desincorporacion', function (Blueprint $table) {
            // Clave foránea para bien_id
            $table->foreignId('bien_id')
                ->constrained('biens')
                ->onDelete('cascade')
                ->references('id'); // Especificar explícitamente la columna referenciada
            // Laravel infiere el nombre del índice aquí como bien_solicitud_desincorporacion_bien_id_foreign, que suele ser corto.

            // Clave foránea para solicitud_desincorporacion_id CON NOMBRE CORTO PARA EL ÍNDICE
            $table->unsignedBigInteger('solicitud_desincorporacion_id'); // Definir la columna primero
            $table->foreign('solicitud_desincorporacion_id', 'bsd_solicitud_des_id_fk') // <--- NOMBRE CORTO PERSONALIZADO PARA EL ÍNDICE
                ->references('id')->on('solicitud_desincorporacions')
                ->onDelete('cascade');
            $table->text('observacion_especifica_bien')->nullable(); // Observación para este bien en esta solicitud específica
            $table->timestamps(); // Opcional: para saber cuándo se asoció el bien a la solicitud

            // Clave primaria compuesta para asegurar que un bien no esté dos veces en la misma solicitud
            $table->primary(['bien_id', 'solicitud_desincorporacion_id'], 'bien_solicitud_des_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_solicitud_desincorporacion');
    }
};
