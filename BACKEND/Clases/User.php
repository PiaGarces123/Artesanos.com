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

    // ====================================================
    // 🔹 Registrar un nuevo usuario
    // ====================================================
    public static function register($conexion, $data) {
        $username = mysqli_real_escape_string($conexion, $data['username']);
        $name = mysqli_real_escape_string($conexion, $data['name']);
        $lastName = mysqli_real_escape_string($conexion, $data['lastName']);
        $email = mysqli_real_escape_string($conexion, $data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $dateBirth = mysqli_real_escape_string($conexion, $data['dateBirth']);
        $registrationDate = date("Y-m-d H:i:s");
        $status = 1; // activo por defecto
        $biography = '';

        $sql = "INSERT INTO users (U_nameUser, U_name, U_lastName, U_email, U_pass, U_dateBirth, U_registrationDate, U_status, U_biography)
                VALUES ('$username', '$name', '$lastName', '$email', '$password', '$dateBirth', '$registrationDate', '$status', '$biography')";
        return mysqli_query($conexion, $sql);
    }

    // ====================================================
    // 🔹 Iniciar sesión
    // ====================================================
    public static function login($conexion, $email, $password) {
        $email = mysqli_real_escape_string($conexion, $email);
        $sql = "SELECT * FROM users WHERE U_email = '$email' LIMIT 1";
        $resultado = mysqli_query($conexion, $sql);

        if ($fila = mysqli_fetch_assoc($resultado)) {
            if (password_verify($password, $fila['U_pass'])) {
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
        return null; // credenciales incorrectas
    }

    //Bloquear usuario si tiene muchas imágenes en revisión
    //CONSULTAR CON ESTA FUNCION ANTES DE CREAR UNA IMAGEN
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
        $nuevoEstado = ($fila['total'] >= 3) ? 1 : 0;

        $sqlUpdate = "UPDATE users SET U_status = $nuevoEstado WHERE U_id = $idUsuario";
        mysqli_query($conn, $sqlUpdate);

        return $nuevoEstado === 0; // Devuelve true si puede publicar
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
}
?>
