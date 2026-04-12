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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->unsignedBigInteger('id_empresa');
            $table->string('NOMBRES', 100);
            $table->string('APELLIDOS', 100);
            $table->string('CORREO', 150);
            $table->string('TELEFONO', 20)->nullable();
            $table->tinyInteger('ACTIVO')->default(1);
            $table->timestamps();

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
        Schema::dropIfExists('clientes');
    }
};
