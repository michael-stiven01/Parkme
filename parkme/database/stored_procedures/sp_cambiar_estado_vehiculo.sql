DROP PROCEDURE IF EXISTS sp_cambiar_estado_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_cambiar_estado_vehiculo(
    IN p_id_vehiculo BIGINT,
    IN p_activo TINYINT
)
BEGIN
    UPDATE vehiculos
    SET 
        ACTIVO = p_activo,
        updated_at = NOW()
    WHERE id_vehiculo = p_id_vehiculo;
END //

DELIMITER ;
