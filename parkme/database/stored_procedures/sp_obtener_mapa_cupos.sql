DROP PROCEDURE IF EXISTS sp_obtener_mapa_cupos;

DELIMITER //

CREATE PROCEDURE sp_obtener_mapa_cupos(
    IN p_id_empresa BIGINT
)
BEGIN
    SELECT
        c.id_cupo,
        c.CODIGO,
        c.ESTADO,
        c.FILA,
        c.COLUMNA,
        tv.NOMBRE AS TIPO_VEHICULO,
        NULL      AS PLACA_ACTUAL
    FROM cupos c
    LEFT JOIN tipos_vehiculo tv ON c.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE c.id_empresa = p_id_empresa
    ORDER BY c.FILA ASC, c.COLUMNA ASC;
END //

DELIMITER ;
