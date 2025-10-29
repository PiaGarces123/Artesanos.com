<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Requerir Clases y Conexión
    require_once "../Clases/Album.php";
    require_once "../Clases/image.php"; // (Tu archivo "image.php" que define la clase "Imagen")
    require_once "../Clases/User.php";
    require_once "../Clases/Follow.php"; // Necesario para la lógica de visibilidad
    require_once "../conexion.php"; 

    session_start();
    header("Content-Type: application/json");

    $conn = conexion();

    // =====================================================
    // 1. OBTENER DATOS DE ENTRADA
    // =====================================================

    $idUsuarioLogueado = $_SESSION['user_id'] ?? null;
    $albumId = $_POST['albumId'] ?? null;
    $isMyProfile = ($_POST['isMyProfile'] ?? 'false') === 'true';
    $profileUserId = $_POST['profileUserId'] ?? null; 

    // =====================================================
    // 2. VALIDACIONES (Siguiendo tu lógica)
    // =====================================================

    // Si falta un ID o no es numérico, devolvemos array vacío
    if (empty($albumId) || !is_numeric($albumId)) {
        echo json_encode([]); // <-- CORREGIDO
        desconexion($conn);
        exit;
    }

    if (empty($profileUserId) || !is_numeric($profileUserId)) {
        echo json_encode([]); // <-- CORREGIDO
        desconexion($conn);
        exit;
    }

    // =====================================================
    // 3. LÓGICA PRINCIPAL
    // =====================================================

    try {
        // Llamamos a la función estática (la que te pasé antes para la clase Imagen)
        $imagenes = Imagen::obtenerImagenesPorAlbum(
            $conn,
            (int)$albumId,
            $isMyProfile,
            (int)$idUsuarioLogueado,  // ID del que mira
            (int)$profileUserId       // ID del dueño
        );

        // Devolver el JSON final (será un array, vacío o con datos)
        echo json_encode($imagenes);

    } catch (Exception $e) {
        // Si algo falla en la lógica de la clase, también devolvemos array vacío
        echo json_encode([]); // <-- CORREGIDO
    }

    desconexion($conn);
?>