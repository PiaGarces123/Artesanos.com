<?php
    class Like {
        private $conn;
        private $table_name = "likes";

        public function __construct($db) {
            $this->conn = $db;
        }

        public function toggleLike($user_id, $image_id) {
            $query = "SELECT L_id FROM " . $this->table_name . " WHERE L_idUser = :user_id AND L_idImage = :image_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':image_id', $image_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $query = "DELETE FROM " . $this->table_name . " WHERE L_idUser = :user_id AND L_idImage = :image_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':image_id', $image_id);
                return $stmt->execute() ? 'removed' : false;
            } else {
                $query = "INSERT INTO " . $this->table_name . " (L_idUser, L_idImage) VALUES (:user_id, :image_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':image_id', $image_id);
                return $stmt->execute() ? 'added' : false;
            }
        }
    }
?>
