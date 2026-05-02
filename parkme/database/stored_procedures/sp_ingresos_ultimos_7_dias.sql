DROP PROCEDURE IF EXISTS sp_ingresos_ultimos_7_dias;

DELIMITER //

CREATE PROCEDURE sp_ingresos_ultimos_7_dias(
    IN p_id_empresa BIGINT
)
BEGIN
    -- Genera los últimos 7 días y hace LEFT JOIN para garantizar todas las fechas
    WITH fechas AS (
        SELECT CURDATE()               AS fecha UNION ALL
        SELECT CURDATE() - INTERVAL 1 DAY      UNION ALL
        SELECT CURDATE() - INTERVAL 2 DAY      UNION ALL
        SELECT CURDATE() - INTERVAL 3 DAY      UNION ALL
        SELECT CURDATE() - INTERVAL 4 DAY      UNION ALL
        SELECT CURDATE() - INTERVAL 5 DAY      UNION ALL
        SELECT CURDATE() - INTERVAL 6 DAY
    )
    SELECT
        f.fecha,
        DATE_FORMAT(f.fecha, '%d/%m') AS etiqueta,
        IFNULL(COUNT(rp.id_registro),      0)    AS total_ingresos,
        IFNULL(SUM(rp.VALOR_COBRADO),      0.00) AS total_recaudado
    FROM fechas f
    LEFT JOIN registros_parqueo rp
           ON DATE(rp.FECHA_INGRESO) = f.fecha
          AND rp.id_empresa          = p_id_empresa
    GROUP BY f.fecha
    ORDER BY f.fecha ASC;
END //

DELIMITER ;
