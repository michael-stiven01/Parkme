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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id('id_empresa');
            $table->string('NOMBRE', 150);
            $table->string('TIPO', 50);
            $table->string('DIRECCION', 255);
            $table->string('NIT', 20)->unique();
            $table->string('CORREO', 150);
            $table->string('TELEFONO', 20);
            $table->tinyInteger('ACTIVO')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
