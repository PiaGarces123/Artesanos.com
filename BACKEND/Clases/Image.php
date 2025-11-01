<?php
    class Imagen {

        // 🔹 Subir/crear una imagen (COMPLETA para el nuevo esquema)
        public static function crear($conn, $titulo, $idUsuario, $visibility = 0, $idAlbum = NULL, $ruta = NULL, $esPerfil = 0, $esPortada = 0) {

            // 1. Escapado y Validación de Tipos:
            // **Obligatorio escapar $titulo y $ruta, ya que son cadenas.**
            $titulo_escaped = mysqli_real_escape_string($conn, $titulo ?? ''); 
            $ruta_escaped = mysqli_real_escape_string($conn, $ruta ?? '');
            
            $idUsuario_sanitized = (int)$idUsuario;

            $visibility = (int)$visibility;
            $esPerfil = (int)$esPerfil;
            $esPortada = (int)$esPortada;

            // Lógica para NULL en $idAlbum: 
            // Si es NULL, la variable de la query debe ser la palabra 'NULL'.
            // Si no es NULL, debe ser un entero escapado para su uso directo en la query.
            $idAlbum_query = "";
            $idAlbum_value = NULL; // Almacenará el valor entero si existe
            
            if ($idAlbum !== NULL) {
                $idAlbum_value = (int)$idAlbum;
                $idAlbum_query = $idAlbum_value; // Usar el número
            } else {
                $idAlbum_query = "NULL"; // Usar la palabra reservada SQL
            }

            $currentProfile = $esPerfil ? 1 : 0;
            $isProfile = $esPerfil ? 1 : 0;
            $isCover = $esPortada ? 1 : 0;
            $I_publicationDate = date("Y-m-d H:i:s");

            // --- 1. Lógica de perfil (Desmarcar perfil anterior) ---
            if ($esPerfil) {
                // Concatenación segura con el valor ya escapado/sanitizado: $idUsuario_sanitized
                $sqlReset = "UPDATE images SET I_currentProfile = 0 WHERE I_idUser = $idUsuario_sanitized";
                mysqli_query($conn, $sqlReset);
            }
            
            // --- 2. Lógica para la portada (Desmarcar portada anterior del mismo álbum) ---
            // Solo aplica si se asigna a un álbum (idAlbum_query NO es la cadena "NULL")
            if ($esPortada && $idAlbum_query !== "NULL") {
                // Concatenación segura con $idAlbum_value y $idUsuario_sanitized
                $sqlResetCover = "UPDATE images SET I_isCover = 0 WHERE I_idAlbum = $idAlbum_value AND I_idUser = $idUsuario_sanitized";
                mysqli_query($conn, $sqlResetCover);
            }

            // --- 3. Consulta de Inserción ---
            // Las cadenas se escapan y se usan entre comillas simples ('$titulo_escaped', '$ruta_escaped').
            // Los números y NULL se concatenan directamente (sin comillas).
            $sql = "INSERT INTO images (I_title, I_visibility, I_idAlbum, I_idUser, I_publicationDate, I_ruta, I_isProfile, I_currentProfile, I_isCover)
                    VALUES ('$titulo_escaped', $visibility, $idAlbum_query, $idUsuario_sanitized, '$I_publicationDate', '$ruta_escaped', $isProfile, $currentProfile, $isCover)";

            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                return mysqli_insert_id($conn);
            }
            return false;
        }


        /**
         * 🔹 Obtener todas las imágenes de un álbum (NORMAL o DE SISTEMA)
         * (Versión simple, sin filtros de visibilidad)
         */
        public static function getByAlbum($conn, $idAlbum) {
            $idAlbum = (int)$idAlbum;

            // 1. Verificar el tipo de álbum
            $sqlAlbumCheck = "SELECT A_isSystemAlbum FROM albums WHERE A_id = $idAlbum LIMIT 1";
            $albumResult = mysqli_query($conn, $sqlAlbumCheck);
            
            if (!$albumResult || mysqli_num_rows($albumResult) == 0) {
                return []; // El álbum no existe
            }
            $albumData = mysqli_fetch_assoc($albumResult);
            $isSystemAlbum = (int)$albumData['A_isSystemAlbum'];

            // 2. Construir la consulta SQL
            $sql = "";
            if ($isSystemAlbum === 1) {
                // --- ÁLBUM DE SISTEMA (usa tabla 'album_images_link') ---
                $sql = "SELECT i.* FROM images i
                        INNER JOIN album_images_link l ON i.I_id = l.L_idImage
                        WHERE l.L_idAlbum = $idAlbum 
                        ORDER BY i.I_publicationDate DESC";
            } else {
                // --- ÁLBUM NORMAL (usa 'I_idAlbum') ---
                $sql = "SELECT * FROM images 
                        WHERE I_idAlbum = $idAlbum 
                        ORDER BY I_publicationDate DESC";
            }
            
            // 3. Ejecutar y devolver
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
                return mysqli_query($conn, $sqlUpdate);
            }
            return true;
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
            $sql1 = "UPDATE images SET I_currentProfile = 0 WHERE I_idUser = $idUsuario AND I_currentProfile = 1";
            mysqli_query($conn, $sql1);

            // Luego marcamos la nueva como perfil
            $sql2 = "UPDATE images SET I_isProfile = 1, I_currentProfile = 1 WHERE I_id = $idImagen AND I_idUser = $idUsuario";
            return mysqli_query($conn, $sql2);
        }

        // 🔹 Quitar imagen de perfil
        public static function removeProfile($conn, $idUsuario) {
            $idUsuario = (int)$idUsuario;

            $sql = "UPDATE images SET I_currentProfile = 0 WHERE I_currentProfile = 1 AND I_idUser = $idUsuario";
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

        // 🔹 Actualizar ruta de de la imagen
        public static function actualizarRuta($conn, $idImagen, $rutaImagen) {
            $idImagen = (int)$idImagen;
            $rutaImagen = mysqli_real_escape_string($conn, $rutaImagen);
            
            $sql = "UPDATE images SET I_ruta = '$rutaImagen' WHERE I_id = $idImagen";
            return mysqli_query($conn, $sql);
        }

        /**
         * 🔹 Edita el título y la visibilidad de una imagen
        */
        public static function editar($conn, $idImagen, $nuevoTitulo, $nuevaVisibilidad) {
            $idImagen = (int)$idImagen;
            $nuevoTitulo = mysqli_real_escape_string($conn, $nuevoTitulo);
            $nuevaVisibilidad = (int)$nuevaVisibilidad;

            $sql = "UPDATE images 
                    SET I_title = '$nuevoTitulo', I_visibility = $nuevaVisibilidad 
                    WHERE I_id = $idImagen";
            
            return mysqli_query($conn, $sql);
        }

        /**
        * 🔹 Obtener todas las imágenes de perfil de un usuario
        * Devuelve un array con las imágenes (o un array vacío si no hay).
        */
        public static function obtenerImagenesDePerfilPorUsuario($conn, $idUser) {
            // 1. Aseguramos que el ID sea un entero (para prevenir inyección SQL)
            $idUser = (int)$idUser;

            // 2. Definimos el SQL
            // Seleccionamos solo los campos que necesita el frontend
            $sql = "SELECT I_id, I_ruta, I_currentProfile 
                    FROM images 
                    WHERE I_idUser = $idUser AND I_isProfile = 1 
                    ORDER BY I_publicationDate DESC";

            // 3. Ejecutamos la consulta
            $resultado = mysqli_query($conn, $sql);
            
            $imagenes = []; // Array para almacenar los resultados

            // 4. Verificamos si la consulta fue exitosa y trajo resultados
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                
                // 5. Recorremos los resultados y los añadimos al array
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    $imagenes[] = $fila;
                }
            }
            
            // 6. Devolvemos el array (estará vacío si no se encontraron imágenes)
            return $imagenes;
        }


        // ====================================================
        // 🔹 (NUEVA) Obtener imágenes de álbum con filtros de visibilidad
        // ====================================================
        /**
         * 🔹 (ACTUALIZADA) Obtener imágenes de álbum con filtros de visibilidad
         * (Maneja álbumes normales y de sistema)
         */
        public static function obtenerImagenesPorAlbum($conn, $albumId, $isMyProfile, $idUsuarioLogueado, $profileUserId) {

            $idAlbum = (int)$albumId;

            // 1. Verificar el tipo de álbum
            $sqlAlbumCheck = "SELECT A_isSystemAlbum FROM albums WHERE A_id = $idAlbum LIMIT 1";
            $albumResult = mysqli_query($conn, $sqlAlbumCheck);
            
            if (!$albumResult || mysqli_num_rows($albumResult) == 0) {
                return []; // El álbum no existe
            }
            $albumData = mysqli_fetch_assoc($albumResult);
            $isSystemAlbum = (int)$albumData['A_isSystemAlbum'];

            // 2. Construir la consulta SQL base
            $sql = "";
            $alias = ""; // Prefijo para las columnas (ej. 'i.')

            if ($isSystemAlbum === 1) {
                // --- ÁLBUM DE SISTEMA ---
                $alias = "i."; // Necesitamos alias por el JOIN
                $sql = "SELECT i.I_id, i.I_ruta, i.I_title, i.I_visibility, i.I_revisionStatus 
                        FROM images i
                        INNER JOIN album_images_link l ON i.I_id = l.L_idImage
                        WHERE l.L_idAlbum = $albumId";
            } else {
                // --- ÁLBUM NORMAL ---
                $sql = "SELECT I_id, I_ruta, I_title, I_visibility, I_revisionStatus 
                        FROM images 
                        WHERE I_idAlbum = $albumId";
            }

            // 3. Aplicar filtros de visibilidad (tu lógica existente)
            if (!$isMyProfile) {
                
                // 3.1. Filtro de Revisión (Asumo 0 = Aprobado)
                $sql .= " AND {$alias}I_revisionStatus = 0";

                // 3.2. Filtro de Visibilidad (Follow)
                $sigueAlUsuario = false;
                if ($idUsuarioLogueado !== null && $idUsuarioLogueado > 0) {
                    // Asumo que la clase Follow está disponible
                    $estadoSeguimiento = Follow::estado($conn, $idUsuarioLogueado, $profileUserId);
                    if ($estadoSeguimiento === 1) { 
                        $sigueAlUsuario = true;
                    }
                }

                if (!$sigueAlUsuario) {
                    // Solo ve Públicas (0)
                    $sql .= " AND {$alias}I_visibility = 0";
                }
            }
            
            // 4. Ordenar y Ejecutar
            $sql .= " ORDER BY {$alias}I_publicationDate DESC";

            $resultado = mysqli_query($conn, $sql);
            $imagenes = [];

            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    $fila["isSystemAlbum"] = $isSystemAlbum;
                    $imagenes[] = $fila;
                }
            }
            
            return $imagenes;
        }

        // 🔹 Obtener IDs de imágenes que un usuario ha 'likeado' de otro usuario
        public static function getLikedImageIdsByOwner($conn, $likerId, $ownerId) {
            $likerId = (int)$likerId;
            $ownerId = (int)$ownerId;

            // 1. Busca en 'likes' (L_idUser = $likerId)
            // 2. Hace JOIN con 'images' (I_idUser = $ownerId)
            $sql = "SELECT i.I_id FROM images i
                    INNER JOIN likes l ON i.I_id = l.L_idImage
                    WHERE l.L_idUser = $likerId AND i.I_idUser = $ownerId";
            
            $resultado = mysqli_query($conn, $sql);
            $imageIds = [];
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    $imageIds[] = (int)$fila['I_id'];
                }
            }
            return $imageIds;
        }
    }
?>