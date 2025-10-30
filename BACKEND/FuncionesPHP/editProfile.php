<?php 
    // Mostrar errores en pantalla (lo usamos durante el desarrollo)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Iniciar o reanudar la sesión del usuario
    session_start();
    
    // Configuración de zona horaria 
    date_default_timezone_set('America/Argentina/San_Luis');

    // Requerir Clases y Conexión
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php"; 
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
    // 2. FUNCIÓN PARA ALMACENAR IMAGEN DE PERFIL
    // =====================================================
    function almacenaImagen($file, $idUser, $idImage) {
        // Obtiene información del archivo subido
        // ... (Lógica para obtener info del archivo, moverlo y devolver la ruta o $rutaError) ...
        $fileTmpName = $file['tmp_name']; // Ruta temporal del archivo   
        $originalFileName = $file['name']; // Nombre original
        $fileError = $file['error']; // Código de error

        // Si hubo error al subir el archivo, no se procesa
        if ($fileError !== UPLOAD_ERR_OK) { return null; }
        
        // Extrae la extensión del archivo (jpg, png, etc.)
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        // Define la ruta base donde se almacenan los archivos
        $base_dir = __DIR__ . '/../../FILES/';
        
        // Crea el nombre final del archivo: ID de imagen + extensión
        $final_filename = $idImage . '.' . $fileExtension;

        // Carpeta destino del usuario → FILES/{idUser}/imagesProfile/
        $album_dir = $base_dir . $idUser . '/' . 'imagesProfile' . '/';
        $final_path = $album_dir . $final_filename;

        // 3. Crear la carpeta imagesProfile si no existe (la con permisos 0777)
        if (!is_dir($album_dir)) {
            if (!@mkdir($album_dir, 0777, true)) {
                return null;
            }
        }

        // Mueve el archivo desde su ubicación temporal a la carpeta final
        if (move_uploaded_file($fileTmpName, $final_path)) {
            // Devuelve la ruta relativa que se guardará en la DB
            return './FILES/' . $idUser . '/' . 'imagesProfile' . '/' . $final_filename;
        }

        // Si falla el movimiento del archivo
        return null;
    }

    // =====================================================
    // 3. CAPTURA DE DATOS ENVIADOS DESDE EL FORMULARIO
    // =====================================================
    $conn = conexion();
    $profilePic = isset($_FILES['profilePic']) ? $_FILES['profilePic'] : null; // Imagen subida
    $dateBirth = mysqli_real_escape_string($conn, $_POST['dateBirth']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $biography = mysqli_real_escape_string($conn, $_POST['biography']);

    
    // ===================================================
    // 4. VALIDACIONES DE DATOS DEL PERFIL
    // ===================================================
    $errores = [
        "fNac" => "",
        "nbre" => "",
        "ape" => "",
        "userName" => ""
    ];

    // Validar fecha de nacimiento (obligatoria y +18 años)
    if (empty($dateBirth)) {
        $errores["fNac"] = "Campo obligatorio.";
    } else {
        $fecha = new DateTime($dateBirth);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha)->y;
        if ($edad < 18) {
            $errores["fNac"] = "Debes tener al menos 18 años.";
        }
    }

    // Validar nombre (solo letras y espacios, 2–30 caracteres)
    if (empty($name)) {
        $errores["nbre"] = "Campo obligatorio.";
    } elseif (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/", $name)) {
        $errores["nbre"] = "Solo letras (2-30 caracteres).";
    }

    // Apellido
    if (empty($lastName)) {
        $errores["ape"] = "Campo obligatorio.";
    } elseif (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,30}$/", $lastName)) {
        $errores["ape"] = "Solo letras (2-30 caracteres).";
    }

    // Usuario
    if (empty($username)) {
        $errores["userName"] = "Campo obligatorio.";
    } elseif (strlen($username) < 4 || strlen($username) > 20) {
        $errores["userName"] = "Debe tener entre 4 y 20 caracteres.";
    }
    


    // =====================================================
    // 5. SI HAY ERRORES, DETENER Y MOSTRAR MENSAJE
    // =====================================================
    if (array_filter($errores)) {
        echo json_encode([
            "status" => "error",
            "message" => "Errores en el formulario."
        ]);
        exit;
    }

    // Verificar que no exista ya el username
    $sqlUser = "SELECT U_id FROM users WHERE U_nameUser = '$username' AND U_id <> " . $user->id;
    $resUser = mysqli_query($conn, $sqlUser);
    if (mysqli_num_rows($resUser) > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Nombre de Usuario Ya Existe. Elige Otro."
        ]);
        exit;
    }

    // =====================================================
    // 6. ACTUALIZAR DATOS DEL PERFIL EN LA BASE DE DATOS
    // =====================================================

    $user->name = $name;
    $user->lastName = $lastName;
    $user->dateBirth = $dateBirth;
    $user->username = $username;
    $user->biography = $biography;

    // Si falla la actualización del perfil, se devuelve error
    if(!$user->updateProfile($conn)){
        echo json_encode([
            "status" => "error",
            "message" => "Error al actualizar el perfil"
        ]);
        exit;
    }

    // =====================================================
    // 7. SUBIDA DE NUEVA IMAGEN DE PERFIL (si existe)
    // =====================================================

    // 1. Verificar si el usuario INTENTÓ subir una imagen
    if (isset($profilePic) && $profilePic['error'] !== UPLOAD_ERR_NO_FILE) {
        
        // 2. Si hubo un error que NO sea simplemente no haber subido un archivo
        if ($profilePic['error'] !== UPLOAD_ERR_OK) {
            // Manejar errores de subida (ej. tamaño excedido, error de disco)
            echo json_encode([
                "status" => "error",
                "message" => "Error al subir la imagen. Intenta con un archivo más pequeño o diferente formato."
            ]);
            exit;
        }

        // 3. Si el error ES UPLOAD_ERR_OK, procede a guardar en DB y mover el archivo
        $idImage = null; // Inicializar
        
        // 3a. Crear el registro en la base de datos
        if(!($idImage = Imagen::crear($conn,"", $user->id,0,null,null,1,0))){
            echo json_encode([
                "status" => "error",
                "message" => "Error al actualizar la Imagen de Perfil."
            ]);
            exit;
        }
        
        // 3b. Mover el archivo al servidor
        $rutaImagen = null; // Inicializar
        if(!($rutaImagen = almacenaImagen($profilePic, $user->id, $idImage))){
            // Si falla mover el archivo, eliminar el registro de la DB
            Imagen::eliminar($conn, $idImage); 
            echo json_encode([
                "status" => "error",
                "message" => "Error Actualizar la Imagen de Perfil."
            ]);
            exit;
        }
        
        // 3c. Actualizar la ruta en la base de datos si todo salió bien
        if (!Imagen::actualizarRuta($conn, $idImage, $rutaImagen)) {
            Imagen::eliminar($conn, $idImage);
            echo json_encode([
                "status" => "error",
                "message" => "Error al Actualizar la Imagen de Perfil."
            ]);
            exit;
        }
    }

     // =====================================================
    // 8. RESPUESTA FINAL DE ÉXITO
    // =====================================================

    // Respuesta de Éxito Final (Se ejecuta si el perfil de texto se actualizó
    // y/o la imagen se subió o no se intentó subir)

    echo json_encode([
        "status" => "success",
        "message" => "Perfil Actualizado Correctamente "
    ]);

    // Cierra la conexión a la base de datos
    desconexion($conn);
    exit;


?>