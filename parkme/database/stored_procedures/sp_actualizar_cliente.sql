DELIMITER //

DROP PROCEDURE IF EXISTS sp_actualizar_cliente//

CREATE PROCEDURE sp_actualizar_cliente(
    IN p_id_cliente BIGINT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    UPDATE clientes 
    SET 
        NOMBRES = p_nombres,
        APELLIDOS = p_apellidos,
        CORREO = p_correo,
        TELEFONO = p_telefono,
        updated_at = NOW()
    WHERE id_cliente = p_id_cliente;
END //

DELIMITER ;
