DROP PROCEDURE IF EXISTS sp_actualizar_cupo;

DELIMITER //

CREATE PROCEDURE sp_actualizar_cupo(
    IN p_id_cupo          BIGINT,
    IN p_id_tipo_vehiculo BIGINT,
    IN p_codigo           VARCHAR(20),
    IN p_fila             VARCHAR(5),
    IN p_columna          INT
)
BEGIN
    DECLARE v_id_empresa BIGINT;
    DECLARE v_error      VARCHAR(50) DEFAULT NULL;

    -- Obtener empresa del cupo a editar
    SELECT id_empresa INTO v_id_empresa
    FROM cupos WHERE id_cupo = p_id_cupo;

    -- 1. Verificar código duplicado (excluye el propio cupo)
    IF EXISTS (
        SELECT 1 FROM cupos
        WHERE id_empresa = v_id_empresa
          AND CODIGO     = p_codigo
          AND id_cupo   != p_id_cupo
    ) THEN
        SET v_error = 'CODIGO_DUPLICADO';
    END IF;

    -- 2. Verificar posición duplicada (excluye el propio cupo)
    IF v_error IS NULL AND p_fila IS NOT NULL AND p_columna IS NOT NULL THEN
        IF EXISTS (
            SELECT 1 FROM cupos
            WHERE id_empresa = v_id_empresa
              AND FILA       = p_fila
              AND COLUMNA    = p_columna
              AND id_cupo   != p_id_cupo
        ) THEN
            SET v_error = 'POSICION_DUPLICADA';
        END IF;
    END IF;

    -- 3. Solo actualizar si no hay error
    IF v_error IS NULL THEN
        UPDATE cupos
        SET id_tipo_vehiculo = p_id_tipo_vehiculo,
            CODIGO           = p_codigo,
            FILA             = p_fila,
            COLUMNA          = p_columna,
            updated_at       = NOW()
        WHERE id_cupo = p_id_cupo;
    END IF;

    SELECT v_error AS error;
END //

DELIMITER ;
