DROP PROCEDURE IF EXISTS sp_marcar_ticket_ingreso;

DELIMITER //

CREATE PROCEDURE sp_marcar_ticket_ingreso(
    IN p_id_registro BIGINT
)
BEGIN
    UPDATE registros_parqueo
    SET    TICKET_INGRESO_ENVIADO = 1,
           updated_at             = NOW()
    WHERE  id_registro = p_id_registro;
END //

DELIMITER ;
