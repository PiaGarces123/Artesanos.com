<?php
class Follow {

    // 🔹 Enviar solicitud de seguimiento
    public static function solicitar($conn, $idSeguidor, $idSeguido) {
        $idSeguidor = (int)$idSeguidor;
        $idSeguido = (int)$idSeguido;

        $sql = "INSERT IGNORE INTO follow (F_idFollower, F_idFollowed, F_status) 
                VALUES ($idSeguidor, $idSeguido, 0)";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Aceptar solicitud pendiente
    public static function aceptar($conn, $idSeguidor, $idSeguido) {
        $idSeguidor = (int)$idSeguidor;
        $idSeguido = (int)$idSeguido;

        $sql = "UPDATE follow 
                SET F_status = 1, F_resolutionDate = NOW() 
                WHERE F_idFollower = $idSeguidor AND F_idFollowed = $idSeguido AND F_status = 0";
        return mysqli_query($conn, $sql);
    }

    // 🔹 Rechazar solicitud pendiente → elimina el registro
    public static function rechazar($conn, $idSeguidor, $idSeguido) {
        return self::eliminar($conn, $idSeguidor, $idSeguido);
    }

    // 🔹 Eliminar seguimiento (pendiente o aceptado)
    public static function eliminar($conn, $idSeguidor, $idSeguido) {
        $idSeguidor = (int)$idSeguidor;
        $idSeguido = (int)$idSeguido;

        $sql = "DELETE FROM follow 
                WHERE F_idFollower = $idSeguidor AND F_idFollowed = $idSeguido";
        return mysqli_query($conn, $sql);
    }
    // 🔹 Obtener lista de seguidores de un usuario
    public static function seguidores($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM follow f
                INNER JOIN users u ON f.F_idFollower = u.U_id
                WHERE f.F_idFollowed = $idUsuario AND f.F_status = 1";
        $resultado = mysqli_query($conn, $sql);

        $seguidores = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $seguidores[] = $fila;
        }
        return $seguidores;
    }

    // 🔹 Obtener lista de usuarios que sigo
    public static function siguiendo($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT u.U_id, u.U_nameUser, u.U_name, u.U_lastName
                FROM follow f
                INNER JOIN users u ON f.F_idFollowed = u.U_id
                WHERE f.F_idFollower = $idUsuario AND f.F_status = 1";
        $resultado = mysqli_query($conn, $sql);

        $siguiendo = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $siguiendo[] = $fila;
        }
        return $siguiendo;
    }

    // 🔹 Verificar estado de seguimiento
    // 0 = pendiente, 1 = aceptado, null = no existe
    public static function estado($conn, $idSeguidor, $idSeguido) {
        $idSeguidor = (int)$idSeguidor;
        $idSeguido = (int)$idSeguido;

        $sql = "SELECT F_status FROM follow 
                WHERE F_idFollower = $idSeguidor AND F_idFollowed = $idSeguido
                LIMIT 1";
        $resultado = mysqli_query($conn, $sql);
        if ($fila = mysqli_fetch_assoc($resultado)) {
            return (int)$fila['F_status'];
        }
        return null; // No existe solicitud
    }

    // ====================================================
    // 🔹 Contar solicitudes pendientes
    // ====================================================
    /**
     * Cuenta las solicitudes de seguimiento pendientes (F_status = 0)
     * que ha recibido un usuario.
     */
    public static function contarPendientes($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;

        // Contar dónde este usuario es el SEGUIDO (F_idFollowed)
        // y el estado es PENDIENTE (0)
        $sql = "SELECT COUNT(*) AS total 
                FROM follow 
                WHERE F_idFollowed = $idUsuario AND F_status = 0";
        
        $resultado = mysqli_query($conn, $sql);
        
        if ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
            return (int)$fila['total'];
        }
        
        return 0; // Retorna 0 si hay error o no hay resultados
    }

    // ====================================================
    // 🔹  Obtener la lista de solicitudes pendientes
    // ====================================================
    /**
     * Obtiene una lista de usuarios (con foto) que han enviado una solicitud
     * pendiente (F_status = 0) a $idUsuario.
     */
    public static function obtenerSolicitudesPendientes($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;
        $defaultPath = './Frontend/assets/images/appImages/default.jpg';

        // Hacemos JOIN con 'users' (para el nombre)
        // Hacemos LEFT JOIN con 'images' (para la foto de perfil)
        $sql = "SELECT 
                    u.U_id, 
                    u.U_nameUser, 
                    i.I_ruta AS U_profilePic 
                FROM follow f
                INNER JOIN users u ON f.F_idFollower = u.U_id
                LEFT JOIN images i ON u.U_id = i.I_idUser AND i.I_currentProfile = 1
                WHERE f.F_idFollowed = $idUsuario AND f.F_status = 0
                ORDER BY f.F_followDate DESC";

        $resultado = mysqli_query($conn, $sql);
        $solicitudes = [];

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                
                // Si no hay foto de perfil (I_ruta es NULL), asignamos la default
                if (empty($fila['U_profilePic'])) {
                    $fila['U_profilePic'] = $defaultPath;
                }
                
                $solicitudes[] = $fila;
            }
        }
        return $solicitudes;
    }
}
?>
