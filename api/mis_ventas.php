<?php
require "../config/db.php";
requireAuth();

$id_usuario = $_SESSION['usuario']['id'];

try {
    // Usar el stored procedure
    $stmt = $pdo->prepare("CALL sp_resumen_ventas_usuario(?)");
    $stmt->execute([$id_usuario]);
    $ventas = $stmt->fetchAll();

    json([
        "success" => true,
        "ventas" => $ventas
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al obtener las ventas",
        "detalle" => $e->getMessage()
    ], 500);
}
?>