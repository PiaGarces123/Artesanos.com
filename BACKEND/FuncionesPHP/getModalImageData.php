<?php
require_once "../Clases/Image.php";
require_once "../Clases/User.php"; 
require_once "../Clases/Album.php";
require_once "../Clases/Like.php";
require_once "../Clases/Comment.php";
require_once "../Clases/Follow.php";
require_once "../conexion.php"; 

session_start();
header("Content-Type: application/json");

// 1. Validar Sesión y Datos de Entrada
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida."]);
    exit;
}

$conn = conexion();
$idUsuarioLogueado = $_SESSION['user_id'];
$imageId = $_POST['imageId'] ?? null;

if (empty($imageId) || !is_numeric($imageId)) {
    echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
    desconexion($conn);
    exit;
}

try {
    // 2. Obtener Datos de la Imagen (título, ruta, dueño)
    $imageData = Imagen::getById($conn, $imageId);
    if (!$imageData) {
        throw new Exception("Imagen no encontrada.");
    }

    $idDueño = $imageData['I_idUser'];

    // 3. Obtener Datos del Dueño (username, avatar)
    $ownerData = User::getById($conn, $idDueño);
    $ownerAvatar = Imagen::getProfileImagePath($conn, $idDueño); // Tu función ya devuelve la default si no hay

    // 4. Obtener Datos de Likes
    $likeCount = Like::contarPorImagen($conn, $imageId);
    $hasLiked = Like::existe($conn, $imageId, $idUsuarioLogueado);

    // 5. Obtener Comentarios (Usando la nueva función que crearás)
    $comments = Comment::getByImagenConAvatar($conn, $imageId);

    // 6. Verificar propiedad
    $isMyImage = ($idUsuarioLogueado == $idDueño);

    // 7. Obtener Estado de Seguimiento
    $followStatus = null;
    if ($idDueño != $idUsuarioLogueado) {
        // (Asegúrate de incluir Follow.php)
        require_once "../Clases/Follow.php";
        $followStatus = Follow::estado($conn, $idUsuarioLogueado, $idDueño);
    }

    // 8. Construir y devolver el JSON
    echo json_encode([
        "status" => "success",
        
        // Info de la imagen
        "imageRuta" => $imageData['I_ruta'],
        "imageTitle" => $imageData['I_title'],

        // Fecha de publicación
        "imagePublicationDate" => $imageData['I_publicationDate'],
        
        // Info del dueño
        "ownerName" => $ownerData->username,
        "ownerAvatar" => $ownerAvatar,
        "ownerId" => $idDueño,
        
        // Info de Likes
        "likeCount" => $likeCount,
        "hasLiked" => $hasLiked, // boolean
        "followStatus" => $followStatus, // <-- AÑADIDO: '1', '0' o null
        
        // Info de Comentarios
        "comments" => $comments, // array
        
        // Propiedad
        "isMyImage" => $isMyImage // boolean
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

desconexion($conn);
?>