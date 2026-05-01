DROP PROCEDURE IF EXISTS sp_obtener_empresa;
DELIMITER //
CREATE PROCEDURE sp_obtener_empresa(
    IN p_id_empresa BIGINT
)
BEGIN
    SELECT 
        id_empresa,
        NOMBRE,
        TIPO,
        DIRECCION,
        NIT,
        CORREO,
        TELEFONO,
        ACTIVO
    FROM empresas
    WHERE id_empresa = p_id_empresa;
END //
DELIMITER ;
