DELIMITER //

DROP PROCEDURE IF EXISTS sp_buscar_cliente_por_correo//

CREATE PROCEDURE sp_buscar_cliente_por_correo(
    IN p_id_empresa BIGINT,
    IN p_correo VARCHAR(150)
)
BEGIN
    SELECT 
        c.id_cliente,
        c.id_empresa,
        c.NOMBRES,
        c.APELLIDOS,
        c.CORREO,
        c.TELEFONO,
        c.ACTIVO
    FROM clientes c
    WHERE c.id_empresa = p_id_empresa
    AND c.CORREO = p_correo COLLATE utf8mb4_unicode_ci
    AND c.ACTIVO = 1;
END //

DELIMITER ;
