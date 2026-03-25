<?php
require "../config/db.php";
requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_detalle_carrito'])) {
    json(["error" => "ID de detalle de carrito requerido"], 400);
}

$id_usuario = $_SESSION['usuario']['id'];
$id_detalle = (int)$data['id_detalle_carrito'];

try {
    $pdo->beginTransaction();

    // Verificar que el detalle pertenece al usuario
    $stmt = $pdo->prepare("
        SELECT dc.id_detalle_carrito
        FROM detalle_carrito dc
        INNER JOIN carrito c ON dc.id_carrito = c.id_carrito
        WHERE dc.id_detalle_carrito = ? AND c.id_usuario = ? AND c.estado = 'activo'
    ");
    $stmt->execute([$id_detalle, $id_usuario]);

    if (!$stmt->fetch()) {
        $pdo->rollBack();
        json(["error" => "Item no encontrado en tu carrito"], 404);
    }

    // Eliminar item
    $stmt = $pdo->prepare("DELETE FROM detalle_carrito WHERE id_detalle_carrito = ?");
    $stmt->execute([$id_detalle]);

    $pdo->commit();

    json([
        "success" => true,
        "mensaje" => "Producto eliminado del carrito"
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    json([
        "error" => "Error al eliminar del carrito",
        "detalle" => $e->getMessage()
    ], 500);
}
?>