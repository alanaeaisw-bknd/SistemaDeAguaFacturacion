<?php
require "../config/db.php";

if(!isset($_SESSION['usuario'])){
    json(["error"=>"No autorizado"],401);
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    json(["error"=>"JSON inválido"],400);
}

$stmt = $pdo->prepare("
INSERT INTO datos_fiscales
(rfc, razon_social, correo, telefono, calle, numero_ext, numero_int,
 colonia, municipio, estado, pais, cp, regimen, uso_cfdi)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([
 $data["rfc"],
 $data["razon_social"],
 $data["correo"],
 $data["telefono"],
 $data["calle"],
 $data["numero_ext"],
 $data["numero_int"],
 $data["colonia"],
 $data["municipio"],
 $data["estado"],
 $data["pais"],
 $data["cp"],
 $data["regimen"],
 $data["uso_cfdi"]
]);

json(["id_datos_fiscales"=>$pdo->lastInsertId()]);
