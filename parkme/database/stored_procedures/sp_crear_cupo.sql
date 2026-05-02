DROP PROCEDURE IF EXISTS sp_crear_cupo;

DELIMITER //

CREATE PROCEDURE sp_crear_cupo(
    IN p_id_empresa       BIGINT,
    IN p_id_tipo_vehiculo BIGINT,
    IN p_codigo           VARCHAR(20),
    IN p_estado           ENUM('disponible', 'ocupado', 'mantenimiento'),
    IN p_fila             VARCHAR(5),
    IN p_columna          INT
)
BEGIN
    DECLARE v_error VARCHAR(50) DEFAULT NULL;

    -- 1. Verificar código duplicado en la misma empresa
    IF EXISTS (
        SELECT 1 FROM cupos
        WHERE id_empresa = p_id_empresa
          AND CODIGO = p_codigo
    ) THEN
        SET v_error = 'CODIGO_DUPLICADO';
    END IF;

    -- 2. Verificar posición duplicada (solo si no hay error previo y ambos datos vienen)
    IF v_error IS NULL AND p_fila IS NOT NULL AND p_columna IS NOT NULL THEN
        IF EXISTS (
            SELECT 1 FROM cupos
            WHERE id_empresa = p_id_empresa
              AND FILA       = p_fila
              AND COLUMNA    = p_columna
        ) THEN
            SET v_error = 'POSICION_DUPLICADA';
        END IF;
    END IF;

    -- 3. Solo insertar si no hay error
    IF v_error IS NULL THEN
        INSERT INTO cupos (
            id_empresa,
            id_tipo_vehiculo,
            CODIGO,
            ESTADO,
            FILA,
            COLUMNA,
            created_at,
            updated_at
        ) VALUES (
            p_id_empresa,
            p_id_tipo_vehiculo,
            p_codigo,
            p_estado,
            p_fila,
            p_columna,
            NOW(),
            NOW()
        );
    END IF;

    SELECT v_error AS error;
END //

DELIMITER ;
