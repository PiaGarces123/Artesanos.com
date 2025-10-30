<?php

    // =====================================================
    // ARCHIVO: dejarDeSeguir.php
    // FUNCIÓN: Permite a un usuario dejar de seguir a otro.
    // También elimina el álbum de sistema creado para ese seguimiento.
    // =====================================================

    // Importamos las clases necesarias y la conexión a la base de datos
    require_once "../Clases/Follow.php";
    require_once "../Clases/User.php";
    require_once "../Clases/Album.php"; 
    require_once "../conexion.php"; 

    // Inicia o reanuda la sesión actual del usuario
    session_start();

    // Indicamos que todas las respuestas del servidor serán en formato JSON
    header('Content-Type: application/json');

    // =====================================================
    // FUNCIÓN AUXILIAR: deleteDirectory()
    // =====================================================

    //Elimina un directorio y todo su contenido (archivos y subcarpetas).
    // Se usa para borrar físicamente la carpeta del álbum cuando
    // se elimina el álbum de sistema asociado a un seguimiento.

    //@param string $dir Ruta absoluta del directorio a eliminar.
    //@return bool Devuelve true si se eliminó correctamente, false si no existe o falla.

    function deleteDirectory($dir) {

        // Verifica si el parámetro es un directorio válido
        if (!is_dir($dir)) {
            return false; // No es un directorio
        }

        // Asegura que la ruta termine con una barra "/"
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/'; // Asegurar que termine con barra
        }

        // Obtiene todos los archivos y subcarpetas dentro del directorio
        $files = glob($dir . '*', GLOB_MARK);

        // Recorre cada elemento
        foreach ($files as $file) {

            // Si es una carpeta → llamada recursiva para eliminar su contenido
            if (is_dir($file)) {
                deleteDirectory($file); // Llamada recursiva para subdirectorios
            } 

            // Si es un archivo → lo elimina
            else {
                @unlink($file); // Borrar archivo
            }

        }

        // Finalmente elimina la carpeta vacía
        return @rmdir($dir); // Borrar el directorio vacío

    }
    // --- Fin de la función ---

    // =====================================================
    // 1. VALIDAR SESIÓN DEL USUARIO
    // =====================================================

    // Si no hay sesión activa, el usuario no puede realizar la acción
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
        exit;
    }

    // Conexión a la base de datos
    $conn = conexion();

    // ID del usuario A (el que deja de seguir)
    $idSeguidor = $_SESSION['user_id'];

    // ID del usuario B (el que era seguido)
    $idSeguido = $_POST['targetUserId'] ?? null; 

    // Validamos que el ID del usuario seguido sea un número válido
    if (empty($idSeguido) || !is_numeric($idSeguido)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
        desconexion($conn);
        exit;
    }

    // =====================================================
    // 2. ELIMINAR LA RELACIÓN DE SEGUIMIENTO
    // =====================================================

    // Llama al método Follow::eliminar() para eliminar el registro de seguimiento
    // Este método elimina la relación (idSeguidor → idSeguido) en la base de datos
    if (Follow::eliminar($conn, $idSeguidor, $idSeguido)) {
        
        // =====================================================
        // 3. ELIMINAR EL ÁLBUM DE SISTEMA ASOCIADO (si existe)
        // =====================================================

        // Cada vez que un usuario sigue a otro, se puede crear un “álbum de sistema”.
        // Este álbum contiene publicaciones relacionadas con el usuario seguido.
        // Al dejar de seguirlo, ese álbum ya no tiene sentido, por lo tanto lo eliminamos.

        // Este método devuelve el ID del álbum eliminado o null si no existía
        $albumIdEliminado = Album::eliminarAlbumDeSistemaPorSeguimiento($conn, $idSeguidor, $idSeguido);


        // =====================================================
        // 4. ELIMINAR LA CARPETA FÍSICA ASOCIADA (si existía)
        // =====================================================
        if ($albumIdEliminado !== null) {

            // Construye la ruta del álbum físico dentro del directorio FILES
            // Ejemplo: ../../FILES/{idSeguidor}/{idAlbum elimminado}
            $base_files_dir = __DIR__ . '/../../FILES/';
            $album_folder_path = $base_files_dir . $idSeguidor . '/' . $albumIdEliminado;

            // Si la carpeta existe, la elimina usando la función deleteDirectory()
            if (is_dir($album_folder_path)) {

                deleteDirectory($album_folder_path);
                // Si falla, no se considera un error crítico (el sistema sigue funcionando)

            }
        }
        
        // =====================================================
        // 5. RESPUESTA DE ÉXITO
        // =====================================================
        echo json_encode([
            "status" => "success",
            "newState" => "follow" 
        ]);
        
    } else {

        // =====================================================
        // 6. ERROR AL ELIMINAR EL SEGUIMIENTO
        // =====================================================

        echo json_encode([
            "status" => "error",
            "message" => "No se pudo cancelar la acción."
        ]);
    }

    // Cierra la conexión con la base de datos
    desconexion($conn);
?>