DELIMITER //

DROP PROCEDURE IF EXISTS sp_verificar_usuario //

CREATE PROCEDURE sp_verificar_usuario(
    IN p_correo VARCHAR(150) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
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
        id_empresa,
        CODIGO_EXPIRA_EN
    FROM usuarios
    WHERE CORREO = p_correo;
END //

DELIMITER ;
