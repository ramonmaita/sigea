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
        Schema::create('detalle_oficinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_oficina')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefonos')->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('path_logo')->nullable(); // Para guardar la ruta al archivo del logo
            $table->json('autoridades')->nullable(); // Para guardar una lista de autoridades como JSON
            $table->json('proyectos_acciones')->nullable(); // O después del último campo que tengas
            $table->json('fuentes_financiamiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_oficinas');
    }
};
