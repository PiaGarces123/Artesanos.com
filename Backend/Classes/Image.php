<?php
    class Image {
        private $conn;
        private $table_name = "images";

        public $I_id;
        public $I_title;
        public $I_visibility;
        public $I_idAlbum;
        public $I_idUser;

        public function __construct($db) {
            $this->conn = $db;
        }

        
        // Obtener imágenes para el feed
        public function getFeedImages($limit = 20, $offset = 0, $album_id = null) {
            $query = "SELECT 
                        i.I_id, i.I_title, i.I_publicationDate, i.I_idAlbum,
                        u.U_id, u.U_nameUser, u.U_name, u.U_lastName,
                        a.A_title as album_title,
                        COUNT(DISTINCT l.L_id) as likes_count,
                        COUNT(DISTINCT c.C_id) as comments_count
                    FROM " . $this->table_name . " i
                    LEFT JOIN users u ON i.I_idUser = u.U_id
                    LEFT JOIN albums a ON i.I_idAlbum = a.A_id
                    LEFT JOIN likes l ON i.I_id = l.L_idImage
                    LEFT JOIN comments c ON i.I_id = c.C_idImage
                    WHERE i.I_visibility = 0 
                    AND i.I_isProfile = 0
                    AND u.U_status = 0";
            
            if ($album_id) {
                $query .= " AND i.I_idAlbum = :album_id";
            }
            
            $query .= " GROUP BY i.I_id 
                    ORDER BY i.I_publicationDate DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            
            if ($album_id) {
                $stmt->bindParam(':album_id', $album_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt;
        }

        // Buscar imágenes
        public function searchImages($search_term, $limit = 20) {
            $query = "SELECT 
                        i.I_id, i.I_title, i.I_publicationDate, i.I_idAlbum,
                        u.U_id, u.U_nameUser, u.U_name, u.U_lastName,
                        a.A_title as album_title,
                        COUNT(DISTINCT l.L_id) as likes_count,
                        COUNT(DISTINCT c.C_id) as comments_count
                    FROM " . $this->table_name . " i
                    LEFT JOIN users u ON i.I_idUser = u.U_id
                    LEFT JOIN albums a ON i.I_idAlbum = a.A_id
                    LEFT JOIN likes l ON i.I_id = l.L_idImage
                    LEFT JOIN comments c ON i.I_id = c.C_idImage
                    WHERE i.I_visibility = 0 
                    AND i.I_isProfile = 0
                    AND u.U_status = 0
                    AND (i.I_title LIKE :search OR a.A_title LIKE :search OR u.U_name LIKE :search)
                    GROUP BY i.I_id 
                    ORDER BY i.I_publicationDate DESC 
                    LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $search_param = "%{$search_term}%";
            $stmt->bindParam(':search', $search_param);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt;
        }

        // Crear imagen
        public function create() {
            $query = "INSERT INTO " . $this->table_name . " 
                    (I_title, I_visibility, I_idAlbum, I_idUser) 
                    VALUES (:title, :visibility, :album_id, :user_id)";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':title', $this->I_title);
            $stmt->bindParam(':visibility', $this->I_visibility);
            $stmt->bindParam(':album_id', $this->I_idAlbum);
            $stmt->bindParam(':user_id', $this->I_idUser);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }
    }
?>
