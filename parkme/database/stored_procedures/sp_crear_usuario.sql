DELIMITER //

DROP PROCEDURE IF EXISTS sp_crear_usuario //

CREATE PROCEDURE sp_crear_usuario(
    IN p_nombres VARCHAR(100),
    IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_contrasena VARCHAR(255),
    IN p_codigo_verificacion VARCHAR(10),
    IN p_codigo_expira_en DATETIME
)
BEGIN
    INSERT INTO usuarios (
        NOMBRES,
        APELLIDOS,
        CORREO,
        CONTRASENA,
        CODIGO_VERIFICACION,
        CODIGO_EXPIRA_EN,
        ROL,
        CORREO_VERIFICADO,
        ACTIVO,
        created_at,
        updated_at
    ) VALUES (
        p_nombres,
        p_apellidos,
        p_correo,
        p_contrasena,
        p_codigo_verificacion,
        p_codigo_expira_en,
        'administrador',
        0,
        1,
        NOW(),
        NOW()
    );
END //

DELIMITER ;
