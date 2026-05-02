DROP PROCEDURE IF EXISTS sp_ingreso_vehiculo;

DELIMITER //

CREATE PROCEDURE sp_ingreso_vehiculo(
    IN p_id_empresa          BIGINT,
    IN p_id_vehiculo         BIGINT,
    IN p_id_cupo             BIGINT,
    IN p_id_usuario_ingreso  BIGINT
)
BEGIN
    DECLARE v_error                VARCHAR(60)  DEFAULT NULL;
    DECLARE v_id_registro          BIGINT       DEFAULT NULL;
    DECLARE v_fecha_ingreso        DATETIME     DEFAULT NULL;
    DECLARE v_placa                VARCHAR(10)  DEFAULT NULL;
    DECLARE v_nombre_cliente       VARCHAR(200) DEFAULT NULL;
    DECLARE v_correo_cliente       VARCHAR(150) DEFAULT NULL;
    DECLARE v_codigo_cupo          VARCHAR(20)  DEFAULT NULL;
    DECLARE v_tipo_vehiculo        VARCHAR(80)  DEFAULT NULL;
    DECLARE v_cupo_id_empresa      BIGINT       DEFAULT NULL;
    DECLARE v_cupo_estado          VARCHAR(20)  DEFAULT NULL;
    DECLARE v_cupo_id_tipo         BIGINT       DEFAULT NULL;
    DECLARE v_vehiculo_id_tipo     BIGINT       DEFAULT NULL;
    DECLARE v_tiene_registro_activo INT         DEFAULT 0;

    -- -------------------------------------------------------
    -- 1. Verificar que el vehículo existe y pertenece a la empresa
    -- -------------------------------------------------------
    SELECT v.PLACA,
           v.id_tipo_vehiculo,
           CONCAT(c.NOMBRES, ' ', c.APELLIDOS),
           c.CORREO
    INTO   v_placa,
           v_vehiculo_id_tipo,
           v_nombre_cliente,
           v_correo_cliente
    FROM   vehiculos v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE  v.id_vehiculo = p_id_vehiculo
      AND  v.id_empresa  = p_id_empresa
      AND  v.ACTIVO = 1
    LIMIT 1;

    IF v_placa IS NULL THEN
        SET v_error = 'VEHICULO_NO_ENCONTRADO';
    END IF;

    -- -------------------------------------------------------
    -- 2. Verificar que el vehículo no tiene un registro activo
    -- -------------------------------------------------------
    IF v_error IS NULL THEN
        SELECT COUNT(*)
        INTO   v_tiene_registro_activo
        FROM   registros_parqueo
        WHERE  id_vehiculo = p_id_vehiculo
          AND  ESTADO = 'activo';

        IF v_tiene_registro_activo > 0 THEN
            SET v_error = 'VEHICULO_YA_EN_PARQUEO';
        END IF;
    END IF;

    -- -------------------------------------------------------
    -- 3. Verificar que el cupo existe, pertenece a la empresa
    --    y está 'disponible'
    -- -------------------------------------------------------
    IF v_error IS NULL THEN
        SELECT id_empresa,
               ESTADO,
               CODIGO,
               id_tipo_vehiculo
        INTO   v_cupo_id_empresa,
               v_cupo_estado,
               v_codigo_cupo,
               v_cupo_id_tipo
        FROM   cupos
        WHERE  id_cupo = p_id_cupo
        LIMIT 1;

        IF v_cupo_id_empresa IS NULL OR v_cupo_id_empresa != p_id_empresa THEN
            SET v_error = 'CUPO_NO_ENCONTRADO';
        ELSEIF v_cupo_estado != 'disponible' THEN
            SET v_error = 'CUPO_NO_DISPONIBLE';
        END IF;
    END IF;

    -- -------------------------------------------------------
    -- 4. Verificar compatibilidad de tipo de vehículo con el cupo
    --    (solo si el cupo tiene restricción de tipo)
    -- -------------------------------------------------------
    IF v_error IS NULL AND v_cupo_id_tipo IS NOT NULL THEN
        IF v_cupo_id_tipo != v_vehiculo_id_tipo THEN
            SET v_error = 'TIPO_VEHICULO_INCOMPATIBLE';
        END IF;
    END IF;

    -- -------------------------------------------------------
    -- 5. Crear el registro de parqueo y actualizar el cupo
    -- -------------------------------------------------------
    IF v_error IS NULL THEN
        SET v_fecha_ingreso = NOW();

        INSERT INTO registros_parqueo (
            id_empresa,
            id_vehiculo,
            id_cupo,
            id_usuario_ingreso,
            FECHA_INGRESO,
            ESTADO,
            TICKET_INGRESO_ENVIADO,
            TICKET_SALIDA_ENVIADO,
            created_at,
            updated_at
        ) VALUES (
            p_id_empresa,
            p_id_vehiculo,
            p_id_cupo,
            p_id_usuario_ingreso,
            v_fecha_ingreso,
            'activo',
            0,
            0,
            NOW(),
            NOW()
        );

        SET v_id_registro = LAST_INSERT_ID();

        UPDATE cupos
        SET    ESTADO     = 'ocupado',
               updated_at = NOW()
        WHERE  id_cupo    = p_id_cupo;

        -- Obtener nombre del tipo de vehículo para la respuesta
        SELECT tv.NOMBRE
        INTO   v_tipo_vehiculo
        FROM   tipos_vehiculo tv
        WHERE  tv.id_tipo_vehiculo = v_vehiculo_id_tipo
        LIMIT 1;
    END IF;

    -- -------------------------------------------------------
    -- 6. Resultado
    -- -------------------------------------------------------
    SELECT
        v_error          AS error,
        v_id_registro    AS id_registro,
        v_fecha_ingreso  AS fecha_ingreso,
        v_placa          AS placa,
        v_nombre_cliente AS nombre_cliente,
        v_correo_cliente AS correo_cliente,
        v_codigo_cupo    AS codigo_cupo,
        v_tipo_vehiculo  AS tipo_vehiculo;

END //

DELIMITER ;
