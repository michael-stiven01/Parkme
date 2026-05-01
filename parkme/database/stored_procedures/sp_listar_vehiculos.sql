DROP PROCEDURE IF EXISTS sp_listar_vehiculos;

DELIMITER //

CREATE PROCEDURE sp_listar_vehiculos(
    IN p_id_empresa BIGINT,
    IN p_busqueda VARCHAR(255)
)
BEGIN
    SELECT 
        v.id_vehiculo,
        v.id_cliente,
        v.id_tipo_vehiculo,
        v.PLACA,
        v.MARCA,
        v.DESCRIPCION,
        v.ACTIVO,
        CONCAT(c.NOMBRES, ' ', c.APELLIDOS) AS NOMBRE_CLIENTE,
        tv.NOMBRE AS TIPO_VEHICULO,
        tv.ICONO AS ICONO_TIPO
    FROM vehiculos v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    INNER JOIN tipos_vehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE v.id_empresa = p_id_empresa
    AND (
        p_busqueda IS NULL OR p_busqueda = ''
        OR v.PLACA COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci
        OR c.NOMBRES COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci
        OR c.APELLIDOS COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci
    )
    ORDER BY v.id_vehiculo DESC;
END //

DELIMITER ;
