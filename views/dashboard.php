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
    <title>Dashboard - Sistema de Agua</title>
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
                <li><a href="dashboard.php" class="active">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="ventas.php">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="card">
            <h1 class="card-title">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?>!</h1>
            
            <div style="margin-top: 30px;">
                <h2 style="color: var(--primary); margin-bottom: 20px;">¿Qué deseas hacer?</h2>
                
                <div class="productos-grid">
                    <div class="producto-card" style="cursor: pointer;" onclick="window.location.href='productos.php'">
                        <div class="producto-icon">🛒</div>
                        <h3 class="producto-nombre">Ver Productos</h3>
                        <p class="producto-descripcion">Explora nuestro catálogo de productos de agua</p>
                        <button class="btn btn-primary btn-block">Ir a Productos</button>
                    </div>

                    <div class="producto-card" style="cursor: pointer;" onclick="window.location.href='carrito.php'">
                        <div class="producto-icon">🛒</div>
                        <h3 class="producto-nombre">Mi Carrito</h3>
                        <p class="producto-descripcion">Revisa los productos que has agregado</p>
                        <button class="btn btn-success btn-block">Ver Carrito</button>
                    </div>

                    <div class="producto-card" style="cursor: pointer;" onclick="window.location.href='ventas.php'">
                        <div class="producto-icon">📋</div>
                        <h3 class="producto-nombre">Mis Ventas</h3>
                        <p class="producto-descripcion">Consulta tu historial de compras</p>
                        <button class="btn btn-secondary btn-block">Ver Historial</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="card">
            <h2 class="card-title">Información del Sistema</h2>
            <div style="line-height: 1.8;">
                <p><strong>📦 Productos Disponibles:</strong> Botellas, Galones y Garrafones</p>
                <p><strong>💼 Ventas al Mayoreo:</strong> Compras de 30, 50 o más unidades</p>
                <p><strong>📄 Facturación:</strong> Todos los pedidos requieren datos fiscales</p>
                <p><strong>🚚 Entrega:</strong> Coordinada después de la compra</p>
            </div>
        </div>
    </div>

    <script src="../assets/app.js"></script>
</body>
</html>