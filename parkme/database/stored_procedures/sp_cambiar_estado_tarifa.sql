DROP PROCEDURE IF EXISTS sp_cambiar_estado_tarifa;

DELIMITER //

CREATE PROCEDURE sp_cambiar_estado_tarifa(
    IN p_id_tarifa BIGINT,
    IN p_activo TINYINT
)
BEGIN
    UPDATE tarifas 
    SET ACTIVO = p_activo,
        updated_at = NOW()
    WHERE id_tarifa = p_id_tarifa;
END //

DELIMITER ;
