DELIMITER //

DROP PROCEDURE IF EXISTS sp_crear_usuario_empresa //

CREATE PROCEDURE sp_crear_usuario_empresa(
    IN p_id_empresa BIGINT,
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_contrasena VARCHAR(255),
    IN p_rol ENUM('administrador', 'recepcionista', 'portero'),
    IN p_codigo_verificacion VARCHAR(10),
    IN p_codigo_expira_en DATETIME
)
BEGIN
    INSERT INTO usuarios (
        id_empresa,
        NOMBRES,
        APELLIDOS,
        CORREO,
        CONTRASENA,
        ROL,
        CODIGO_VERIFICACION,
        CODIGO_EXPIRA_EN,
        CORREO_VERIFICADO,
        DEBE_CAMBIAR_CONTRASENA,
        ACTIVO,
        created_at,
        updated_at
    ) VALUES (
        p_id_empresa,
        p_nombres,
        p_apellidos,
        p_correo,
        p_contrasena,
        p_rol,
        p_codigo_verificacion,
        p_codigo_expira_en,
        0, -- Requiere verificación de correo
        1, -- Obligar cambio de contraseña
        1, -- Activo
        NOW(),
        NOW()
    );

    SELECT LAST_INSERT_ID() AS id_usuario;
END //

DELIMITER ;
