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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_tipo_vehiculo');
            $table->string('PLACA', 10);
            $table->string('MARCA', 80)->nullable();
            $table->string('DESCRIPCION', 255)->nullable();
            $table->tinyInteger('ACTIVO')->default(1);
            $table->timestamps();

            $table->unique(['PLACA', 'id_empresa']);

            $table->foreign('id_cliente')
                  ->references('id_cliente')
                  ->on('clientes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('id_empresa')
                  ->references('id_empresa')
                  ->on('empresas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('id_tipo_vehiculo')
                  ->references('id_tipo_vehiculo')
                  ->on('tipos_vehiculo')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
