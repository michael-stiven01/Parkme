DROP PROCEDURE IF EXISTS sp_salida_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_salida_vehiculo(
    IN p_id_empresa       BIGINT,
    IN p_placa            VARCHAR(10),
    IN p_id_usuario_salida BIGINT
)
BEGIN
    DECLARE v_error                VARCHAR(60)   DEFAULT NULL;
    DECLARE v_id_registro          BIGINT        DEFAULT NULL;
    DECLARE v_id_vehiculo          BIGINT        DEFAULT NULL;
    DECLARE v_id_cupo              BIGINT        DEFAULT NULL;
    DECLARE v_id_tipo_vehiculo     BIGINT        DEFAULT NULL;
    DECLARE v_fecha_ingreso        DATETIME      DEFAULT NULL;
    DECLARE v_fecha_salida         DATETIME      DEFAULT NULL;
    DECLARE v_placa                VARCHAR(10)   DEFAULT NULL;
    DECLARE v_nombre_cliente       VARCHAR(200)  DEFAULT NULL;
    DECLARE v_correo_cliente       VARCHAR(150)  DEFAULT NULL;
    DECLARE v_codigo_cupo          VARCHAR(20)   DEFAULT NULL;

    -- Variables para cálculo de tarifa (misma lógica que sp_calcular_tarifa)
    DECLARE v_id_tarifa            BIGINT        DEFAULT NULL;
    DECLARE v_nombre_tarifa        VARCHAR(100)  DEFAULT NULL;
    DECLARE v_tipo_tarifa          VARCHAR(50)   DEFAULT NULL;
    DECLARE v_valor                DECIMAL(10,2) DEFAULT NULL;
    DECLARE v_fraccion_minutos     INT           DEFAULT NULL;
    DECLARE v_minutos_transcurridos INT          DEFAULT 0;
    DECLARE v_total_cobrado        DECIMAL(10,2) DEFAULT 0;
    DECLARE v_hora_salida          TIME          DEFAULT NULL;

    -- -------------------------------------------------------
    -- 1. Buscar el registro activo por placa dentro de la empresa
    -- -------------------------------------------------------
    SELECT rp.id_registro,
           rp.id_vehiculo,
           rp.id_cupo,
           rp.FECHA_INGRESO,
           v.PLACA,
           v.id_tipo_vehiculo,
           CONCAT(c.NOMBRES, ' ', c.APELLIDOS),
           c.CORREO,
           cu.CODIGO
    INTO   v_id_registro,
           v_id_vehiculo,
           v_id_cupo,
           v_fecha_ingreso,
           v_placa,
           v_id_tipo_vehiculo,
           v_nombre_cliente,
           v_correo_cliente,
           v_codigo_cupo
    FROM   registros_parqueo rp
    INNER JOIN vehiculos v ON rp.id_vehiculo = v.id_vehiculo
    INNER JOIN clientes  c ON v.id_cliente   = c.id_cliente
    LEFT  JOIN cupos    cu ON rp.id_cupo     = cu.id_cupo
    WHERE  rp.id_empresa = p_id_empresa
      AND  v.PLACA COLLATE utf8mb4_unicode_ci = p_placa COLLATE utf8mb4_unicode_ci
      AND  rp.ESTADO = 'activo'
    LIMIT 1;

    IF v_id_registro IS NULL THEN
        SET v_error = 'REGISTRO_ACTIVO_NO_ENCONTRADO';
    END IF;

    -- -------------------------------------------------------
    -- 2. Calcular tarifa (misma lógica embebida de sp_calcular_tarifa)
    -- -------------------------------------------------------
    IF v_error IS NULL THEN
        SET v_fecha_salida           = NOW();
        SET v_minutos_transcurridos  = TIMESTAMPDIFF(MINUTE, v_fecha_ingreso, v_fecha_salida);
        SET v_hora_salida            = TIME(v_fecha_salida);

        -- Buscar la tarifa aplicable (prioridad: tipo específico > tarifa especial > por_hora)
        SELECT id_tarifa, NOMBRE, TIPO_TARIFA, VALOR, FRACCION_MINUTOS
        INTO   v_id_tarifa, v_nombre_tarifa, v_tipo_tarifa, v_valor, v_fraccion_minutos
        FROM   tarifas
        WHERE  id_empresa = p_id_empresa
          AND  ACTIVO = 1
          AND  (id_tipo_vehiculo = v_id_tipo_vehiculo OR id_tipo_vehiculo IS NULL)
          AND  (
                  (TIPO_TARIFA = 'especial' AND (
                      (HORA_INICIO <= HORA_FIN AND v_hora_salida BETWEEN HORA_INICIO AND HORA_FIN) OR
                      (HORA_INICIO >  HORA_FIN AND (v_hora_salida >= HORA_INICIO OR v_hora_salida <= HORA_FIN))
                  ))
                  OR (TIPO_TARIFA != 'especial')
               )
        ORDER BY
            (id_tipo_vehiculo IS NOT NULL) DESC,
            (TIPO_TARIFA = 'especial')    DESC,
            (TIPO_TARIFA = 'por_hora')    DESC
        LIMIT 1;

        -- Si no hay tarifa específica ni genérica, se registra como Sin Tarifa ($0)
        -- (la consulta anterior ya cubre: tipo específico OR id_tipo_vehiculo IS NULL)

        -- Calcular monto según tipo de tarifa
        IF v_id_tarifa IS NOT NULL THEN
            IF v_tipo_tarifa = 'por_hora' THEN
                SET v_total_cobrado = CEILING(v_minutos_transcurridos / 60) * v_valor;
                IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF;
            ELSEIF v_tipo_tarifa = 'por_fraccion' THEN
                SET v_total_cobrado = CEILING(v_minutos_transcurridos / v_fraccion_minutos) * v_valor;
                IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF;
            ELSEIF v_tipo_tarifa = 'plana_dia' THEN
                SET v_total_cobrado = CEILING(v_minutos_transcurridos / (24 * 60)) * v_valor;
                IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF;
            ELSEIF v_tipo_tarifa = 'especial' THEN
                SET v_total_cobrado = v_valor;
            END IF;
        ELSE
            SET v_total_cobrado = 0;
            SET v_nombre_tarifa = 'Sin Tarifa';
        END IF;

        -- -------------------------------------------------------
        -- 3. Actualizar el registro de parqueo como finalizado
        -- -------------------------------------------------------
        UPDATE registros_parqueo
        SET    FECHA_SALIDA       = v_fecha_salida,
               TIEMPO_MINUTOS    = v_minutos_transcurridos,
               VALOR_COBRADO     = v_total_cobrado,
               ESTADO            = 'finalizado',
               id_tarifa         = v_id_tarifa,
               id_usuario_salida = p_id_usuario_salida,
               updated_at        = NOW()
        WHERE  id_registro = v_id_registro;

        -- -------------------------------------------------------
        -- 4. Liberar el cupo si estaba asignado
        -- -------------------------------------------------------
        IF v_id_cupo IS NOT NULL THEN
            UPDATE cupos
            SET    ESTADO     = 'disponible',
                   updated_at = NOW()
            WHERE  id_cupo    = v_id_cupo;
        END IF;
    END IF;

    -- -------------------------------------------------------
    -- 5. Resultado
    -- -------------------------------------------------------
    SELECT
        v_error                  AS error,
        v_id_registro            AS id_registro,
        v_placa                  AS placa,
        v_nombre_cliente         AS nombre_cliente,
        v_correo_cliente         AS correo_cliente,
        v_fecha_ingreso          AS fecha_ingreso,
        v_fecha_salida           AS fecha_salida,
        v_minutos_transcurridos  AS tiempo_minutos,
        v_total_cobrado          AS valor_cobrado,
        v_nombre_tarifa          AS nombre_tarifa,
        v_codigo_cupo            AS codigo_cupo;

END //

DELIMITER ;
