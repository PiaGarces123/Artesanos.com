<?php
    session_start();
    date_default_timezone_set('America/Argentina/San_Luis');

    require_once "../Clases/User.php";
    require_once "../Clases/Image.php";
    require_once "../conexion.php";

    header("Content-Type: application/json");

    $conn = conexion();

    $isLoggedIn = isset($_SESSION['user_id']);
    $currentUserId = $isLoggedIn ? $_SESSION['user_id'] : null;

    $results = [];

    // =====================================================
    // OBTENER FEED DE IMÁGENES
    // =====================================================

    // Si el usuario NO está logueado, solo mostrar imágenes públicas
    if (!$isLoggedIn) {
        $sql = "SELECT i.I_id, i.I_title, i.I_ruta, i.I_publicationDate, i.I_visibility,
                    u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM images i
                INNER JOIN users u ON i.I_idUser = u.U_id
                WHERE i.I_visibility = 0
                    AND i.I_revisionStatus = 0
                    AND i.I_isProfile = 0
                    AND u.U_status = 1
                ORDER BY i.I_publicationDate DESC
                LIMIT 50";
    } 
    // Si el usuario SÍ está logueado
    else {
        $currentUserId = (int)$currentUserId;
        
        $sql = "SELECT i.I_id, i.I_title, i.I_ruta, i.I_publicationDate, i.I_visibility,
                    u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM images i
                INNER JOIN users u ON i.I_idUser = u.U_id
                WHERE i.I_revisionStatus = 0
                    AND i.I_isProfile = 0
                    AND u.U_status = 1
                    AND i.I_idUser <> $currentUserId
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
                    )
                ORDER BY i.I_publicationDate DESC
                LIMIT 50";
    }

    $resultado = mysqli_query($conn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            // 🔥 ESTO ES LO QUE FALTABA: Obtener la imagen de perfil del usuario
            $profileImage = Imagen::getProfileImagePath($conn, $fila['U_id']);
            
            $results[] = [
                'id' => $fila['I_id'],
                'title' => $fila['I_title'],
                'imageUrl' => $fila['I_ruta'],
                'publicationDate' => $fila['I_publicationDate'],
                'visibility' => (int)$fila['I_visibility'],
                'userId' => $fila['U_id'],
                'username' => $fila['U_nameUser'],
                'userFullName' => $fila['U_name'] . ' ' . $fila['U_lastName'],
                'profileImage' => $profileImage // ✅ AGREGADO
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "results" => $results,
        "count" => count($results)
    ]);

    desconexion($conn);
?>