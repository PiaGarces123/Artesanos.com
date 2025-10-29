<?php
    require_once "../Clases/Album.php"; 
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php"; 
    require_once "../conexion.php";

    session_start();

    // Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");


    $conn = conexion();

    // ----------------------------------------------------
    // LÓGICA CLAVE: DETERMINAR EL ID DEL PERFIL A CONSULTAR
    // ----------------------------------------------------
    
    $idUser = $_POST['user_id'] ?? null;

    if (empty($idUser) && isset($_SESSION['user_id'])) {
        // Si no se pasó un ID por POST (ej: no se está visitando un perfil),
        // se usa el ID del usuario logueado.
        $idUser = $_SESSION['user_id'];
    }
    
    // Si todavía no tenemos ID (ni por POST ni por SESIÓN), salimos.
    if (empty($idUser)) {
        echo json_encode([]);
        exit;
    }
    
    // Verificación de existencia del usuario (requerida por User::getById)
    $user = User::getById($conn, $idUser);

    if (!$user) {
        echo json_encode([]); // Devuelve array vacío si el usuario no existe.
        exit;
    }

    // ----------------------------------------------------
    // LÓGICA PRINCIPAL: OBTENER Y FORMATEAR ÁLBUMES
    // ----------------------------------------------------
    
    // Asumimos que Album::getByUser y Album::getCoverImagePath existen y son correctos.
    $resultadoAlbums = Album::getByUser($conn, $idUser);

    $albums = [];
    foreach($resultadoAlbums as $album){
        
        // Asumo que la función de portada está en la clase Album (según tu indicación).
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