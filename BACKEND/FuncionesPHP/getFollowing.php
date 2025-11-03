<?php
require_once "../Clases/Follow.php";
require_once "../Clases/Image.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

// 1. Validar que tengamos el ID del usuario objetivo
$targetUserId = $_POST['user_id'] ?? null;

if (empty($targetUserId) || !is_numeric($targetUserId)) {
    echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
    exit;
}

$conn = conexion();
$defaultPath = './Frontend/assets/images/appImages/default.jpg';

try {
    // 2. Obtener la lista de usuarios que sigue el usuario objetivo
    $siguiendo = Follow::siguiendo($conn, $targetUserId);

    // 3. Enriquecer con foto de perfil
    $siguiendoConFoto = [];
    foreach ($siguiendo as $seguido) {
        $profilePic = Imagen::getProfileImagePath($conn, $seguido['U_id']);
        
        $siguiendoConFoto[] = [
            'U_id' => $seguido['U_id'],
            'U_nameUser' => $seguido['U_nameUser'],
            'U_name' => $seguido['U_name'],
            'U_lastName' => $seguido['U_lastName'],
            'U_profilePic' => $profilePic ?: $defaultPath
        ];
    }

    echo json_encode([
        "status" => "success",
        "siguiendo" => $siguiendoConFoto
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al obtener siguiendo."
    ]);
}

desconexion($conn);
?>