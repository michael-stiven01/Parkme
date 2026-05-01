<?php
use Illuminate\Support\Facades\DB;

$sps = [
    'sp_listar_clientes.sql',
    'sp_crear_cliente.sql',
    'sp_actualizar_cliente.sql',
    'sp_cambiar_estado_cliente.sql',
    'sp_obtener_cliente.sql',
    'sp_buscar_cliente_por_correo.sql'
];

foreach ($sps as $sp) {
    $path = database_path('stored_procedures/' . $sp);
    if (file_exists($path)) {
        echo "Cargando $sp...\n";
        $sql = file_get_contents($path);
        
        // Remove DELIMITER commands
        $sql = preg_replace('/DELIMITER \/\//', '', $sql);
        $sql = preg_replace('/DELIMITER ;/', '', $sql);
        
        // Replace // with empty string
        // Actually, just extracting what is between DROP PROCEDURE and END //
        // A better approach is to explode by '//' and run statements
        $statements = explode('//', $sql);
        foreach ($statements as $statement) {
            $stmt = trim($statement);
            if (!empty($stmt) && strpos($stmt, 'DELIMITER') === false) {
                DB::unprepared($stmt);
            }
        }
    } else {
        echo "No se encontró $sp\n";
    }
}
echo "Listo!\n";
