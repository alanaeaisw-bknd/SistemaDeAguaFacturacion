<?php
require "../config/db.php";
requireAuth();

try {
    $stmt = $pdo->query("
        SELECT 
            id_producto,
            nombre,
            descripcion,
            precio,
            stock,
            activo
        FROM productos
        WHERE activo = 1
        ORDER BY id_producto ASC
    ");

    $productos = $stmt->fetchAll();

    json([
        "success" => true,
        "productos" => $productos
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al obtener productos",
        "detalle" => $e->getMessage()
    ], 500);
}
?>