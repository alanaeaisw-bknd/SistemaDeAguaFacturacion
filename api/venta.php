<?php
require "../config/db.php";
requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_datos_fiscales'])) {
    json(["error" => "Los datos fiscales son obligatorios"], 400);
}

$id_usuario       = $_SESSION['usuario']['id'];
$id_datos_fiscales = (int)$data['id_datos_fiscales'];

try {
    $pdo->beginTransaction();

    // Verificar que los datos fiscales pertenecen al usuario
    $stmt = $pdo->prepare("
        SELECT id_datos_fiscales
        FROM datos_fiscales
        WHERE id_datos_fiscales = ? AND id_usuario = ?
    ");
    $stmt->execute([$id_datos_fiscales, $id_usuario]);
    $datosFiscales = $stmt->fetch();

    if (!$datosFiscales) {
        $pdo->rollBack();
        json(["error" => "Datos fiscales no válidos"], 400);
    }

    // Obtener el carrito activo del usuario
    $stmt = $pdo->prepare("
        SELECT id_carrito, total
        FROM carrito
        WHERE id_usuario = ? AND estado = 'activo'
        FOR UPDATE
    ");
    $stmt->execute([$id_usuario]);
    $carrito = $stmt->fetch();

    if (!$carrito) {
        $pdo->rollBack();
        json(["error" => "No tienes un carrito activo"], 400);
    }

    $id_carrito = $carrito['id_carrito'];

    // Obtener items del carrito
    $stmt = $pdo->prepare("
        SELECT dc.id_producto, dc.cantidad, dc.precio_unitario, dc.subtotal, p.stock
        FROM detalle_carrito dc
        INNER JOIN productos p ON dc.id_producto = p.id_producto
        WHERE dc.id_carrito = ?
    ");
    $stmt->execute([$id_carrito]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        $pdo->rollBack();
        json(["error" => "El carrito está vacío"], 400);
    }

    // Validar stock de todos los productos antes de continuar
    foreach ($items as $item) {
        if ($item['cantidad'] > $item['stock']) {
            $pdo->rollBack();
            json([
                "error"      => "Stock insuficiente para uno o más productos",
                "id_producto" => $item['id_producto']
            ], 400);
        }
    }

    // Calcular total
    $total = array_sum(array_column($items, 'subtotal'));

    // Crear la venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (id_usuario, id_datos_fiscales, total, estado)
        VALUES (?, ?, ?, 'pagada')
    ");
    $stmt->execute([$id_usuario, $id_datos_fiscales, $total]);
    $id_venta = $pdo->lastInsertId();

    // Insertar detalle de venta
    // El trigger trg_descontar_stock descuenta el stock automáticamente
    $stmt = $pdo->prepare("
        INSERT INTO detalle_venta
            (id_venta, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([
            $id_venta,
            $item['id_producto'],
            $item['cantidad'],
            $item['precio_unitario'],
            $item['subtotal']
        ]);
    }

    // ============================================================
    // CORRECCIÓN PRINCIPAL:
    // NO cambiar el estado del carrito a 'pagado'.
    // En su lugar, vaciar los items y dejar el carrito activo
    // para que el usuario pueda volver a comprar de inmediato.
    // ============================================================

    // 1. Eliminar todos los items del carrito
    $stmt = $pdo->prepare("
        DELETE FROM detalle_carrito
        WHERE id_carrito = ?
    ");
    $stmt->execute([$id_carrito]);

    // 2. Resetear el total del carrito (los triggers lo hacen también,
    //    pero lo dejamos explícito para mayor claridad)
    $stmt = $pdo->prepare("
        UPDATE carrito
        SET total = 0, estado = 'activo'
        WHERE id_carrito = ?
    ");
    $stmt->execute([$id_carrito]);

    $pdo->commit();

    json([
        "success"  => true,
        "mensaje"  => "¡Venta realizada con éxito!",
        "id_venta" => $id_venta,
        "total"    => $total
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    json([
        "error"   => "Error al procesar la venta",
        "detalle" => $e->getMessage()
    ], 500);
}
?>