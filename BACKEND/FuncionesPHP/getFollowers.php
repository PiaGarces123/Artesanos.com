    <?php
    require_once "../Clases/Follow.php";
    require_once "../Clases/Image.php";
    require_once "../conexion.php"; 

    session_start();
    header('Content-Type: application/json');

    // 1. Validar que tengamos el ID del usuario objetivo
    $targetUserId = $_POST['user_id'] ?? null;

    if (empty($targetUserId) || !is_numeric($targetUserId)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
        exit;
    }

    $conn = conexion();
    $defaultPath = './Frontend/assets/images/appImages/default.jpg';

    try {
        // 2. Obtener la lista de seguidores (quienes siguen al usuario objetivo)
        $seguidores = Follow::seguidores($conn, $targetUserId);

        // 3. Enriquecer con foto de perfil
        $seguidoresConFoto = [];
        foreach ($seguidores as $seguidor) {
            $profilePic = Imagen::getProfileImagePath($conn, $seguidor['U_id']);
            
            $seguidoresConFoto[] = [
                'U_id' => $seguidor['U_id'],
                'U_nameUser' => $seguidor['U_nameUser'],
                'U_name' => $seguidor['U_name'],
                'U_lastName' => $seguidor['U_lastName'],
                'U_profilePic' => $profilePic ?: $defaultPath
            ];
        }

        echo json_encode([
            "status" => "success",
            "seguidores" => $seguidoresConFoto
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Error al obtener seguidores."
        ]);
    }

    desconexion($conn);
    ?>