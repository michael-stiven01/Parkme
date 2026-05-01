DELIMITER //

DROP PROCEDURE IF EXISTS sp_cambiar_estado_cliente//

CREATE PROCEDURE sp_cambiar_estado_cliente(
    IN p_id_cliente BIGINT,
    IN p_activo TINYINT
)
BEGIN
    UPDATE clientes 
    SET 
        ACTIVO = p_activo,
        updated_at = NOW()
    WHERE id_cliente = p_id_cliente;
END //

DELIMITER ;
