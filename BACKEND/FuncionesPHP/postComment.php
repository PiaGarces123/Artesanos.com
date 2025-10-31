<?php
require_once "../Clases/Comment.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "errorSession", "message" => "Sesión no válida."]);
    exit; }

$conn = conexion();
$myId = (int)$_SESSION['user_id'];
$imageId = (int)($_POST['imageId'] ?? 0);
$commentText = trim($_POST['commentText'] ?? '');

if (empty($imageId)) { echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
    exit; }

// Validación de comentario vacío
if (empty($commentText) || strlen($commentText) > 255) {
    echo json_encode(["status" => "error", "message" => "El comentario no puede estar vacío y debe tener entre 1 y 255 caracteres."]);
    exit;
}

// Insertar en la BD
if (Comment::agregar($conn, $imageId, $myId, $commentText)) {
    
    // Devolvemos la nueva lista de comentarios
    $comments = Comment::getByImagenConAvatar($conn, $imageId);
    echo json_encode([
        "status" => "success",
        "comments" => $comments
    ]);

} else { echo json_encode(["status" => "error", "message" => "Error al agregar el comentario."]);
    exit; }
desconexion($conn);
?>