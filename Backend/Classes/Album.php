<?php
    class Album {
        private $conn;
        private $table_name = "albums";

        public $A_id;
        public $A_title;
        public $A_idUser;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function create() {
            $query = "INSERT INTO " . $this->table_name . " (A_title, A_idUser) VALUES (:title, :user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $this->A_title);
            $stmt->bindParam(':user_id', $this->A_idUser);
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }

        // Obtener álbumes de un usuario
        public function getUserAlbums($user_id) {
            $query = "SELECT 
                        a.A_id, a.A_title, a.A_creationDate,
                        COUNT(i.I_id) as image_count,
                        (SELECT I_id FROM images WHERE I_idAlbum = a.A_id LIMIT 1) as cover_image
                    FROM " . $this->table_name . " a
                    LEFT JOIN images i ON a.A_id = i.I_idAlbum
                    WHERE a.A_idUser = :user_id
                    GROUP BY a.A_id
                    ORDER BY a.A_creationDate DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            return $stmt;
        }

        // Obtener álbum por ID
        public function getById($album_id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE A_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $album_id);
            $stmt->execute();
            return $stmt;
        }
    }
?>
