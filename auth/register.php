<?php
require "../config/db.php";

// Obtener datos del request
$data = json_decode(file_get_contents("php://input"), true);

// Validaciones
if (!$data) {
    json(["error" => "JSON inválido"], 400);
}

$errores = [];

if (empty($data['nombre'])) {
    $errores[] = "El nombre es obligatorio";
}

if (empty($data['correo']) || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Correo electrónico inválido";
}

if (empty($data['password']) || strlen($data['password']) < 6) {
    $errores[] = "La contraseña debe tener al menos 6 caracteres";
}

if (!empty($errores)) {
    json(["error" => "Errores de validación", "detalles" => $errores], 400);
}

try {
    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $stmt->execute([$data['correo']]);
    
    if ($stmt->fetch()) {
        json(["error" => "El correo electrónico ya está registrado"], 409);
    }

    // Insertar usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, correo, password, telefono)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['nombre'],
        $data['correo'],
        $data['password'], // Sin hash según especificaciones
        $data['telefono'] ?? null
    ]);

    $id_usuario = $pdo->lastInsertId();

    // Crear sesión automática
    $_SESSION['usuario'] = [
        'id' => $id_usuario,
        'nombre' => $data['nombre'],
        'correo' => $data['correo']
    ];

    json([
        "success" => true,
        "mensaje" => "Usuario registrado exitosamente",
        "usuario" => $_SESSION['usuario']
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al registrar usuario",
        "detalle" => $e->getMessage()
    ], 500);
}
?>