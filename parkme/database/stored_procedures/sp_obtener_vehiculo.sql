DROP PROCEDURE IF EXISTS sp_obtener_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_obtener_vehiculo(
    IN p_id_vehiculo BIGINT
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
        tv.NOMBRE AS TIPO_VEHICULO
    FROM vehiculos v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    INNER JOIN tipos_vehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE v.id_vehiculo = p_id_vehiculo
    LIMIT 1;
END //

DELIMITER ;
