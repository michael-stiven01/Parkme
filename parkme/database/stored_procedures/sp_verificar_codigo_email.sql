DELIMITER //

DROP PROCEDURE IF EXISTS sp_verificar_codigo_email //

CREATE PROCEDURE sp_verificar_codigo_email(
    IN p_correo VARCHAR(150),
    IN p_codigo VARCHAR(10)
)
BEGIN
    DECLARE v_id_usuario BIGINT;
    DECLARE v_codigo_guardado VARCHAR(10);
    DECLARE v_expira_en DATETIME;
    DECLARE v_estado INT DEFAULT 0;
    DECLARE v_mensaje VARCHAR(255);

    SELECT id_usuario, CODIGO_VERIFICACION, CODIGO_EXPIRA_EN 
    INTO v_id_usuario, v_codigo_guardado, v_expira_en
    FROM usuarios 
    WHERE CORREO = p_correo;

    IF v_id_usuario IS NULL THEN
        SET v_mensaje = 'Usuario no encontrado.';
    ELSEIF v_codigo_guardado IS NULL OR v_codigo_guardado != p_codigo THEN
        SET v_mensaje = 'Código incorrecto.';
    ELSEIF v_expira_en < NOW() THEN
        SET v_mensaje = 'El código ha expirado.';
    ELSE
        UPDATE usuarios 
        SET CORREO_VERIFICADO = 1,
            CODIGO_VERIFICACION = NULL,
            CODIGO_EXPIRA_EN = NULL,
            updated_at = NOW()
        WHERE id_usuario = v_id_usuario;
        
        SET v_estado = 1;
        SET v_mensaje = 'Correo verificado exitosamente.';
    END IF;

    SELECT v_estado AS estado, v_mensaje AS mensaje;
END //

DELIMITER ;
