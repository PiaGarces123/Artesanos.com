<?php 
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();
    
    // Configuración de zona horaria (opcional, pero buena práctica)
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    require_once "../Clases/User.php";
    require_once "../conexion.php"; 

    // Indicar que la respuesta es JSON
    header("Content-Type: application/json");

    // Conexión
    $conn = conexion();

    // =====================================================
    // 1. CHEQUEO DE SESIÓN y CONEXIÓN
    // =====================================================
    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Tu sesión ha expirado."
        ]);
        exit;
    }

    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;
    }

    // =====================================================
    
    $changePassCurrent = mysqli_real_escape_string($conn, $_POST['changePassCurrent']);
    $changePassNew = mysqli_real_escape_string($conn, $_POST['changePassNew']);
   

    
    // ===================================================
    // VALIDACIONES
    // ===================================================
    $errores = [
        "passCurrent" => "",
        "passNew" => ""
    ];

    // Validar Contraseña

    if(!($user = User::login($conn, $user->email, $changePassCurrent))){
        echo json_encode([
            "status" => "error",
            "message" => "Contraseña actual incorrecta."
        ]);
        exit;
    }


    $cont = 0;
    if (empty($changePassNew)) {
        $errores['passNew'] = "Campo Obligatorio.";
        $cont++;
    } elseif (strlen($changePassNew) < 6) {
        $errores['passNew'] = "Debe tener al menos 6 caracteres.";
        $cont++;
    } elseif (
        !preg_match("/[A-Z]/", $changePassNew) ||     // al menos una mayúscula
        !preg_match("/[a-z]/", $changePassNew) ||     // al menos una minúscula
        !preg_match("/[0-9]/", $changePassNew) ||     // al menos un número
        !preg_match("/[!@#$%^&*(),.?\":{}|<>_\-]/", $changePassNew) // al menos un símbolo
    ) {
        $errores['passNew'] = "Debe contener mayúsculas, minúsculas, números y un símbolo (por ej: . @ # ? !).";
        $cont++;
    } elseif (preg_match("/\s/", $changePassNew)) {
        $errores['passNew'] = "No debe contener espacios en blanco.";
        $cont++;
    } else {
        $changePassNew = htmlspecialchars($changePassNew);
    }

    if($cont!==0){
        echo json_encode([
            "status" => "error",
            "message" => "Contraseña Nueva Invalida."
        ]);
        exit;
    }


    // =====================================================
    //  Si hay errores → responder y salir
    // =====================================================
    if (array_filter($errores)) {
        echo json_encode([
            "status" => "error",
            "message" => "Errores en el formulario."
        ]);
        exit;
    }


    if(($changePassCurrent == $changePassNew)){
        echo json_encode([
            "status" => "error",
            "message" => "La contraseña actual y la nueva no deben ser iguales."
        ]);
        exit;
    }
    
    if(!($user->changePassword($conn, $changePassNew))){
        echo json_encode([
            "status" => "error",
            "message" => "Hubo un error al cambiar la contraseña. Intentalo mas tarde."
        ]);
    }else{
        echo json_encode([
            "status" => "success",
            "message" => "Contraseña Cambiada Exitosamente."
        ]);
    }
    
    desconexion($conn);
    exit;

    



?>