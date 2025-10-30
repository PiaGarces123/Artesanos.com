<?php
    require_once "../Clases/Follow.php";
    require_once "../Clases/User.php";
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php"; // (La necesitamos para las nuevas funciones)
    require_once "../conexion.php"; 

    session_start();
    header('Content-Type: application/json');

    // 1. Validar Sesión (Usuario B: Yo, el que acepta)
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "errorSession", "message" => "Tu sesión ha expirado."]);
        exit;
    }

    $conn = conexion();

    // 2. Obtener IDs
    $idUsuarioB = $_SESSION['user_id']; // (Usuario B: Yo, el aceptante)
    $idUsuarioA = $_POST['targetUserId'] ?? null; // (Usuario A: El solicitante)

    if (empty($idUsuarioA) || !is_numeric($idUsuarioA)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
        desconexion($conn);
        exit;
    }

    // 3. Obtener mis datos (Usuario B) para el título del álbum
    $userB = User::getById($conn, $idUsuarioB);
    if (!$userB) {
        echo json_encode(["status" => "error", "message" => "Error al obtener tu información de usuario."]);
        desconexion($conn);
        exit;
    }

    // =====================================================
    // 4. LÓGICA DE ACEPTACIÓN
    // =====================================================

    // 4.1. Aceptar la solicitud en la tabla 'follow'
    if (Follow::aceptar($conn, $idUsuarioA, $idUsuarioB)) {

        // 4.2. Crear el álbum de sistema para el Usuario A
        $albumTitle = "Likes de " . $userB->name . " " . $userB->lastName;
        
        // El álbum pertenece a A, pero rastrea a B
        $newAlbumId = Album::crear(
            $conn, 
            $albumTitle, 
            $idUsuarioA, // Dueño del álbum
            1,            // Es un álbum de sistema
            $idUsuarioB   // Usuario rastreado
        );

        if (!$newAlbumId) {
            echo json_encode(["status" => "error", "message" => "Fallo al crear el álbum de sistema."]);
            desconexion($conn);
            exit;
        }

        // 4.3. Crear la carpeta física (como en publicarContenido.php)
        $base_files_dir = __DIR__ . '/../../FILES/';
        $album_dir = $base_files_dir . $idUsuarioA . '/' . $newAlbumId;
        
        if (!is_dir($album_dir) && !@mkdir($album_dir, 0777, true)) {
            // Error creando la carpeta. Borramos el álbum para no dejar basura.
            Album::eliminar($conn, $newAlbumId);
            echo json_encode(["status" => "error", "message" => "Error de permisos al crear la carpeta del álbum."]);
            desconexion($conn);
            exit;
        }

        // 4.4. Poblar el álbum con los 'likes' existentes
        // (Añade las funciones de abajo a tus clases)
        
        // Obtener los IDs de las imágenes de B que A ya le ha dado like
        $likedImageIds = Imagen::getLikedImageIdsByOwner($conn, $idUsuarioA, $idUsuarioB);
        
        foreach ($likedImageIds as $imageId) {
            // Vincular cada imagen al nuevo álbum de sistema
            Album::linkImageToAlbum($conn, $newAlbumId, $imageId);
        }

        echo json_encode([
            "status" => "success",
            "message" => "Solicitud aceptada. Álbum de 'Likes' creado para el usuario."
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo aceptar la solicitud."
        ]);
    }

    desconexion($conn);
?>