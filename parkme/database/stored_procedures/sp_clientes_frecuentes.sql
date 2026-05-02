DROP PROCEDURE IF EXISTS sp_clientes_frecuentes;

DELIMITER //

CREATE PROCEDURE sp_clientes_frecuentes(
    IN p_id_empresa BIGINT,
    IN p_limite     INT
)
BEGIN
    SELECT
        CONCAT(c.NOMBRES, ' ', c.APELLIDOS) AS nombre_cliente,
        c.CORREO                            AS correo_cliente,
        COUNT(rp.id_registro)               AS total_visitas,
        MAX(rp.FECHA_INGRESO)               AS ultima_visita
    FROM registros_parqueo rp
    INNER JOIN vehiculos v ON rp.id_vehiculo = v.id_vehiculo
    INNER JOIN clientes  c ON v.id_cliente   = c.id_cliente
    WHERE rp.id_empresa = p_id_empresa
    GROUP BY c.id_cliente, c.NOMBRES, c.APELLIDOS, c.CORREO
    ORDER BY total_visitas DESC
    LIMIT p_limite;
END //

DELIMITER ;
