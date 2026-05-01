DROP PROCEDURE IF EXISTS sp_listar_tarifas;

DELIMITER //

CREATE PROCEDURE sp_listar_tarifas(
    IN p_id_empresa BIGINT
)
BEGIN
    SELECT 
        t.id_tarifa,
        t.id_empresa,
        t.id_tipo_vehiculo,
        t.NOMBRE,
        t.TIPO_TARIFA,
        t.VALOR,
        t.FRACCION_MINUTOS,
        t.HORA_INICIO,
        t.HORA_FIN,
        t.ACTIVO,
        tv.NOMBRE AS TIPO_VEHICULO
    FROM tarifas t
    LEFT JOIN tipos_vehiculo tv ON t.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE t.id_empresa = p_id_empresa
    ORDER BY t.ACTIVO DESC, t.id_tarifa DESC;
END //

DELIMITER ;
