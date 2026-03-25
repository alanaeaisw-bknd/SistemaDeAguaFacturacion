<?php
require "../config/db.php";
requireAuth();

$id_usuario = $_SESSION['usuario']['id'];

try {
    // Obtener carrito activo
    $stmt = $pdo->prepare("
        SELECT id_carrito, total, estado, created_at
        FROM carrito
        WHERE id_usuario = ? AND estado = 'activo'
    ");
    $stmt->execute([$id_usuario]);
    $carrito = $stmt->fetch();

    if (!$carrito) {
        json([
            "success" => true,
            "carrito" => null,
            "items" => [],
            "total" => 0
        ]);
    }

    // Obtener items del carrito
    $stmt = $pdo->prepare("
        SELECT 
            dc.id_detalle_carrito,
            dc.id_producto,
            p.nombre,
            p.descripcion,
            dc.cantidad,
            dc.precio_unitario,
            dc.subtotal,
            p.stock
        FROM detalle_carrito dc
        INNER JOIN productos p ON dc.id_producto = p.id_producto
        WHERE dc.id_carrito = ?
        ORDER BY dc.id_detalle_carrito ASC
    ");
    $stmt->execute([$carrito['id_carrito']]);
    $items = $stmt->fetchAll();

    json([
        "success" => true,
        "carrito" => $carrito,
        "items" => $items,
        "total" => (float)$carrito['total']
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al obtener el carrito",
        "detalle" => $e->getMessage()
    ], 500);
}
?>