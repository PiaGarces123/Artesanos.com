<?php 
    // Mostrar errores en pantalla (lo usamos durante el desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Iniciar o reanudar la sesión del usuario
    session_start();
    
    // Configuración de zona horaria
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    // No necesitamos Album.php, pero sí Imagen.php y User.php
    require_once "../Clases/image.php"; 
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    // Conexión
    $conn = conexion();

    // Indicar que la respuesta es JSON
    header("Content-Type: application/json");
    
    // =====================================================
    // 1. CHEQUEO DE SESIÓN y CONEXIÓN
    // =====================================================

    // Verifica si el usuario tiene una sesión activa
    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Tu sesión ha expirado."
        ]);
        exit;// Detiene la ejecución del script
    }

    // Obtiene el usuario actual desde la base de datos usando su ID de sesión
    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;// Detiene la ejecución del script
    }
    
    $userId = $user->id; // Guarda el ID del usuario logueado
    

    // =====================================================
    // 2. VALIDAR ID DE IMAGEN
    // =====================================================

    // Obtenemos el ID de la imagen desde el POST
    $imageId = $_POST['imageId'] ?? null;
    
    // Verifica que el ID exista y sea un número válido
    if (empty($imageId) || !is_numeric($imageId)) {
        echo json_encode(["status" => "error", "message" => "ID de imagen no válido."]);
        desconexion($conn);
        exit;
    }
    
    // Convierte el ID a número entero por seguridad
    $imageId = (int)$imageId;
    
    // =====================================================
    // 2. VERIFICAR PROPIEDAD DE LA IMAGEN
    // =====================================================
    
    // Buscamos los datos de la imagen para verificar propiedad y obtener el título
    $imageData = Imagen::getById($conn, $imageId);
    
    // Si no existe o no pertenece al usuario logueado → acceso denegado
    if (!$imageData || $imageData['I_idUser'] != $userId) {
        echo json_encode(["status" => "error", "message" => "No tienes permiso para eliminar esta imagen."]);
        desconexion($conn);
        exit;
    }

    // =====================================================
    // 3. ELIMINAR IMAGEN (BD y FÍSICO)
    // =====================================================
    
    // 3.1. Eliminar registro de BD y archivo físico
    // Tu método Imagen::eliminar() ya se encarga de:
    //  - Borrar el archivo físico (unlink)
    //  - Desmarcar si es perfil (I_currentProfile = 0)
    //  - Borrar el registro de la BD (lo que activa el CASCADE)
    
    if (Imagen::eliminar($conn, $imageId)) {

        // Si la eliminación fue exitosa, responde con mensaje de éxito
        echo json_encode([
            "status" => "success",
            "message" => "Imagen '{$imageData['I_title']}' eliminada correctamente."
        ]);
        
    } else {
        // Error de base de datos durante la eliminación
        echo json_encode([
            "status" => "error",
            "message" => "Fallo al eliminar la imagen en la base de datos."
        ]);
    }

    // Cierra la conexión con la base de datos
    desconexion($conn);
?>