<?php

    class Imagen {

        // 🔹 Subir/crear una imagen (COMPLETA para el nuevo esquema)
        public static function crear($conn, $titulo, $ruta, $idUsuario, $idAlbum = NULL, $visibility = 0, $esPerfil = 0, $esPortada = 0) {
            $titulo = mysqli_real_escape_string($conn, $titulo);
            $ruta = mysqli_real_escape_string($conn, $ruta);
            $idUsuario = (int)$idUsuario;
            $idAlbum = $idAlbum !== NULL ? (int)$idAlbum : "NULL";
            $visibility = (int)$visibility;
            $esPerfil = (int)$esPerfil;
            $esPortada = (int)$esPortada; 

            // 1. Lógica de perfil (Desmarcar perfil anterior)
            if ($esPerfil) {
                $sqlReset = "UPDATE images SET I_currentProfile = 0 WHERE I_idUser = $idUsuario";
                mysqli_query($conn, $sqlReset);
            }
            
            // 2. Lógica para la portada (Desmarcar portada anterior del mismo álbum)
            // Esto solo aplica si se está asignando a un álbum específico (no NULL)
            if ($esPortada && $idAlbum !== "NULL") {
                $sqlResetCover = "UPDATE images SET I_isCover = 0 WHERE I_idAlbum = $idAlbum AND I_idUser = $idUsuario";
                mysqli_query($conn, $sqlResetCover);
            }

            $currentProfile = $esPerfil ? 1 : 0;
            $isProfile = $esPerfil ? 1 : 0;
            $isCover = $esPortada ? 1 : 0;

            // 3. Consulta de Inserción
            $sql = "INSERT INTO images (I_title, I_visibility, I_idAlbum, I_idUser, I_ruta, I_isProfile, I_currentProfile, I_isCover)
                    VALUES ('$titulo', $visibility, $idAlbum, $idUsuario, '$ruta', $isProfile, $currentProfile, $isCover)";

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

            // 1️⃣ Borrado físico
            $sqlRuta = "SELECT I_ruta FROM images WHERE I_id = $idImagen";
            $resultado = mysqli_query($conn, $sqlRuta);
            if ($fila = mysqli_fetch_assoc($resultado)) {
                $rutaCompleta = $fila['I_ruta'];
                if (strpos($rutaCompleta, './FILES/') === 0 && file_exists($rutaCompleta)) {
                    @unlink($rutaCompleta);
                }
            }

            // 2️⃣ Desmarcar perfil
            $sqlPerfil = "UPDATE images SET I_currentProfile = 0 WHERE I_id = $idImagen";
            mysqli_query($conn, $sqlPerfil);

            // 3️⃣ La eliminación en cascada de likes, comments, complaints, y album_images_link la maneja la BD.
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

        // 🔹 Quitar imagen de perfil
        public static function removeProfile($conn, $idImagen, $idUsuario) {
            $idImagen = (int)$idImagen;
            $idUsuario = (int)$idUsuario;

            $sql = "UPDATE images SET I_currentProfile = 0 WHERE I_id = $idImagen AND I_idUser = $idUsuario";
            return mysqli_query($conn, $sql);
        }
        
        // 🔹 Marcar/cambiar la portada de un álbum (Implementación en I_isCover)
        public static function setCover($conn, $idImagen, $idAlbum, $idUsuario) {
            $idImagen = (int)$idImagen;
            $idAlbum = (int)$idAlbum;
            $idUsuario = (int)$idUsuario;
            
            // 1. Desmarcar cualquier otra portada en ese mismo álbum
            $sql1 = "UPDATE images SET I_isCover = 0 WHERE I_idAlbum = $idAlbum AND I_idUser = $idUsuario";
            mysqli_query($conn, $sql1);
            
            // 2. Marcar esta imagen como la nueva portada
            $sql2 = "UPDATE images SET I_isCover = 1 WHERE I_id = $idImagen AND I_idAlbum = $idAlbum AND I_idUser = $idUsuario";
            return mysqli_query($conn, $sql2);
        }

        // 🔹 Obtener la ruta de la imagen de perfil
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
    }
?>