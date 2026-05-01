<?php
use Illuminate\Support\Facades\DB;

try {
    $clientes = DB::select('CALL sp_listar_clientes(?, ?)', [1, null]);
    echo "sp_listar_clientes existe. Clientes: " . count($clientes) . "\n";
} catch (\Exception $e) {
    echo "Error en sp_listar_clientes: " . $e->getMessage() . "\n";
}

try {
    $nuevo = DB::select('CALL sp_crear_cliente(?, ?, ?, ?, ?)', [1, 'Test', 'User', 'test@example.com', '123456789']);
    echo "sp_crear_cliente existe. ID insertado: " . json_encode($nuevo) . "\n";
} catch (\Exception $e) {
    echo "Error en sp_crear_cliente: " . $e->getMessage() . "\n";
}
