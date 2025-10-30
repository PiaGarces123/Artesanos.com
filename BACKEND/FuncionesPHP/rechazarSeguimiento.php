<?php
require_once "../Clases/Follow.php";
require_once "../Clases/User.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
    exit;
}

$conn = conexion();

// 2. Obtener IDs
// Yo (el que está logueado) soy el F_idFollowed (el que es seguido)
$idUsuarioLogueado = $_SESSION['user_id']; 

// El usuario a rechazar es el F_idFollower (el que envió la solicitud)
$idUsuarioARechazar = $_POST['targetUserId'] ?? null;

if (empty($idUsuarioARechazar) || !is_numeric($idUsuarioARechazar)) {
    echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
    desconexion($conn);
    exit;
}

// 3. Ejecutar la acción
// Tu clase Follow::rechazar($conn, $idSeguidor, $idSeguido)
// $idSeguidor = $idUsuarioARechazar
// $idSeguido = $idUsuarioLogueado
if (Follow::rechazar($conn, $idUsuarioARechazar, $idUsuarioLogueado)) {
    
    echo json_encode([
        "status" => "success"
    ]);
    
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo rechazar la solicitud."
    ]);
}

desconexion($conn);
?>