<?php
session_start();

// Redirigir si ya está autenticado
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

// Redirigir al login
header('Location: login.html');
exit;
?>