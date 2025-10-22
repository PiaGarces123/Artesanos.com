<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
    
    require_once "../Clases/Album.php";
    require_once "../Clases/Image.php";
    require_once "../Clases/User.php"; 
    require_once "../conexion.php"; 

    date_default_timezone_set('America/Argentina/San_Luis');
    session_start();
    //Le dice al navegador que la respuesta del servidor no es HTML, sino JSON
    header("Content-Type: application/json");

    // Conexión
    
    $conn = conexion();
    $error = "";
    $cont=0;

    if(!isset($_SESSION['user_id'])){
        echo json_encode([
            "status" => "errorSession",
            "message" => "Necesitas iniciar sesión para publicar."
        ]);
        exit;
    }

    $user = User::getById($conn, $_SESSION['user_id']);

    if (!$user) {
        // Si el usuario existe en sesión pero no en la BD (error crítico)
        echo json_encode([
            "status" => "errorSession",
            "message" => "Error interno del usuario En la Base de Datos."
        ]);
        exit;
    }
    //si esta bloqueado devuelve true y muestra error
    if($user->isBlockedForPublishing()){
        echo json_encode([
            "status" => "error",
            "message" => "EL USUARIO SE ENCUENTRA BLOQUEADO"
        ]);
        exit;
    }


    //Almacena el archivo físico en el directorio del álbum del usuario.
    function almacenaImagen($fileArray, $imageIndex, $idUser, $idAlbum, $idImage) {
        $rutaNull = "./Frontend/assets/images/appImages/imagenError.png";
        // 1. Obtener la información del archivo específico
        $fileTmpName = $fileArray['tmp_name'][$imageIndex];
        $originalFileName = $fileArray['name'][$imageIndex];
        $fileError = $fileArray['error'][$imageIndex];

        // Verificar si no hay errores de subida
        if ($fileError !== UPLOAD_ERR_OK) {
            return $rutaNull; // Retorna NULL si hay error de subida (ej: archivo demasiado grande)
        }
        
        // 2. Determinar la extensión del archivo
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        // 3. Definir las rutas (Asumiendo que estás en BACKEND/FuncionesPHP/)
        $base_dir = __DIR__ . '/../../FILES/';
        
        // Nombre final del archivo y ruta completa
        $final_filename = $idImage . '.' . $fileExtension;
        $album_dir = $base_dir . $idUser . '/' . $idAlbum . '/';
        $final_path = $album_dir . $final_filename;

        // 4. Mover el archivo temporal a la ubicación final
        if (move_uploaded_file($fileTmpName, $final_path)) {
            // Retorna la ruta relativa para guardar en la BD
            return './FILES/' . $idUser . '/' . $idAlbum . '/' . $final_filename;
        }

        return $rutaNull; // Error al mover el archivo (ej: problemas de permisos)
    }

    // Obtener datos de publicacion
    $imageInput = $_FILES["imageInput"];
    $titleImage = $_POST["titleImage"];
    $visibilityImage = $_POST["visibilityImage"];
    $actionPost = $_POST["actionPost"];

    if($actionPost=='create'){
        $titleAlbum = trim($_POST["titleAlbum"] ?? "");
        $coverImageIndex = $_POST["coverImageIndex"];

        $regex = '/^[a-zA-Z0-9._+()ÁÉÍÓÚáéíóúÑñ\s-]{1,30}$/';
    
        if (empty($titleAlbum)) {
            echo json_encode([
                "status" => "error",
                "message" => "El título del álbum no puede estar vacío."
            ]);
            exit;
        }
        
        if (!preg_match($regex, $titleAlbum)) {
            echo json_encode([
                "status" => "error",
                "message" => "El título del álbum contiene caracteres no permitidos o excede los 30 caracteres. Sólo se permiten letras, números, espacios y los caracteres: . _ + -()"
            ]);
            exit;
        }

        $idAlbum = Album::crear($conn,$titleAlbum,$user->id);

        if($idAlbum){
            // ===================================================================
            // 🚀 LÓGICA CLAVE: CREACIÓN DE LA CARPETA DEL ÁLBUM
            // ===================================================================
            
            // 1. Definir la ruta base del directorio de archivos (FILES en la raíz)
            $base_files_dir = __DIR__ . '/../../FILES/';
            
            // 2. Definir la ruta del directorio del USUARIO (ej: /FILES/5/)
            $user_dir = $base_files_dir . $user->id;
            
            // 3. Definir la ruta del directorio del ÁLBUM (ej: /FILES/5/12/)
            // Usamos el ID del álbum (A_id) para nombrar la carpeta.
            $album_dir = $user_dir . '/' . $idAlbum;
            
            // 4. Crear la carpeta del álbum si no existe.
            if (!is_dir($album_dir)) {
                // Usamos @ para suprimir la advertencia si ya existiera, y 'true' para recursivo.
                if (!@mkdir($album_dir, 0777, true)) { 
                    
                    // Manejo de error si la carpeta no pudo ser creada (ej: problema de permisos)
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error de permisos al crear la carpeta del álbum."
                    ]);
                    // Es crítico: si falla, borra el registro de la BD para mantener la integridad
                    Album::eliminar($conn, $idAlbum); 
                    exit;
                }
            }
            
            

            for($i=0; $i<count($titleImage); $i++){
                $isCover = ($i == $coverImageIndex) ? 1 : 0;
                $idImage = Imagen::crear(
                    $conn,
                    $titleImage[$i], 
                    $user->id,              // 3. $idUsuario (valor sin default)
                    $visibilityImage[$i],   // 4. $visibility (sobrescribe 0)
                    $idAlbum,               // 5. $idAlbum (sobrescribe NULL)
                    NULL,                   // 6. $ruta (sobrescribe NULL) 
                    0,                      // 7. $esPerfil (sobrescribe 0)
                    $isCover                // 8. $esPortada (sobrescribe 0)
                );
                if($idImage){
                    $rutaImagen = almacenaImagen($imageInput,$i, $user->id, $idAlbum, $idImage);
                    Imagen::actualizarRuta($conn,$idImage,$rutaImagen); 
                }else{
                    $cont++;
                }
                
            }
            if($cont!==0){
                echo json_encode([
                    "status" => "error",
                    "message" => "PROBLEMAS AL SUBIR CIERTAS IMAGENES"
                ]);
                
            }else{
                echo json_encode([
                    "status" => "success",
                    "message" => "CARPETA E IMAGENES CREADAS CORRECTAMENTE"
                ]);
                
            }

        }else{
            echo json_encode([
                "status" => "error",
                "message" => "Problemas al crear el Album"
            ]);
            
        }
    }else{
        // logica para $oprionPost == 'select'
    }
    desconexion($conn);
?>