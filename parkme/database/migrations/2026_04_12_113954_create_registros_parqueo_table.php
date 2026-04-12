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
        Schema::create('registros_parqueo', function (Blueprint $table) {
            $table->id('id_registro');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_cupo')->nullable();
            $table->unsignedBigInteger('id_tarifa')->nullable();
            $table->unsignedBigInteger('id_usuario_ingreso');
            $table->unsignedBigInteger('id_usuario_salida')->nullable();
            $table->dateTime('FECHA_INGRESO');
            $table->dateTime('FECHA_SALIDA')->nullable();
            $table->integer('TIEMPO_MINUTOS')->nullable();
            $table->decimal('VALOR_COBRADO', 10, 2)->nullable();
            $table->enum('ESTADO', ['activo', 'finalizado'])->default('activo');
            $table->tinyInteger('TICKET_INGRESO_ENVIADO')->default(0);
            $table->tinyInteger('TICKET_SALIDA_ENVIADO')->default(0);
            $table->timestamps();

            $table->foreign('id_empresa')
                  ->references('id_empresa')
                  ->on('empresas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('id_vehiculo')
                  ->references('id_vehiculo')
                  ->on('vehiculos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('id_cupo')
                  ->references('id_cupo')
                  ->on('cupos')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('id_tarifa')
                  ->references('id_tarifa')
                  ->on('tarifas')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('id_usuario_ingreso')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('id_usuario_salida')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_parqueo');
    }
};
