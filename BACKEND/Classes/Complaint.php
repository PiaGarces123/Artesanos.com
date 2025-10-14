<?php
class Complaint {

    // 🔹 Agregar una denuncia sobre una imagen
    public static function agregar($conn, $idImagen, $idUsuario, $razon) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;
        $razon = mysqli_real_escape_string($conn, $razon);

        $sql = "INSERT INTO complaints (D_idImage, D_idUser, D_reason) 
                VALUES ($idImagen, $idUsuario, '$razon')";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Obtener denuncias de una imagen
    public static function getByImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "SELECT c.*, u.U_name, u.U_lastName 
                FROM complaints c
                INNER JOIN users u ON c.D_idUser = u.U_id
                WHERE c.D_idImage = $idImagen
                ORDER BY c.D_complaintDate ASC";

        $resultado = mysqli_query($conn, $sql);
        $denuncias = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $denuncias[] = $fila;
            }
        }
        return $denuncias;
    }

    // 🔹 Cambiar el estado de la denuncia
    // 0 = pendiente, 1 = resuelta, -1 = rechazada
    public static function setEstado($conn, $idDenuncia, $estado) {
        $idDenuncia = (int)$idDenuncia;
        $estado = (int)$estado;

        $sql = "UPDATE complaints SET D_status = $estado WHERE D_id = $idDenuncia";
        return mysqli_query($conn, $sql);
    }
    
//Control de denuncias por usuario
    public static function puedeDenunciar($conn, $idImagen, $idUsuario) {
        $idImagen = (int)$idImagen;
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT D_status 
                FROM complaints 
                WHERE D_idImage = $idImagen AND D_idUser = $idUsuario 
                ORDER BY D_complaintDate DESC 
                LIMIT 1";
        
        $resultado = mysqli_query($conn, $sql);
        if ($fila = mysqli_fetch_assoc($resultado)) {
            $estado = (int)$fila['D_status'];
            // Solo puede denunciar si la última denuncia está resuelta (1) o rechazada (-1)
            return $estado !== 0;
        }
        // No hay denuncia previa, puede denunciar
        return true;
    }

    // 🔹 Eliminar una denuncia específica
    public static function eliminar($conn, $idDenuncia) {
        $idDenuncia = (int)$idDenuncia;

        $sql = "DELETE FROM complaints WHERE D_id = $idDenuncia";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Eliminar todas las denuncias de una imagen (cuando se elimina la imagen)
    public static function eliminarPorImagen($conn, $idImagen) {
        $idImagen = (int)$idImagen;

        $sql = "DELETE FROM complaints WHERE D_idImage = $idImagen";
        return mysqli_query($conn, $sql);
    }
}
?>
