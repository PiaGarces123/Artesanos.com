<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

    session_start();
    
    // Configuración de zona horaria
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    // No necesitamos Album.php, pero sí Imagen.php y User.php
    require_once "../Clases/image.php"; 
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    $conn = conexion();

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

    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;
    }
    
    $conn = conexion();
    $userId = $user->id;
    
    // Obtenemos el ID de la imagen desde el POST
    $imageId = $_POST['imageId'] ?? null;
    
    if (empty($imageId) || !is_numeric($imageId)) {
        echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
        desconexion($conn);
        exit;
    }
    
    $imageId = (int)$imageId;
    
    // =====================================================
    // 2. VERIFICAR PROPIEDAD
    // =====================================================
    
    // Buscamos los datos de la imagen para verificar propiedad y obtener el título
    $imageData = Imagen::getById($conn, $imageId);
    
    if (!$imageData || $imageData['I_idUser'] != $userId) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para eliminar esta imagen."]);
        desconexion($conn);
        exit;
    }

    // =====================================================
    // 3. ELIMINAR IMAGEN (BD y FÍSICO)
    // =====================================================
    
    // 3.1. Eliminar registro de BD y archivo físico
    // Tu método Imagen::eliminar() ya se encarga de:
    //  - Borrar el archivo físico (unlink)
    //  - Desmarcar si es perfil (I_currentProfile = 0)
    //  - Borrar el registro de la BD (lo que activa el CASCADE)
    
    if (Imagen::eliminar($conn, $imageId)) {
        
        echo json_encode([
            "status" => "success",
            "message" => "Imagen '{$imageData['I_title']}' eliminada correctamente."
        ]);
        
    } else {
        // Error de base de datos durante la eliminación
        echo json_encode([
            "status" => "error",
            "message" => "Fallo al eliminar la imagen en la base de datos."
        ]);
    }

    desconexion($conn);
?>