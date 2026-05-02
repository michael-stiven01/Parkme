<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cupos', function (Blueprint $table) {
            // Restricción: no puede existir dos cupos en la misma posición
            // para la misma empresa. MySQL permite múltiples NULLs en unique,
            // por lo que cupos sin FILA/COLUMNA no conflictúan entre sí.
            $table->unique(['id_empresa', 'FILA', 'COLUMNA'], 'uq_cupos_posicion');
        });
    }

    public function down(): void
    {
        Schema::table('cupos', function (Blueprint $table) {
            $table->dropUnique('uq_cupos_posicion');
        });
    }
};
