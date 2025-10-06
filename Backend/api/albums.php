<?php

    // Endpoint para obtener álbumes de un usuario
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    include_once './Backend/Classes/Database.php';
    include_once './Backend/Classes/Album.php';

    $database = new Database();
    $db = $database->getConnection();
    $album = new Album($db);

    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1; // Por defecto usuario 1

    try {
        $stmt = $album->getUserAlbums($user_id);
        $albums = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $album_item = array(
                "id" => $row['A_id'],
                "title" => $row['A_title'],
                "creation_date" => $row['A_creationDate'],
                "image_count" => (int)$row['image_count'],
                "cover_image" => $row['cover_image'] ? "uploads/images/" . $row['cover_image'] . ".jpg" : null
            );
            
            array_push($albums, $album_item);
        }
        
        echo json_encode(array(
            "success" => true,
            "data" => $albums
        ));

    } catch(Exception $e) {
        echo json_encode(array(
            "success" => false,
            "message" => "Error al obtener álbumes: " . $e->getMessage()
        ));
    }

?>