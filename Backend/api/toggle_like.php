<?php

    // Endpoint para dar/quitar like
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once './Backend/Classes/Database.php';
        include_once './Backend/Classes/Like.php';

        $database = new Database();
        $db = $database->getConnection();

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->user_id) && !empty($data->image_id)) {
            $like = new Like($db);
            $result = $like->toggleLike($data->user_id, $data->image_id);

            if ($result) {
                echo json_encode(array(
                    "success" => true,
                    "action" => $result
                ));
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Error al procesar el like"
                ));
            }
        }
    }

?>