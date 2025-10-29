<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

    session_start();
    
    // Configuración de zona horaria
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    require_once "../Clases/Album.php";
    require_once "../Clases/image.php"; 
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    // Indicar que la respuesta es JSON
    header("Content-Type: application/json");
    
    
    
    $conn = conexion();
    
    
    // =====================================================
    // 1. CHEQUEO DE SESIÓN y CONEXIÓN (Tu lógica)
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
    // 2. VERIFICAR DATOS Y PROPIEDAD (Tu lógica adaptada)
    // =====================================================

    $imageId = $_POST['imageId'] ?? null;
    $albumId = $_POST['albumId'] ?? null;

    // --- Validar Image ID ---
    if (empty($imageId) || !is_numeric($imageId)) {
        echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
        exit;
    }

    // --- Validar Album ID ---
    if (empty($albumId) || !is_numeric($albumId)) {
        echo json_encode(["status" => "error", "message" => "ID de álbum no válido."]);
        exit;
    }

    $imageId = (int)$imageId;
    $albumId = (int)$albumId;

    // --- Verificar Propiedad de la Imagen ---
    $imageData = Imagen::getById($conn, $imageId);

    if (!$imageData || $imageData['I_idUser'] != $user->id) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para modificar esta imagen."]);
        exit;
    }
    
    // --- Verificar Propiedad del Álbum ---
    $albumData = Album::getById($conn, $albumId);
    
    if (!$albumData || $albumData['A_idUser'] != $user->id) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para modificar este álbum."]);
        exit;
    }
    
    // --- Verificar que la imagen pertenece al álbum ---
    if ($imageData['I_idAlbum'] != $albumId) {
         echo json_encode(["status" => "error", "message" => "Esta imagen no pertenece a ese álbum."]);
        exit;
    }

    // =====================================================
    // 3. ESTABLECER PORTADA (Lógica Principal)
    // =====================================================
    
    // Llamamos a la función de tu clase Imagen
    // Imagen::setCover($conn, $idImagen, $idAlbum, $idUsuario)
    if (Imagen::setCover($conn, $imageId, $albumId, $user->id)) {
        echo json_encode([
            "status" => "success",
            "message" => "¡'{$imageData['I_title']}' es la nueva portada del álbum '{$albumData['A_title']}'!"
        ]);
        
    } else {
        // Error de base de datos
        echo json_encode([
            "status" => "error",
            "message" => "Fallo al establecer la portada en la base de datos."
        ]);
    }

    desconexion($conn);
?>