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
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id('id_tarifa');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_tipo_vehiculo')->nullable();
            $table->string('NOMBRE', 100);
            $table->enum('TIPO_TARIFA', ['por_hora', 'por_fraccion', 'plana_dia', 'especial']);
            $table->decimal('VALOR', 10, 2);
            $table->integer('FRACCION_MINUTOS')->nullable();
            $table->time('HORA_INICIO')->nullable();
            $table->time('HORA_FIN')->nullable();
            $table->tinyInteger('ACTIVO')->default(1);
            $table->timestamps();

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
        Schema::dropIfExists('tarifas');
    }
};
