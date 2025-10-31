<?php
require_once "../Clases/Comment.php";
require_once "../Clases/Image.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "errorSession", "message" => "Sesión no válida."]);
    exit; }

$conn = conexion();
$myId = (int)$_SESSION['user_id'];
$commentId = (int)($_POST['commentId'] ?? 0);
$imageId = (int)($_POST['imageId'] ?? 0); // Para poder recargar la lista

if (empty($commentId) || empty($imageId)) { echo json_encode(["status" => "error", "message" => "ID de comentario o imagen no válido."]);
    exit; }

// --- Validación de Propiedad (CRUCIAL) ---
$commentOwnerId = Comment::getCommentOwner($conn, $commentId);
$imageData = Imagen::getById($conn, $imageId);
$imageOwnerId = (int)$imageData['I_idUser'];

// Puedes borrar si eres el dueño del comentario O el dueño de la foto
if ($commentOwnerId == $myId || $imageOwnerId == $myId) {
    
    if (Comment::eliminar($conn, $commentId)) {
        // Devolvemos la nueva lista de comentarios
        $comments = Comment::getByImagenConAvatar($conn, $imageId);
        echo json_encode([
            "status" => "success",
            "comments" => $comments
        ]);
    } else { echo json_encode(["status" => "error", "message" => "Error al eliminar el comentario."]);
    }
    
} else {
    echo json_encode(["status" => "error", "message" => "No tienes permiso para borrar este comentario."]);
}
desconexion($conn);
?>