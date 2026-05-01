DROP PROCEDURE IF EXISTS sp_asignar_empresa_usuario;
DELIMITER //
CREATE PROCEDURE sp_asignar_empresa_usuario(
    IN p_id_usuario BIGINT,
    IN p_id_empresa BIGINT
)
BEGIN
    UPDATE usuarios 
    SET id_empresa = p_id_empresa,
        updated_at = NOW()
    WHERE id_usuario = p_id_usuario;
END //
DELIMITER ;
