DELIMITER //

DROP PROCEDURE IF EXISTS sp_verificar_usuario //

CREATE PROCEDURE sp_verificar_usuario(
    IN p_correo VARCHAR(150)
)
BEGIN
    SELECT 
        id_usuario,
        NOMBRES,
        APELLIDOS,
        CORREO,
        CONTRASENA,
        ROL,
        CORREO_VERIFICADO,
        ACTIVO,
        id_empresa
    FROM usuarios
    WHERE CORREO = p_correo;
END //

DELIMITER ;
