<?php
class User {
    // 🔹 Atributos del usuario
    public $id;
    public $username;
    public $name;
    public $lastName;
    public $email;
    public $password;
    public $dateBirth;
    public $registrationDate;
    public $status;
    public $biography;

    // 🔹 Constructor
    public function __construct($id, $username, $name, $lastName, $email, $password, $dateBirth, $registrationDate, $status, $biography) {
        $this->id = $id;
        $this->username = $username;
        $this->name = $name;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->dateBirth = $dateBirth;
        $this->registrationDate = $registrationDate;
        $this->status = $status;
        $this->biography = $biography;
    }

    // 🔹 Setter y Getter
    public function __set($var, $value){
        if(property_exists($this, $var)){
            $this->$var= $value;
        }
    }

    public function __get($var){
        if(property_exists($this, $var)){
            return $this->$var;
        }else{
            return null;
        }
    }

    // ====================================================
    // 🔹 Registrar un nuevo usuario (CORREGIDO: Formato de Fecha y Devolución de ID)
    // ====================================================
    public static function register($conexion, $data) {
        $username = mysqli_real_escape_string($conexion, $data['username']);
        $name = mysqli_real_escape_string($conexion, $data['name']);
        $lastName = mysqli_real_escape_string($conexion, $data['lastName']);
        $email = mysqli_real_escape_string($conexion, $data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // 💡 CORRECCIÓN DE FECHA: Convertir de 'd-m-Y' (Frontend) a 'Y-m-d' (MySQL)
        $dateBirth_frontend = $data['dateBirth'];
        $dateObject = DateTime::createFromFormat('d-m-Y', $dateBirth_frontend);
        $dateBirth = $dateObject ? $dateObject->format('Y-m-d') : null;
        $dateBirth = mysqli_real_escape_string($conexion, $dateBirth); 
        
        $registrationDate = date("Y-m-d H:i:s");
        $status = 1; // activo por defecto
        $biography = '';

        $sql = "INSERT INTO users (U_nameUser, U_name, U_lastName, U_email, U_pass, U_dateBirth, U_registrationDate, U_status, U_biography)
                VALUES ('$username', '$name', '$lastName', '$email', '$password', '$dateBirth', '$registrationDate', '$status', '$biography')";
        
        $result = mysqli_query($conexion, $sql);
        
        // 💡 CORRECCIÓN DE DEVOLUCIÓN: Devolver el ID si el registro fue exitoso
        if ($result) {
            return mysqli_insert_id($conexion);
        } else {
            return false;
        }
    }

    // ====================================================
    // 🔹 Iniciar sesión (CORREGIDO: Lógica de verificación de contraseña y estado)
    // ====================================================
    public static function login($conexion, $email, $password) {
        $email = mysqli_real_escape_string($conexion, $email);
        
        // 1. Buscamos el usuario y verificamos el estado (U_status = 1 para activo)
        $sql = "SELECT * FROM users WHERE U_email = '$email' AND U_status = 1 LIMIT 1";
        $resultado = mysqli_query($conexion, $sql);

        if ($fila = mysqli_fetch_assoc($resultado)) {
            $hash_almacenado = $fila['U_pass']; // Obtenemos el hash

            // 2. Verificamos la contraseña
            if (password_verify($password, $hash_almacenado)) {
                // Login correcto → devolvemos el objeto usuario
                return new User(
                    $fila['U_id'],
                    $fila['U_nameUser'],
                    $fila['U_name'],
                    $fila['U_lastName'],
                    $fila['U_email'],
                    $fila['U_pass'],
                    $fila['U_dateBirth'],
                    $fila['U_registrationDate'],
                    $fila['U_status'],
                    $fila['U_biography']
                );
            }
        }
        return null; // credenciales incorrectas o usuario inactivo
    }

    // ====================================================
    // 🔹 Lógica de Bloqueo por Publicación
    // ====================================================
    // Bloquear usuario si tiene muchas imágenes en revisión
    public static function actualizarEstadoPublicacion($conn, $idUsuario) {
        $idUsuario = (int)$idUsuario;

        // Contar imágenes en revisión
        $sql = "SELECT COUNT(*) AS total 
                FROM imagenes 
                WHERE I_idUser = $idUsuario AND I_revisionStatus = 1";
        $resultado = mysqli_query($conn, $sql);
        $fila = mysqli_fetch_assoc($resultado);

        // Si tiene más de 3 imágenes en revisión, bloquear al usuario (U_status = 1)
        // Si tiene 3 o menos, activarlo (U_status = 0)
        // NOTA: Asumo que 1 es 'Bloqueado' y 0 es 'Activo' según tu lógica.
        // Si 1 es 'Activo' y 0 es 'Bloqueado', invierte la asignación de $nuevoEstado.
        $nuevoEstado = ($fila['total'] >= 3) ? 0 : 1; 

        $sqlUpdate = "UPDATE users SET U_status = $nuevoEstado WHERE U_id = $idUsuario";
        mysqli_query($conn, $sqlUpdate);

        // Devuelve true si el nuevo estado permite publicar (es decir, el estado es Activo)
        return $nuevoEstado === 1; 
    }



    // ====================================================
    // 🔹 Obtener usuario por ID
    // ====================================================
    public static function getById($conexion, $id) {
        $id = (int)$id;
        $sql = "SELECT * FROM users WHERE U_id = $id LIMIT 1";
        $resultado = mysqli_query($conexion, $sql);

        if ($fila = mysqli_fetch_assoc($resultado)) {
            return new User(
                $fila['U_id'],
                $fila['U_nameUser'],
                $fila['U_name'],
                $fila['U_lastName'],
                $fila['U_email'],
                $fila['U_pass'],
                $fila['U_dateBirth'],
                $fila['U_registrationDate'],
                $fila['U_status'],
                $fila['U_biography']
            );
        }
        return null;
    }

    // ====================================================
    // 🔹 Actualizar perfil
    // ====================================================
    public function updateProfile($conexion) {
        $username = mysqli_real_escape_string($conexion, $this->username);
        $name = mysqli_real_escape_string($conexion, $this->name);
        $lastName = mysqli_real_escape_string($conexion, $this->lastName);
        $email = mysqli_real_escape_string($conexion, $this->email);
        $biography = mysqli_real_escape_string($conexion, $this->biography);
        $dateBirth = mysqli_real_escape_string($conexion, $this->dateBirth);

        $sql = "UPDATE users 
                SET U_nameUser='$username', U_name='$name', U_lastName='$lastName', 
                    U_email='$email', U_biography='$biography', U_dateBirth = '$dateBirth'
                WHERE U_id = $this->id";
        return mysqli_query($conexion, $sql);
    }

    // ====================================================
    // 🔹 Cambiar contraseña
    // ====================================================
    public function changePassword($conexion, $newPass) {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET U_pass='$hash' WHERE U_id = $this->id";
        return mysqli_query($conexion, $sql);
    }

    // ====================================================
    // 🔹 Buscar usuarios por nombre o username
    // ====================================================
    public static function search($conexion, $query) {
        $query = mysqli_real_escape_string($conexion, $query);
        $sql = "SELECT * FROM users 
                WHERE U_nameUser LIKE '%$query%' OR U_name LIKE '%$query%' OR U_lastName LIKE '%$query%'";
        $resultado = mysqli_query($conexion, $sql);

        $usuarios = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $usuarios[] = new User(
                $fila['U_id'],
                $fila['U_nameUser'],
                $fila['U_name'],
                $fila['U_lastName'],
                $fila['U_email'],
                $fila['U_pass'],
                $fila['U_dateBirth'],
                $fila['U_registrationDate'],
                $fila['U_status'],
                $fila['U_biography']
            );
        }
        return $usuarios;
    }

