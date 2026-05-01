DELIMITER //

DROP PROCEDURE IF EXISTS sp_listar_clientes//

CREATE PROCEDURE sp_listar_clientes(
    IN p_id_empresa BIGINT,
    IN p_busqueda VARCHAR(255)
)
BEGIN
    SELECT 
        c.id_cliente,
        c.id_empresa,
        c.NOMBRES,
        c.APELLIDOS,
        c.CORREO,
        c.TELEFONO,
        c.ACTIVO,
        c.created_at,
        c.updated_at,
        (SELECT COUNT(*) FROM vehiculos v WHERE v.id_cliente = c.id_cliente AND v.ACTIVO = 1) AS vehiculos_registrados
    FROM clientes c
    WHERE c.id_empresa = p_id_empresa
    AND (
        p_busqueda IS NULL OR 
        p_busqueda = '' OR 
        c.NOMBRES LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci OR 
        c.APELLIDOS LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci OR 
        c.CORREO LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci OR
        CONCAT(c.NOMBRES, ' ', c.APELLIDOS) LIKE CONCAT('%', p_busqueda, '%') COLLATE utf8mb4_unicode_ci
    )
    ORDER BY c.NOMBRES ASC, c.APELLIDOS ASC;
END //

DELIMITER ;
