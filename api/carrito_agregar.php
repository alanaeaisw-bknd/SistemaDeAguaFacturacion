<?php
require "../config/db.php";
requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

// Validaciones
if (!isset($data['id_producto']) || !isset($data['cantidad'])) {
    json(["error" => "Producto y cantidad son obligatorios"], 400);
}

if (!is_numeric($data['cantidad']) || $data['cantidad'] <= 0) {
    json(["error" => "La cantidad debe ser un número mayor a 0"], 400);
}

$id_usuario  = $_SESSION['usuario']['id'];
$id_producto = (int)$data['id_producto'];
$cantidad    = (int)$data['cantidad'];

try {
    $pdo->beginTransaction();

    // Verificar que el producto existe y está activo
    $stmt = $pdo->prepare("
        SELECT id_producto, precio, stock, activo
        FROM productos
        WHERE id_producto = ?
    "); 
    $stmt->execute([$id_producto]);
    $producto = $stmt->fetch();

    if (!$producto) {
        $pdo->rollBack();
        json(["error" => "Producto no encontrado"], 404);
    }

    if (!$producto['activo']) {
        $pdo->rollBack();
        json(["error" => "Producto no disponible"], 400);
    }

    if ($cantidad > $producto['stock']) {
        $pdo->rollBack();
        json([
            "error"            => "Stock insuficiente",
            "stock_disponible" => $producto['stock']
        ], 400);
    }

    // ============================================================
    // CORRECCIÓN PRINCIPAL:
    // Buscar carrito existente SIN filtrar por estado.
    // Si no existe → crear uno nuevo.
    // Si existe pero no está activo → reactivarlo y limpiar total.
    // ============================================================
    $stmt = $pdo->prepare("
        SELECT id_carrito, estado
        FROM carrito
        WHERE id_usuario = ?
        FOR UPDATE
    ");
    $stmt->execute([$id_usuario]);
    $carrito = $stmt->fetch();

    if (!$carrito) {
        // Primera compra del usuario: crear carrito
        $stmt = $pdo->prepare("
            INSERT INTO carrito (id_usuario, total, estado)
            VALUES (?, 0, 'activo')
        ");
        $stmt->execute([$id_usuario]);
        $id_carrito = $pdo->lastInsertId();

    } else {
        $id_carrito = $carrito['id_carrito'];

        // Si el carrito quedó en otro estado (pagado, etc.), reactivarlo
        if ($carrito['estado'] !== 'activo') {
            $stmt = $pdo->prepare("
                UPDATE carrito
                SET estado = 'activo', total = 0
                WHERE id_carrito = ?
            ");
            $stmt->execute([$id_carrito]);
        }
    }

    // Calcular subtotal
    $subtotal = $producto['precio'] * $cantidad;

    // Insertar o acumular cantidad si el producto ya está en el carrito
    $stmt = $pdo->prepare("
        INSERT INTO detalle_carrito
            (id_carrito, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            cantidad  = cantidad + VALUES(cantidad),
            subtotal  = subtotal  + VALUES(subtotal)
    ");
    $stmt->execute([
        $id_carrito,
        $id_producto,
        $cantidad,
        $producto['precio'],
        $subtotal
    ]);

    $pdo->commit();

    json([
        "success"    => true,
        "mensaje"    => "Producto agregado al carrito",
        "id_carrito" => $id_carrito
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    json([
        "error"   => "Error al agregar al carrito",
        "detalle" => $e->getMessage()
    ], 500);
}
?>