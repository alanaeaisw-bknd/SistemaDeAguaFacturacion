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
    <title>Ticket de Venta - Sistema de Agua</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        @media print {
            .navbar, .btn-group, .alert {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .card {
                box-shadow: none !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- NAVBAR (no se imprime) -->
        <nav class="navbar">
            <div class="navbar-brand">
                Sistema de Agua
            </div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="ventas.php">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- TICKET -->
        <div id="ticket-container">
            <!-- El ticket se carga dinámicamente -->
        </div>
    </div>

    <script src="../assets/app.js"></script>
    <script>
        // Cargar ticket al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadTicket();
        });
    </script>
</body>
</html>