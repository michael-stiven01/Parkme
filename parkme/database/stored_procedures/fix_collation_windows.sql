-- ============================================================
-- Script de corrección: Collation fix para Windows (MariaDB)
-- Ejecutar en la base de datos: parkme
-- ============================================================

DELIMITER //

-- ========================
-- sp_verificar_codigo_email
-- ========================
DROP PROCEDURE IF EXISTS sp_verificar_codigo_email //

CREATE PROCEDURE sp_verificar_codigo_email(
    IN p_correo VARCHAR(150),
    IN p_codigo  VARCHAR(10)
)
BEGIN
    DECLARE v_id_usuario BIGINT;
    DECLARE v_codigo_guardado VARCHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_expira_en DATETIME;
    DECLARE v_estado INT DEFAULT 0;
    DECLARE v_mensaje VARCHAR(255);

    SELECT id_usuario, CODIGO_VERIFICACION, CODIGO_EXPIRA_EN 
    INTO v_id_usuario, v_codigo_guardado, v_expira_en
    FROM usuarios 
    WHERE CORREO = p_correo COLLATE utf8mb4_unicode_ci;

    IF v_id_usuario IS NULL THEN
        SET v_mensaje = 'Usuario no encontrado.';
    ELSEIF v_codigo_guardado IS NULL OR v_codigo_guardado != p_codigo COLLATE utf8mb4_unicode_ci THEN
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

-- ========================
-- sp_verificar_usuario
-- ========================
DROP PROCEDURE IF EXISTS sp_verificar_usuario //

CREATE PROCEDURE sp_verificar_usuario(
    IN p_correo VARCHAR(150)
)
BEGIN
    SELECT 
        id_usuario,
        NOMBRES,
        APELLIDOS,
        CORREO,
        CONTRASENA,
        ROL,
        CORREO_VERIFICADO,
        ACTIVO,
        id_empresa
    FROM usuarios
    WHERE CORREO = p_correo COLLATE utf8mb4_unicode_ci;
END //

-- ========================
-- sp_guardar_codigo_recuperacion
-- ========================
DROP PROCEDURE IF EXISTS sp_guardar_codigo_recuperacion //

CREATE PROCEDURE sp_guardar_codigo_recuperacion(
    IN p_correo    VARCHAR(150),
    IN p_codigo    VARCHAR(10),
    IN p_expira_en DATETIME
)
BEGIN
    UPDATE usuarios 
    SET CODIGO_VERIFICACION = p_codigo,
        CODIGO_EXPIRA_EN = p_expira_en,
        updated_at = NOW()
    WHERE CORREO = p_correo COLLATE utf8mb4_unicode_ci;
END //

-- ========================
-- sp_actualizar_contrasena
-- ========================
DROP PROCEDURE IF EXISTS sp_actualizar_contrasena //

CREATE PROCEDURE sp_actualizar_contrasena(
    IN p_correo           VARCHAR(150),
    IN p_nueva_contrasena VARCHAR(255)
)
BEGIN
    UPDATE usuarios 
    SET CONTRASENA = p_nueva_contrasena,
        updated_at = NOW()
    WHERE CORREO = p_correo COLLATE utf8mb4_unicode_ci;
END //

DELIMITER ;
