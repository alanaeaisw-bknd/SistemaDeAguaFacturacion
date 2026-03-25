<?php
require "../config/db.php";
requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_detalle_carrito']) || !isset($data['cantidad'])) {
    json(["error" => "ID de detalle y cantidad son requeridos"], 400);
}

if ($data['cantidad'] <= 0) {
    json(["error" => "La cantidad debe ser mayor a 0"], 400);
}

$id_usuario = $_SESSION['usuario']['id'];
$id_detalle = (int)$data['id_detalle_carrito'];
$cantidad = (int)$data['cantidad'];

try {
    $pdo->beginTransaction();

    // Obtener detalle y validar pertenencia
    $stmt = $pdo->prepare("
        SELECT dc.id_producto, dc.precio_unitario, p.stock
        FROM detalle_carrito dc
        INNER JOIN carrito c ON dc.id_carrito = c.id_carrito
        INNER JOIN productos p ON dc.id_producto = p.id_producto
        WHERE dc.id_detalle_carrito = ? AND c.id_usuario = ? AND c.estado = 'activo'
        FOR UPDATE
    ");
    $stmt->execute([$id_detalle, $id_usuario]);
    $detalle = $stmt->fetch();

    if (!$detalle) {
        $pdo->rollBack();
        json(["error" => "Item no encontrado"], 404);
    }

    if ($cantidad > $detalle['stock']) {
        $pdo->rollBack();
        json([
            "error" => "Stock insuficiente",
            "stock_disponible" => $detalle['stock']
        ], 400);
    }

    // Actualizar cantidad y subtotal
    $nuevo_subtotal = $detalle['precio_unitario'] * $cantidad;

    $stmt = $pdo->prepare("
        UPDATE detalle_carrito
        SET cantidad = ?, subtotal = ?
        WHERE id_detalle_carrito = ?
    ");
    $stmt->execute([$cantidad, $nuevo_subtotal, $id_detalle]);

    $pdo->commit();

    json([
        "success" => true,
        "mensaje" => "Cantidad actualizada"
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    json([
        "error" => "Error al actualizar cantidad",
        "detalle" => $e->getMessage()
    ], 500);
}
?>