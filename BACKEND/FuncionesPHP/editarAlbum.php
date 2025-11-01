<?php
require_once "../Clases/Album.php";
require_once "../Clases/User.php";
require_once "../conexion.php"; 

session_start();
header('Content-Type: application/json');

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
    exit;
}

$conn = conexion();
$idUsuarioLogueado = $_SESSION['user_id'];

// 2. Obtener datos del POST
$albumId = $_POST['albumId'] ?? null;
$newTitle = trim($_POST['editAlbumTitle'] ?? "");

// 3. Validar Título (Misma regex de publicarContenido.php)
$regex = '/^[a-zA-Z0-9._+()ÁÉÍÓÚáéíóúÑñ\s-]{1,30}$/';
if (empty($newTitle) || !preg_match($regex, $newTitle)) {
    echo json_encode(["status" => "error", "message" => "Título inválido. Debe tener entre 1 y 30 caracteres."]);
    desconexion($conn);
    exit;
}

if (empty($albumId) || !is_numeric($albumId)) {
    echo json_encode(["status" => "error", "message" => "ID de álbum no válido."]);
    desconexion($conn);
    exit;
}

try {
    // 4. VERIFICAR PROPIEDAD Y TIPO DE ÁLBUM
    $albumData = Album::getById($conn, $albumId);

    if (!$albumData) {
        throw new Exception("El álbum no existe.");
    }

    if ($albumData['A_idUser'] != $idUsuarioLogueado) {
        throw new Exception("No tienes permiso para editar este álbum.");
    }
    
    if ($albumData['A_isSystemAlbum'] == 1) {
        throw new Exception("No puedes editar un álbum del sistema.");
    }

    // 5. Ejecutar la edición (Tu clase Album::editar ya existe)
    if (Album::editar($conn, $albumId, $newTitle)) {
        echo json_encode([
            "status" => "success",
            "message" => "Álbum actualizado correctamente."
        ]);
    } else {
        throw new Exception("Error al actualizar la base de datos.");
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

desconexion($conn);
?>