<?php
require "../config/db.php";
requireAuth();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    json(["error" => "JSON inválido"], 400);
}

// Validaciones
$campos_requeridos = [
    'rfc', 'razon_social', 'correo', 'calle', 'numero_ext',
    'colonia', 'municipio', 'estado', 'cp', 'regimen', 'uso_cfdi'
];

$errores = [];
foreach ($campos_requeridos as $campo) {
    if (empty($data[$campo])) {
        $errores[] = "El campo {$campo} es obligatorio";
    }
}

// Validar formato RFC (básico)
if (!empty($data['rfc']) && !preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/', strtoupper($data['rfc']))) {
    $errores[] = "Formato de RFC inválido";
}

// Validar correo
if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Formato de correo inválido";
}

// Validar CP
if (!empty($data['cp']) && !preg_match('/^\d{5}$/', $data['cp'])) {
    $errores[] = "El código postal debe tener 5 dígitos";
}

if (!empty($errores)) {
    json(["error" => "Errores de validación", "detalles" => $errores], 400);
}

$id_usuario = $_SESSION['usuario']['id'];

try {
    $stmt = $pdo->prepare("
        INSERT INTO datos_fiscales
        (id_usuario, rfc, razon_social, correo, telefono, calle, numero_ext, numero_int,
         colonia, municipio, estado, pais, cp, regimen, uso_cfdi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_usuario,
        strtoupper($data['rfc']),
        $data['razon_social'],
        $data['correo'],
        $data['telefono'] ?? null,
        $data['calle'],
        $data['numero_ext'],
        $data['numero_int'] ?? null,
        $data['colonia'],
        $data['municipio'],
        $data['estado'],
        $data['pais'] ?? 'México',
        $data['cp'],
        $data['regimen'],
        $data['uso_cfdi']
    ]);

    json([
        "success" => true,
        "mensaje" => "Datos fiscales guardados correctamente",
        "id_datos_fiscales" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    json([
        "error" => "Error al guardar datos fiscales",
        "detalle" => $e->getMessage()
    ], 500);
}
?>