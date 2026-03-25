DROP PROCEDURE IF EXISTS sp_cancelar_venta;
DELIMITER $$

CREATE PROCEDURE sp_cancelar_venta(IN p_venta_id INT)
BEGIN
    DECLARE v_estado VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT estado INTO v_estado
    FROM ventas
    WHERE id_venta = p_venta_id
    FOR UPDATE;

    IF v_estado IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La venta no existe';
    END IF;

    IF v_estado = 'CANCELADA' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La venta ya fue cancelada';
    END IF;

    UPDATE productos p
    JOIN detalle_venta dv ON dv.id_producto = p.id_producto
    SET p.stock = p.stock + dv.cantidad
    WHERE dv.id_venta = p_venta_id;

    UPDATE ventas
    SET estado='CANCELADA'
    WHERE id_venta = p_venta_id;

    COMMIT;
END$$
DELIMITER ;
