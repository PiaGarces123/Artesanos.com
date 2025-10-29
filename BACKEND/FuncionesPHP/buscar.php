<?php
session_start();
date_default_timezone_set('America/Argentina/San_Luis');

require_once "../Clases/User.php";
require_once "../Clases/Image.php";
require_once "../Clases/Follow.php";
require_once "../conexion.php";

header("Content-Type: application/json");

$conn = conexion();

// Obtener parámetros
$query = isset($_POST['query']) ? trim($_POST['query']) : '';
$searchType = isset($_POST['searchType']) ? $_POST['searchType'] : 'perfil';
$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Validar que haya una query
if (empty($query)) {
    echo json_encode([
        "status" => "error",
        "message" => "Debes ingresar un término de búsqueda.",
        "results" => []
    ]);
    desconexion($conn);
    exit;
}

$query = mysqli_real_escape_string($conn, $query);
$results = [];

// =====================================================
// BÚSQUEDA DE PERFILES
// =====================================================
if ($searchType === 'perfil') {
    
    $sql = "SELECT U_id, U_nameUser, U_name, U_lastName, U_biography 
            FROM users 
            WHERE (U_nameUser LIKE '%$query%' 
                OR U_name LIKE '%$query%' 
                OR U_lastName LIKE '%$query%')
            AND U_status = 1
            ORDER BY U_nameUser ASC
            LIMIT 20";
    
    $resultado = mysqli_query($conn, $sql);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $profileImage = Imagen::getProfileImagePath($conn, $fila['U_id']);
            
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
                    AND i.I_visibility = 0
                    AND i.I_revisionStatus = 0
                    AND i.I_isProfile = 0
                    AND u.U_status = 1
                ORDER BY i.I_publicationDate DESC
                LIMIT 30";
    } else {
        $currentUserId = (int)$currentUserId;
        $sql = "SELECT i.I_id, i.I_title, i.I_ruta, i.I_publicationDate, i.I_visibility,
                       u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM images i
                INNER JOIN users u ON i.I_idUser = u.U_id
                WHERE i.I_title LIKE '%$query%'
                    AND i.I_revisionStatus = 0
                    AND i.I_isProfile = 0
                    AND u.U_status = 1
                    AND (
                        i.I_visibility = 0
                        OR (
                            i.I_visibility = 1 
                            AND EXISTS (
                                SELECT 1 FROM follow f
                                WHERE f.F_idFollower = $currentUserId
                                  AND f.F_idFollowed = u.U_id
                                  AND f.F_status = 1
                            )
                        )
                        OR i.I_idUser = $currentUserId
                    )
                ORDER BY i.I_publicationDate DESC
                LIMIT 30";
    }
    
    $resultado = mysqli_query($conn, $sql);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $profileImage = Imagen::getProfileImagePath($conn, $fila['U_id']);
            
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
// RESPUESTA FINAL
// =====================================================

echo json_encode([
    "status" => "success",
    "searchType" => $searchType,
    "query" => $query,
    "results" => $results,
    "count" => count($results)
]);

desconexion($conn);
