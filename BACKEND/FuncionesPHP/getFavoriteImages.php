<?php
// getFavoriteImages.php - Obtiene las últimas 20 imágenes con like del usuario

session_start();
date_default_timezone_set('America/Argentina/San_Luis');

require_once "../Clases/Image.php";
require_once "../Clases/User.php";
require_once "../conexion.php";

header("Content-Type: application/json");

$conn = conexion();

// =====================================================
// VERIFICACIÓN DE SESIÓN
// =====================================================

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "errorSession",
        "message" => "Debes iniciar sesión para ver tus favoritos."
    ]);
    desconexion($conn);
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];

// =====================================================
// CONSULTA DE IMÁGENES FAVORITAS
// =====================================================

try {
    // Consulta SQL: obtiene las últimas 20 imágenes a las que el usuario le dio "like"
    $sql = "SELECT 
                i.I_id, 
                i.I_title, 
                i.I_ruta, 
                i.I_publicationDate,
                i.I_visibility,
                u.U_id AS ownerId,
                u.U_nameUser AS ownerUsername
            FROM likes l
            INNER JOIN images i ON l.L_idImage = i.I_id
            INNER JOIN users u ON i.I_idUser = u.U_id
            WHERE l.L_idUser = $currentUserId
                AND i.I_revisionStatus = 0
                AND i.I_isProfile = 0
                AND u.U_status = 1
            ORDER BY l.L_id DESC
            LIMIT 20";
    
    $resultado = mysqli_query($conn, $sql);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . mysqli_error($conn));
    }
    
    $images = [];
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Obtener la imagen de perfil del dueño usando tu método
        $profileImage = Imagen::getProfileImagePath($conn, $fila['ownerId']);
        
        $images[] = [
            'I_id' => $fila['I_id'],
            'I_title' => $fila['I_title'],
            'I_ruta' => $fila['I_ruta'],
            'I_publicationDate' => $fila['I_publicationDate'],
            'I_visibility' => $fila['I_visibility'],
            'ownerUsername' => $fila['ownerUsername'],
            'ownerProfileImage' => $profileImage,
            'isSystemAlbum' => 1 // Marcamos como "sistema" para que no tenga menú de edición
        ];
    }
    
    // =====================================================
    // RESPUESTA JSON
    // =====================================================
    
    // Devolvemos el array directamente (como en tus otros endpoints)
    echo json_encode($images);
    
} catch (Exception $e) {
    // En caso de error, devolvemos array vacío (consistente con tu patrón)
    echo json_encode([]);
} finally {
    desconexion($conn);
}
?>