    <?php
        // Inicia o reanuda la sesión actual
        session_start();

        // Define la zona horaria del servidor
        date_default_timezone_set('America/Argentina/San_Luis');

        // Importa las clases necesarias y el archivo de conexión a la base de datos
        require_once "../Clases/User.php";
        require_once "../Clases/Image.php";
        require_once "../Clases/Follow.php";
        require_once "../conexion.php";

        // Indica que la respuesta será en formato JSON
        header("Content-Type: application/json");

        // Crea la conexión con la base de datos
        $conn = conexion();

        // =====================================================
        // OBTENCIÓN DE PARÁMETROS Y VALIDACIONES
        // =====================================================

        // Se obtiene el término de búsqueda enviado por POST (si existe)
        $query = isset($_POST['query']) ? trim($_POST['query']) : '';

        // Se obtiene el tipo de búsqueda: puede ser "perfil" o "imagen"
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : 'perfil';

        // Se comprueba si el usuario tiene una sesión iniciada
        $isLoggedIn = isset($_SESSION['user_id']);
        $currentUserId = $isLoggedIn ? $_SESSION['user_id'] : null;

        // Si no se ingresó un texto de búsqueda, se devuelve un error en formato JSON
        if (empty($query)) {
            echo json_encode([
                "status" => "error",
                "message" => "Debes ingresar un término de búsqueda.",
                "results" => []
            ]);
            desconexion($conn);
            exit;
        }

        // Escapa caracteres peligrosos para evitar inyección SQL
        $query = mysqli_real_escape_string($conn, $query);

        // Se inicializa un arreglo vacío para almacenar los resultados
        $results = [];

        // =====================================================
        // BÚSQUEDA DE PERFILES (usuarios)
        // =====================================================
        if ($searchType === 'perfil') {
            
            // Consulta SQL para buscar usuarios cuyo nombre, apellido o usuario coincida con la búsqueda
            $sql = "SELECT U_id, U_nameUser, U_name, U_lastName, U_biography 
                    FROM users 
                    WHERE (U_nameUser LIKE '%$query%' 
                        OR U_name LIKE '%$query%' 
                        OR U_lastName LIKE '%$query%')
                    ORDER BY U_nameUser ASC
                    LIMIT 20";
            
            // Ejecuta la consulta
            $resultado = mysqli_query($conn, $sql);
            
            // Si hay resultados, los recorre uno por uno
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {

                    // Obtiene la imagen de perfil del usuario
                    $profileImage = Imagen::getProfileImagePath($conn, $fila['U_id']);
                    
                    // Agrega los datos del usuario al arreglo de resultados
                    $results[] = [
                        'type' => 'perfil',
                        'id' => $fila['U_id'],
                        'username' => $fila['U_nameUser'],
                        'fullName' => $fila['U_name'] . ' ' . $fila['U_lastName'],
                        'biography' => $fila['U_biography'],
                        'profileImage' => $profileImage
                    ];
                }
            }
        }

        // =====================================================
        // BÚSQUEDA DE IMÁGENES
        // =====================================================
        else if ($searchType === 'imagen') {
            
            if (!$isLoggedIn) {
                // Usuario no logueado: solo imágenes públicas
                $sql = "SELECT i.I_id, i.I_title, i.I_ruta, i.I_publicationDate, i.I_visibility,
                            u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                        FROM images i
                        INNER JOIN users u ON i.I_idUser = u.U_id
                        WHERE i.I_title LIKE '%$query%'
                            AND i.I_revisionStatus = 0
                            AND i.I_visibility = 0
                            AND (i.I_isProfile = 0 OR i.I_idAlbum IS NOT NULL)
                        ORDER BY i.I_publicationDate DESC
                        LIMIT 30";
            } else {
                // Si el usuario SÍ está logueado, puede ver:
                // - Imágenes públicas
                // - Imágenes privadas de usuarios a los que sigue
                $currentUserId = (int)$currentUserId;

                $sql = "SELECT i.I_id, i.I_title, i.I_ruta, i.I_publicationDate, i.I_visibility,
                    u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM images i
                INNER JOIN users u ON i.I_idUser = u.U_id
                WHERE i.I_title LIKE '%$query%'
                    AND i.I_revisionStatus = 0
                    AND (i.I_isProfile = 0 OR i.I_idAlbum IS NOT NULL)
                    AND (
                        i.I_visibility = 0
                        OR i.I_idUser = $currentUserId
                        OR (
                            i.I_visibility = 1
                            AND EXISTS (
                                SELECT 1 FROM follow f
                                WHERE f.F_idFollower = $currentUserId
                                AND f.F_idFollowed = u.U_id
                                AND f.F_status = 1
                            )
                        )
                        
                    )
                ORDER BY i.I_publicationDate DESC
                LIMIT 30";
            }
            
            // Ejecuta la consulta SQL
            $resultado = mysqli_query($conn, $sql);
            
            // Si se obtienen resultados, se recorren y se formatea la información
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {

                    // Obtiene la imagen de perfil del autor de la publicación
                    $profileImage = Imagen::getProfileImagePath($conn, $fila['U_id']);
                    
                    // Agrega los datos de la imagen al arreglo de resultados
                    $results[] = [
                        'type' => 'imagen',
                        'id' => $fila['I_id'],
                        'title' => $fila['I_title'],
                        'imageUrl' => $fila['I_ruta'],
                        'publicationDate' => $fila['I_publicationDate'],
                        'visibility' => isset($fila['I_visibility']) ? (int)$fila['I_visibility'] : 0,
                        'userId' => $fila['U_id'],
                        'username' => $fila['U_nameUser'],
                        'userFullName' => $fila['U_name'] . ' ' . $fila['U_lastName'],
                        'profileImage' => $profileImage
                    ];
                }
            }
        }

        // =====================================================
        // RESPUESTA FINAL EN JSON
        // =====================================================

        // Devuelve la respuesta al frontend con:  
        echo json_encode([
            "status" => "success", // - el estado
            "searchType" => $searchType, // - el tipo de búsqueda
            "query" => $query, // - el término buscado
            "results" => $results, // - los resultados obtenidos
            "count" => count($results)// - y el número total de coincidencias
        ]);

        // Cierra la conexión a la base de datos
        desconexion($conn);
    ?>