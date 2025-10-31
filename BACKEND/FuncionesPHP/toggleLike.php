<?php
require_once "../Clases/Like.php";
require_once "../Clases/Follow.php";
require_once "../Clases/Album.php";
require_once "../Clases/Image.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida."]);
    exit;
}

$conn = conexion();
$myId = (int)$_SESSION['user_id'];
$imageId = (int)($_POST['imageId'] ?? 0);

if (empty($imageId)) { echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
    exit; }

// --- Lógica Principal de Like ---
$hasLiked = Like::existe($conn, $imageId, $myId);
$newLikeStatus = !$hasLiked;

if ($hasLiked) {
    Like::quitar($conn, $imageId, $myId);
} else {
    Like::agregar($conn, $imageId, $myId);
}

// --- Lógica del Álbum de Sistema (Tu lógica de negocio) ---
$imageData = Imagen::getById($conn, $imageId);
$ownerId = (int)$imageData['I_idUser'];

if ($myId != $ownerId) {
    // Verificamos si seguimos al dueño
    $followStatus = Follow::estado($conn, $myId, $ownerId);
    
    if ($followStatus === 1) {
        // Sí lo seguimos, buscar el álbum de sistema
        $sqlFind = "SELECT A_id FROM albums 
                    WHERE A_idUser = $myId 
                    AND A_idFollowedUser = $ownerId 
                    AND A_isSystemAlbum = 1 LIMIT 1";
        
        $result = mysqli_query($conn, $sqlFind);
        
        if ($result && $fila = mysqli_fetch_assoc($result)) {
            $systemAlbumId = (int)$fila['A_id'];
            
            if ($newLikeStatus) { // Acabamos de dar Like
                Album::linkImageToAlbum($conn, $systemAlbumId, $imageId);
            } else { // Acabamos de quitar Like
                Album::unlinkImageFromAlbum($conn, $systemAlbumId, $imageId);
            }
        }
    }
}
// --- Fin Lógica de Álbum ---

// Devolver el nuevo estado
$newLikeCount = Like::contarPorImagen($conn, $imageId);
echo json_encode([
    "status" => "success",
    "newLikeCount" => $newLikeCount,
    "newLikeStatus" => $newLikeStatus // true (le di like), false (quité like)
]);
desconexion($conn);
?>