DROP PROCEDURE IF EXISTS sp_crear_tarifa;

DELIMITER //

CREATE PROCEDURE sp_crear_tarifa(
    IN p_id_empresa BIGINT,
    IN p_id_tipo_vehiculo BIGINT,
    IN p_nombre VARCHAR(100),
    IN p_tipo_tarifa ENUM('por_hora', 'por_fraccion', 'plana_dia', 'especial'),
    IN p_valor DECIMAL(10,2),
    IN p_fraccion_minutos INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    INSERT INTO tarifas (
        id_empresa,
        id_tipo_vehiculo,
        NOMBRE,
        TIPO_TARIFA,
        VALOR,
        FRACCION_MINUTOS,
        HORA_INICIO,
        HORA_FIN,
        ACTIVO,
        created_at,
        updated_at
    ) VALUES (
        p_id_empresa,
        p_id_tipo_vehiculo,
        p_nombre,
        p_tipo_tarifa,
        p_valor,
        p_fraccion_minutos,
        p_hora_inicio,
        p_hora_fin,
        1,
        NOW(),
        NOW()
    );
END //

DELIMITER ;
