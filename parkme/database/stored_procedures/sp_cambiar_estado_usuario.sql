DELIMITER //

DROP PROCEDURE IF EXISTS sp_cambiar_estado_usuario //

CREATE PROCEDURE sp_cambiar_estado_usuario(
    IN p_id_usuario BIGINT,
    IN p_activo TINYINT
)
BEGIN
    UPDATE usuarios
    SET 
        ACTIVO = p_activo,
        updated_at = NOW()
    WHERE id_usuario = p_id_usuario;
END //

DELIMITER ;
