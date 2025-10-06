<?php

    // Endpoint para crear un álbum
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once './Backend/Classes/Database.php';
        include_once './Backend/Classes/Album.php';
        include_once './Backend/Classes/Image.php';

        $database = new Database();
        $db = $database->getConnection();

        // Obtener datos del POST
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->title) && !empty($data->user_id)) {
            $album = new Album($db);
            $album->A_title = $data->title;
            $album->A_idUser = $data->user_id;

            $album_id = $album->create();

            if ($album_id) {
                // Si hay imágenes, crearlas
                if (!empty($data->images)) {
                    $image = new Image($db);
                    $created_images = array();

                    foreach ($data->images as $img) {
                        $image->I_title = $img->title;
                        $image->I_visibility = 0; // Público por defecto
                        $image->I_idAlbum = $album_id;
                        $image->I_idUser = $data->user_id;
                        
                        $image_id = $image->create();
                        if ($image_id) {
                            // Aquí guardarías el archivo de imagen
                            // move_uploaded_file($img->tmp_name, "uploads/images/" . $image_id . ".jpg");
                            array_push($created_images, $image_id);
                        }
                    }
                }

                echo json_encode(array(
                    "success" => true,
                    "message" => "Álbum creado correctamente",
                    "album_id" => $album_id
                ));
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Error al crear el álbum"
                ));
            }
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Datos incompletos"
            ));
        }
    }

?>