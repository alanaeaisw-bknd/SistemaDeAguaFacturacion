<?php
require "../config/db.php";
requireAuth();

if (!isset($_GET['id_venta'])) {
    json(["error" => "ID de venta requerido"], 400);
}

$id_venta = (int)$_GET['id_venta'];
$id_usuario = $_SESSION['usuario']['id'];

try {
    // Verificar que la venta pertenece al usuario
    $stmt = $pdo->prepare("
        SELECT 
            v.id_venta,
            v.total,
            v.estado,
            v.fecha_venta,
            df.rfc,
            df.razon_social,
            df.correo as correo_fiscal,
            df.calle,
            df.numero_ext,
            df.numero_int,
            df.colonia,
            df.municipio,
            df.estado,
            df.cp,
            df.regimen,
            df.uso_cfdi,
            u.nombre as nombre_cliente,
            u.correo as correo_cliente
        FROM ventas v
        INNER JOIN datos_fiscales df ON v.id_datos_fiscales = df.id_datos_fiscales
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE v.id_venta = ? AND v.id_usuario = ?
    ");
    $stmt->execute([$id_venta, $id_usuario]);
    $venta = $stmt->fetch();

    if (!$venta) {
        json(["error" => "Venta no encontrada"], 404);
    }

    // Obtener detalle de la venta
    $stmt = $pdo->prepare("
        SELECT 
            p.nombre,
            p.descripcion,
            dv.cantidad,
            dv.precio_unitario,
            dv.subtotal
        FROM detalle_venta dv
        INNER JOIN productos p ON dv.id_producto = p.id_producto
        WHERE dv.id_venta = ?
    ");
    $stmt->execute([$id_venta]);
    $items = $stmt->fetchAll();

    json([
        "success" => true,
        "venta" => $venta,
        "items" => $items
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al obtener el ticket",
        "detalle" => $e->getMessage()
    ], 500);
}
?>