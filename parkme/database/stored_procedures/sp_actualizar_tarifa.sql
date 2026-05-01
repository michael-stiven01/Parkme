DROP PROCEDURE IF EXISTS sp_actualizar_tarifa;

DELIMITER //

CREATE PROCEDURE sp_actualizar_tarifa(
    IN p_id_tarifa BIGINT,
    IN p_nombre VARCHAR(100),
    IN p_tipo_tarifa ENUM('por_hora', 'por_fraccion', 'plana_dia', 'especial'),
    IN p_valor DECIMAL(10,2),
    IN p_fraccion_minutos INT,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME
)
BEGIN
    UPDATE tarifas SET
        NOMBRE = p_nombre,
        TIPO_TARIFA = p_tipo_tarifa,
        VALOR = p_valor,
        FRACCION_MINUTOS = p_fraccion_minutos,
        HORA_INICIO = p_hora_inicio,
        HORA_FIN = p_hora_fin,
        updated_at = NOW()
    WHERE id_tarifa = p_id_tarifa;
END //

DELIMITER ;
