<?php
    require_once "../Clases/Album.php"; 
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php"; 
    require_once "../conexion.php";

    session_start();

    header("Content-Type: application/json");

    $conn = conexion();

    // ----------------------------------------------------
    // LÓGICA CLAVE: DETERMINAR ID Y FILTRO
    // ----------------------------------------------------
    
    // 1. Determinar el ID del usuario (desde POST o SESIÓN)
    $idUser = $_POST['user_id'] ?? null;

    if (empty($idUser) && isset($_SESSION['user_id'])) {
        $idUser = $_SESSION['user_id'];
    }
    
    if (empty($idUser)) {
        echo json_encode([]);
        exit;
    }
    
    // 2. --- ¡CAMBIO AQUÍ! OBTENER EL TIPO DE FILTRO ---
    // El valor por defecto es 'all' si no se envía nada
    $filterType = $_POST['filterType'] ?? 'all';

    // 3. Verificación de existencia del usuario
    $user = User::getById($conn, $idUser);

    if (!$user) {
        echo json_encode([]); 
        exit;
    }

    // ----------------------------------------------------
    // LÓGICA PRINCIPAL: OBTENER Y FORMATEAR ÁLBUMES
    // ----------------------------------------------------
    
    // 4. --- ¡CAMBIO AQUÍ! PASAMOS EL FILTRO A LA FUNCIÓN ---
    $resultadoAlbums = Album::getByUser($conn, $idUser, $filterType);

    $albums = [];
    foreach($resultadoAlbums as $album){
        
        // (El resto de tu lógica para buscar portada y conteo es correcta)
        $rutaPortada = Album::getCoverImagePath($conn, $album['A_id']); 
        $totalImagenes = Album::contarImagenes($conn, $album['A_id']);

        $albums[] = [
            'A_id' => $album['A_id'],
            'A_title' => $album['A_title'],
            'A_creationDate' => $album['A_creationDate'],
            'A_idUser' => $album['A_idUser'],
            'A_isSystemAlbum' => $album['A_isSystemAlbum'],
            'A_idFollowedUser' => $album['A_idFollowedUser'],
            'A_cover' => $rutaPortada, // Ruta de la portada
            'A_count' => $totalImagenes // Total de imágenes
        ];
    }
    
    // Devolver el JSON final
    echo json_encode($albums);
    
    desconexion($conn);
?>