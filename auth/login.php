<?php
require "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['correo']) || empty($data['password'])) {
    json(["error" => "Correo y contraseña son obligatorios"], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT id_usuario, nombre, correo, password, activo
        FROM usuarios
        WHERE correo = ?
    ");
    
    $stmt->execute([$data['correo']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        json(["error" => "Credenciales incorrectas"], 401);
    }

    if (!$usuario['activo']) {
        json(["error" => "Usuario desactivado"], 403);
    }

    // Validar contraseña (sin hash según especificaciones)
    if ($data['password'] !== $usuario['password']) {
        json(["error" => "Credenciales incorrectas"], 401);
    }

    // Crear sesión
    $_SESSION['usuario'] = [
        'id' => $usuario['id_usuario'],
        'nombre' => $usuario['nombre'],
        'correo' => $usuario['correo']
    ];

    json([
        "success" => true,
        "mensaje" => "Sesión iniciada correctamente",
        "usuario" => $_SESSION['usuario']
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error en el login",
        "detalle" => $e->getMessage()
    ], 500);
}
?>