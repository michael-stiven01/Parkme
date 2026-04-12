DELIMITER //

DROP PROCEDURE IF EXISTS sp_listar_tipos_vehiculo //

CREATE PROCEDURE sp_listar_tipos_vehiculo()
BEGIN
    SELECT 
        id_tipo_vehiculo,
        NOMBRE,
        ICONO,
        ACTIVO
    FROM tipos_vehiculo
    WHERE ACTIVO = 1;
END //

DELIMITER ;
