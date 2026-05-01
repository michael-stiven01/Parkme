DELIMITER //

DROP PROCEDURE IF EXISTS sp_obtener_cliente//

CREATE PROCEDURE sp_obtener_cliente(
    IN p_id_cliente BIGINT
)
BEGIN
    SELECT 
        c.id_cliente,
        c.id_empresa,
        c.NOMBRES,
        c.APELLIDOS,
        c.CORREO,
        c.TELEFONO,
        c.ACTIVO,
        c.created_at,
        c.updated_at,
        (SELECT COUNT(*) FROM vehiculos v WHERE v.id_cliente = c.id_cliente AND v.ACTIVO = 1) AS vehiculos_registrados
    FROM clientes c
    WHERE c.id_cliente = p_id_cliente;
END //

DELIMITER ;
