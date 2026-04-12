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
        Schema::create('cupos', function (Blueprint $table) {
            $table->id('id_cupo');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_tipo_vehiculo')->nullable();
            $table->string('CODIGO', 20);
            $table->enum('ESTADO', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible');
            $table->string('FILA', 5)->nullable();
            $table->integer('COLUMNA')->nullable();
            $table->timestamps();

            $table->unique(['id_empresa', 'CODIGO']);

            $table->foreign('id_empresa')
                  ->references('id_empresa')
                  ->on('empresas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('id_tipo_vehiculo')
                  ->references('id_tipo_vehiculo')
                  ->on('tipos_vehiculo')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupos');
    }
};
