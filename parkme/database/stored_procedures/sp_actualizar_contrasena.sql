DELIMITER //

DROP PROCEDURE IF EXISTS sp_actualizar_contrasena //

CREATE PROCEDURE sp_actualizar_contrasena(
    IN p_correo           VARCHAR(150) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_nueva_contrasena VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
    UPDATE usuarios 
    SET CONTRASENA = p_nueva_contrasena,
        updated_at = NOW()
    WHERE CORREO = p_correo;
END //

DELIMITER ;
