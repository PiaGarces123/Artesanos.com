<?php
class Like {

    // 🔹 Agregar un like a una imagen
    public static function agregar($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        // Prevenir duplicados (gracias al UNIQUE en la tabla)
        $sql = "INSERT IGNORE INTO likes (L_idImage, L_idUser) VALUES ($idImagen, $idUsuario)";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Quitar un like de una imagen
    public static function quitar($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        $sql = "DELETE FROM likes WHERE L_idImage = $idImagen AND L_idUser = $idUsuario";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Contar los likes de una imagen
    public static function contarPorImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "SELECT COUNT(*) AS totalLikes FROM likes WHERE L_idImage = $idImagen";
        $resultado = mysqli_query($conn, $sql);
        $fila = mysqli_fetch_assoc($resultado);

        return $fila['totalLikes'] ?? 0;
    }

    // 🔹 Saber si un usuario ya le dio like a una imagen
    public static function existe($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT 1 FROM likes WHERE L_idImage = $idImagen AND L_idUser = $idUsuario LIMIT 1";
        $resultado = mysqli_query($conn, $sql);

        return mysqli_num_rows($resultado) > 0;
    }

    // 🔹 Eliminar todos los likes de una imagen (cuando se elimina la imagen)
    public static function eliminarPorImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "DELETE FROM likes WHERE L_idImage = $idImagen";
        return mysqli_query($conn, $sql);
    }
}
?>
