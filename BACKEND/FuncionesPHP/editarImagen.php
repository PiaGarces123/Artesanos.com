<?php
require_once "../Clases/Image.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
    exit;
}
$conn = conexion();
$myId = (int)$_SESSION['user_id'];

// 1. Obtener Datos
$imageId = (int)($_POST['imageId'] ?? 0);
$newTitle = trim($_POST['editImageTitle'] ?? "");
// El checkbox envía 'on' si está marcado, o no envía nada si no lo está.
$newVisibility = isset($_POST['editImageVisibility']) ? 1 : 0; // 1 = Privado, 0 = Público

// 2. Validar Título (VARCHAR(30))
if (empty($newTitle) || strlen($newTitle) > 30) {
    echo json_encode(["status" => "error", "message" => "Título inválido (1-30 caracteres)."]);
    exit;
}
if (empty($imageId)) { echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]); exit; }

try {
    // 3. Verificar Propiedad
    $imageData = Imagen::getById($conn, $imageId);
    if (!$imageData || $imageData['I_idUser'] != $myId) {
        throw new Exception("No tienes permiso para editar esta imagen.");
    }
    
    // 4. Ejecutar la edición (Necesitas una nueva función en la clase Imagen)
    if (Imagen::editar($conn, $imageId, $newTitle, $newVisibility)) {
        echo json_encode([
            "status" => "success",
            "message" => "Imagen actualizada.",
            "newTitle" => $newTitle,
            "newVisibility" => $newVisibility
        ]);
    } else { throw new Exception("Error al guardar en la base de datos."); }

} catch (Exception $e) { echo json_encode(["status" => "error", "message" => $e->getMessage()]); }
desconexion($conn);
?>