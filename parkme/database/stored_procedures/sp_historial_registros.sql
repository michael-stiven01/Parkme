DROP PROCEDURE IF EXISTS sp_historial_registros;

DELIMITER //

CREATE PROCEDURE sp_historial_registros(
    IN p_id_empresa   BIGINT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE,
    IN p_busqueda     VARCHAR(100)
)
BEGIN
    -- Normalizar búsqueda
    SET p_busqueda = TRIM(IFNULL(p_busqueda, ''));

    SELECT
        rp.id_registro,
        rp.FECHA_INGRESO,
        rp.FECHA_SALIDA,
        rp.TIEMPO_MINUTOS,
        rp.VALOR_COBRADO,
        rp.ESTADO,
        v.PLACA,
        v.MARCA,
        CONCAT(c.NOMBRES, ' ', c.APELLIDOS) AS nombre_cliente,
        c.CORREO                            AS correo_cliente,
        tv.NOMBRE                           AS tipo_vehiculo,
        cu.CODIGO                           AS codigo_cupo,
        t.NOMBRE                            AS nombre_tarifa,
        CONCAT(u_in.NOMBRES, ' ', u_in.APELLIDOS) AS usuario_ingreso,
        CONCAT(u_sa.NOMBRES, ' ', u_sa.APELLIDOS) AS usuario_salida,
        rp.TICKET_INGRESO_ENVIADO,
        rp.TICKET_SALIDA_ENVIADO
    FROM   registros_parqueo rp
    INNER JOIN vehiculos     v    ON rp.id_vehiculo        = v.id_vehiculo
    INNER JOIN clientes      c    ON v.id_cliente           = c.id_cliente
    INNER JOIN tipos_vehiculo tv  ON v.id_tipo_vehiculo     = tv.id_tipo_vehiculo
    INNER JOIN usuarios      u_in ON rp.id_usuario_ingreso  = u_in.id_usuario
    LEFT  JOIN cupos         cu   ON rp.id_cupo             = cu.id_cupo
    LEFT  JOIN tarifas       t    ON rp.id_tarifa            = t.id_tarifa
    LEFT  JOIN usuarios      u_sa ON rp.id_usuario_salida   = u_sa.id_usuario
    WHERE  rp.id_empresa = p_id_empresa
      AND  rp.ESTADO     = 'finalizado'
      -- Filtro por rango de fechas (sobre FECHA_INGRESO)
      AND  (p_fecha_inicio IS NULL OR DATE(rp.FECHA_INGRESO) >= p_fecha_inicio)
      AND  (p_fecha_fin    IS NULL OR DATE(rp.FECHA_INGRESO) <= p_fecha_fin)
      -- Filtro por búsqueda: placa o nombre del cliente
      AND  (
               p_busqueda = ''
               OR v.PLACA        LIKE CONCAT('%', p_busqueda, '%')
               OR c.NOMBRES      LIKE CONCAT('%', p_busqueda, '%')
               OR c.APELLIDOS    LIKE CONCAT('%', p_busqueda, '%')
               OR CONCAT(c.NOMBRES, ' ', c.APELLIDOS) LIKE CONCAT('%', p_busqueda, '%')
           )
    ORDER BY rp.FECHA_INGRESO DESC;
END //

DELIMITER ;
