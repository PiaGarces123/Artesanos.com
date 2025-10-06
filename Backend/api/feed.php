<?php

    // Endpoint para obtener el feed de imágenes
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    include_once './Backend/Classes/Database.php';
    include_once './Backend/Classes/Image.php';

    $database = new Database();
    $db = $database->getConnection();
    $image = new Image($db);

    // Parámetros de la solicitud
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    $offset = ($page - 1) * $limit;

    try {
        if ($search) {
            $stmt = $image->searchImages($search, $limit);
        } else {
            $stmt = $image->getFeedImages($limit, $offset, $album_id);
        }

        $images = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $image_item = array(
                "id" => $row['I_id'],
                "title" => $row['I_title'],
                "image_url" => "./Frontend/assets/images/usersImages/" . $row['I_id'] . ".jpg", // Ruta donde guardarás las imágenes
                "album" => array(
                    "id" => $row['I_idAlbum'],
                    "title" => $row['album_title']
                ),
                "artisan" => array(
                    "id" => $row['U_id'],
                    "username" => $row['U_nameUser'],
                    "name" => $row['U_name'] . ' ' . $row['U_lastName']
                ),
                "likes_count" => (int)$row['likes_count'],
                "comments_count" => (int)$row['comments_count'],
                "publication_date" => $row['I_publicationDate']
            );
            
            array_push($images, $image_item);
        }
        
        echo json_encode(array(
            "success" => true,
            "data" => $images,
            "page" => $page,
            "has_more" => count($images) === $limit
        ));

    } catch(Exception $e) {
        echo json_encode(array(
            "success" => false,
            "message" => "Error al obtener imágenes: " . $e->getMessage()
        ));
    }

?>