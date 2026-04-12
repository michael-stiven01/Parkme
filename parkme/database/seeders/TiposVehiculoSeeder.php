<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoVehiculo;

class TiposVehiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['NOMBRE' => 'Automóvil', 'ICONO' => 'key'],               // Llave del auto
            ['NOMBRE' => 'Motocicleta', 'ICONO' => 'fire'],            // Motor/Rapidez
            ['NOMBRE' => 'Camioneta', 'ICONO' => 'truck'],             // Icono de recolección/pickup
            ['NOMBRE' => 'Camión', 'ICONO' => 'cube'],                 // Carga/Pesado
            ['NOMBRE' => 'Bicicleta', 'ICONO' => 'arrow-path'],        // Ciclo/Pedales
            ['NOMBRE' => 'Patineta Eléctrica', 'ICONO' => 'battery-100'], // Energía/Batería
        ];

        foreach ($tipos as $tipo) {
            TipoVehiculo::updateOrCreate(
                ['NOMBRE' => $tipo['NOMBRE']],
                ['ICONO' => $tipo['ICONO'], 'ACTIVO' => 1]
            );
        }
    }
}
