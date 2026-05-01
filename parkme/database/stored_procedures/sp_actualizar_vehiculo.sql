DROP PROCEDURE IF EXISTS sp_actualizar_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_actualizar_vehiculo(
    IN p_id_vehiculo BIGINT,
    IN p_descripcion VARCHAR(255)
)
BEGIN
    UPDATE vehiculos
    SET 
        DESCRIPCION = p_descripcion,
        updated_at = NOW()
    WHERE id_vehiculo = p_id_vehiculo;
END //

DELIMITER ;
