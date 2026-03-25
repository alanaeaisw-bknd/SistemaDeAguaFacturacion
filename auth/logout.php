<?php
require "../config/db.php";

session_destroy();

json([
    "success" => true,
    "mensaje" => "Sesión cerrada correctamente"
]);
?>