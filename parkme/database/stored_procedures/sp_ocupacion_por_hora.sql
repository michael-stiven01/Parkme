DROP PROCEDURE IF EXISTS sp_ocupacion_por_hora;

DELIMITER //

CREATE PROCEDURE sp_ocupacion_por_hora(
    IN p_id_empresa BIGINT,
    IN p_fecha      DATE
)
BEGIN
    SELECT
        HOUR(rp.FECHA_INGRESO)       AS hora,
        LPAD(HOUR(rp.FECHA_INGRESO), 2, '0') AS etiqueta_hora,
        COUNT(rp.id_registro)        AS total_ingresos
    FROM registros_parqueo rp
    WHERE rp.id_empresa = p_id_empresa
      AND DATE(rp.FECHA_INGRESO) = p_fecha
    GROUP BY HOUR(rp.FECHA_INGRESO)
    ORDER BY hora ASC;
END //

DELIMITER ;
