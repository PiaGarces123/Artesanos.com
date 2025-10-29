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
    // 2. VERIFICAR PROPIEDAD
    // =====================================================

    $imageId = $_POST['imageId'] ?? null;

    if (empty($imageId) || !is_numeric($imageId)) {
        echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
        exit;
    }

    $imageId = (int)$imageId;

    $imageData = Imagen::getById($conn, $imageId);

    if (!$imageData || $imageData['I_idUser'] != $user->id) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para modificar esta imagen."]);
        exit;
    }

    // =====================================================
    // 3. ELIMINAR ÁLBUM (BD y FÍSICO)
    // =====================================================
    
    // 3.1. Eliminar registro de BD (Activa CASCADE en imágenes, likes, etc.)
    if (Imagen::setAsProfile($conn, $imageId, $user->id)) {
        echo json_encode([
            "status" => "success",
            "message" => "Imagen '{$imageData['I_title']}' establecida como perfil correctamente."
        ]);
        
    } else {
        // Error de base de datos durante la eliminación
        echo json_encode([
            "status" => "error",
            "message" => "Fallo al establecer la imagen como perfil en la base de datos."
        ]);
    }

    desconexion($conn);
?>