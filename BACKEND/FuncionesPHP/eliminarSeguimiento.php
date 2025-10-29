<?php
    require_once "../Clases/Follow.php";
    require_once "../Clases/User.php";
    require_once "../Clases/Album.php"; 
    require_once "../conexion.php"; 

    session_start();
    header('Content-Type: application/json');

    // --- ¡NUEVO! Incluir la función para borrar carpetas ---
    /**
     * Elimina un directorio y todo su contenido recursivamente.
     */
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false; // No es un directorio
        }
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/'; // Asegurar que termine con barra
        }
        $files = glob($dir . '*', GLOB_MARK); // Obtener todo (archivos y carpetas)
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDirectory($file); // Llamada recursiva para subdirectorios
            } else {
                @unlink($file); // Borrar archivo
            }
        }
        return @rmdir($dir); // Borrar el directorio vacío
    }
    // --- Fin de la función ---

    // 1. Validar Sesión
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
        exit;
    }

    $conn = conexion();
    $idSeguidor = $_SESSION['user_id']; // ID del usuario A (el que deja de seguir)
    $idSeguido = $_POST['targetUserId'] ?? null; // ID del usuario B (el que era seguido)

    if (empty($idSeguido) || !is_numeric($idSeguido)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
        desconexion($conn);
        exit;
    }

    // 2. Ejecutar la acción de dejar de seguir
    if (Follow::eliminar($conn, $idSeguidor, $idSeguido)) {
        
        // 3. Buscar y eliminar el álbum de sistema asociado
        $albumIdEliminado = Album::eliminarAlbumDeSistemaPorSeguimiento($conn, $idSeguidor, $idSeguido);

        // --- ¡NUEVO! Eliminar carpeta física si se eliminó un álbum ---
        if ($albumIdEliminado !== null) {
            // Construimos la ruta (igual que en tu eliminarAlbum.php)
            // ../../FILES/{id_seguidor}/{id_album_eliminado}
            $base_files_dir = __DIR__ . '/../../FILES/';
            $album_folder_path = $base_files_dir . $idSeguidor . '/' . $albumIdEliminado;

            // Intentamos borrar la carpeta física
            if (is_dir($album_folder_path)) {
                deleteDirectory($album_folder_path);
                // No necesitamos verificar el resultado aquí, si falla no es crítico
            }
        }
        // --- Fin de la nueva lógica ---

        echo json_encode([
            "status" => "success",
            "newState" => "follow" 
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo cancelar la acción."
        ]);
    }

    desconexion($conn);
?>