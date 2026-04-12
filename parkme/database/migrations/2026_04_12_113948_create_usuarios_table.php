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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->string('NOMBRES', 100);
            $table->string('APELLIDOS', 100);
            $table->string('CORREO', 150)->unique();
            $table->string('CONTRASENA', 255);
            $table->enum('ROL', ['administrador', 'recepcionista', 'portero'])->default('administrador');
            $table->tinyInteger('CORREO_VERIFICADO')->default(0);
            $table->string('CODIGO_VERIFICACION', 10)->nullable();
            $table->dateTime('CODIGO_EXPIRA_EN')->nullable();
            $table->enum('TEMA', ['claro', 'oscuro'])->default('claro');
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
        Schema::dropIfExists('usuarios');
    }
};
