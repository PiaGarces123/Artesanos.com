<?php 

    // Mostrar errores en pantalla (lo usamos durante el desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Iniciar o reanudar la sesión del usuario
    session_start();
    
    // Configuración de zona horaria
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

    // Si no hay usuario logueado, devolver error
    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Tu sesión ha expirado."
        ]);
        exit;
    }

    // Obtener el usuario actual desde la base de datos
    if(!($user = User::getById($conn, $_SESSION['user_id']))){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Usuario no encontrado."
        ]);
        exit;
    }

    // =====================================================
    // 2. CAPTURA Y LIMPIEZA DE DATOS ENVIADOS POR POST
    // =====================================================

    // Se escapan los valores para evitar inyección SQL
    $changePassCurrent = mysqli_real_escape_string($conn, $_POST['changePassCurrent']);
    $changePassNew = mysqli_real_escape_string($conn, $_POST['changePassNew']);
   

    
    // ===================================================
    // VALIDACIONES DE CONTRASEÑAS
    // ===================================================

    // Inicializa un array para almacenar posibles errores
    $errores = [
        "passCurrent" => "",
        "passNew" => ""
    ];

    // ---------------------------- Validar Contraseña ---------------------------- 
    // Se usa la función login() de la clase User para verificar
    // que la contraseña actual coincide con la guardada en la base de datos
    if(!($user = User::login($conn, $user->email, $changePassCurrent))){
        echo json_encode([
            "status" => "error",
            "message" => "Contraseña actual incorrecta."
        ]);
        exit;
    }

    // ---------------------------- Validar la Nueva Contraseña ---------------------------- 

    $cont = 0;// Contador de errores de validación

    // Campo vacío
    if (empty($changePassNew)) {
        $errores['passNew'] = "Campo Obligatorio.";
        $cont++;
    }
    // Longitud mínima
    elseif (strlen($changePassNew) < 6) {
        $errores['passNew'] = "Debe tener al menos 6 caracteres.";
        $cont++;
    } 
    // Requisitos de seguridad: mayúscula, minúscula, número y símbolo
    elseif (
        !preg_match("/[A-Z]/", $changePassNew) ||     // al menos una mayúscula
        !preg_match("/[a-z]/", $changePassNew) ||     // al menos una minúscula
        !preg_match("/[0-9]/", $changePassNew) ||     // al menos un número
        !preg_match("/[!@#$%^&*(),.?\":{}|<>_\-]/", $changePassNew) // al menos un símbolo
    ) {
        $errores['passNew'] = "Debe contener mayúsculas, minúsculas, números y un símbolo (por ej: . @ # ? !).";
        $cont++;
    } 
    // Sin espacios en blanco
    elseif (preg_match("/\s/", $changePassNew)) {
        $errores['passNew'] = "No debe contener espacios en blanco.";
        $cont++;
    } 
    // Si pasa todas las validaciones, se limpia el valor
    else {
        $changePassNew = htmlspecialchars($changePassNew);
    }

    // Si hay errores de validación, se detiene la ejecución
    if($cont!==0){
        echo json_encode([
            "status" => "error",
            "message" => "Contraseña Nueva Invalida."
        ]);
        exit;
    }


    // =====================================================
    // 4. VALIDACIÓN FINAL Y CAMBIO DE CONTRASEÑA
    // =====================================================

    
    //  Si hay errores → responder y salir
    if (array_filter($errores)) {
        echo json_encode([
            "status" => "error",
            "message" => "Errores en el formulario."
        ]);
        exit;
    }

    // No permitir que la nueva contraseña sea igual a la actual
    if(($changePassCurrent == $changePassNew)){
        echo json_encode([
            "status" => "error",
            "message" => "La contraseña actual y la nueva no deben ser iguales."
        ]);
        exit;
    }
    
    // Intentar actualizar la contraseña en la base de datos
    if(!($user->changePassword($conn, $changePassNew))){
        //Si hubo un error
        echo json_encode([
            "status" => "error",
            "message" => "Hubo un error al cambiar la contraseña. Intentalo mas tarde."
        ]);
    }else{
        //Si salió bien
        echo json_encode([
            "status" => "success",
            "message" => "Contraseña Cambiada Exitosamente."
        ]);
    }
    
    // Cierra la conexión con la base de datos y finaliza el script
    desconexion($conn);
    exit; 

    

?>