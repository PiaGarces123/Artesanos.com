<?php
    class Album {

        // 🔹 Crear un nuevo álbum (Añadido lógica de sistema/seguimiento)
        // Retorna el ID del nuevo álbum si tiene éxito, o false si falla.
        public static function crear($conn, $titulo, $idUsuario, $isSystem = 0, $idFollowedUser = null) {
            $titulo = mysqli_real_escape_string($conn, $titulo);
            $idUsuario = (int)$idUsuario;
            $isSystem = (int)$isSystem;
            
            // Sanitizar el ID del seguido, o dejarlo como NULL para SQL
            $idFollowedUserSQL = $idFollowedUser !== null ? (int)$idFollowedUser : "NULL";
            $A_creationDate = date("Y-m-d H:i:s");

            $sql = "INSERT INTO albums (A_title, A_idUser, A_creationDate, A_isSystemAlbum, A_idFollowedUser) 
                    VALUES ('$titulo', $idUsuario, '$A_creationDate', $isSystem, $idFollowedUserSQL)";
            
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                return mysqli_insert_id($conn);
            }
            return false;
        }

        //obtener la cantidad de imagenes en un album
        public static function contarImagenes($conn, $idAlbum) {
            $idAlbum = (int)$idAlbum;

            // 1. Primero, verificamos qué tipo de álbum es
            $sqlCheck = "SELECT A_isSystemAlbum FROM albums WHERE A_id = $idAlbum LIMIT 1";
            $albumResult = mysqli_query($conn, $sqlCheck);
            
            if (!$albumResult || mysqli_num_rows($albumResult) == 0) {
                return 0; // El álbum no existe
            }
            $albumData = mysqli_fetch_assoc($albumResult);
            $isSystemAlbum = (int)$albumData['A_isSystemAlbum'];

            
            // 2. Preparamos la consulta de COUNT según el tipo
            $sql = "";
            if ($isSystemAlbum === 1) {
                // --- Es un ÁLBUM DE SISTEMA ---
                // Contamos las entradas en la tabla 'album_images_link'
                $sql = "SELECT COUNT(*) AS total 
                        FROM album_images_link 
                        WHERE L_idAlbum = $idAlbum";
            } else {
                // --- Es un ÁLBUM NORMAL ---
                // Contamos las entradas en la tabla 'images'
                $sql = "SELECT COUNT(*) AS total 
                        FROM images 
                        WHERE I_idAlbum = $idAlbum";
            }

            // 3. Ejecutamos la consulta de COUNT
            $resultado = mysqli_query($conn, $sql);
            if ($fila = mysqli_fetch_assoc($resultado)) {
                return (int)$fila['total'];
            }
            
            return 0;
        }
        
        // 🔹 Obtener todos los álbumes de un usuario (para visualización general)
        public static function getByUser($conn, $idUsuario) {
            $idUsuario = (int)$idUsuario;

            $sql = "SELECT * FROM albums 
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
        
        // 🔹 Obtener álbumes válidos para publicar (Excluye álbumes del sistema)
        public static function getByUserForPublishing($conn, $idUsuario) {
            $idUsuario = (int)$idUsuario;

            $sql = "SELECT * FROM albums 
                    WHERE A_idUser = $idUsuario AND A_isSystemAlbum = 0
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

        // 🔹 Eliminar álbum
        public static function eliminar($conn, $idAlbum) {
            $idAlbum = (int)$idAlbum;
            
            // La eliminación en cascada de imágenes y links de colección es manejada por la BD.
            $sql = "DELETE FROM albums WHERE A_id = $idAlbum";
            return mysqli_query($conn, $sql);
        }
        
        
        //   Devuelve la ruta de la imagen de portada de un álbum.
        //   - Si es un álbum normal, busca la imagen marcada como I_isCover.
        //   - Si es un álbum de sistema, busca la foto de perfil actual (I_currentProfile)
        //   del usuario seguido (A_idFollowedUser).
        //   - Si no encuentra nada, devuelve una ruta por defecto.
        public static function getCoverImagePath($conn, $idAlbum) {
            $idAlbum = (int)$idAlbum;
            
            // Ruta por defecto para portadas de álbumes normales
            $defaultAlbumCover = './Frontend/assets/images/appImages/coverDefault.png'; 
            // Ruta por defecto para fotos de perfil (si el usuario del álbum de sistema no tiene)
            $defaultProfilePic = './Frontend/assets/images/appImages/default.jpg'; 

            
            // 1. Primero, verificamos qué tipo de álbum es
            $sqlAlbumCheck = "SELECT A_isSystemAlbum, A_idFollowedUser 
                            FROM albums 
                            WHERE A_id = $idAlbum 
                            LIMIT 1";
            
            $albumResult = mysqli_query($conn, $sqlAlbumCheck);

            if (!$albumResult || mysqli_num_rows($albumResult) == 0) {
                return $defaultAlbumCover; // El álbum no existe, devolvemos portada por defecto
            }
            
            $albumData = mysqli_fetch_assoc($albumResult);
            $isSystemAlbum = (int)$albumData['A_isSystemAlbum'];
            $idFollowedUser = (int)$albumData['A_idFollowedUser'];

            
            // 2. Aplicamos la lógica según el tipo de álbum
            if ($isSystemAlbum === 1 && $idFollowedUser > 0) {
                
                // --- ES UN ÁLBUM DE SISTEMA ---
                // Buscamos la foto de perfil actual del usuario seguido (Usuario B)
                
                $sqlProfilePic = "SELECT I_ruta 
                                FROM images 
                                WHERE I_idUser = $idFollowedUser AND I_currentProfile = 1 
                                LIMIT 1";
                
                $profileResult = mysqli_query($conn, $sqlProfilePic);
                
                if ($profileResult && $fila = mysqli_fetch_assoc($profileResult)) {
                    return $fila['I_ruta']; // Devuelve la foto de perfil actual
                }
                
                // Si el usuario seguido no tiene foto de perfil, devolvemos la foto por defecto
                return $defaultProfilePic; 

            } else {
                
                // --- ES UN ÁLBUM NORMAL (Tu lógica original) ---
                // Buscamos la imagen marcada como portada (I_isCover = 1)
                
                $sqlCover = "SELECT I_ruta 
                            FROM images 
                            WHERE I_idAlbum = $idAlbum AND I_isCover = 1 
                            LIMIT 1";
                
                $coverResult = mysqli_query($conn, $sqlCover);
                
                if ($coverResult && $fila = mysqli_fetch_assoc($coverResult)) {
                    return $fila['I_ruta']; // Devuelve la portada del álbum
                }
                
                // Si no hay portada asignada, devolvemos la portada por defecto
                return $defaultAlbumCover;
            }
        }

        // Verifica si un album existe, true si existe
        public static function exists($conn, $idAlbum) {
            // Reutilizamos getById. Si el resultado no es NULL, el álbum existe.
            return (self::getById($conn, $idAlbum) !== null);
        }


        // ... (tus otras funciones) ...

        /**
         * 🔹 Elimina un álbum de sistema basado en una relación de seguimiento.
         * Busca y elimina el álbum de colección que $idSeguidor (A) tenía de $idSeguido (B).
         * @return int|null Retorna el ID del álbum eliminado si tuvo éxito, o null si no se encontró.
         */
        public static function eliminarAlbumDeSistemaPorSeguimiento($conn, $idSeguidor, $idSeguido) {
            $idSeguidor = (int)$idSeguidor;
            $idSeguido = (int)$idSeguido;

            // 1. Buscar el álbum de sistema
            $sqlFind = "SELECT A_id FROM albums 
                        WHERE A_idUser = $idSeguidor 
                        AND A_idFollowedUser = $idSeguido 
                        AND A_isSystemAlbum = 1 
                        LIMIT 1";
            
            $resultado = mysqli_query($conn, $sqlFind);

            if ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
                $albumIdParaEliminar = (int)$fila['A_id'];
                
                // 2. Intentar eliminar el álbum de la BD
                if (self::eliminar($conn, $albumIdParaEliminar)) {
                    // Si la eliminación en BD fue exitosa, devolvemos el ID
                    return $albumIdParaEliminar; 
                } else {
                    // Si falló la eliminación en BD, retornamos null
                    return null;
                }
            }
            
            // No se encontró álbum
            return null;
        }

        // 🔹 Vincular una imagen a un álbum (para poblar álbumes de sistema)
        public static function linkImageToAlbum($conn, $idAlbum, $idImage) {
            $idAlbum = (int)$idAlbum;
            $idImage = (int)$idImage;
            
            // Usamos IGNORE para evitar errores si el link ya existe (doble like, etc.)
            $sql = "INSERT IGNORE INTO album_images_link (L_idAlbum, L_idImage) 
                    VALUES ($idAlbum, $idImage)";
            return mysqli_query($conn, $sql);
        }

        /**
        * 🔹 Desvincula una imagen de un álbum (para álbumes de sistema).
        * Usa la tabla 'album_images_link'.
        */
        public static function unlinkImageFromAlbum($conn, $idAlbum, $idImage) {
            $idAlbum = (int)$idAlbum;
            $idImage = (int)$idImage;
            
            $sql = "DELETE FROM album_images_link 
                    WHERE L_idAlbum = $idAlbum AND L_idImage = $idImage";
            return mysqli_query($conn, $sql);
        }
    }
?>