DELIMITER //

DROP PROCEDURE IF EXISTS sp_actualizar_usuario //

CREATE PROCEDURE sp_actualizar_usuario(
    IN p_id_usuario BIGINT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_rol ENUM('administrador', 'recepcionista', 'portero')
)
BEGIN
    UPDATE usuarios
    SET 
        NOMBRES = p_nombres,
        APELLIDOS = p_apellidos,
        CORREO = p_correo,
        ROL = p_rol,
        updated_at = NOW()
    WHERE id_usuario = p_id_usuario;
END //

DELIMITER ;
