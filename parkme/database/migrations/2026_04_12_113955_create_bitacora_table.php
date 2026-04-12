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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->string('ACCION', 100);
            $table->string('MODULO', 50);
            $table->text('DETALLE')->nullable();
            $table->string('IP', 45)->nullable();
            $table->dateTime('FECHA')->useCurrent();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('id_empresa')
                  ->references('id_empresa')
                  ->on('empresas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
