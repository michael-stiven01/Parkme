DROP PROCEDURE IF EXISTS sp_cambiar_estado_cupo;

DELIMITER //

CREATE PROCEDURE sp_cambiar_estado_cupo(
    IN p_id_cupo BIGINT,
    IN p_estado  ENUM('disponible', 'ocupado', 'mantenimiento')
)
BEGIN
    UPDATE cupos
    SET ESTADO     = p_estado,
        updated_at = NOW()
    WHERE id_cupo = p_id_cupo;
END //

DELIMITER ;