    // ====================================================
    // 🔹 Verificar si el usuario está bloqueado para publicar
    // ====================================================
    //NOTA: es una funcion de instancia osea se usa con el objeto creado de USER
    //Verifica si el usuario tiene status 0 (Bloqueado/Inactivo) y no puede publicar.
    //@return bool True si está bloqueado, False si puede publicar.
    public function isBlockedForPublishing() {
        // Devuelve TRUE si el estado es 0 (Bloqueado/Inactivo)
        return $this->status === 0;
    }


    // ====================================================
    // 🔹 Contar la cantidad de seguidores
    // ====================================================
    /**
     * Cuenta el número de seguidores activos (F_status = 1) para un usuario.
     */
    public static function countFollowers($conexion, $idUsuario) {
        $idUsuario = (int)$idUsuario;
        
        // Contar dónde este usuario es el SEGUIDO (F_idFollowed)
        $sql = "SELECT COUNT(*) AS total 
                FROM follow 
                WHERE F_idFollowed = $idUsuario AND F_status = 1";
        
        $resultado = mysqli_query($conexion, $sql);
        
        if ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
            return (int)$fila['total'];
        }
        return 0;
    }

    // ====================================================
    // 🔹 Contar la cantidad de seguidos
    // ====================================================
    /**
     * Cuenta el número de usuarios que sigue activamente (F_status = 1) un usuario.
     */
    public static function countFollowing($conexion, $idUsuario) {
        $idUsuario = (int)$idUsuario;
        
        // Contar dónde este usuario es el SEGUIDOR (F_idFollower)
        $sql = "SELECT COUNT(*) AS total 
                FROM follow 
                WHERE F_idFollower = $idUsuario AND F_status = 1";
        
        $resultado = mysqli_query($conexion, $sql);
        
        if ($resultado && $fila = mysqli_fetch_assoc($resultado)) {
            return (int)$fila['total'];
        }
        return 0;
    }
}
?>