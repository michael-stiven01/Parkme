DROP PROCEDURE IF EXISTS sp_verificar_vehiculo_en_parqueo;

DELIMITER //

CREATE PROCEDURE sp_verificar_vehiculo_en_parqueo(
    IN p_id_empresa BIGINT,
    IN p_placa      VARCHAR(10)
)
BEGIN
    SELECT
        IF(rp.id_registro IS NOT NULL, 1, 0)  AS esta_adentro,
        rp.id_registro                         AS id_registro,
        rp.FECHA_INGRESO                       AS fecha_ingreso,
        v.id_vehiculo                          AS id_vehiculo,
        v.PLACA                                AS placa,
        CONCAT(c.NOMBRES, ' ', c.APELLIDOS)   AS nombre_cliente,
        c.CORREO                               AS correo_cliente,
        cu.CODIGO                              AS codigo_cupo,
        cu.id_cupo                             AS id_cupo
    FROM   vehiculos v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    LEFT JOIN registros_parqueo rp
           ON  rp.id_vehiculo = v.id_vehiculo
           AND rp.ESTADO      = 'activo'
    LEFT JOIN cupos cu ON rp.id_cupo = cu.id_cupo
    WHERE  v.id_empresa = p_id_empresa
      AND  v.PLACA COLLATE utf8mb4_unicode_ci = p_placa COLLATE utf8mb4_unicode_ci
      AND  v.ACTIVO = 1
    LIMIT 1;
END //

DELIMITER ;
