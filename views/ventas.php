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
    <title>Mis Ventas - Sistema de Agua</title>
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
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="ventas.php" class="active">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- CONTENIDO -->
        <div class="card">
            <h1 class="card-title">📋 Historial de Ventas</h1>
            
            <div class="alert alert-info">
                <strong>💡 Tip:</strong> Haz clic en "Ver Ticket" para consultar el detalle completo de cada venta.
            </div>

            <div id="ventas-container">
                <!-- Las ventas se cargan dinámicamente -->
            </div>
        </div>
    </div>

    <script src="../assets/app.js"></script>
    <script>
        // Cargar ventas al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadMisVentas();
        });
    </script>
</body>
</html>