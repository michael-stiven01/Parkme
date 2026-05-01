DROP PROCEDURE IF EXISTS sp_buscar_vehiculo_por_placa;

DELIMITER //

CREATE PROCEDURE sp_buscar_vehiculo_por_placa(
    IN p_id_empresa BIGINT,
    IN p_placa VARCHAR(10)
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
        c.CORREO AS CORREO_CLIENTE,
        tv.NOMBRE AS TIPO_VEHICULO,
        tv.ICONO AS ICONO_TIPO
    FROM vehiculos v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    INNER JOIN tipos_vehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE v.id_empresa = p_id_empresa
    AND v.PLACA COLLATE utf8mb4_unicode_ci = p_placa COLLATE utf8mb4_unicode_ci
    LIMIT 1;
END //

DELIMITER ;
