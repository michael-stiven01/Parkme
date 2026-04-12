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
        Schema::create('tipos_vehiculo', function (Blueprint $table) {
            $table->id('id_tipo_vehiculo');
            $table->string('NOMBRE', 50)->unique();
            $table->string('ICONO', 50)->nullable();
            $table->tinyInteger('ACTIVO')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_vehiculo');
    }
};
