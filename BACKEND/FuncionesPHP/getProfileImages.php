<?php
    require_once "../Clases/Album.php"; 
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php"; 
    require_once "../conexion.php";

    session_start();

    // Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");


    // Conexión
    $conn = conexion();

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

    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;
    }

    // =====================================================
    // ----------------------------------------------------
    // 2. OBTENER IMÁGENES DE PERFIL DEL USUARIO
    // ----------------------------------------------------
    
    // Asumimos que Album::getByUser y Album::getCoverImagePath existen y son correctos.
    $resultadoimages = Imagen::obtenerImagenesDePerfilPorUsuario($conn, $user->id);

    
    // Devolver el JSON final
    echo json_encode($resultadoimages);
    
    desconexion($conn);
?>