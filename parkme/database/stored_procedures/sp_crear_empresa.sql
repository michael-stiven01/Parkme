DROP PROCEDURE IF EXISTS sp_crear_empresa;
DELIMITER //
CREATE PROCEDURE sp_crear_empresa(
    IN p_nombre VARCHAR(150),
    IN p_tipo VARCHAR(50),
    IN p_direccion VARCHAR(255),
    IN p_nit VARCHAR(20),
    IN p_correo VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO empresas (NOMBRE, TIPO, DIRECCION, NIT, CORREO, TELEFONO, ACTIVO, created_at, updated_at)
    VALUES (p_nombre, p_tipo, p_direccion, p_nit, p_correo, p_telefono, 1, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() AS id_empresa;
END //
DELIMITER ;
