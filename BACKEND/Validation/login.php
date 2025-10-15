<?php

    /* El archivo PHP no devuelve una página HTML, sino una respuesta estructurada en JSON, 
    ideal para que el JavaScript del frontend la entienda fácilmente. */

    require_once "../Clases/User.php"; 
    require_once "../conexion.php"; 

    //Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");

    // Conexión
    $conn = conexion();

    //Crear array de errores
    $errores= array (
        "email" => "",
        "password" => ""
    );

    // Obtener datos del formulario
    $email = trim($_POST["mail"] ?? "");
    $password = trim($_POST["pass"] ?? "");

    // Validación Email
    if( empty($email)){
        $errores['email']= "Campo Obligatorio.";
    } elseif(!filter_var($eMail,FILTER_VALIDATE_EMAIL)){
        $errores['email']= "Formato Invalido.";
    } else{
        $email = htmlspecialchars($email);
    }

    // Validar Contraseña
    if (empty($password)) {
        $errores['password'] = "Campo Obligatorio.";
    } elseif (strlen($password) < 6) {
        $errores['password'] = "Debe tener al menos 6 caracteres.";
    } elseif (
        !preg_match("/[A-Z]/", $password) ||     // al menos una mayúscula
        !preg_match("/[a-z]/", $password) ||     // al menos una minúscula
        !preg_match("/[0-9]/", $password) ||     // al menos un número
        !preg_match("/[!@#$%^&*(),.?\":{}|<>_\-]/", $password) // al menos un símbolo
    ) {
        $errores['password'] = "Debe contener mayúsculas, minúsculas, números y un símbolo (por ej: . @ # ? !).";
    } elseif (preg_match("/\s/", $password)) {
        $errores['password'] = "No debe contener espacios en blanco.";
    } else {
        $password = htmlspecialchars($password);
    }

    // =====================================================
    //  Si hay errores → responder y salir
    // =====================================================
    if (array_filter($errores)) {
        // "json_encode" función de PHP que convierte un array o un objeto PHP en una cadena de texto con formato JSON
        echo json_encode([
            "status" => "error",
            "message" => "Errores en el formulario.",
            "errores" => $errores
        ]);
        exit;
    }



    // =====================================================
    //  Intentar iniciar sesión usando el método de la clase
    // =====================================================
    $user = User::login($conn, $email, $password);

    if ($user) {
        session_start();
        $_SESSION["user_id"] = $user->id; 
        $_SESSION["username"] = $user->username; 
        $_SESSION["email"] = $user->email; 

        echo json_encode([
            "status" => "success",
            "type" => "login",
            "message" => "Inicio de sesión correcto.",
            "user" => [
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Credenciales incorrectas. Verifica tu email o contraseña."
        ]);
    }

    desconexion($conn);
?>
