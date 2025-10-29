<?php
    require_once "../Clases/Follow.php";
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
    $idSeguidor = $_SESSION['user_id'];
    $idSeguido = $_POST['targetUserId'] ?? null;

    if (empty($idSeguido) || !is_numeric($idSeguido)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no válido."]);
        desconexion($conn);
        exit;
    }

    // 2. Ejecutar la acción
    if (Follow::solicitar($conn, $idSeguidor, $idSeguido)) {
        
        // (AQUÍ DEBERÍAS VERIFICAR SI EL SEGUIMIENTO ES AUTOMÁTICO O PENDIENTE)
        // Por ahora, asumimos que siempre queda "Pendiente" (estado 0)
        
        echo json_encode([
            "status" => "success",
            "newState" => "pending" // El JS usará esto para cambiar el botón a "Pendiente"
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo enviar la solicitud."
        ]);
    }

    desconexion($conn);
?>