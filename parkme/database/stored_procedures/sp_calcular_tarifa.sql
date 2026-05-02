DROP PROCEDURE IF EXISTS sp_calcular_tarifa;

DELIMITER //

CREATE PROCEDURE sp_calcular_tarifa(
    IN p_id_empresa BIGINT,
    IN p_id_tipo_vehiculo BIGINT,
    IN p_hora_ingreso DATETIME,
    IN p_hora_salida DATETIME
)
BEGIN
    DECLARE v_id_tarifa BIGINT;
    DECLARE v_nombre_tarifa VARCHAR(100);
    DECLARE v_tipo_tarifa VARCHAR(50);
    DECLARE v_valor DECIMAL(10,2);
    DECLARE v_fraccion_minutos INT;
    DECLARE v_minutos_transcurridos INT;
    DECLARE v_total_cobrado DECIMAL(10,2);
    DECLARE v_hora_actual TIME;
    
    -- Inicializar variables
    SET v_minutos_transcurridos = TIMESTAMPDIFF(MINUTE, p_hora_ingreso, p_hora_salida);
    SET v_hora_actual = TIME(p_hora_salida);

    -- Buscar tarifa (orden de prioridad: vehiculo específico -> null, especial -> hora)
    -- Para especial, consideramos si la hora de salida cae en el rango
    SELECT id_tarifa, NOMBRE, TIPO_TARIFA, VALOR, FRACCION_MINUTOS
    INTO v_id_tarifa, v_nombre_tarifa, v_tipo_tarifa, v_valor, v_fraccion_minutos
    FROM tarifas
    WHERE id_empresa = p_id_empresa 
      AND ACTIVO = 1
      AND (id_tipo_vehiculo = p_id_tipo_vehiculo OR id_tipo_vehiculo IS NULL)
      AND (
          (TIPO_TARIFA = 'especial' AND (
              (HORA_INICIO <= HORA_FIN AND v_hora_actual BETWEEN HORA_INICIO AND HORA_FIN) OR
              (HORA_INICIO > HORA_FIN AND (v_hora_actual >= HORA_INICIO OR v_hora_actual <= HORA_FIN))
          ))
          OR
          (TIPO_TARIFA != 'especial')
      )
    ORDER BY 
        (id_tipo_vehiculo IS NOT NULL) DESC, -- Tipo específico primero
        (TIPO_TARIFA = 'especial') DESC, -- Especial primero
        (TIPO_TARIFA = 'por_hora') DESC  -- Default fallback
    LIMIT 1;

    -- Si no se encontró tarifa específica ni genérica, no aplicar ninguna
    -- (la consulta anterior ya cubre: tipo específico OR id_tipo_vehiculo IS NULL)

    -- Calcular el cobro si tenemos una tarifa
    IF v_id_tarifa IS NOT NULL THEN
        IF v_tipo_tarifa = 'por_hora' THEN
            -- Cobro por hora o fracción de hora (redondeo hacia arriba a la hora más cercana si hay minutos)
            SET v_total_cobrado = CEILING(v_minutos_transcurridos / 60) * v_valor;
            IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF; -- Minimo 1 hora
        ELSEIF v_tipo_tarifa = 'por_fraccion' THEN
            -- Cobro por fracción exacta de X minutos
            SET v_total_cobrado = CEILING(v_minutos_transcurridos / v_fraccion_minutos) * v_valor;
            IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF; -- Minimo 1 fracción
        ELSEIF v_tipo_tarifa = 'plana_dia' THEN
            -- Cobro plano por día
            SET v_total_cobrado = CEILING(v_minutos_transcurridos / (24 * 60)) * v_valor;
            IF v_total_cobrado = 0 THEN SET v_total_cobrado = v_valor; END IF; -- Minimo 1 dia
        ELSEIF v_tipo_tarifa = 'especial' THEN
            -- Cobro fijo de la tarifa especial, o podemos aplicar lógica por hora según convenga.
            -- Asumiremos que especial es tarifa fija por entrar en ese horario
            SET v_total_cobrado = v_valor;
        END IF;
    ELSE
        SET v_total_cobrado = 0;
        SET v_nombre_tarifa = 'Sin Tarifa';
    END IF;

    -- Retornar resultado
    SELECT 
        v_id_tarifa AS id_tarifa,
        v_nombre_tarifa AS nombre_tarifa,
        v_total_cobrado AS total_cobrado,
        v_minutos_transcurridos AS minutos_transcurridos;

END //

DELIMITER ;
