<?php
class Imagen {

    // 🔹 Subir/crear una imagen
    public static function crear($conn, $titulo, $ruta, $idUsuario, $idAlbum = NULL, $visibility = 0, $esPerfil = 0) {
        $titulo = mysqli_real_escape_string($conn, $titulo);
        $ruta = mysqli_real_escape_string($conn, $ruta);
        $idUsuario = (int)$idUsuario;
        $idAlbum = $idAlbum !== NULL ? (int)$idAlbum : "NULL";
        $visibility = (int)$visibility;
        $esPerfil = (int)$esPerfil;

        // Si es perfil, desmarcar cualquier otra imagen actual
        if ($esPerfil) {
            $sqlReset = "UPDATE images SET I_currentProfile = 0 WHERE I_idUser = $idUsuario";
            mysqli_query($conn, $sqlReset);
        }

        $currentProfile = $esPerfil ? 1 : 0;
        $isProfile = $esPerfil ? 1 : 0;

        $sql = "INSERT INTO images (I_title, I_visibility, I_idAlbum, I_idUser, I_ruta, I_isProfile, I_currentProfile)
                VALUES ('$titulo', $visibility, $idAlbum, $idUsuario, '$ruta', $isProfile, $currentProfile)";

        return mysqli_query($conn, $sql);
    }


    // 🔹 Obtener todas las imágenes de un álbum
    public static function getByAlbum($conn, $idAlbum) {
        $idAlbum = (int)$idAlbum;

        $sql = "SELECT * FROM images WHERE I_idAlbum = $idAlbum ORDER BY I_publicationDate DESC";
        $resultado = mysqli_query($conn, $sql);

        $images = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $images[] = $fila;
            }
        }
        return $images;
    }

    // 🔹 Obtener una imagen por ID
    public static function getById($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "SELECT * FROM images WHERE I_id = $idImagen LIMIT 1";
        $resultado = mysqli_query($conn, $sql);

        return $resultado && mysqli_num_rows($resultado) > 0
            ? mysqli_fetch_assoc($resultado)
            : null;
    }

    //Cambiar estado de revisión según número de denuncias
    //EJECUTAR LUEGO DE AGREGAR UNA DENUNCIA
    public static function actualizarRevision($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "SELECT COUNT(*) AS total FROM complaints 
        WHERE D_idImage = $idImagen AND D_status <> -1";
        $resultado = mysqli_query($conn, $sql);
        $fila = mysqli_fetch_assoc($resultado);

        if ($fila['total'] >= 5) {
            $sqlUpdate = "UPDATE images SET I_revisionStatus = 1 WHERE I_id = $idImagen";
            mysqli_query($conn, $sqlUpdate);
        }
    }


    // 🔹 Eliminar imagen
    public static function eliminar($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        // 1️⃣ Actualizar álbumes que tengan esta imagen como portada
        $sqlPortada = "UPDATE albums SET A_idPortada = NULL WHERE A_idPortada = $idImagen";
        mysqli_query($conn, $sqlPortada);

        // 2️⃣ Eliminar comentarios relacionados
        Comment::eliminarPorImagen($conn, $idImagen);

        // 3️⃣ Eliminar likes relacionados
        Like::eliminarPorImagen($conn, $idImagen);

        // 4️⃣ Eliminar denuncias relacionadas
        Complaint::eliminarPorImagen($conn, $idImagen);

        // 5️⃣ Finalmente eliminar la imagen
        $sql = "DELETE FROM images WHERE I_id = $idImagen";
        return mysqli_query($conn, $sql);
    }



    // 🔹 Cambiar visibilidad (0=publica, 1=privada)
    public static function setVisibility($conn, $idImagen, $visibility) {
        $idImagen = (int)$idImagen;
        $visibility = (int)$visibility;

        $sql = "UPDATE images SET I_visibility = $visibility WHERE I_id = $idImagen";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Marcar imagen como foto de perfil
    public static function setAsProfile($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        // Primero desmarcamos cualquier imagen de perfil actual
        $sql1 = "UPDATE images SET I_currentProfile = 0 WHERE I_idUser = $idUsuario";
        mysqli_query($conn, $sql1);

        // Luego marcamos la nueva como perfil
        $sql2 = "UPDATE images SET I_isProfile = 1, I_currentProfile = 1 WHERE I_id = $idImagen AND I_idUser = $idUsuario";
        return mysqli_query($conn, $sql2);
    }

    public static function getProfileImagePath($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;
        $defaultPath = './Frontend/assets/images/appImages/default.jpg';
        
        // 1. Buscar la imagen marcada como I_currentProfile = 1
        $sql = "SELECT I_ruta FROM images 
                WHERE I_idUser = $idUsuario AND I_currentProfile = 1 
                LIMIT 1";
        
        $resultado = mysqli_query($conn, $sql);
        
        // 2. Si se encuentra una fila, devolver la ruta almacenada
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $fila = mysqli_fetch_assoc($resultado);
            return $fila['I_ruta'];
        }
        
        // 3. Si no se encuentra, devolver la ruta por defecto
        return $defaultPath;
    }

    // 🔹 Quitar imagen de perfil
    public static function removeProfile($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        $sql = "UPDATE images SET I_currentProfile = 0 WHERE I_id = $idImagen AND I_idUser = $idUsuario";
        return mysqli_query($conn, $sql);
    }

}
?>

