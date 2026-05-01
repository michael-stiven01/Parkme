DELIMITER //

DROP PROCEDURE IF EXISTS sp_obtener_usuario //

CREATE PROCEDURE sp_obtener_usuario(
    IN p_id_usuario BIGINT
)
BEGIN
    SELECT 
        id_usuario,
        id_empresa,
        NOMBRES,
        APELLIDOS,
        CORREO,
        ROL,
        ACTIVO,
        DEBE_CAMBIAR_CONTRASENA,
        CORREO_VERIFICADO
    FROM usuarios
    WHERE id_usuario = p_id_usuario;
END //

DELIMITER ;
