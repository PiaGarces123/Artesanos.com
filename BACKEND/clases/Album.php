<?php
class Album {

    // 🔹 Crear un nuevo álbum (sin portada aún)
    public static function crear($conn, $titulo, $idUsuario) {
        $titulo = mysqli_real_escape_string($conn, $titulo);
        $idUsuario = (int)$idUsuario;

        $sql = "INSERT INTO albums (A_title, A_idUser) VALUES ('$titulo', $idUsuario)";
        return mysqli_query($conn, $sql);
    }

    //obtener la cantidad de imagenes en un album
    public static function contarImagenes($conn, $idAlbum) {
        $idAlbum = (int)$idAlbum;

        $sql = "SELECT COUNT(*) AS total 
                FROM imagenes 
                WHERE I_idAlbum = $idAlbum";

        $resultado = mysqli_query($conn, $sql);
        if ($fila = mysqli_fetch_assoc($resultado)) {
            return (int)$fila['total'];
        }
        return 0;
    }

    // 🔹 Obtener todos los álbumes de un usuario (con info de portada)
    public static function getByUser($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT * 
        FROM albums 
        WHERE A_idUser = $idUsuario
        ORDER BY A_creationDate DESC";

        $resultado = mysqli_query($conn, $sql);
        $albums = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $albums[] = $fila;
            }
        }
        return $albums;
    }

    // 🔹 Obtener un álbum específico por ID
    public static function getById($conn, $idAlbum) {
        $idAlbum = (int)$idAlbum;

        $sql = "SELECT * FROM albums WHERE A_id = $idAlbum LIMIT 1";
        $resultado = mysqli_query($conn, $sql);

        return $resultado && mysqli_num_rows($resultado) > 0
            ? mysqli_fetch_assoc($resultado)
            : null;
    }

    // 🔹 Editar título de un álbum
    public static function editar($conn, $idAlbum, $nuevoTitulo) {
        $nuevoTitulo = mysqli_real_escape_string($conn, $nuevoTitulo);
        $idAlbum = (int)$idAlbum;

        $sql = "UPDATE albums SET A_title = '$nuevoTitulo' WHERE A_id = $idAlbum";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Eliminar álbum y sus imágenes
    public static function eliminar($conn, $idAlbum) {
        $idAlbum = (int)$idAlbum;

        // Primero eliminar imágenes asociadas
        $sqlImg = "DELETE FROM imagenes WHERE I_idAlbum = $idAlbum";
        mysqli_query($conn, $sqlImg);

        // Luego eliminar el álbum
        $sql = "DELETE FROM albums WHERE A_id = $idAlbum";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Asignar o cambiar la portada de un álbum
    public static function setPortada($conn, $idAlbum, $idImagen) {
        $idAlbum = (int)$idAlbum;
        $idImagen = (int)$idImagen;

        $sql = "UPDATE albums SET A_idPortada = $idImagen WHERE A_id = $idAlbum";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Quitar la portada de un álbum
    public static function quitarPortada($conn, $idAlbum) {
        $idAlbum = (int)$idAlbum;

        $sql = "UPDATE albums SET A_idPortada = NULL WHERE A_id = $idAlbum";
        return mysqli_query($conn, $sql);
    }
}
?>
