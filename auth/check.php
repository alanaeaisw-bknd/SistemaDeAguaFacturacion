<?php
require "../config/db.php";

if (isset($_SESSION['usuario'])) {
    json([
        "autenticado" => true,
        "usuario" => $_SESSION['usuario']
    ]);
} else {
    json([
        "autenticado" => false
    ], 401);
}
?>