DELIMITER //

DROP PROCEDURE IF EXISTS sp_listar_usuarios //

CREATE PROCEDURE sp_listar_usuarios(
    IN p_id_empresa BIGINT
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
    WHERE id_empresa <=> p_id_empresa
    ORDER BY id_usuario DESC;
END //

DELIMITER ;
