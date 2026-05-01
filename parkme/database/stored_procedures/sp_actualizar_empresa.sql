DROP PROCEDURE IF EXISTS sp_actualizar_empresa;
DELIMITER //
CREATE PROCEDURE sp_actualizar_empresa(
    IN p_id_empresa BIGINT,
    IN p_nombre VARCHAR(150),
    IN p_tipo VARCHAR(50),
    IN p_direccion VARCHAR(255),
    IN p_correo VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    UPDATE empresas 
    SET NOMBRE = p_nombre,
        TIPO = p_tipo,
        DIRECCION = p_direccion,
        CORREO = p_correo,
        TELEFONO = p_telefono,
        updated_at = NOW()
    WHERE id_empresa = p_id_empresa;
END //
DELIMITER ;
