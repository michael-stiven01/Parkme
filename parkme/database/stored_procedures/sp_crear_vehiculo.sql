DROP PROCEDURE IF EXISTS sp_crear_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_crear_vehiculo(
    IN p_id_empresa BIGINT,
    IN p_id_cliente BIGINT,
    IN p_id_tipo_vehiculo BIGINT,
    IN p_placa VARCHAR(10),
    IN p_marca VARCHAR(80),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    DECLARE v_placa_existe INT;

    -- Verificar si la placa ya existe para la empresa
    SELECT COUNT(*) INTO v_placa_existe
    FROM vehiculos
    WHERE PLACA COLLATE utf8mb4_unicode_ci = p_placa COLLATE utf8mb4_unicode_ci
    AND id_empresa = p_id_empresa;

    IF v_placa_existe > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La placa ya está registrada en esta empresa.';
    ELSE
        INSERT INTO vehiculos (
            id_empresa,
            id_cliente,
            id_tipo_vehiculo,
            PLACA,
            MARCA,
            DESCRIPCION,
            ACTIVO,
            created_at,
            updated_at
        ) VALUES (
            p_id_empresa,
            p_id_cliente,
            p_id_tipo_vehiculo,
            UPPER(p_placa),
            p_marca,
            p_descripcion,
            1,
            NOW(),
            NOW()
        );
        SELECT LAST_INSERT_ID() AS id_vehiculo;
    END IF;
END //

DELIMITER ;
