DELIMITER //

DROP PROCEDURE IF EXISTS sp_registrar_bitacora //

CREATE PROCEDURE sp_registrar_bitacora(
    IN p_id_usuario BIGINT,
    IN p_id_empresa BIGINT,
    IN p_accion VARCHAR(100),
    IN p_modulo VARCHAR(50),
    IN p_detalle TEXT,
    IN p_ip VARCHAR(45)
)
BEGIN
    INSERT INTO bitacora (
        id_usuario, id_empresa, ACCION, MODULO, DETALLE, IP, FECHA
    ) VALUES (
        p_id_usuario, p_id_empresa, p_accion, p_modulo, p_detalle, p_ip, NOW()
    );
END //

DELIMITER ;
