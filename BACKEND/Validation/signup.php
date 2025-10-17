<?php

    /* El archivo PHP no devuelve una página HTML, sino una respuesta estructurada en JSON, 
    ideal para que el JavaScript del frontend la entienda fácilmente. */
    date_default_timezone_set('America/Argentina/San_Luis');
    require_once "../Clases/User.php"; 
    require_once "../conexion.php"; 

    //Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");

    // Conexión
    $conn = conexion();

    // Crear array de errores
    $errores = [
        "fNac" => "",
        "nbre" => "",
        "ape" => "",
        "userName" => "",
        "mail" => "",
        "pass" => ""
    ];

    // Obtener datos del formulario
    $fNac = trim($_POST["fNac"] ?? "");
    $nbre = trim($_POST["nbre"] ?? "");
    $ape = trim($_POST["ape"] ?? "");
    $userName = trim($_POST["userName"] ?? "");
    $mail = trim($_POST["mail"] ?? "");
    $pass = trim($_POST["pass"] ?? "");

    // ===================================================
    // VALIDACIONES
    // ===================================================

    // Fecha de nacimiento
    if (empty($fNac)) {
        $errores["fNac"] = "Campo obligatorio.";
    } else {
        $fecha = new DateTime($fNac);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha)->y;
        if ($edad < 18) {
            $errores["fNac"] = "Debes tener al menos 18 años.";
        }
    }

    // Nombre
    if (empty($nbre)) {
        $errores["nbre"] = "Campo obligatorio.";
    } elseif (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/", $nbre)) {
        $errores["nbre"] = "Solo letras (2-30 caracteres).";
    }

    // Apellido
    if (empty($ape)) {
        $errores["ape"] = "Campo obligatorio.";
    } elseif (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/", $ape)) {
        $errores["ape"] = "Solo letras (2-30 caracteres).";
    }

    // Usuario
    if (empty($userName)) {
        $errores["userName"] = "Campo obligatorio.";
    } elseif (strlen($userName) < 4 || strlen($userName) > 20) {
        $errores["userName"] = "Debe tener entre 4 y 20 caracteres.";
    } else {
        // Verificar que no exista ya el username
        $sqlUser = "SELECT U_id FROM users WHERE U_nameUser = '$userName'";
        $resUser = mysqli_query($conn, $sqlUser);
        if (mysqli_num_rows($resUser) > 0) {
            $errores["userName"] = "Este usuario ya existe.";
        }
    }

    // Email
    if (empty($mail)) {
        $errores["mail"] = "Campo obligatorio.";
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $errores["mail"] = "Formato inválido.";
    } else {
        // Verificar que no exista ya el email
        $sqlMail = "SELECT U_id FROM users WHERE U_email = '$mail'";
        $resMail = mysqli_query($conn, $sqlMail);
        if (mysqli_num_rows($resMail) > 0) {
            $errores["mail"] = "Este email ya está registrado.";
        }
    }

    // Contraseña
    if (empty($pass)) {
        $errores["pass"] = "Campo obligatorio.";
    } elseif (strlen($pass) < 6) {
        $errores["pass"] = "Debe tener al menos 6 caracteres.";
    } elseif (
        !preg_match("/[A-Z]/", $pass) ||
        !preg_match("/[a-z]/", $pass) ||
        !preg_match("/[0-9]/", $pass) ||
        !preg_match("/[!@#$%^&*(),.?\":{}|<>_\-]/", $pass)
    ) {
        $errores["pass"] = "Debe contener mayúsculas, minúsculas, números y símbolos.";
    }

    // =====================================================
    //  Si hay errores → responder y salir
    // =====================================================
    if (array_filter($errores)) {
        echo json_encode([
            "status" => "error",
            "message" => "Errores en el formulario.",
            "errores" => $errores
        ]);
        exit;
    }




    // ===================================================================
    //       Intentar Registrar Usuario usando el método de la clase
    // ===================================================================
    $data = [
        "username" => $userName,
        "name" => $nbre,
        "lastName" => $ape,
        "email" => $mail,
        "password" => $pass,
        "dateBirth" => $fNac
    ];

    // Asumimos que User::register devuelve el ID del nuevo usuario si es exitoso, 
    // o false/0 si falla.
    $new_user_id = User::register($conn, $data); 

    if ($new_user_id) {
        
        // ===================================================================
        // 🚀 LÓGICA CLAVE: CREACIÓN DE LA CARPETA DEL USUARIO
        // ===================================================================
        
        // 1. Definir la ruta base (asumiendo que FILES está en la raíz del proyecto, 
        // y este script está en BACKEND/Validation)
        $base_dir = __DIR__ . '/../../FILES/';
        
        // 2. Crear el directorio específico del usuario
        $user_dir = $base_dir . $new_user_id;
        
        // 3. Verificar si el directorio ya existe y crearlo si no.
        // El 0777 es el modo (permisos), y true indica que cree directorios recursivamente.
        if (!is_dir($user_dir)) {
            // Suprimimos los errores con @ si no es crítico, aunque es mejor manejarlos.
            @mkdir($user_dir, 0777, true); 
        }

        echo json_encode([
            "status" => "success",
            "message" => "Usuario registrado correctamente."
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error al registrar usuario."
        ]);
    }

    desconexion($conn);
?>