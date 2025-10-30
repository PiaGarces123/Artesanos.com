<?php
require_once "../Clases/Follow.php";
require_once "../Clases/User.php";
require_once "../Clases/image.php"; // (La necesitamos para la nueva función de Follow)
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    // Devolvemos array vacío si no hay sesión
    echo json_encode([]);
    exit;
}

$conn = conexion();
$idUsuarioLogueado = $_SESSION['user_id'];

try {
    // 2. Llamar a la nueva función estática
    $solicitudes = Follow::obtenerSolicitudesPendientes($conn, $idUsuarioLogueado);

    // 3. Devolver la respuesta
    echo json_encode($solicitudes);

} catch (Exception $e) {
    // Devolver array vacío si hay error
    echo json_encode([]);
}

desconexion($conn);
?>