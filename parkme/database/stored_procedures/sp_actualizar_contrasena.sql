DELIMITER //

DROP PROCEDURE IF EXISTS sp_actualizar_contrasena //

CREATE PROCEDURE sp_actualizar_contrasena(
    IN p_correo VARCHAR(150),
    IN p_nueva_contrasena VARCHAR(255)
)
BEGIN
    UPDATE usuarios 
    SET CONTRASENA = p_nueva_contrasena,
        updated_at = NOW()
    WHERE CORREO = p_correo;
END //

DELIMITER ;
