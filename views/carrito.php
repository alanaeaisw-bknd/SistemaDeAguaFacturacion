<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Sistema de Agua</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="navbar-brand">
                Sistema de Agua
            </div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="carrito.php" class="active">Carrito</a></li>
                <li><a href="ventas.php">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- CONTENIDO -->
        <div class="card">
            <h1 class="card-title">🛒 Mi Carrito de Compras</h1>
            
            <div id="carrito-container">
                <!-- El carrito se carga dinámicamente -->
            </div>
        </div>
    </div>

    <script src="../assets/app.js"></script>
    <script>
        // Cargar carrito al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadCarrito();
        });
    </script>
</body>
</html>