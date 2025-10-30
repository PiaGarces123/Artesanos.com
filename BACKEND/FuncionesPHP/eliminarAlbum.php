<?php 
    // Mostrar errores en pantalla (lo usamos durante el desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Iniciar o reanudar la sesión del usuario
    session_start();
    
    // Configuración de zona horaria
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php"; 
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    // Indicar que la respuesta es JSON
    header("Content-Type: application/json");

    // =====================================================
    // FUNCIÓN AUXILIAR: ELIMINAR DIRECTORIO RECURSIVAMENTE
    // =====================================================

    //Elimina un directorio y todo su contenido (subcarpetas y archivos).
    //Se usa para borrar la carpeta física del álbum cuando se elimina.
    function deleteDirectory($dir) {

        // Si no es un directorio, no hace nada
        if (!is_dir($dir)) {
            return;
        }

        // Obtiene todos los archivos y carpetas dentro del directorio
        $files = array_diff(scandir($dir), array('.', '..'));

        // Recorre cada elemento del directorio
        foreach ($files as $file) {

            // Si es una subcarpeta → se llama recursivamente
            // Si es un archivo → se elimina con unlink()
            (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : @unlink("$dir/$file");

        }

        // Finalmente elimina el directorio vacío
        return @rmdir($dir);
    }
    
    // =====================================================
    // 1. CHEQUEO DE SESIÓN y CONEXIÓN
    // =====================================================

    // Verifica que haya una sesión activa (usuario logueado)
    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Tu sesión ha expirado."
        ]);
        exit; // Detiene el script
    }
    
    // Establece conexión con la base de datos
    $conn = conexion();

    // Obtiene el ID del usuario logueado desde la sesión
    $userId = $_SESSION['user_id'];

    // Obtiene el ID del álbum enviado por POST (desde el frontend)
    $albumId = $_POST['albumId'] ?? null;
    
    // Verifica que el ID de álbum sea válido (numérico y no vacío)
    if (empty($albumId) || !is_numeric($albumId)) {
        echo json_encode(["status" => "error", "message" => "ID de álbum no válido."]);
        exit; // Detiene el script
    }
    
    // Convierte el ID a entero (por seguridad)
    $albumId = (int)$albumId;

    
    // =====================================================
    // 2. VERIFICAR PROPIEDAD Y PREPARAR RUTA
    // =====================================================

    // Busca los datos del álbum en la base de datos por su ID
    $albumData = Album::getById($conn, $albumId);
    
    // Si no existe el álbum o pertenece a otro usuario → no se permite eliminar
    if (!$albumData || $albumData['A_idUser'] != $userId) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para eliminar este álbum."]);
        exit;
    }

    // DETERMINAR RUTA FÍSICA DE LA CARPETA
    // Asumo la misma estructura de ruta que usamos en publicarContenido.php
    $base_files_dir = __DIR__ . '/../../FILES/';
    $album_folder_path = $base_files_dir . $userId . '/' . $albumId;

    
    // =====================================================
    // 3. ELIMINAR ÁLBUM (BD y FÍSICO)
    // =====================================================
    
    // 3.1. Eliminar registro de BD (Activa CASCADE en imágenes, likes, etc.)
    if (Album::eliminar($conn, $albumId)) {
        
        // 3.2. Eliminar la carpeta física si existe, también se borra del servidor
        if (is_dir($album_folder_path)) {
            $estado = deleteDirectory($album_folder_path);
        }
        
        // Devuelve respuesta de éxito en formato JSON
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

    // Cierra la conexión con la base de datos
    desconexion($conn);
?>