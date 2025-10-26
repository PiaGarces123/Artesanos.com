<?php 
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();
    
    // Configuración de zona horaria (opcional, pero buena práctica)
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php"; 
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    // Indicar que la respuesta es JSON
    header("Content-Type: application/json");
    
    // =====================================================
    // 1. CHEQUEO DE SESIÓN y CONEXIÓN
    // =====================================================
    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Tu sesión ha expirado."
        ]);
        exit;
    }
    
    $conn = conexion();

    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;
    }
    
    if(!(Imagen::removeProfile($conn, $user->id))){
        echo json_encode([
            "status" => "error",
            "message" => "Error al remover la imagen de perfil."
        ]);
        exit;
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "Imagen de perfil removida correctamente."
        ]);
    }
    
    desconexion($conn);
    exit;
?>