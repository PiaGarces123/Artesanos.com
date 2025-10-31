<?php
class Comment {

    // 🔹 Agregar un comentario a una imagen
    public static function agregar($conn, $idImagen, $idUsuario, $contenido) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;
        $contenido = mysqli_real_escape_string($conn, $contenido);

        $sql = "INSERT INTO comments (C_content, C_idImage, C_idUser) 
                VALUES ('$contenido', $idImagen, $idUsuario)";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Obtener todos los comentarios de una imagen
    public static function getByImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "SELECT c.*, u.U_name, u.U_lastName 
                FROM comments c
                INNER JOIN users u ON c.C_idUser = u.U_id
                WHERE C_idImage = $idImagen
                ORDER BY C_publicationDate ASC";

        $resultado = mysqli_query($conn, $sql);
        $comentarios = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $comentarios[] = $fila;
            }
        }
        return $comentarios;
    }

    // 🔹 Eliminar un comentario específico
    public static function eliminar($conn, $idComentario) {
        $idComentario = (int)$idComentario;

        $sql = "DELETE FROM comments WHERE C_id = $idComentario";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Eliminar todos los comentarios de una imagen (cuando se elimina la imagen)
    public static function eliminarPorImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "DELETE FROM comments WHERE C_idImage = $idImagen";
        return mysqli_query($conn, $sql);
    }

    // ====================================================
    // 🔹 Obtener comentarios con el avatar del usuario
    // ====================================================
    public static function getByImagenConAvatar($conn, $idImagen) {
        $idImagen = (int)$idImagen;
        $defaultPath = './Frontend/assets/images/appImages/default.jpg';

        // JOIN con users (para nombre)
        // LEFT JOIN con images (para la foto de perfil)
        $sql = "SELECT 
                    c.*, 
                    u.U_name, 
                    u.U_lastName, 
                    u.U_nameUser,
                    i.I_ruta AS U_profilePic
                FROM comments c
                INNER JOIN users u ON c.C_idUser = u.U_id
                LEFT JOIN images i ON u.U_id = i.I_idUser AND i.I_currentProfile = 1
                WHERE c.C_idImage = $idImagen
                ORDER BY c.C_publicationDate ASC";

        $resultado = mysqli_query($conn, $sql);
        $comentarios = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Si el comentarista no tiene foto, asignamos la default
                if (empty($fila['U_profilePic'])) {
                    $fila['U_profilePic'] = $defaultPath;
                }
                $comentarios[] = $fila;
            }
        }
        return $comentarios;
    }
}
?>
