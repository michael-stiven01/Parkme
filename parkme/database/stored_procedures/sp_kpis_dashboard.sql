DROP PROCEDURE IF EXISTS sp_kpis_dashboard;

DELIMITER //

CREATE PROCEDURE sp_kpis_dashboard(
    IN p_id_empresa BIGINT,
    IN p_fecha      DATE
)
BEGIN
    DECLARE v_vehiculos_adentro   INT DEFAULT 0;
    DECLARE v_ingresos_hoy        INT DEFAULT 0;
    DECLARE v_recaudado_hoy       DECIMAL(15,2) DEFAULT 0.00;
    DECLARE v_cupos_disponibles   INT DEFAULT 0;
    DECLARE v_cupos_totales       INT DEFAULT 0;
    DECLARE v_cupos_ocupados      INT DEFAULT 0;
    DECLARE v_ocupacion_pct       DECIMAL(5,2) DEFAULT 0.00;

    -- Vehículos actualmente dentro (registros activos)
    SELECT COUNT(*)
      INTO v_vehiculos_adentro
      FROM registros_parqueo
     WHERE id_empresa = p_id_empresa
       AND ESTADO = 'activo';

    -- Ingresos del día (cantidad de entradas)
    SELECT COUNT(*)
      INTO v_ingresos_hoy
      FROM registros_parqueo
     WHERE id_empresa = p_id_empresa
       AND DATE(FECHA_INGRESO) = p_fecha;

    -- Recaudado hoy (suma de salidas finalizadas en el día)
    SELECT IFNULL(SUM(VALOR_COBRADO), 0.00)
      INTO v_recaudado_hoy
      FROM registros_parqueo
     WHERE id_empresa = p_id_empresa
       AND ESTADO = 'finalizado'
       AND DATE(FECHA_SALIDA) = p_fecha;

    -- Cupos totales activos (no en mantenimiento)
    SELECT COUNT(*)
      INTO v_cupos_totales
      FROM cupos
     WHERE id_empresa = p_id_empresa
       AND ESTADO != 'mantenimiento';

    -- Cupos ocupados (estado = ocupado)
    SELECT COUNT(*)
      INTO v_cupos_ocupados
      FROM cupos
     WHERE id_empresa = p_id_empresa
       AND ESTADO = 'ocupado';

    -- Cupos disponibles
    SELECT COUNT(*)
      INTO v_cupos_disponibles
      FROM cupos
     WHERE id_empresa = p_id_empresa
       AND ESTADO = 'disponible';

    -- Porcentaje de ocupación
    IF v_cupos_totales > 0 THEN
        SET v_ocupacion_pct = ROUND((v_cupos_ocupados / v_cupos_totales) * 100, 2);
    ELSE
        SET v_ocupacion_pct = 0.00;
    END IF;

    SELECT
        v_vehiculos_adentro  AS vehiculos_adentro,
        v_ingresos_hoy       AS ingresos_hoy,
        v_recaudado_hoy      AS recaudado_hoy,
        v_cupos_disponibles  AS cupos_disponibles,
        v_cupos_totales      AS cupos_totales,
        v_cupos_ocupados     AS cupos_ocupados,
        v_ocupacion_pct      AS ocupacion_porcentaje;
END //

DELIMITER ;
