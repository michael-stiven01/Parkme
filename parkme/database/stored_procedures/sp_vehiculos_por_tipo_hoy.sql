DROP PROCEDURE IF EXISTS sp_vehiculos_por_tipo_hoy;

DELIMITER //

CREATE PROCEDURE sp_vehiculos_por_tipo_hoy(
    IN p_id_empresa BIGINT,
    IN p_fecha      DATE
)
BEGIN
    SELECT
        tv.NOMBRE   AS tipo_vehiculo,
        COUNT(rp.id_registro) AS cantidad
    FROM registros_parqueo rp
    INNER JOIN vehiculos      v  ON rp.id_vehiculo    = v.id_vehiculo
    INNER JOIN tipos_vehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
    WHERE rp.id_empresa = p_id_empresa
      AND DATE(rp.FECHA_INGRESO) = p_fecha
    GROUP BY tv.id_tipo_vehiculo, tv.NOMBRE
    ORDER BY cantidad DESC;
END //

DELIMITER ;
