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
    <title>Productos - Sistema de Agua</title>
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
                <li><a href="productos.php" class="active">Productos</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="ventas.php">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- CONTENIDO -->
        <div class="card">
            <h1 class="card-title">Catálogo de Productos</h1>
            
            <div class="alert alert-info">
                <strong>💡 Ventas al Mayoreo:</strong> Agrega las cantidades que necesites. Mínimo 30 unidades por pedido.
            </div>

            <div id="productos-grid" class="productos-grid">
                <!-- Los productos se cargan dinámicamente -->
            </div>
        </div>
    </div>

    <script src="../assets/app.js"></script>
    <script>
        // Cargar productos al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadProductos();
        });
    </script>
</body>
</html>