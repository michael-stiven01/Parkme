DELIMITER //

DROP PROCEDURE IF EXISTS sp_guardar_codigo_recuperacion //

CREATE PROCEDURE sp_guardar_codigo_recuperacion(
    IN p_correo    VARCHAR(150) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_codigo    VARCHAR(10)  CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_expira_en DATETIME
)
BEGIN
    UPDATE usuarios 
    SET CODIGO_VERIFICACION = p_codigo,
        CODIGO_EXPIRA_EN = p_expira_en,
        updated_at = NOW()
    WHERE CORREO = p_correo;
END //

DELIMITER ;
