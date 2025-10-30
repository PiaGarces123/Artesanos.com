<?php
require_once "../Clases/Follow.php"; 
require_once "../Clases/User.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida"]);
    exit;
}

$conn = conexion();
$idUsuarioLogueado = $_SESSION['user_id'];

try {
    // 2. Llamar a la nueva función estática
    // (Asegúrate de añadir 'contarPendientes' a tu clase Follow)
    $conteo = Follow::contarPendientes($conn, $idUsuarioLogueado);

    // 3. Devolver la respuesta
    echo json_encode([
        "status" => "success",
        "count" => $conteo
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener el conteo."
    ]);
}

desconexion($conn);
?>
