DELIMITER //

DROP PROCEDURE IF EXISTS sp_crear_cliente//

CREATE PROCEDURE sp_crear_cliente(
    IN p_id_empresa BIGINT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO clientes (
        id_empresa, NOMBRES, APELLIDOS, CORREO, TELEFONO, ACTIVO, created_at, updated_at
    ) VALUES (
        p_id_empresa, p_nombres, p_apellidos, p_correo, p_telefono, 1, NOW(), NOW()
    );
    
    SELECT LAST_INSERT_ID() AS id_cliente;
END //

DELIMITER ;
