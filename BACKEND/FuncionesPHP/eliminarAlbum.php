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

    // Función auxiliar para eliminar el directorio físico de forma recursiva
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : @unlink("$dir/$file");
        }
        return @rmdir($dir);
    }
    
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
    $userId = $_SESSION['user_id'];
    $albumId = $_POST['albumId'] ?? null;
    
    if (empty($albumId) || !is_numeric($albumId)) {
        echo json_encode(["status" => "error", "message" => "ID de álbum no válido."]);
        exit;
    }
    
    $albumId = (int)$albumId;
    
    // =====================================================
    // 2. VERIFICAR PROPIEDAD Y PREPARAR RUTA
    // =====================================================
    $albumData = Album::getById($conn, $albumId);
    
    if (!$albumData || $albumData['A_idUser'] != $userId) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para eliminar este álbum."]);
        exit;
    }

    // 💡 DETERMINAR RUTA FÍSICA DE LA CARPETA
    // Asumo la misma estructura de ruta que usamos en publicarContenido.php
    $base_files_dir = __DIR__ . '/../../FILES/';
    $album_folder_path = $base_files_dir . $userId . '/' . $albumId;

    
    // =====================================================
    // 3. ELIMINAR ÁLBUM (BD y FÍSICO)
    // =====================================================
    
    // 3.1. Eliminar registro de BD (Activa CASCADE en imágenes, likes, etc.)
    if (Album::eliminar($conn, $albumId)) {
        
        // 3.2. Eliminar la carpeta física si existe
        if (is_dir($album_folder_path)) {
            $estado = deleteDirectory($album_folder_path);
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Álbum '{$albumData['A_title']}' eliminado correctamente."
        ]);
        
    } else {
        // Error de base de datos durante la eliminación
        echo json_encode([
            "status" => "error",
            "message" => "Fallo al eliminar el álbum en la base de datos."
        ]);
    }

    desconexion($conn);
?>