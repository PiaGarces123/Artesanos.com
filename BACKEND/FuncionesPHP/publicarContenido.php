<?php 
    date_default_timezone_set('America/Argentina/San_Luis');
    session_start();
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php"; 
    require_once "../conexion.php"; 

    // Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");

    // Conexión
    $conn = conexion();
    $cont=0; // Contador de fallos
    $idAlbumDestino = null; // Variable para almacenar el ID final del álbum

    // =========================================================
    // 1. VERIFICACIÓN DE SESIÓN, BLOQUEO Y DATOS
    // =========================================================
    
    // Verificación de sesión/bloqueo (Se mantiene tu lógica)
    if(!isset($_SESSION['user_id']) || !($user = User::getById($conn, $_SESSION['user_id'])) || $user->isBlockedForPublishing()){
        $status = (!isset($_SESSION['user_id']) || !$user) ? "errorSession" : "error";
        $message = ($status == "errorSession") ? "Sesión inválida o cuenta bloqueada." : "EL USUARIO SE ENCUENTRA BLOQUEADO";
        echo json_encode(["status" => $status, "message" => $message]);
        exit;
    }

    // Recepción de datos (Asumimos que vienen correctamente por el JS)
    $uploadedFiles = $_FILES['imageInput']; 
    $titleImage = $_POST["titleImage"]; 
    $visibilityImage = $_POST["visibilityImage"];
    $actionPost = $_POST["actionPost"]; 
    $coverImageIndex = (int)($_POST["coverImageIndex"] ?? -1); 
    $numImagesToUpload = count($titleImage);

    
    // =========================================================
    // 2. LÓGICA DE MANEJO DE ÁLBUMES
    // =========================================================

    if($actionPost === 'create'){
        $titleAlbum = trim($_POST["titleAlbum"] ?? "");
        $regex = '/^[a-zA-Z0-9._+()ÁÉÍÓÚáéíóúÑñ\s-]{1,30}$/';
        
        // Validación de título
        if (empty($titleAlbum) || !preg_match($regex, $titleAlbum)) {
            echo json_encode(["status" => "error", "message" => "Título de álbum inválido o vacío."]);
            exit;
        }

        // 2a. Crear Álbum en BD
        $idAlbumDestino = Album::crear($conn, $titleAlbum, $user->id); 

        if(!$idAlbumDestino){
            echo json_encode(["status" => "error", "message" => "Problemas al crear el Álbum en la base de datos."]);
            exit;
        }

        // 2b. Crear Carpeta Física
        $base_files_dir = __DIR__ . '/../../FILES/';
        $album_dir = $base_files_dir . $user->id . '/' . $idAlbumDestino;
        
        if (!is_dir($album_dir) && !@mkdir($album_dir, 0777, true)) { 
            echo json_encode(["status" => "error", "message" => "Error de permisos al crear la carpeta del álbum."]);
            Album::eliminar($conn, $idAlbumDestino); 
            exit;
        }

    } elseif($actionPost === 'select'){
        $albumSelected = $_POST["albumSelected"]; 
        $idAlbumDestino = (int)$albumSelected;

        // 2a. Validar Álbum Existente
        if(!Album::exists($conn, $idAlbumDestino)){
            echo json_encode(["status" => "error", "message" => "El Álbum Seleccionado No Existe."]);
            exit;
        }
        
        // 2b. Validar Espacio (Máx 20 imágenes)
        $cantImages = Album::contarImagenes($conn, $idAlbumDestino);
        if(($cantImages + $numImagesToUpload) > 20){
            echo json_encode(["status" => "error", "message" => "No Hay Espacio Suficiente en el Álbum (Máximo: 20 imágenes)."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Acción de publicación inválida."]);
        exit;
    }


    // =========================================================
    // 3. BUCLE CENTRALIZADO DE SUBIDA E INSERCIÓN
    // =========================================================

    for($i=0; $i < $numImagesToUpload; $i++){
        $isCover = ($i == $coverImageIndex) ? 1 : 0;
        
        // 1. Crear la entrada en la BD (ruta NULL temporal)
        $idImage = Imagen::crear(
            $conn,
            $titleImage[$i], 
            $user->id,          
            $visibilityImage[$i], 
            $idAlbumDestino,            
            NULL,               
            0,                  
            $isCover            
        );

        if($idImage){
            // 2. Intentar almacenar archivo físico y obtener ruta
            $rutaImagen = almacenaImagen($uploadedFiles, $i, $user->id, $idAlbumDestino, $idImage);
            
            // --- ¡CAMBIO 2! ---
            // 3. Comprobar si el almacenamiento falló
            if ($rutaImagen==null) {
                // 3a. Fallo: Eliminar la fila de la BD (Rollback)
                Imagen::eliminar($conn, $idImage); 
                $cont++; // Incrementar contador de fallos
            } else {
                // 3b. Éxito: Actualizar la ruta en la BD
                Imagen::actualizarRuta($conn, $idImage, $rutaImagen); 
            }
        }else{
            $cont++; // Cuenta fallo al crear la fila en BD
        }
    }


    // =========================================================
    // 4. RESPUESTA FINAL
    // =========================================================

    if($cont !== 0){
        // Si falló la subida de alguna imagen, el álbum se queda pero notificamos el error.
        echo json_encode(["status" => "error", "message" => "PROBLEMAS AL SUBIR ". $cont . " IMAGEN(ES)." . " Album Sin Problemas."]);
    }else{
        echo json_encode(["status" => "success", "message" => "Imagenes Subidas Correctamente."]);
    }

    desconexion($conn);

    // --------------------------------------------------------
    // FUNCIÓN DE ALMACENAMIENTO FÍSICO (Para referencia, debe estar accesible)
    // --------------------------------------------------------

    function almacenaImagen($fileArray, $imageIndex, $idUser, $idAlbum, $idImage) {
        $rutaError = "./Frontend/assets/images/appImages/imagenError.png";
        
        // ... (Lógica para obtener info del archivo, moverlo y devolver la ruta o $rutaError) ...
        $fileTmpName = $fileArray['tmp_name'][$imageIndex];
        $originalFileName = $fileArray['name'][$imageIndex];
        $fileError = $fileArray['error'][$imageIndex];

        if ($fileError !== UPLOAD_ERR_OK) { return null; }
        
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $base_dir = __DIR__ . '/../../FILES/';
        
        $final_filename = $idImage . '.' . $fileExtension;
        $album_dir = $base_dir . $idUser . '/' . $idAlbum . '/';
        $final_path = $album_dir . $final_filename;

        if (move_uploaded_file($fileTmpName, $final_path)) {
            return './FILES/' . $idUser . '/' . $idAlbum . '/' . $final_filename;
        }

        return null;
    }
?>