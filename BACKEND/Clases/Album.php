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

            // Esta consulta es ahora más compleja si es un álbum del sistema,
            // pero para un álbum normal (I_idAlbum), se mantiene:
            $sql = "SELECT COUNT(*) AS total 
                    FROM images 
                    WHERE I_idAlbum = $idAlbum";

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
        
        // Devuelve la ruta de la imagen de portada de un album o una ruta por defecto
        public static function getCoverImagePath($conn, $idAlbum) {
            $idAlbum = (int)$idAlbum;
            
            // Ruta de la imagen por defecto que se usa cuando no hay portada asignada
            $defaultPath = './Fronend/assets/images/appImages/coverDefault.png'; 
            
            // 1. Consulta para buscar la ruta de la imagen marcada como portada (I_isCover = 1)
            $sql = "SELECT I_ruta FROM images 
                    WHERE I_idAlbum = $idAlbum AND I_isCover = 1 
                    LIMIT 1";
            
            $resultado = mysqli_query($conn, $sql);
            
            // 2. Verificar si la consulta fue exitosa y encontró una fila
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                // Si encuentra una portada, extrae y devuelve su ruta
                $fila = mysqli_fetch_assoc($resultado);
                return $fila['I_ruta'];
            }
            
            // 3. Si no hay resultados (o si la consulta falló), devuelve la ruta por defecto
            return $defaultPath;
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
    }
?>